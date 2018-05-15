<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This helper will display a practice definition
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_PracticeDefinitionRow extends Zend_View_Helper_Abstract
{
    
    /**
     * @var string the html to return
     */
    protected $returnContent;
    
    public function practiceDefinitionRow($category, $def, $formElements = null)
    {
        if (strlen(trim($def->name)) == 0) {
            $def_name = "New Practice Item";
        } else {
            $def_name = $def->name;
        }
        $activeCheckboxText = 'category' . $category->id . '_definition' . $def->id . '_active';
        $activeCheckbox = ($formElements['active']) ? $formElements['active'] : '<input type="checkbox" class="slider-checkbox" name="' . $activeCheckboxText . '" id="' . $activeCheckboxText . '" value="1" checked="checked">';
        
        $defNameText = 'category' . $category->id . '_definition' . $def->id . '_name';
        $defName = ($formElements['defName']) ? $formElements['defName'] : '<input type="text" name="' . $defNameText . '" id="' . $defNameText . '" value="' . $def_name . '" class="def-name-input">';
        
        $peerText = 'category' . $category->id . '_definition' . $def->id . '_peer';
        $peerGoal = ($formElements['peerGoal']) ? $formElements['peerGoal'] : '<input type="text" name="' . $peerText . '" id="' . $peerText . '" value="' . $def->peer_goal . '" maxlength="5">';
                
        $instructorText = 'category' . $category->id . '_definition' . $def->id . '_instructor';
        $instructorGoal = ($formElements['instructorGoal']) ? $formElements['instructorGoal'] : '<input type="text" name="' . $instructorText . '" id="' . $instructorText . '" value="' . $def->instructor_goal . '" maxlength="5">';
                
        $eurekaGoalText = 'category' . $category->id . '_definition' . $def->id . '_eureka_goal';
        $eurekaGoal = ($formElements['eurekaGoal']) ? $formElements['eurekaGoal'] : '<input type="text" name="' . $eurekaGoalText . '" id="' . $eurekaGoalText . '" value="' . $def->eureka_goal . '" maxlength="5">';

        $eurekaWindowText = 'category' . $category->id . '_definition' . $def->id . '_eureka_window';
        $eurekaWindow = ($formElements['eurekaWindow']) ? $formElements['eurekaWindow'] : '<input type="text" name="' . $eurekaWindowText . '" id="' . $eurekaWindowText . '" value="' . $def->eureka_window . '" maxlength="5">';

        $evalClass = ($def->skillsheet) ? "" : "no-eval";
        $evalIcon = ($def->skillsheet) ? "_check" : "";
        
        if ($formElements['skillSelect']) {
            $skillSelect = $formElements['skillSelect'];
        } else {
            $skills = \Fisdap\EntityUtils::getRepository('PracticeSkill')->getAllFormOptions(true);
            $skills['Airway']['0airway_management'] = "Airway Management";
            ksort($skills);
            ksort($skills['Airway']);
            $skillSelect = new Zend_Form_Element_Select('category' . $category->id . '_definition' . $def->id . '_practice_skills');
            $skillSelect->setMultiOptions($skills)
                        ->setAttribs(array("class" => "chzn-select",
                                           "data-placeholder" => "Practice Skills",
                                           "style" => "width:300px",
                                           "multiple" => "multiple",
                                           "tabindex" => count($skills)));
        }
                    
        $returnContent = '<tr>';
        $returnContent .=  "<td class='active-col'>" . $activeCheckbox . "</td>";
        $returnContent .= 	"<td class='def-name-col'>";
        $returnContent .=	   '<div class="def-name-wrapper">';
        $returnContent .=			'<a class="defintion-name" href="#">' . $def_name . '</a>';
        $returnContent .=			'<div class="hidden-name-input">' . $defName . '</div>';
        $returnContent .=		'</div>';
        $returnContent .=	"</td>";
                                
        $returnContent .=	"<td class='skillsheet-col " . $evalClass . "'>";
        $returnContent .=		"<a href='#' class='attach-skillsheet-trigger' id='" . $def->id . "-skillsheet'>";
        $returnContent .=			"<span class='hidden-def-name'>" . $def_name . "</span>";
        $returnContent .=			"<span class='hidden-skillsheet-id'>" . $def->skillsheet->id . "</span>";
        $returnContent .=			"<img src='/images/icons/eval_icon" . $evalIcon . ".png'>";
        $returnContent .=		"</a>";
        $returnContent .=	"</td>";
                                
        $returnContent .=	"<td class='skill-col'>" . $skillSelect . "<img src='/images/icons/airway_management.png' class='airway_management_icon' id='airway_management_icon_" . $def->id . "'></td>";
        $returnContent .=	"<td class='peer-col'>" . $peerGoal . "</td>";
        $returnContent .=	"<td class='instructor-col'>" . $instructorGoal . "</td>";

        $returnContent .=	"<td class='eureka-col'>" . $eurekaGoal . ' / ' . $eurekaWindow . "</td>";
        $returnContent .= "</tr>";
        
        return $returnContent;
    }
}
