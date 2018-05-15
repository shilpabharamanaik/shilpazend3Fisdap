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
 * This file contains a view helper to render a subject prompt
 */

/**
 * @package SkillsTracker
 */
class SkillsTracker_View_Helper_SubjectElement extends Zend_View_Helper_FormElement
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
    public function subjectElement($name, $value = null, $attribs = null)
    {
        $subject = \Fisdap\EntityUtils::getEntity('Subject', $value);
        
        $subjName = $subject->name;
        $subjType = $subject->type;
        
        $nameOptions = \Fisdap\Entity\Subject::getFormOptions();
        $typeOptions = array(
            'live' => 'live',
            'dead' => 'dead',
            'sim' => 'sim',
            'other' => 'other'
        );
   

        $this->html .= "<div style='float:left; margin-top:.5em;'>" .$this->view->formSelect($name . "[name]", $subjName, array("class" => "subject-name"), $nameOptions). "</div>";
        $this->html .=  $this->view->formRadio($name . "[type]", $subjType, array("class" => "subject-type"), $typeOptions);
        
        return $this->html;
    }
}
