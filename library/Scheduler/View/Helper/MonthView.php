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
 * This helper will display a calendar (in month view) with events
 *
 * @author hammer:)
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_MonthView extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    public $_html;

    /**
     * @var int the month to be displayed
     */
    public $month;

    /**
     * @var int the year of the month to be displayed
     */
    public $year;

    /**
     * @var array the events for the month (organized by day->base->event)
     */
    public $events;

    /**
     * @var array a collection of events that do not end on the day they began (will be added to as we step through events)
     */
    public $rollOverEvents;

    /**
     * @var array the top 12 locations that are the 'priority'
     */
    public $locations;

    /**
     * @var int the number of prioritized locations
     */
    public $locationCount;

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
    public function monthView($month, $year, $events, $locations, $view)
    {
        $this->month = $month;
        $this->year = $year;
        $this->events = $events;
        $this->locations = $locations;
        $this->rollOverEvents = array();
        return $this->drawMonth($view);
    }

    /**
     * Draws the month with events included and displayed through shift bars/zoom-view/event-details
     * @param $view the zend view (used so that we can do a partialLoop)
     * @return string the display rendered as html
     */
    private function drawMonth($view)
    {

        // set up some variables to start
        $calViewHelper = new Scheduler_View_Helper_CalendarView();
        $calViewHelper->setCurrentUserData();

        $today = $this->hasToday();
        $runningDay = $this->getRunningDay();
        $daysInMonth = $this->getDaysInMonth($this->month, $this->year);
        $dayCount = 0;
        $weekCount = 1;
        $daysInThisWeek = $runningDay;
        $dayOfWeek = ($daysInThisWeek+1);
        $this->locationCount = count($this->locations);

        // begin drawing the calendar - start with days from the previous month
        $calendar = "<div class='calendar-blocks'>";
        $calendar .= $this->displayNewWeek($weekCount);
        $calendar .= $this->printDaysFromPreviousMonth($runningDay);

        // now step through every day in this month
        for ($listDay = 1; $listDay <= $daysInMonth; $listDay++) {

            // set up some classes/ids/attributes for the day-block
            $todayClass = ($today == $listDay) ? "today" : "";
            $myShiftClass = ($this->events[$listDay]['has_my_shift_today']) ? "day-has-my-shift" : "";
            $dayBlockId = 'day-block-' . $listDay;
            $classes = 'day-block ' . $todayClass . ' ' . $myShiftClass;

            $totalHtml = $calViewHelper->getShiftTotals($this->events[$listDay]['total_counts']);

            // create the day-block and day-num
            $calendar .= '<div id="' . $dayBlockId . '" class="' . $classes . '" data-day="' . $listDay . '" data-weekDay="' . $dayOfWeek . '">';
            $calendar .= ($todayClass == "") ? "" : "<img class='today-img' src='/images/today.png'>";
            $calendar .= ($myShiftClass == "") ? "" : "<img class='day-has-my-shift-img' src='/images/icons/my-shift.png'>";
            $calendar .= '<div class="day-num">'. $listDay . '</div>';
            $calendar .= $this->printDaysEvents($listDay);
            
            // for all of the events for this day, print out the zoom view (include ones that may not be at a proritized location)
            $calendar .= $view->partial('zoom-events.phtml', array("events" => $this->events[$listDay]['events'], "today" => $todayClass, "weekday_name" => $this->daysOfWeek[$dayOfWeek-1], "dayNum" => $listDay, "noShiftsMsg" => $calViewHelper->getNoShiftsMsg(), "totals" => $totalHtml, "current_user_data" => $calViewHelper->current_user_data));
            $calendar .= '</div>';

            // clear the float and start a new week (unless it's the last day of the month)
            if ($runningDay == 6 && $listDay < $daysInMonth) {
                $weekCount++;
                $calendar .= '<div class="clear"></div></div><div class="clear"></div>';
                if (($dayCount+1) != $daysInMonth) {
                    $calendar .= $this->displayNewWeek($weekCount);
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
        //$daysInThisWeek is actually one larger than the number of days
        //and the printDaysFromNextMonth() function takes this into account.
        $calendar .= $this->printDaysFromNextMonth($daysInThisWeek);

        // close the wrapper divs and clear floats!
        $calendar .= '</div><div class="clear"></div>';
        $calendar .= "</div>";
        $calendar .= "</div>";

        return $calendar;
    }

    /**
    * Steps through each location and handles displaying hte events at that location for the specified day
    * @param int $listDay the day of the month
    * @return string the events rendered as html
    */
    public function printDaysEvents($listDay)
    {
        $output = '';

        // step through today's events at a prioritized location and create a shift bar for each
        if ($this->locations) {
            foreach ($this->locations as $baseId => $baseDetails) {
                $output .= '<div class="base-row" data-baseId="' . $baseId . '" data-description="' . $baseDetails['description'] . '" style="height:' . $this->getBaseRowHeight($this->locationCount) . ';">';
                $output .= 		$this->displayRollOverEvents($listDay, $baseId, $this->locationCount);
                $output .= 		$this->displayEvents($listDay, $baseId, $this->locationCount);
                $output .= "</div>";
            }
        }
        
        return $output;
    }

    /**
    * Steps through events for a given day for a given location
    * (determines if the event is a "rollover" and then prints the shift bars)
    * @param int day the day of the month
    * @param int baseId the location we're displaying events for
    * @return string the events rendered as html
    */
    private function displayEvents($day, $baseId)
    {
        $output = '';

        // do we have events on this day at this location?
        if ($this->events[$day]['events'][$baseId]) {
            foreach ($this->events[$day]['events'][$baseId] as $eventId => $event) {
                $this->checkForRollOver($event, $day, $baseId);
                $output .= $this->drawShiftBar($event, $eventId);
            }
        }

        return $output;
    }

    /**
     * Check to see if this event rolls over to
     * the next day and remember it if it does
     *
     * @param array $event
     * @param integer $day day of the month
     * @param integer $baseId the ID of the base where this event occurs
     * @return boolean
     */
    private function checkForRollOver($event, $day, $baseId)
    {
        // if the end time is on the next day, tell the next day about it
        if ($event['end_datetime']->format("j") != $day) {
            
            //First check to see if it's the last day of the month
            if (date_create($this->year . "-" . $this->month . "-" . $day)->format("t") == $day) {
                $nextDay = 1;
            } else {
                $nextDay = $day+1;
            }
            
            if (!$this->rollOverEvents[$nextDay][$baseId]) {
                $this->rollOverEvents[$nextDay][$baseId] = array();
            }
            
            $this->rollOverEvents[$nextDay][$baseId][$eventId] = $event;
            return true;
        }
        return false;
    }
    
    /**
    * Steps through events for a given day for a given location that have
    * started on a different day and are ending on this day.
    * This function will adjust start time/duration and then print the shift bars
    * @param int day the day of the month
    * @param int baseId the location we're displaying events for
    * @return string the events rendered as html
    */
    private function displayRollOverEvents($day, $baseId)
    {
        $output = '';

        // do we have any rolled over events on this day at this location?
        if ($this->rollOverEvents[$day][$baseId]) {
            foreach ($this->rollOverEvents[$day][$baseId] as $eventId => $event) {

                //Check to see if this event continues to rollover and adjust the duration accordingly
                if ($this->checkForRollOver($event, $day, $baseId)) {
                    $event['duration'] = 24;
                } else {
                    $event['duration'] = $event['end_datetime']->format("G");
                }
                
                // adjust the start time
                $event['start_datetime'] = 00;

                $output .= $this->drawShiftBar($event, $eventId, true);
            }
        }

        return $output;
    }

    /**
    * Returns the html to display an event as a "shift bar"
    * @param array $event the event to be displayed
    * @param int $eventId the id of the event we're displaying
    * @param bool $rolledOver if this shift is a "rolling shift"
    * @return string the event as a shift bar rendered as html
    */
    private function drawShiftBar($event, $eventId, $rolledOver = false)
    {
        $pixelsPerHour = 5;
        $duration = $event['duration']*$pixelsPerHour;
        $rollingClass = ($rolledOver) ? "rolled-over-shift-bar" : "";

        // if this is a datetime, get the time in 24 hour format
        if ($event['start_datetime'] instanceof DateTime) {
            $startTime = intval($event['start_datetime']->format("G"))*$pixelsPerHour;
        } else {
            $startTime = $event['start_datetime']*$pixelsPerHour;
        }

        // if the start time is midnight, move it over one pixel so the display is a little cleaner
        $startTime = ($startTime == 0) ? 1 : $startTime;

        // put together the HTML
        // the width of the bar is determined by the duration (left to right represents time)
        // the starting point of the bar (its left css propery) is determined by start time
        // if we have a filled shift, it will have an extra class that provides the patterned look
        $shiftBar = "<div ";
        $shiftBar .= "data-eventId='".$event['id']."' data-description=\"".$this->getShiftBarTitle($event)."\" ";
        $shiftBar .= "class='shift-bar " .  $event['event_type'] . " " . $rollingClass . " ";
        $shiftBar .= $this->getFilledClass($event['slot_count'], count($event['slot_assignments'])) . "'";
        $shiftBar .= " style='width: " . $duration . "px; left:" . $startTime . "px; height:" . $this->getShiftBarHeight($this->locationCount) . ";'></div>";

        return $shiftBar;
    }

    private function getShiftBarHeight($locCount)
    {
        $barHeights = array(0 => "5px", 13 => "0.28em", 14 => "0.25em", 15 => "0.22em", 16 => "0.22em", 17 => "0.19em");
        //return ($locCount < 13) ? $barHeights[0] : $barHeights[$locCount];
        return $barHeights[0];
    }

    private function getShiftBarTitle($event)
    {
        $total = $event['slot_count'];
        $filled = count($event['slot_assignments']);
        $location = $event['site_name'].": ".$event['base_name'];
        if ($filled >= $total) {
            $slots = "full";
        } else {
            $available = $total - $filled;
            $slots = $available." of ".$total." ".Util_String::pluralize("slot", $total)." available";
        }
        return "$location ($slots)";
    }

    private function getBaseRowHeight($locCount)
    {
        $rowHeights = array(0 => "6px", 13 => "0.39em", 14 => "0.36em", 15 => "0.33em", 16 => "0.31em", 17 => "0.28em");
        //return ($locCount < 13) ? $rowHeights[0] : $rowHeights[$locCount];
        return $rowHeights[0];
    }

    /**
    * Returns the name of the class that indicates a shift is filled (applied to the shift bar)
    * if the number of slots is equal to the number of assignments.
    * Returns an empty string if the shift is not full.
    * @param int $openSlots the number of total slots
    * @param int $filledSlots the number of slots that have been filled
    * @return string the name of the class that should be applied to the shift bar
    */
    public function getFilledClass($openSlots, $filledSlots)
    {
        $filledClass = "";
        if ($openSlots <= $filledSlots) {
            $filledClass = "event-filled";
        }
        return $filledClass;
    }

    /**
    * Returns html for creating a new "week-row" div
    * @param int weekNum the number of the week we are drawing
    * @return string the week row class rendered as html
    */
    public function displayNewWeek($weekNum)
    {
        return '<div class="week-row" data-weekCount="' . $weekNum . '">';
    }

    /**
    * Constructs an array of day numbers from the previous month (26, 27, 28, etc.) until a specified day of the week
    * @param int $month the current month (we want to know about the one before this)
    * @param int $year the current year of the current month
    * @param int $runningDay the day of the week that will stop building the array
    * @return array an array of day numbers from the previous month
    */
    public function getDaysFromPreviousMonth($month, $year, $runningDay)
    {
        // figure out the previous month (adjust the year if necessary)
        if ($month == 1) {
            $prevMonth = 12;
            $prevYear = $year-1;
        } else {
            $prevMonth = $month-1;
            $prevYear = $year;
        }

        // build the array
        $daysInPrevMonth = $this->getDaysInMonth($prevMonth, $prevYear);
        $dayNumbersInPrevMonth = array();
        for ($x = 0; $x < $runningDay; $x++) {
            $dayNumbersInPrevMonth[] = $daysInPrevMonth-$x;
        }

        return $dayNumbersInPrevMonth;
    }

    /**
    * Determines if this month has today in it
    * @return int the day of the month if today is found
    * @return bool false if today is not found
    */
    public function hasToday()
    {
        $today = new DateTime();

        if (($today->format("m") == $this->month) && ($today->format("Y") == $this->year)) {
            $today = intval($today->format("j"));
        } else {
            $today = false;
        }

        return $today;
    }

    /**
    * Displays the opaque day blocks from the previous month
    * @param int $runningDay the day of the week that starts this view helper's month
    * @return the day-block classes rendered as html
    */
    public function printDaysFromPreviousMonth($runningDay)
    {
        $output = '';
        $dayNumbersInPrevMonth = $this->getDaysFromPreviousMonth($this->month, $this->year, $runningDay);
        for ($x = 0; $x < $runningDay; $x++) {
            $output .= '<div class="day-block has-opacity"></div>';
        }
        return $output;
    }

    /**
    * Displays the opaque day blocks from the next month to fill the week
    * @param int $daysInThisWeek the number of days in the week that ends this view helper's month
    * @return the day-block classes rendered as html
    */
    public function printDaysFromNextMonth($daysInThisWeek)
    {
        $output = '';
        if ($daysInThisWeek < 8 && $daysInThisWeek > 1) {
            for ($x = 1; $x <= (8 - $daysInThisWeek); $x++) {
                $output .= '<div class="day-block has-opacity"><div class="day-num">' . $x . '</div></div>';
            }
        }
        return $output;
    }

    /**
    * Gets the day of the week that starts $this->month and $this->year
    * @return int the day of the week that starts this month
    */
    public function getRunningDay()
    {
        return date('w', mktime(0, 0, 0, $this->month, 1, $this->year));
    }

    /**
    * Gets the number of days in a given month (public becuase other views will use this)
    * @param int $m the month
    * @param int $y the year
    * @return int the number of days in the given month
    */
    public function getDaysInMonth($m, $y)
    {
        return date('t', mktime(0, 0, 0, $m, 1, $y));
    }
}
