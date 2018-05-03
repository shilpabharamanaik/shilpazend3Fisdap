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
 * This file contains a view helper to render a pulse prompt
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_pulseElement extends Zend_View_Helper_FormElement
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
    public function pulseElement($name, $value = null, $attribs = null)
    {
        //get data from values
        $rate = isset($value['rate']) ? $value['rate'] : "";
        $quality = isset($value['quality']) ? $value['quality'] : 1;
		
		//get options for dropdown
		$options = \Fisdap\Entity\VitalPulseQuality::getFormOptions();
    
		$this->html .= $this->view->formText($name . "[rate]", $rate, array('size' => $attribs['size'])) . " ";
		$this->html .= $this->view->formSelect($name . "[quality]", $quality, array(), $options);
        
        return $this->html;
    }
}