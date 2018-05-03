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
 * This helper will display a calendar for choosing dates on the new/edit event form
 *
 * @author hammer:)
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_EventFormPickCal extends Zend_View_Helper_Abstract 
{
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;

	/**
	 * @var view helper
	 */
	protected $monthViewHelper;
	
	/**
	 * @var array the days of the week!
	 */
	protected $daysOfWeek = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	
	/**
	 * @param int $month the month to display
	 * @param int $year the year of the month to display
	 * @param array $events the events for the month to be shown
	 * @param array $locations the prioritized locations to show
	 * @param zendView $view the view
	 *
	 * @return string the month rendered as html
	 */
	public function eventFormPickCal($selectedDays, $echoedDays, $startDate = null)
	{
		$this->monthViewHelper = new Scheduler_View_Helper_MonthView();
		
		if(is_null($startDate)){
			$startDate = new DateTime();
		}
		
		$rollingYear = intval($startDate->format("Y"));
		$rollingMonth = intval($startDate->format("m"));
		
		for($i = $rollingMonth; $i < ($rollingMonth+3); $i++){
			
			if($i > 12){
				$i = 1;
				$rollingYear++;
			}
			
			$this->_html .= $this->drawMonth($i, $rollingYear);
		}
		
		return $this->_html;
	}
	
	
	private function drawMonth($month, $year)
	{
		$monthViewHelper = $this->monthViewHelper;
		
		$monthViewHelper->month = $month;
		$monthViewHelper->year = $year;
		
		$today = $monthViewHelper->hasToday();
		$runningDay = $monthViewHelper->getRunningDay();
		$daysInMonth = $monthViewHelper->getDaysInMonth($month, $year);
		$dayCount = 0;
		$weekCount = 1;
		$daysInThisWeek = $runningDay;
		$dayOfWeek = ($daysInThisWeek+1);
		
		// begin drawing the calendar - start with days from the previous month
		$calendar .= "<div class='month'>";
		$calendar .= $monthViewHelper->displayNewWeek($weekCount);
		$calendar .= $monthViewHelper->printDaysFromPreviousMonth($runningDay);
		
		// now step through every day in this month
		for($listDay = 1; $listDay <= $daysInMonth; $listDay++){
			
			// set up some classes/ids/attributes for the day-block
			$todayClass = ($today == $listDay) ? "today" : "";
			$timestamp = $year . "-" . $month . "-" . $listDay;
			$dayBlockId = 'day-' . $timestamp;
			$classes = 'day-block ' . $todayClass;
			
			// create the day-block and day-num
			$calendar .= '<div id="' . $dayBlockId . '" class="' . $classes . '" data-timestamp="' . $timestamp . '">';
			$calendar .= ($todayClass == "") ? "" : "<img class='today-img' src='/images/today.png'>";
			$calendar .= $listDay;
			$calendar .= '</div>';
			
			// clear the float and start a new week
			if($runningDay == 6){
				
				$weekCount++;
				$calendar .= '<div class="clear"></div></div><div class="clear"></div>';
				if(($dayCount+1) != $daysInMonth){
					$calendar .= $monthViewHelper->displayNewWeek($weekCount);
				}
				
				$runningDay = -1;
				$daysInThisWeek = 0;
				$dayOfWeek = 0;
			}
			
			// increment everything
			$dayOfWeek++;
			$daysInThisWeek++;
			$runningDay++;
			$dayCount++;
		}
		
		// finish the month with the rest of the days of the week
		$calendar .= $monthViewHelper->printDaysFromNextMonth($daysInThisWeek);
		
		// close the wrapper divs and clear floats!
		$calendar .= '</div><div class="clear"></div>';
		$calendar .= "</div>";
		
		
		return $calendar;
	}
	
}
