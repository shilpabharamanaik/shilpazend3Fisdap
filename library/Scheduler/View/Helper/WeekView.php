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
 * This helper will display a calendar (in week view) with events
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_WeekView extends Zend_View_Helper_Abstract
{
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;

	protected $date;

	/**
	 * @var array the events for the month (organized by day->base->event)
	 */
	protected $events;

	/**
	 * @var array a collection of events that do not end on the day they began (will be added to as we step through events)
	 */
	protected $rollOverEvents;

	/**
	 * @var array the top 12 locations that are the 'priority'
	 */
	protected $locations;

	/**
	 * @var array the days of the week!
	 */
	protected $daysOfWeek = array('', 'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');

	/**
	 * @param string $type the view type - either month/week/day
	 * @param dateTime $date the date used to display the appropriate month/week/day
	 * @param array $filters various filters used to filter the shifts
	 *
	 * @return string the calednar rendered as html
	 */
	public function weekView($date, $events, $locations, $view)
	{
		$this->date = $date;
		$this->events = $events;
		$this->locations = $locations;
		return $this->drawWeek($view);
	}

	private function drawWeek($view)
	{
		// this will be helpful (we can use some of this guys functions for printing the days)
		$monthViewHelper = new Scheduler_View_Helper_MonthView();
		$calViewHelper = new Scheduler_View_Helper_CalendarView();
		$calViewHelper->setCurrentUserData();

		$monthViewHelper->events = $this->events;
		$monthViewHelper->locations = $this->locations;
		$monthViewHelper->locationCount = count($this->locations);
		$monthViewHelper->month = $this->date->format("m");
		$monthViewHelper->year = $this->date->format("Y");

		$actualDay = intval($this->date->format("j"));
		$dayOfWeek = 1;
		$dateMonth = $this->date->format("m");
		$dateYear = $this->date->format("Y");
		$daysInMonth = $monthViewHelper->getDaysInMonth($dateMonth, $dateYear);
		$today = $this->hasToday($dateMonth, $dateYear);

		$calendar = "<div class='calendar-blocks'>";
		$calendar .= '<div class="week-row">';

		for($listDay = ($this->date->format("w")+1); $listDay < 8; $listDay++){

			$actualDay = ($actualDay > $daysInMonth) ? 1 : $actualDay;
			$todayClass = ($today == $actualDay) ? "today" : "";
			$myShiftClass = ($this->events[$actualDay]['has_my_shift_today']) ? "day-has-my-shift" : "";
			
			$dayBlockHeight = ($monthViewHelper->locationCount > 12) ? intval(($monthViewHelper->locationCount)*7.3) : 92;
			
			$calendar .= '<div class="day-block ' . $todayClass . '" style="height:' . $dayBlockHeight . 'px;" data-day="' . $this->daysOfWeek[$listDay] . '">';
			$calendar .= ($todayClass == "") ? "" : "<img class='today-img' src='/images/today.png'>";
			$calendar .= ($myShiftClass == "") ? "" : "<img class='day-has-my-shift-img' src='/images/icons/my-shift.png'>";
			$calendar .= '<div class="day-num">'. $actualDay . '</div>';
			$calendar .= $monthViewHelper->printDaysEvents($actualDay);
			$calendar .= '</div>';

			$actualDay++;
		}

		$calendar .= '</div>';
		$calendar .= $this->clearFloat();
		$calendar .= '</div></div></div>';

		// now print out the events below
		$actualDay = intval($this->date->format("j"));

		for($listDay = ($this->date->format("w")+1); $listDay < 8; $listDay++){
			$actualDay = ($actualDay > $daysInMonth) ? 1 : $actualDay;
			$calendar .= $this->clearFloat();
			$calendar .= '<h3 class="day-header section-header" id="header-' . $this->daysOfWeek[$listDay] . '">' . $this->daysOfWeek[$listDay];
			$calendar .= 	$calViewHelper->getShiftTotals($this->events[$actualDay]['total_counts']);
			$calendar .= '</h3>';

			if(count($this->events[$actualDay]['events']) > 0) {
				$calendar .= $view->partial('week-events.phtml', array("events" => $this->events[$actualDay]['events'],
																	   "dayNum" => $actualDay,
																	   "current_user_data" => $calViewHelper->current_user_data,
																	   "pdf" => $view->pdf));
			}
			else {
				$calendar .= $calViewHelper->getNoShiftsMsg();
			}
			$actualDay++;
		}

		$calendar .= "</div>";

		return $calendar;
	}

	private function hasToday($dateMonth, $dateYear)
	{
		$today = new DateTime();
		if(($today->format("m") == $dateMonth) && ($today->format("Y") == $dateYear)){
			$today = intval($today->format("j"));
		}
		else {
			$today = false;
		}

		return $today;
	}

	private function clearFloat()
	{
		return "<div class='clear'></div>";
	}

}
