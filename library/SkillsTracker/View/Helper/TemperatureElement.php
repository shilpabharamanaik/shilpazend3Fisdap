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
 * This file contains a view helper to render a temperature prompt
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_temperatureElement extends Zend_View_Helper_FormElement
{
    /**
     * @var string the HTML to be rendered
     */
    protected $html = "";
    
    /**
     * The function to render the html
     *
     * @return string the HTML rendering the pulse element
     */
    public function temperatureElement($name, $value = null, $attribs = null)
    {
        //get data from values
        $temperature = isset($value['temperature']) ? $value['temperature'] : "";
        $units = isset($value['units']) ? $value['units'] : "F";
        
        //get options for dropdown
        $options = \Fisdap\Entity\VitalPulseQuality::getFormOptions();
    
        $this->html .= $this->view->formText($name . "[temperature]", $temperature, array('temperature' => $attribs['temperature'])) . " ";
        $this->html .= $this->view->formSelect($name . "[units]", $units, array(), array('F' => 'F', 'C' => 'C'));
        
        return $this->html;
    }
}
