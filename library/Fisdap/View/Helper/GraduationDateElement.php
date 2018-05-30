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
 * This file contains a view helper to render a graduation date prompt
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_GraduationDateElement extends Zend_View_Helper_FormElement
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
    public function graduationDateElement($name, $value = null, $attribs = null)
    {
        //get data from values
        $month = isset($value['month']) ? $value['month'] : 0;
        $year = isset($value['year']) ? $value['year'] : 0;
        
        //get month/year options
        $months = isset($attribs['monthAttribs']['formOptions']) ? $attribs['monthAttribs']['formOptions'] : $this->getMonthPrompts();
        $years = isset($attribs['yearAttribs']['formOptions']) ? $attribs['yearAttribs']['formOptions'] : $this->getYearPrompts();
        
        
        //Use zend view helpers to render select boxes
        $this->html = $this->view->formSelect($name . "[month]", $month, array(), $months);
        $this->html .= $this->view->formSelect($name . "[year]", $year, array(), $years);
        
        return $this->html;
    }
    
    private function getYearPrompts()
    {
        if ($user = \Fisdap\Entity\User::getLoggedInUser()) {
            return $user->getCurrentProgram()->get_possible_graduation_years();
        } else {
            return Util_FisdapDate::get_year_prompt_names();
        }
    }
    
    private function getMonthPrompts()
    {
        return Util_FisdapDate::get_month_prompt_names();
    }
}
