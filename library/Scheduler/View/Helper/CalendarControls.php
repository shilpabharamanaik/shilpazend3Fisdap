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
 * This helper will display a calendar's controls (next/prev/today/title/view type)
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_CalendarControls extends Zend_View_Helper_Abstract 
{
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	/**
	 * @param string $type the view type - either month/week/day
	 * @param dateTime $date the date used to display the appropriate month/week/day
	 * @param array $filters various filters used to filter the shifts
	 *
	 * @return string the calednar rendered as html
	 */
	public function calendarControls($type, $date, $previous_month_view_type)
	{
		$this->_html .= $this->drawControls($type, $date, $previous_month_view_type);
		return $this->_html;
	}
	
	private function drawControls($type, $date, $previous_month_view_type)
	{
		$this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/calendar-controls.css");
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/calendar-modal-triggers.js");
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/calendar-controls.js");
		$viewTypes = array("Month", "Week", "Day", "List");
		$details = array(array("title" => $this->getTitle($type, $date), "type" => $type, "viewTypes" => $viewTypes, "previous_month_view_type" => $previous_month_view_type));
		
		return $this->view->partialLoop('calendarControls.phtml', $details);
	}
	
	public function getTitle($type, $date)
	{
		if($type == "month" || $type == "month-details"){
			$title = $date->format("F") . " " . $date->format("Y");
		}
		else if ($type == "week"){
			$endDate = strtotime("+6 day", strtotime($date->format("m/j/Y")));
			$endDate = new DateTime(date("Y-m-d", $endDate));
			$title = $date->format("M j, Y") . " - " . $endDate->format("M j, Y");
		}
		else if ($type == "day") {
			$title = $date->format("M j, Y");
		}
		else {
			// list view
			$title = "List";
		}
		
		return $title;
	}
	
	public function getFirstDayInWeek($date)
	{

	}
}
