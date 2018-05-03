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
 * This helper will display a calendar (in day view) with events
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_DayView extends Zend_View_Helper_Abstract 
{
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	protected $date;
	protected $events;

	/**
	 * @param string $type the view type - either month/week/day
	 * @param dateTime $date the date used to display the appropriate month/week/day
	 * @param array $filters various filters used to filter the shifts
	 *
	 * @return string the calendar rendered as html
	 */
	public function dayView($date, $events, $view)
	{
		$this->date = $date;
		$this->events = $events;
		return $this->drawDay($view);
	}
	
	private function drawDay($view)
	{
		// day view is easy!
		$calViewHelper = new Scheduler_View_Helper_CalendarView();
		$calViewHelper->setCurrentUserData();
		
		$today = new DateTime();
		$todayClass = ($this->date->format("Y-M-j") == $today->format("Y-M-j")) ? "today" : "";
		$totalHtml = $calViewHelper->getShiftTotals($this->events[$this->date->format("j")]['total_counts']);
		
		$day = '<div class="day-view">';
		$day .= 	'<h3 id="day-header-name" class="day-header-day section-header ' . $todayClass . '">';
		$day .= 		($todayClass == "") ? "" : "<img class='today-img' src='/images/today.png'>";
		$day .= 		'<div class="date-description">' . $this->date->format("l") . '</div>';
		$day .=			$totalHtml;
		$day .= 		'<div class="clear"></div>';
		$day .= 	'</h3>';
		
		$day .= '<div class="clear"></div>';
		
		if(count($this->events[$this->date->format("j")]['events']) > 0){
			$day .= $view->partial('day-events.phtml', array("events" => $this->events[$this->date->format("j")]['events'],
															 "current_user_data" => $calViewHelper->current_user_data,
															 "pdf" => $view->pdf));
		}
		else {
			$day .= $calViewHelper->getNoShiftsMsg();
		}
		
		//$day .= "";
		
		$day .= "</div>";
		
		return $day;
	}
	
}
