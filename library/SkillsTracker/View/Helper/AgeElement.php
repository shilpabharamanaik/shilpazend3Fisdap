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
 * This file contains a view helper to render an age prompt
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_AgeElement extends Zend_View_Helper_FormElement
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
    public function ageElement($name, $value = null, $attribs = null)
    {
        //get data from values
        $months = isset($value['months']) ? $value['months'] : "";
        $years = isset($value['years']) ? $value['years'] : "";
    
		
		$this->html .= $this->view->formText($name . "[years]", $years, array('size' => $attribs['size'])) . " years ";
		$this->html .= $this->view->formText($name . "[months]", $months, array('size' => $attribs['size'])) . " months";
        
        return $this->html;
    }
}