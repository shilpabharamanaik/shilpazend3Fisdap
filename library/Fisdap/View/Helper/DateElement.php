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
 * This file contains a view helper to render a date prompt
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_DateElement extends Zend_View_Helper_FormElement
{
    /**
     * @var string the HTML to be rendered
     */
    protected $html = "";
    
    /**
     * The function to render the html
     *
     * @return string the HTML rendering the graduation date element
     */
    public function dateElement($name, $value = null, $attribs = null)
    {
        $months = Util_FisdapDate::get_month_prompt_names();
        $years = Util_FisdapDate::get_year_prompt_names();
        $days = Util_FisdapDate::get_day_prompt_names();
        
        //get data from values
        $day = isset($value['day']) ? $value['day'] : 0;
        $month = isset($value['month']) ? $value['month'] : 0;
        $year = isset($value['year']) ? $value['year'] : 0;
        
        $this->html .= $this->view->formSelect($name . "[day]", $day, array(), $days);
        $this->html .= $this->view->formSelect($name . "[month]", $month, array(), $months);
        $this->html .= $this->view->formSelect($name . "[year]", $year, array(), $years);
        
        return $this->html;
    }
}
