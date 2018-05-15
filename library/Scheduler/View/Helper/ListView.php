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
 * This helper will display a calendar (in list view) with events
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_ListView extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;
    protected $startDate;
    protected $endDate;
    protected $events;
    protected $viewRef;

    /**
     * @param string $type the view type - either month/week/day
     * @param dateTime $date the date used to display the appropriate month/week/day
     * @param array $filters various filters used to filter the shifts
     *
     * @return string the calednar rendered as html
     */
    public function listView($startDate, $endDate, $events, $view)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->events = $events;
        $this->viewRef = $view;
        return $this->drawList($view);
    }

    private function drawList($view)
    {
        $list = "<div class='list-view'>";
        if (!$view->pdf) {
            $list .= 	"<div id='date-pickers-for-title'>";
            $list .= 		"<input type='text' id='startDate' value='" . $this->startDate->format("m/d/Y") . "' class='selectDate'> - ";
            $list .= 		"<input type='text' id='endDate' value='" . $this->endDate->format("m/d/Y") . "' class='selectDate'>";
            $list .= 		"<div class='extra-small blue-button'>";
            $list .= 			"<a href='#' id='list-go-btn' class='first last'>Go</a>";
            $list .= 		"</div>";
            $list .= 	"</div>";
        }

        // step through every day in this date range and get the corresponding
        $calViewHelper = new Scheduler_View_Helper_CalendarView();
        $calViewHelper->setCurrentUserData();

        $interval = DateInterval::createFromDateString('1 day');
        $period = new DatePeriod($this->startDate, $interval, $this->endDate->setTime(23, 59, 59));
        $today = new DateTime();
        $count = 0;
        $printed = 0;

        if (count($this->events) > 0) {
            foreach ($period as $dt) {
                $events = $this->events[$dt->format("Y")][$dt->format("n")][$dt->format("j")]['events'];
                $topMarginClass = ($count == 0) ? "small-top-margin" : "";
                $todayClass = ($dt->format("Y-M-j") == $today->format("Y-M-j")) ? "today" : "";
                $totalHtml = $calViewHelper->getShiftTotals($this->events[$dt->format("Y")][$dt->format("n")][$dt->format("j")]['total_counts']);

                if (count($events) > 0) {
                    $printed++;
                    
                    $list .= '<h3 id="header-' . $dt->format("m-j-Y") . '" class="day-header-list section-header ' . $topMarginClass . ' ' . $todayClass . '">';
                    $list .= 	($todayClass == "") ? "" : "<img class='today-img' src='/images/today.png'>";
                    $list .= 	'<div class="date-description">' . $dt->format("l M j, Y") . "</div>";
                    $list .=	$totalHtml;
                    $list .= 	'<div class="clear"></div>';
                    $list .= '</h3>';

                    $list .= '<div class="clear"></div>';
                    $list .= $view->partial('list-events.phtml', array("dayDescription" => $dt->format("m-j-Y"),
                                                                       "events" => $events,
                                                                       "current_user_data" => $calViewHelper->current_user_data));
                }
                
                $count++;
            }
        }
        
        
        if ($printed == 0) {
            $list .= "<br />" . $calViewHelper->getNoShiftsMsg();
        }
        
        $list .= "</div>";
        $list .= "</div>";
        return $list;
    }
}
