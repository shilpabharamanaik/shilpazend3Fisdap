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
 * This file contains a view helper to render a graduation the date range prompt
 */

/**
 * @package Fisdap
 */
class Fisdap_View_Helper_DateRangeElement extends Zend_View_Helper_FormElement
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
    public function dateRangeElement($name, $value = null, $attribs = null)
    {
		// add our css/js
		$this->view->headScript()->appendFile("/js/library/Fisdap/View/Helper/date-range.js");
		//$this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/navbar-menu.css");
		
		// figure out the default values
		$defaultStartDate = $attribs['defaultStart'] ? date_create($attribs['defaultStart'])->format("m/d/Y") : "";
		$defaultEndDate = $attribs['defaultEnd'] ? date_create($attribs['defaultEnd'])->format("m/d/Y") : "";
		
        // set values
        $startDate = isset($value['startDate']) ? $value['startDate'] : $defaultStartDate;
        $endDate = isset($value['endDate']) ? $value['endDate'] : $defaultEndDate;
        
		$this->html = "
			<div id='".$name."-date-range' class='input-section'>
			    <div id='".$name."-date_range-element'>
			        <label for='{$name}-startDate'>From: </label>
					<input name='".$name."[startDate]' type='text' id='".$name."-startDate' value='$startDate' class='selectDate selectStartDate fancy-input'>
					<label for='{$name}-endDate'>Through: </label>
					<input name='".$name."[endDate]' type='text' id='".$name."-endDate' value='$endDate' class='selectDate selectEndDate fancy-input'>
			    </div>
			</div>";
        
        return $this->html;
    }

    /** 
     * Display the dateRangeElement in a report
     * @param array $options Any options passed from the report that is using it
     * @param array $config  
     */
    public function DateRangeElementSummary($name, $options = array(), $config = array())
    {
    	$summary = array();

    	$summary["Date range"] = $this->formatDateRange($config[$name]['startDate'], $config[$name]['endDate']);

    	return $summary;
    }

    /**
	 * processes the chosen results from the date pickers to return a date range summary
	 */
	public function formatDateRange($start_date, $end_date) {
		// no dates selected means give them all dates
		if (empty($start_date) && empty($end_date)) {
			return "All dates";
		}
		
		// no end date selected means give them all dates from start date
		if (empty($end_date)) {
			return "From ".date('F j, Y', strtotime($start_date));
		}
		
		// no start date selected means give them all dates through end date
		if (empty($start_date)) {
			return "Through ".date('F j, Y', strtotime($end_date));
		}
		
		// both dates selected means give them the date range
		return date('F j, Y', strtotime($start_date)) . " through " . date('F j, Y', strtotime($end_date));

	}

}