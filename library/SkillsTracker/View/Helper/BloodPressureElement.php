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
 * This file contains a view helper to render a blood pressure prompt
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_BloodPressureElement extends Zend_View_Helper_FormElement
{
    /**
     * @var string the HTML to be rendered
     */
    protected $html = "";
    
    /**
     * The function to render the html
     *
     * @return string the HTML rendering the age date element
     */
    public function bloodPressureElement($name, $value = null, $attribs = null)
    {
        //get data from values
        $systolic = isset($value['systolic']) ? $value['systolic'] : "";
        $diastolic = isset($value['diastolic']) ? $value['diastolic'] : "";
    
        $this->html .= $this->view->formText($name . "[systolic]", $systolic, array('size' => $attribs['size'])) . "/";
        $this->html .= $this->view->formText($name . "[diastolic]", $diastolic, array('size' => $attribs['size']));
        
        return $this->html;
    }
}
