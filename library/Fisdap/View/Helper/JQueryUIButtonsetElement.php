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
class Fisdap_View_Helper_JQueryUIButtonsetElement extends Zend_View_Helper_FormElement
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
    public function jQueryUIButtonsetElement($name, $value = null, $attribs = null, $options = null)
    {
        $this->view->jQuery()->addOnLoad("$('#button-set-$name').buttonset(); $('#button-set-$name').parents('.form-prompt').css('min-height', '2.6em'); ");

        //get data from values
        $default = isset($value) ? $value : null;
        $theme = $attribs['ui-theme'];
        $size = $attribs['ui-size'];
        $radioAttribs = $attribs['radio'];
        $this->html = "<span id='button-set-$name' class='$theme $size'>";
        $this->html .= $this->view->fisdapFormRadio($name, $default, $radioAttribs, $options, "");
        $this->html .= "</span>";



        return $this->html;
    }
}
