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
 * This helper will display the add shift buttons
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_AddShiftWidget extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;

    public function addShiftWidget()
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();

        $this->_html .= '<div id="add-shift-container">';

        //If the user is an instructor, we check to make sure they have permission to add each type of shift before displaying the requisite button.
        //We're drawing the permission int values from the fisdap2_permissions table.
        if ($user->isInstructor()) {
            if ($user->hasPermission(14)) {
                $this->_html .= '<a href="#" id="add-lab-shift" title="add a lab shift">+ Lab Practice</a>';
            }
            if ($user->hasPermission(10)) {
                $this->_html .= '<a href="#" id="add-clinical-shift" title="add a clinical shift">+ Clinical Shift</a>';
            }
            if ($user->hasPermission(9)) {
                $this->_html .= '<a href="#" id="add-field-shift" title="add a field shift">+ Field Shift</a>';
            }
        } else {
            if ($user->getCurrentProgram()->can_students_create_lab) {
                $this->_html .= '<a href="#" id="add-lab-shift" title="add a lab shift">+ Lab Practice</a>';
            }
            if ($user->getCurrentProgram()->can_students_create_clinical) {
                $this->_html .= '<a href="#" id="add-clinical-shift" title="add a clinical shift">+ Clinical Shift</a>';
            }
            if ($user->getCurrentProgram()->can_students_create_field) {
                $this->_html .= '<a href="#" id="add-field-shift" title="add a field shift">+ Field Shift</a>';
            }
        }

        $this->_html .= "</div>";

        return $this->_html;
    }
}
