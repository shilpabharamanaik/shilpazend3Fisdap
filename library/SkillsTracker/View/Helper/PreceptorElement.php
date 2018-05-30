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
class SkillsTracker_View_Helper_PreceptorElement extends Zend_View_Helper_FormElement
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
    public function preceptorElement($name, $value = null, $attribs = null)
    {
        $this->html .= $this->view->formSelect($name, $value, array(), $attribs['options']);
        $this->html .= '<a href="#" id="new-preceptor" style="width:2.5em; margin-left: .5em;">Add a new preceptor</a>';
        
        return $this->html;
    }
}
