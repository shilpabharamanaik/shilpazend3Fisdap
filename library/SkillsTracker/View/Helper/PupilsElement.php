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
 * This file contains a view helper to render a pupils prompt
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_PupilsElement extends Zend_View_Helper_FormElement
{
    /**
     * @var string the HTML to be rendered
     */
    protected $html = "";
    
    /**
     * The function to render the html
     *
     * @return string the HTML rendering the pupils element
     */
    public function pupilsElement($name, $value = null, $attribs = null)
    {
        //get data from values
        $equal = isset($value['equal']) ? $value['equal'] : "-1";
        $round = isset($value['round']) ? $value['round'] : "-1";
        $reactive = isset($value['reactive']) ? $value['reactive'] : "-1";
		
		$options = array(-1 => 'N\A', 0 => 'No', 1 => 'Yes');
    
		
		$this->html .= "Equal? " . $this->view->formRadio($name . "[equal]", $equal, array(), $options, " ") . "<br>";
		$this->html .= "Round? " . $this->view->formRadio($name . "[round]", $round, array(), $options, " ") . "<br>";
		$this->html .= "Reactive to Light? " . $this->view->formRadio($name . "[reactive]", $reactive, array(), $options, " ") . "<br>";
        
        return $this->html;
    }
}