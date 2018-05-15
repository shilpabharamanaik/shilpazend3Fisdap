<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted witdout prior autdorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This view helper will render the lab practice widget
 */
use Fisdap\Entity\User;

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_PracticeSkillTable extends Zend_View_Helper_Abstract
{
    /**
     * @var string tde html to be rendered
     */
    protected $_html;
    
    public function practiceSkillTable($shift, $includeModalDialog = false, $removable = true, $defaultDef = null)
    {
        $this->view->headScript()->appendFile('/js/library/SkillsTracker/View/Helper/lab-practice-widget.js');
        $this->view->headLink()->appendStylesheet('/css/library/SkillsTracker/View/Helper/lab-practice-widget.css');
        $this->view->headLink()->appendStylesheet('/css/jquery.menuButton.css');
        $this->view->headScript()->appendFile('/js/jquery.menuButton.js');
        
        $this->session = new \Zend_Session_Namespace("ShiftsController");

        // make for sure we even have a shift
        if (!$shift) {
            return;
        }

        $type = ucfirst($shift->type);
        
        //Grab all lab practice items for this shift
        $unconfirmedPracticeItemCount = 0;
        $labPracticePartials = array();
        foreach ($shift->practice_items as $item) {
            $labPracticePartials[] = array('item' => $item);

            //Keep a running count of all unconfirmed practice items signed off by instructor
            if (!$item->confirmed && $item->evaluator_type->id == 1) {
                $unconfirmedPracticeItemCount++;
            }
        }

        //Get the count of practice items
        $practiceItemCount = count($labPracticePartials);

        $this->_html = "<h3 class='practice-header'>" . $shift->student->user->getName() . "'s $type Practice Items</h3>";
        
        if ($removable) {
            $this->_html .= "<a href='#' class='remove-practice-widget' data-shiftid='" . $shift->id . "'><img class='remove-practice-widget-img' src='/images/icons/delete.png'> Remove</a>";
        }
        
        $this->_html .= "<table class='fisdap-table my-shift-table lab-practice-widget' id='lab_practice_items_" . $shift->id . "'>";
        $this->_html .= "<thead class='" . $shift->type . "'><tr>"
                      . "<th class='name-col'>Name</th>"
                      . "<th class='time-col'>Time</th>"
                      . "<th class='success-col'>Pass/Fail</th>"
                      . "<th class='ptype-col'>Patient type</th>"
                      . "<th class='evaluator-col'>Evaluator</th>"
                      . "<th class='delete-col'>";
        $this->_html .= "</th>"
                      . "</tr></thead>";
        $this->_html .= "<tbody>";

        if ($practiceItemCount > 0) {
            $this->_html .= $this->view->partialLoop("practiceItemRow.phtml", $labPracticePartials);
        } else {
            $this->_html .= "<tr><td colspan='6'>You have not entered any lab practice yet. Click the button below to begin.</th></tr>";
        }
        
        $this->_html .= "</tbody>";
        $definitions = \Fisdap\Entity\PracticeDefinition::getFormOptions($shift->student->getCertification()->id, $shift->student->program->id, true);
        $definitions[0] = "$type practice item";
        ksort($definitions);
        
        $sortedDefs = $definitions;
        
        // it's gunna get weird: go get the lab practice definitions that have airway management credit
        // we'll need to add an additional label: to avoid using a bunch o' entities, do this 1 query and compare IDs
        $am_repo = \Fisdap\EntityUtils::getRepository('AirwayManagement');
        $defs_with_airway_management = $am_repo->getPracticeDefinitionsByProgram($shift->student->program->id, $shift->student->getCertification()->id);
        
        // empty it out
        $definitions = array();
        
        
        foreach ($sortedDefs as $categoryId => $defs) {
            $cat = \Fisdap\EntityUtils::getEntity("PracticeCategory", $categoryId);
            if (!$definitions[$cat->name]) {
                $definitions[$cat->name] = array();
            }
            
            if ($defs_with_airway_management) {
                // step through and add and icon if the definition has airway management attached
                //If $defs is in array, we know it's populated with actual practice items, if not, it's our "$type practice item" default value
                if (is_array($defs)) {
                    foreach ($defs as $def_id => $def_display) {
                        if (in_array($def_id, $defs_with_airway_management)) {
                            $definitions[$cat->name][$def_id] = "<img src='/images/icons/airway_management.png' class='airway_management_icon_pro_list'>" . $def_display;
                        } else {
                            $definitions[$cat->name][$def_id] = $def_display;
                        }
                    }
                } else {
                    $definitions[$categoryId] = $defs;
                }
            } else {
                //If $defs is in array, we know it's populated with actual practice items, if not, it's our "$type practice item" default value
                if (is_array($defs)) {
                    $definitions[$cat->name] = $defs;
                } else {
                    $definitions[$categoryId] = $defs;
                }
            }
        }
        
        if (!$defaultDef) {
            if ($this->session->defaultDefinitions[$shift->student->id]) {
                $defaultDef = $this->session->defaultDefinitions[$shift->student->id];
            } else {
                $defaultDef = 0;
            }
        }

        $this->_html .= "<tfoot><tr><td colspan='6'>" . $this->view->formSelect("newPracticeItem_" . $shift->id, $defaultDef, array("class" => "new-practice-item", "data-shiftid" => $shift->id, "data-shifttype" => $shift->type), $definitions);

        if (User::getLoggedInUser()->isInstructor()) {
            $this->_html .= "<a id='confirm-multiple-items' href='/skills-tracker/shifts/confirm-practice-items'>Confirm multiple items</a>";
        }

        $this->_html .= "</td></tr></tfoot>";
        
        //<a href='#' class='add-lab-practice-item'>Add Lab Practice</a></td></tr></tfoot>";
        $this->_html .= "</table>";
        
        $this->_html .= $this->view->formHidden("practiceWidgetShiftId_" . $shift->id, $shift->id);
        
        if ($includeModalDialog) {
            $this->view->modalDialogs .= new SkillsTracker_Form_PracticeModal();
        }
        
        return $this->_html;
    }
}
