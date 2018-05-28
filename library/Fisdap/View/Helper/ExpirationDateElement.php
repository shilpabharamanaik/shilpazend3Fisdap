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
 * This file contains a view helper to render an expiration date prompt
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_ExpirationDateElement extends Zend_View_Helper_FormElement
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
    public function expirationDateElement($name, $value = null, $attribs = null)
    {
        $years = array("-1" => "Year");
		for ($i = date('Y'); $i <= date('Y')+10; $i++) {
			$years[$i] = $i;
		}
		
        $months = Util_FisdapDate::get_month_prompt_names();
        
        //get data from values
        $month = isset($value['month']) ? $value['month'] : 0;
        $year = isset($value['year']) ? $value['year'] : 0;
        
        $this->html = $this->view->formSelect($name . "[month]", $month, array(), $months);
        $this->html .= $this->view->formSelect($name . "[year]", $year, array(), $years);
        
        return $this->html;
    }
}