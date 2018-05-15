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
 * This helper will display a calendar (in month details view) with events
 *
 * @author hammer :)
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_MonthDetailsView extends Zend_View_Helper_Abstract
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
     * @var array the days of the week!
     */
    protected $daysOfWeek = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
    
    public $filter_set;

    /**
     * @param int $month the month to display
     * @param int $year the year of the month to display
     * @param array $events the events for the month to be shown
     * @param array $locations the prioritized locations to show
     * @param zendView $view the view
     *
     * @return string the month rendered as html
     */
    public function monthDetailsView($month, $year, $events, $view, $filter_set = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->events = $events;
        $this->initFilterSet($filter_set);
        
        return array("calendar" => $this->drawMonth($view), "display_options" => $this->drawDisplayOptions());
    }
    
    private function initFilterSet($filter_set = null)
    {
        if ($filter_set) {
            $this->filter_set = $filter_set;
        } else {
            $session = new \Zend_Session_Namespace("Scheduler");
            $this->filter_set = \Fisdap\EntityUtils::getEntity("SchedulerFilterSet", $session->filterSet);
        }
    }
    
    private function drawDisplayOptions()
    {
        $html  = "<div class='no-pdf month_details_display_options_summary'>";
        $html .=		"<b>Display options:</b> " . $this->filter_set->getDisplayOptionDescription() ;
        $html .=		"<span id='edit_display_options_wrapper'> <a href='#' id='edit_disply_options'>Edit</a></span>";
        $html .= "</div>";
        
        return $html;
    }
    
    /**
     * Draws the month with events included and displayed through shift bars/zoom-view/event-details
     * @param $view the zend view (used so that we can do a partialLoop)
     * @return string the display rendered as html
     */
    private function drawMonth($view)
    {
        $monthViewHelper = new Scheduler_View_Helper_MonthView($this->month, $this->year, $this->events, $this->locations, $view);
            
        // set up some variables to start
        $calViewHelper = new Scheduler_View_Helper_CalendarView();
        $calViewHelper->setCurrentUserData();
        
        $today = $this->hasToday();
        $runningDay = $this->getRunningDay();
        $daysInMonth = $monthViewHelper->getDaysInMonth($this->month, $this->year);
        $dayCount = 0;
        $weekCount = 1;
        $daysInThisWeek = $runningDay;
        $dayOfWeek = ($daysInThisWeek+1);
        $display_options = $this->filter_set->getDisplayOptionsArray();

        // begin drawing the calendar - start with days from the previous month
        $calendar = "<table class='month-details-calendar' data-today='" . $today . "'>";
        $calendar .= $this->displayNewWeek($weekCount);
        $calendar .= $this->printDaysFromPreviousMonth($runningDay, $monthViewHelper);

        // now step through every day in this month
        for ($listDay = 1; $listDay <= $daysInMonth; $listDay++) {
            
            
            // set up some classes/ids/attributes for the day-block
            $todayClass = ($today == $listDay) ? "details_has_today" : "";
            $today_wrapper_img_class = "";
            
            if ($todayClass) {
                if ($display_options['totals']['value']) {
                    $todayClass = "details_has_today_with_totals";
                    $today_wrapper_img_class = "details_today_img_wrapper_with_totals";
                }
            }
            
            //	$myShiftClass = ($this->events[$listDay]['has_my_shift_today']) ? "day-has-my-shift" : "";
            $dayBlockId = 'details-day-block-' . $listDay;
            $classes = 'details-day-block ' . $todayClass . ' ' . $myShiftClass;

            //$totalHtml = $calViewHelper->getShiftTotals($this->events[$listDay]['total_counts']);

            // create the day-block and day-num
            $calendar .= '<td id="' . $dayBlockId . '" class="' . $classes . '" data-day="' . $listDay . '" data-weekDay="' . $dayOfWeek . '">';
            $calendar .= ($myShiftClass == "") ? "" : "<img class='day-has-my-shift-img' src='/images/icons/my-shift.png'>";
            //$calendar .= '<div class="day-num">'. $listDay . ' ' .  . '</div>';
            
            $total_counts_array = $this->getTotalCountsArray($this->events[$listDay]['total_counts']);
            
            $totalHtml = $calViewHelper->getShiftTotals($total_counts_array);
            
            
            $calendar .= '<div class="week_day_class_' . $dayOfWeek . ' day-num month_details_day_totals_week_' . $weekCount . ' ' . $todayClass . '">';
            $calendar .= 		'<span class="number_wrapper">'. $listDay . '</span> ';
            $calendar .=		($display_options['totals']['value']) ? $totalHtml : '';
            $calendar .= 		($todayClass == "") ? "" : "<div class='details_today_img_wrapper " . $today_wrapper_img_class . "'><img class='today-img' src='/images/today.png'></div>";
            $calendar .= '<div class="clear"></div></div>';
            //$calendar .= $this->printDaysEvents($listDay);
            
            //var_dump($this->events[$listDay]['total_counts']);
            
            // for all of the events for this day, print out the zoom view (include ones that may not be at a proritized location)
            $calendar .= $view->partial('month-details-events.phtml', array("events" => $this->events[$listDay]['events'], "noShiftsMsg" => $calViewHelper->getNoShiftsMsg(), "current_user_data" => $calViewHelper->current_user_data, "display_options" => $display_options));
            $calendar .= '</td>';

            // clear the float and start a new week (unless it's the last day of the month)
            if ($runningDay == 6 && $listDay < $daysInMonth) {
                $weekCount++;
                $calendar .= '<div class="clear"></div></tr>';
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
        $calendar .= "</table>";
        $calendar .= "</div>";
        $calendar .= "</div>";
        $calendar .= "</div>";

        return $calendar;
    }
    
    public function getTotalCountsArray($total_counts_from_event_data)
    {
        $default = array('lab' => array('available' => 0, 'total' => 0),
                           'clinical' => array('available' => 0, 'total' => 0),
                           'field' => array('available' => 0, 'total' => 0));
        
        return ($total_counts_from_event_data) ? $total_counts_from_event_data : $default;
    }
    
    /**
    * Returns html for creating a new "week-row" div
    * @param int weekNum the number of the week we are drawing
    * @return string the week row class rendered as html
    */
    public function displayNewWeek($weekNum)
    {
        return '<tr class="week-row" data-weekCount="' . $weekNum . '">';
    }
    
    
    /**
    * Displays the opaque day blocks from the previous month
    * @param int $runningDay the day of the week that starts this view helper's month
    * @return the day-block classes rendered as html
    */
    public function printDaysFromPreviousMonth($runningDay, $monthViewHelper)
    {
        $output = "";
        $dayNumbersInPrevMonth = $monthViewHelper->getDaysFromPreviousMonth($this->month, $this->year, $runningDay);
        for ($x = 0; $x < $runningDay; $x++) {
            $output .= '<td class="details-day-block has-opacity"></td>';
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
    * Displays the opaque day blocks from the next month to fill the week
    * @param int $daysInThisWeek the number of days in the week that ends this view helper's month
    * @return the day-block classes rendered as html
    */
    public function printDaysFromNextMonth($daysInThisWeek)
    {
        $output = "";
        if ($daysInThisWeek < 8 && $daysInThisWeek > 1) {
            for ($x = 1; $x <= (8 - $daysInThisWeek); $x++) {
                $output .= '<td class="details-day-block has-opacity"><div class="day-num">' . $x . '</div></td>';
            }
        }
        return $output;
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
}
