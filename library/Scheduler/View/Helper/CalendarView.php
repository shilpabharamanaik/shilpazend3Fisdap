<?php

/**
 * This helper will display a calendar with events (month/week/day)
 *
 * @package Scheduler
 */
class Scheduler_View_Helper_CalendarView extends Zend_View_Helper_Abstract
{
    /**
     * @var string the html to be rendered
     */
    protected $_html;

    /*
     * @var string the view type day/list/week/month
     */
    public $view_type;

    protected $daysOfWeek = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');

    /**
     * @var \Fisdap\Entity\User
     */
    public $user;

    /**
     * @var bool is student
     */
    public $student;

    /**
     * @var int program id
     */
    public $programId;

    /**
     * @var int userContextId
     */
    public $userContextId;

    /**
     * @var bool has_skills_tracker
     */
    public $has_skills_tracker;

    /**
     * @var \Fisdap\Entity\InstructorLegacy instructor
     */
    public $instructor;

    /**
     * @var array current_user_data
     */
    public $current_user_data;

    /**
     * @var array certification level bit values
     */
    public $avail_certs_config;

    public $profession_cert_levels;

    /**
     * @var \Fisdap\Data\Event\DoctrineEventLegacyRepository
     */
    public $eventLegacyRepo;

    /**
     * @var boolean
     */
    public $pdf = false;

    public function __construct()
    {
        $this->eventLegacyRepo = \Fisdap\EntityUtils::getRepository('EventLegacy');
    }

    /**
     * @param string   $type    the view type - either month/week/day
     * @param dateTime $date    the date used to display the appropriate month/week/day
     * @param mixed    $endDate
     * @param array    $filters various filters used to filter the shifts
     * @param bool     $load_data
     * @param bool     $returnData
     * @param null     $user
     *
     * @throws Zend_Exception
     * @return string the calendar rendered as html
     */
    public function calendarView($type, \DateTime $date, $endDate, array $filters = null, $load_data = true, $returnData = false, $user = null)
    {
        $this->addStyles();
        $date = $this->getStartDate($date, $type);

        $this->setVars($type, $user);

        if(!$endDate && !$returnData){
            $endDate = $this->getEndDate($date, $type);
        }

        if ($load_data) {
            $data = $this->getData($date, $endDate, $filters, $type);
            $events = $data['events'];
            $locations = $data['locations'];
        }

        if ($returnData) {
            return $data;
        }

        // if we aren't viewing a list, we can clean up our array a bit (no need to key by year/month)

        if($type != "list"){
            $simpleEvents = $events[$date->format("Y")][$date->format("n")];

            for($i = 1; $i <= 31; $i++){
                if($events[$endDate->format("Y")][$endDate->format("n")][$i]){
                    $simpleEvents[$i] = $events[$endDate->format("Y")][$endDate->format("n")][$i];
                }
            }

            $events = $simpleEvents;
        }

        $calControls = new Scheduler_View_Helper_CalendarControls();

        $this->_html .= '<div class="calendar-display-wrapper" ';
        $this->_html .= 'data-studentlimitwarning="' . $this->getStudentShiftLimitWarning($filters['sites']) . '" ';
        $this->_html .= 'data-title="' . $calControls->getTitle($type, $date) . '" ';
        $this->_html .= $this->getDateAttrib("month", "m", $date);
        $this->_html .= $this->getDateAttrib("day", "j", $date);
        $this->_html .= $this->getDateAttrib("year", "Y", $date);
        $this->_html .= $this->getEndDateAttribs($endDate) . ">";

        if($type == "month" || $type == "week"){
            $this->_html .= '<div class="clear"></div><div class="cal-wrapper-' . $type . '">';
            $this->_html .= $this->drawHeadings();
            $this->_html .= '<div class="clear"></div><div class="orange-cal-wrapper">';
        }

        $this->_html .= $this->drawCalendar($date, $endDate, $type, $events, $locations);
        return $this->_html;
    }

    /**
     * Determine if a student has met their shift limit for any type of site that has been filtered to
     *
     * @param array $sites
     * @return string
     */
    public function getStudentShiftLimitWarning($sites)
    {
        //Get the distinct types from the site filters
        $siteTypes = array_keys(\Fisdap\EntityUtils::getRepository("SiteLegacy")->parseSelectedSites($sites, true));

        //Check to see if the student's calendar contains shifts they can't sign up for due to shift limits
        $limitHit = array();
        if ($this->student) {
            foreach($this->current_user_data['shift_limits'] as $shiftType => $limit) {
                if ($limit && in_array($shiftType, $siteTypes)) {
                    $limitHit[] = $shiftType;
                }
            }
        }

        return implode(" & ", $limitHit);
    }

    /**
     * @param string   $type    the view type - either month/week/day
     * @param dateTime $date    the date used to display the appropriate month/week/day
     * @param dateTime $endDate
     * @param array    $filters various filters used to filter the shifts
     * @param          $view
     * @param          $filter_set
     *
     * @return string the calendar rendered as html
     */
    public function getCalendarHtml($type, $date, $endDate, $filters, $view, $filter_set = null)
    {
        $this->view = $view;
        $this->setVars($type);
        $date = $this->getStartDate($date, $type);

        if(!$endDate){
            $endDate = $this->getEndDate($date, $type);
        }

        $data = $this->getData($date, $endDate, $filters, $type);
        $events = $data['events'];
        $locations = $data['locations'];

        // if we aren't viewing a list, we can clean up our array a bit (no need to key by year/month)

        if($type != "list"){
            $simpleEvents = $events[$date->format("Y")][$date->format("n")];

            for($i = 1; $i <= 31; $i++){
                if($events[$endDate->format("Y")][$endDate->format("n")][$i]){
                    $simpleEvents[$i] = $events[$endDate->format("Y")][$endDate->format("n")][$i];
                }
            }

            $events = $simpleEvents;
        }


        $calControls = new Scheduler_View_Helper_CalendarControls();

        $output = "";
        $output .= $view->pdf ? '' : '<img src="/images/throbber_small.gif" id="cal-throbber">';
        $output .= '<div class="calendar-display-wrapper" data-title="' . $calControls->getTitle($type, $date) . '"';
        $output .= $this->getDateAttrib("month", "m", $date);
        $output .= $this->getDateAttrib("day", "j", $date);
        $output .= $this->getDateAttrib("year", "Y", $date);
        $output .= $this->getEndDateAttribs($endDate) . ">";

        if($type == "month" || $type == "month-details" || $type == "week"){
            $output .= '<div class="clear"></div><div class="cal-wrapper-' . $type . '">';
            $output .= $this->drawHeadings();
            $output .= '<div class="clear"></div><div class="orange-cal-wrapper">';
        }

        $output .= $this->drawCalendar($date, $endDate, $type, $events, $locations, $filter_set);
        return $output;
    }

    /*
     * get this view helper going!
     * may be called from some other classes later
     */
    public function setVars($type, $user = null)
    {
        $this->view_type = $type;
        $this->setCurrentUserData($user);
        $this->programId = $this->user->getProgramId();
        $certs = \Fisdap\EntityUtils::getEntity('CertificationLevel')->getAll($this->user->getProgram()->profession->id);
        $this->profession_cert_levels = array();
        foreach($certs as $cert){
            $this->profession_cert_levels[] = array('type' => 2, 'description' => $cert->description, 'value' => $cert->id);
        }
    }

    /*
     * Draws the calendar of events!
     * Will figure out which view helper to use and call that.
     * @param dateTime the starting date
     * @param string $type the type of display (week/month/day/list)
     * @param array $events the events to be displayed
     * @param array $locations the prioritized locations to use
     * @return string the html to be rendered
    */
    public function drawCalendar($startDate, $endDate, $type, $events, $locations, $filter_set = null)
    {
        $output = "";

        if($type == "month"){
            $monthViewHelper = new Scheduler_View_Helper_MonthView();
            $output .= $monthViewHelper->monthView($startDate->format("m"), $startDate->format("Y"), $events, $locations, $this->view);
            $output .= '</div></div>';
        }
        else if($type == "week"){
            $weekViewHelper = new Scheduler_View_Helper_WeekView();
            $output .= $weekViewHelper->weekView($startDate, $events, $locations, $this->view);
        }
        else if($type == "day"){
            $dayViewHelper = new Scheduler_View_Helper_DayView();
            $output .= $dayViewHelper->dayView($startDate, $events, $this->view) . "</div>";
        }
        else if($type == "month-details"){

            $monthDetailsViewHelper = new Scheduler_View_Helper_MonthDetailsView();
            $month_details_view = $monthDetailsViewHelper->monthDetailsView($startDate->format("m"), $startDate->format("Y"), $events, $this->view, $filter_set);

            $output .= '<div class="clear"></div><div class="cal-wrapper-' . $type . '">';
            $output .= 	$month_details_view['display_options'];
            $output .= 	'<div id="month_details_day_names" class="no-pdf">' . $this->drawHeadings() . '<div class="clear"></div></div>';
            $output .= '<div class="orange-cal-wrapper">';
            $output .= $month_details_view['calendar'];
        }
        else {
            // got to be list!
            $listViewHelper = new Scheduler_View_Helper_ListView();
            $output .= $listViewHelper->listView($startDate, $endDate, $events, $this->view);
        }

        return $output;
    }

    /**
     * gets data from the database
     *
     * @param dateTime $date the starting date
     * @param dateTime $endDate the end date
     * @param array $filters the filters for hte data
     * @param string $type the type of display to be rendered (week/month/day/list)
     *
     * @return array with both events and prioritized locations used
     */
    private function getData(\DateTime $date, \DateTime $endDate, $filters, $type)
    {
        $details_view = ($this->view_type == "month-details") ? true : false;
        $data = $this->eventLegacyRepo->getOptimizedEvents($this->programId, $date, $endDate, $filters, $this, null, $details_view);
        return array("events" => $data['events'], "locations" =>  $this->getLocations($data['locations'], $type));
    }

    /**
     * @param DateTime $start_date
     * @param DateTime $end_date
     *
     * @return string
     */
    public function getWindowWhen(\DateTime $start_date, \DateTime $end_date)
    {
        $today = new \DateTime();
        $today_time = $today->format("U");
        $break = ($this->view_type == "day") ? "<br />" : "";

        if($start_date->format("U") > $today_time){
            $when = "Sign up opens on " . $break . $start_date->format("M j, Y") . ".";
        }
        else {
            if($end_date->format("U") < $today_time){
                $when = "Sign up closed on " . $break . $end_date->format("M j, Y") . ".";
            }
            else {
                //Ignore the time, and just compare dates
                $when = ($end_date->format("Y-m-d") == $today->format("Y-m-d")) ? "Sign up by today." : "Sign up by " . $break . $end_date->format("M j, Y") . ".";
            }
        }

        return $when;
    }

    public function getDropSwapCoverDisplay($perms)
    {
        $output = '<div class="change-permissions">';
        $permCount = count($perms);
        $permPos = 1;

        foreach($perms as $perm){
            $img     = ($perm['can']) ? "approved" : "denied";
            $output .= '<img src="/images/icons/' . $img . '.png">';
            $output .= ucfirst($perm['name']);
            $output .= ($perm['can'] && $perm['needs_permission']) ? '<span class="subtle"> (permission)</span>' : "";
            $output .= ($this->view_type == "day" && !($permPos == $permCount)) ? "<br />" : "";
            $permPos++;
        }

        $output .= "</div>";

        return $output;
    }

    public function getRequestCode($code_type, $shared_preferences, $event_codes) {
        // see if a program-specific code exists for this event
        if ($shared_preferences[$code_type]) {
            return $shared_preferences[$code_type];
        }

        // otherwise, use the event code
        return $event_codes[$code_type];
    }

    public function getRequestPerms($requestTypes, $shared_preferences, $event_codes) {

        $can = $this->getRequestCode('student_can_switch', $shared_preferences, $event_codes);
        $permission = $this->getRequestCode('switch_needs_permission', $shared_preferences, $event_codes);

        $at_least_one_can = false;

        $results = array();
        foreach ($requestTypes as $requestType) {
            $bit = $requestType['bit_value'];
            $needs_permission = (boolean)($permission & $bit) ? 1 : 0;
            $can_request = (boolean)($can & $bit) ? 1 : 0;
            if($can_request){$at_least_one_can = true;}
            $results[$requestType['id']] = array('name' => $requestType['name'], 'can' => $can_request, 'needs_permission' => $needs_permission);
        }

        return array("results"=>$results, "show_change_request_btn"=>$at_least_one_can);
    }

    public function canUserSeeWindow($status, $who, $userCertLevel, $userGroups, $active_window)
    {
        if($this->student){
            $user_can_see = false;
            if($status == "open" && $active_window){
                if($this->isCertInWindow($who['certs'], array($userCertLevel)) && $this->isGroupInWindow($who['groups'], $userGroups)){
                    $user_can_see = true;
                }
            }
        }
        else {
            $user_can_see = true;
        }

        return $user_can_see;
    }

    /*
     * searches through a given set of certs level (from a window) and compares to an array of cert levels
     * if at least one of the "certsToFind" is found, this function will return true
     */
    public function isCertInWindow($windowCerts, $certsToFind)
    {
        $hasCert = false;
        if($windowCerts){
            foreach($windowCerts as $certId){
                if($certsToFind){
                    if(in_array($certId, $certsToFind)){
                        $hasCert = true;
                    }
                }
            }
        }
        else {
            $hasCert = true;
        }

        return $hasCert;
    }

    /*
     * searches through a given set of group ids (from a window) and compares to an array of group ids
     * if at least one of the "groupsToFind" is found, this function will return true
     */
    public function isGroupInWindow($windowGroups, $groupsToFind)
    {
        $hasGroup = false;

        if(count($windowGroups) == 0){
            $hasGroup = true;
        }

        if($windowGroups){
            foreach($windowGroups as $groupId){
                if($groupsToFind){
                    if(in_array($groupId, $groupsToFind)){
                        $hasGroup = true;
                    }
                }
            }
        }

        return $hasGroup;
    }

    public function getWindowStatus($start_date, $end_date)
    {
        $today = new \DateTime();
        $today = $today->format("U");

        if($start_date->format("U") > $today){ $stat = "not-open-yet"; }
        else { $stat = ($end_date->format("U") < $today) ? "closed" : "open";}
        return $stat;
    }

    public function getWindowWho($constraints)
    {
        $cert_levels = array();
        $cert_ids = array();
        $student_groups = array();
        $group_ids = array();

        //If we don't have any constraints, default to the profession cert levels
        if(!$constraints){
            foreach($this->profession_cert_levels as $cert) {
                $cert_levels[] = $cert['description'] . "s";
                $cert_ids[] = $cert['value'];
            }
        } else {
            foreach($constraints as $constraint){
                $constraint_type_id = $constraint['constraint_type']['id'];

                foreach($constraint['values'] as $constraint_value) {
                    if($constraint_type_id == "2"){
                        $cert_levels[] = $constraint_value['description'] . "s";
                        $cert_ids[] = $constraint_value['value'];
                    }
                    else {
                        $student_groups[] = $constraint_value['description'];
                        $group_ids[] = $constraint_value['value'];
                    }
                }
            }
        }

        $cert_level_count = count($cert_levels);

        $txt = (($cert_level_count == $this->current_user_data['profession_cert_count']) || ($cert_level_count == 0)) ? "All " . $this->current_user_data['profession_name'] . " students " : $this->getConstraintDescription($cert_levels, null);
        $txt .= $this->getConstraintDescription($student_groups, " in ");

        $who = array();
        $who['description'] = $txt;
        $who['certs'] = $cert_ids;
        $who['groups'] =  $group_ids;

        return $who;
    }

    public function getConstraintDescription($collection, $phrase)
    {
        $description = null;

        if ($collection) {
            $description = $phrase;
            $count = 0;
            foreach($collection as $item){
                $description .= ($count != 0) ? " or " . $item : $item;
                $count++;
            }
        }
        return $description;
    }

    /*
     * the html for displaying the shift totals (called from outside this view helper)
     * @param array $totals a keyed array (by shift type) of the total shift counts
     * @return stirng the html to be rendered
    */
    public function getShiftTotals($totals)
    {
        $output = "<div class='event-totals-wrapper'>";

        if($totals){
            foreach($totals as $type => $counts){
                $opacityClass = ($counts['total'] == 0) ? "has-opacity" : "";
                $output .= "<div class='event-total " . $type . " " . $opacityClass . "'>";
                $output .= 		"<img src='/images/icons/" . $type . "SiteIconColor.png'>";
                $output .=		$counts['total'];
                $output .= "</div>";
            }
        }

        $output .= "<div class='clear'></div>";
        $output .= "</div>";

        return $output;
    }

    /*
     * gets the locations that will be used for displaying
     * data in month/week view's shift bars
     * @param array $locations the list of bases used for this data set (may need to be prioritized)
     * @param string $type the type of display we're trying to render (month/week/day/list)
     * @return array of prioritizedLocations (could be the same as the locations coming in)
    */
    public function getLocations($locations, $type)
    {
        $prioritizedLocations = $locations;
        if($type == "month" || $type == "week"){

            $prioritizedLocations = array();
            $locCount = count($locations);

            if($locCount > 12){
                if($type == "week"){
                    $limit = ($locCount > 28) ? 28 : $locCount;
                    $prioritizedLocations  = $this->prioritize($limit, $locations);
                }
                else {
                    $prioritizedLocations  = $this->prioritize(12, $locations);
                }
            }
            else {
                $prioritizedLocations  = $this->prioritize($locCount, $locations);
            }

        }

        return $prioritizedLocations;
    }

    /*
     * prioritizes an array of locations based on their frequency
     * @param int $max the number of locations to keep (the limit)
     * @param array $locations the array of locations (with a base id, count, description)
     * @return array the array of prioritized locations
    */
    private function prioritize($max, $locations)
    {
        $prioritizedLocations = array();

        $counts = array();
        foreach($locations as $baseId => $loc){
            $counts[$baseId] = $loc['count'];
        }

        arsort($counts);
        $priority = array_slice($counts, 0, $max, true);

        foreach($priority as $baseId => $count){
            $prioritizedLocations[$baseId] = $locations[$baseId];
        }

        return $prioritizedLocations;
    }

    /**
     * returns a new date time object that will be the 'start date' for this display
     * @param dateTime $date the date passed to the view helper (we'll adjust this guy)
     * @param string $type the type of display (week/month/day/list)
     * @return dateTime the start date to be used
     */
    public function getStartDate(\DateTime $date, $type)
    {
        if($type == "week"){
            $weekStartDate = date('Y-m-d',strtotime('sunday last week', strtotime($date->format("m/j/Y"))));
            $date = new DateTime($weekStartDate);
        }
        else if($type == "month" || $type == "month-details"){
            $date = new DateTime($date->format("Y") . "-" . $date->format("m") . "-01");
        }

        return $date;
    }

    /**
     * gets the end date for hte calendar display
     * (could be specified if list view, or will be calculated for month/week/day)
     * @param dateTime $date the start date
     * @param string $type the type of display (week/month/day/list)
     * @return dateTime the end date to be used for this display
     */
    public function getEndDate(\DateTime $date, $type)
    {
        if($type == "list"){
            // they haven't specified an end date - we'll default to two weeks
            $startDate = $date;
            if ($date) {
                $endDate = strtotime("+2 weeks", strtotime($date->format("m/j/Y")));
                $endDate = new DateTime(date("Y-m-d", $endDate));
            } else {
                $endDate = null;
            }
        }
        else {

            // just for this function
            if($type == "month-details"){
                $type = "month";
            }

            $endDate = strtotime("+1 " . $type, strtotime($date->format("m/j/Y")));
            $endDateModified = strtotime("-1 day", $endDate);
            $endDateModified = new DateTime(date("Y-m-d", $endDateModified));
            $endDate = $endDateModified;
        }

        return $endDate;
    }

    /*
     * returns the html for displaying an event title
     * @param array $event an array of event details (type/duration/etc)
     * needed to display the title
     * @param array $special_icons_html the html to display special icons - need to include in
     * the event title if and only if we're using list view
     * @return string $output the html to be rendered
     */
    public function getEventTitle(&$event, $event_id, $special_icons_html)
    {
        // is this a quick-add shift?
        $quick_add = (substr($event_id, 0, 8) == 'shift_id') ? true : false;
		$shift_add = '';		
		$shifts = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $event_id);
		if($shifts){							
			$shift_add = $shifts->site->getMapAddress($shifts);							
		}
	
		$output = '<h3 class="event-title ' . $event['event_type'] . '">';
		
        $durationPrint = $this->getDurationPrint($event['duration']);

        $includeSpecialIcons = ($this->view_type == "list" || $this->view_type == "day") ? true : false;

        $clickable_title = ($this->view_type == "day" && !$quick_add) ? "clickable-event-title" : "";

        $output .= ($includeSpecialIcons) ? "<div data-eventid='" . $event_id . "' class='header-text " . $clickable_title . "'>" : "";
        $output .= $event['start_datetime']->format("Hi");
        $output .= $durationPrint;
        $output .= ($event['event_name'] != "") ? $event['event_name'] . ", " : "";
        $output .= $event['site_name'] . ": " . $event['base_name'];
        $output .= ($includeSpecialIcons) ? "</div>" : "";
        $output .= ($includeSpecialIcons) ? $special_icons_html : "";
        $output .= ($includeSpecialIcons) ? "<div class='clear'></div>" : "";
        $output .= "</h3>";
		$output .= "<div class='details_map_address' style='display:none;'>".$shift_add."</div>";

        return $output;
    }

    public function getDurationPrint($duration)
    {
        $output = "";

        if(is_numeric( $duration ) && floor( $duration ) != $duration){
            // it has a decimal
            $durationPrint = $duration;
        }
        else {
            $durationPrint = intval($duration);
        }

        $output .= " (" . $durationPrint . "hr";
        $output .= ($durationPrint != 1) ? "s":"";
        $output .= ") ";

        return $output;
    }

    public function getStartTimeDurationDisplay($duration, $start_time)
    {
        return $start_time->format("Hi") . " " . $this->getDurationPrint($duration);
    }

    public function getEventSiteBaseName(&$event)
    {
        $output = "";
        $output .= ($event['event_name'] != "") ? $event['event_name'] . ", " : "";
        $output .= "<span class='event_location'>" . $event['site_name'] . ": " . $event['base_name'] . "</span>";

        return $output;
    }

    /*
     * returns the html for displaying special icons (student created/shared)
     * @param bool $student_created is it a student created shift?
     * @param bool $shared is it a shared shift?
     * @return string $output the html to be rendered
     */
    public function getSpecialIcon($quick_add, $shared, $my_shift, $previously_shared, $student_created, $instructor_created)
    {
        $output  = ($this->view_type != "list") ? '<div class="special-icon">' : '';

        if($my_shift && $quick_add && $student_created){
            $output .= "<img class='student-created-icon' src='/images/icons/my-student-created-shift.png' data-tooltip='My quick add shift'>";
        }
        else if ($my_shift && $quick_add && $instructor_created){
            $output .= "<img class='student-created-icon' src='/images/icons/my-instructor-created-shift.png' data-tooltip='My quick add shift created by an instructor'>";
        }
        else {
            $output .= ($my_shift) ? "<img class='my-shift-icon' src='/images/icons/my-shift.png' data-tooltip='My shift'>" : "";
            $output .= ($quick_add && $student_created) ? "<img class='student-created-icon' src='/images/icons/student-created-shift.png' data-tooltip='Quick add shift created by a student'>" : "";
            $output .= ($quick_add && $instructor_created) ? "<img class='student-created-icon' src='/images/icons/instructor-quick-add-shift.png' data-tooltip='Quick add shift created by an instructor'>" : "";
        }

        $output .= ($shared && !$this->student) ? "<img class='shared-icon' src='/images/icons/sharing.png' data-tooltip='Shared shift'>" : "";
        $output .= ($previously_shared && !$this->student) ? "<img class='shared-icon' src='/images/icons/previously-shared.png' data-tooltip='No longer shared shift'>" : "";
        $output .= ($this->view_type != "list") ? '</div>' : '';
        return $output;
    }

    /*
     * gets the html for displaying a list of preceptors
     * @param array $preceptors an array of preceptors (first/last name)
     * @return string the html for displaying a list of preceptors
    */
    public function getPreceptorList($preceptors, $instructors = array(), $month_details_view = false)
    {
        $output = "";
        $output .= $this->preceptorOnlyList($preceptors, $month_details_view);
        $output .= $this->instructorOnlyList($instructors, $month_details_view);
        return $output;
    }

    public function instructorOnlyList($instructors = array(), $month_details_view)
    {
        $output = "";
        if($instructors){
            $additional_inst_class = ($month_details_view) ? "month_details_instructors" : "";
            $instructor_title = ($month_details_view) ? "Inst" : "Instructor";
            $output .= '<p class="preceptor-list ' . $additional_inst_class . '">' . $instructor_title . '';
            $output .= (count($instructors) > 1 && !$month_details_view) ? "s: " : ": ";
            $output .= implode(", ", $instructors);
            $output .= "</p>";
        }

        return $output;
    }

    public function preceptorOnlyList($preceptors, $month_details_view)
    {
        $output = "";
        if($preceptors){
            $additional_class = ($month_details_view) ? "month_details_preceptors" : "";
            $preceptor_title = ($month_details_view) ? "Pr" : "Preceptor";
            $output .= '<p class="preceptor-list ' . $additional_class . '">' . $preceptor_title . '';
            $output .= (count($preceptors) > 1 && !$month_details_view) ? "s: " : ": ";
            $output .= implode(", ", $preceptors);
            $output .= "</p>";
        }

        return $output;
    }

    /*
     * gets the html for displaying open weebles
     * @param int $assignmentCount the total number of assignments
     * @param int $slotCount the total number of slots
     * @param int $eventId the id of the event
     * @return string the html for displaying open weebles
    */
    public function getOpenWeebles($assignmentCount, $slotCount, $eventId, $hasOpenWindow, $has_active_window, $event_type, $max_to_show = 5)
    {
        $invisible = true;
        $output = '<div class="open-weebles">';
        $output .= ($assignmentCount > 0) ? "<div class='spacer'></div>" : "";

        if($hasOpenWindow && $has_active_window){
            $invisible = false;
        }

        if(!$this->current_user_data['student_scheduler_permissions'][$event_type]){
            $invisible = true;
        }

        // note this tag is not closed
        $imgSrc = ($invisible) ? "student-weeble-invisible" : "student-weeble-outline";
        $openWeebleImg = '<img class="open-weeble-img ' . $imgSrc . '" src="/images/icons/' . $imgSrc . '.svg"';

        $openSlots = $slotCount - $assignmentCount;
        $openSlotsToShow = ($openSlots > $max_to_show) ? $max_to_show : $openSlots;
        $plusSlots = $openSlots - $openSlotsToShow;

        $addS = (($openSlots != 1) && ($plusSlots > 0)) ? "s" : "";

        if($plusSlots > 0){
            $altVal = $openSlots . ' ';
        }

        $altVal .= 'open slot' . $addS;
        $altVal .= ($invisible) ? " (not available for signup)" : "";

        if($plusSlots > 0){
            $output .= $openWeebleImg . ' alt="' . $altVal . '" data-openSlot="openSlot-' . $eventId . '">';
            $output .= '<div class="plus-slots" data-toolTip="' .  $altVal . '"><p>x' . $openSlots . '</p></div>';
        }
        else {
            for($i = 0; $i < $openSlotsToShow; $i++){
                $output .= $openWeebleImg . ' alt="' . $altVal . '" data-openSlot="openSlot' . $i . '-' . $eventId . '">';
            }
        }

        $output .= ($this->view_type == "list") ? "<div class='clear'></div>" : "";

        $output .= "</div>";
        return $output;
    }

    public function separateByCompliance($site_id, $assignments, $past_event)
    {
        // we'll need to separate the red non-compliant weebles from the gray compliant weebles
        $compliant = array();
        $non_compliant = array();

        if (count($assignments) > 0) {
            foreach ($assignments as $assignment) {

                // if it's a student, only their weeble should appear red if they are out of compliance
                if ($this->current_user_data['role_name'] == "student") {
                    // if this is the users shift AND they either have global_site_compliant or compliant set to 0, add them to the non compliant list
                    if (($assignment['userContextId'] == $this->current_user_data['userContextId']) && (($assignment['global_site_compliant'] === 0) || ($assignment['compliant'] === 0))){
                        $is_compliant = false;
                        $modal_access = true;
                    } else {
                        $is_compliant = true;
                        $modal_access = false;
                    }
                } else {
                    // do we care only about global_site requirements?
                    // if this user is admin for this site and a student does not belong to them, we care about the casey state and only global requriements
                    $casey = (in_array($site_id, $this->current_user_data['admin_for'])) ? true : false;

                    // does this student belong to the current logged in user?
                    if($assignment['program_id'] == $this->current_user_data['program_id']){
                        $modal_access = true;
                        $is_compliant = (($assignment['global_site_compliant'] === 0) || ($assignment['compliant'] === 0)) ? false : true;
                    }
                    else {
                        $modal_access = $casey;
                        $is_compliant = ($assignment['global_site_compliant'] === 0) ? false : true;
                    }
                }

                // if this event is in the past, modal access is false
                if ($past_event) {
                    $modal_access = false;
                }

                $assignment['compliance_modal'] = $modal_access;
                $assignment['red'] = ($is_compliant) ? false : true;

                if($is_compliant){
                    $compliant[] = $assignment;
                }
                else {
                    $non_compliant[] = $assignment;
                }

            }
        }

        return array("compliant" => $compliant, "non_compliant" => $non_compliant);
    }

    /*
     * gets the html for displaying closed weebles AND the non-compliant weebles
     * @param array $assignments the assignments for this event (just has a name and cert level)
     * @param int $eventId the id of the event
     * @param int $site_id the id of the site that the event takes place
     * @return string the html for displaying closed weebles
    */
    public function getClosedWeebles($assignments, $eventId, $site_id, $past_event, $max_to_show = 5)
    {
        $output = '<div class="closed-weebles">';

        $assignments_by_compliance = $this->separateByCompliance($site_id, $assignments, $past_event);
        $compliant_weebles = $this->buildFilledWeebles("/images/icons/student-weeble.svg", "closed-weeble-img", $assignments_by_compliance['compliant'], "plus-slots", $eventId, $max_to_show);
        $non_compliant_weebles = $this->buildFilledWeebles("/images/icons/student-weeble-red.svg", "red-weeble-img", $assignments_by_compliance['non_compliant'], "plus-slots red-plus-slots", $eventId, $max_to_show);

        $output .= $compliant_weebles;
        $output .= (count($assignments_by_compliance['compliant']) > 0) ? "<div class='spacer'></div>" : "";
        $output .= $non_compliant_weebles;

        $output .= ($this->view_type == "list") ? "<div class='clear'></div>" : "";

        $output .= '</div>';

        $return_assignments = array_merge($assignments_by_compliance['compliant'], $assignments_by_compliance['non_compliant']);
        return array("output" => $output, "return_assignments" => $return_assignments);
    }

    public function buildFilledWeebles($img_src, $img_class, $assignments, $plus_slots_class, $eventId, $max_to_show)
    {
        $output = "";
        $closedWeebleImg = '<img class="' . $img_class. '" src="' . $img_src . '"';
        $totalAssignments = count($assignments);
        $filledSlotsToShow = ($totalAssignments > $max_to_show) ? $max_to_show : $totalAssignments;
        $plusSlots = $totalAssignments - $filledSlotsToShow;

        if($plusSlots > 0){

            // builds the tool tip
            $moreStudentNamesTooltip = "";
            $count = 0;
            for($i = 0; $i < $totalAssignments; $i++){
                $assignment = array_shift($assignments);
                $moreStudentNamesTooltip .= ($count != 0) ? "<br>" : "";
                $moreStudentNamesTooltip .= substr($assignment['name'], 0, -1);
                $count++;
            }

            $output .= $closedWeebleImg . ' alt="' . $moreStudentNamesTooltip . '" data-closedSlot="' . $eventId . '-closed-weeble-sum">';
            $output .= '<div class="' . $plus_slots_class . '" data-toolTip="' . $moreStudentNamesTooltip . '"><p>x' . $totalAssignments . '</p></div>';
        }
        else {
            for($i = 0; $i < $filledSlotsToShow; $i++){
                $assignment = array_shift($assignments);
                //$closedSlotName = str_replace("'", "", str_replace(' ', '', $assignment['name']));
                $output .= $closedWeebleImg . '" alt="' . $assignment['name'] . ' ' . $assignment['certLevel'] . '" data-closedSlot="' . $assignment['id'] . '">';
            }
        }


        return $output;
    }

    /*
     * adds the styles sheets need for the views
    */
    private function addStyles()
    {
        // LOOKING FOR WEEK/DAY/LIST/MONTH/DETAILS specific styles???
        // calendar-view.css is using @imports for those - you can thank IE for this lovely fix ;)
        if ($this->view) {
            $this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/calendar-view.css");
            $this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/get-filter-values.js");
        }
    }

    /*
     * sets up a bunch of flags/variables we'll use throughout the view
     */
    public function setCurrentUserData($user = null)
    {
        if ($user) {
            $this->user = $user;
        } else {
            $this->user = \Fisdap\Entity\User::getLoggedInUser();
        }
        $this->userContextId = $this->user->getCurrentUserContext()->id;
        $program = $this->user->getCurrentUserContext()->program;

        // the students scheduler permissions (about picking shifts)
        $program_settings = $program->program_settings;
        $lab = $program_settings->student_pick_lab;
        $field = $program_settings->student_pick_field;
        $clinical = $program_settings->student_pick_clinical;

        $student_scheduler_permissions = array("lab" => $lab, "clinical" => $clinical, "field" => $field);

        // the students shift limits (applicable only if they have limited scheduler)
        $lab_limit = false;
        $field_limit = false;
        $clinical_limit = false;

        if($this->user->getCurrentRoleName() == 'instructor'){
            $this->instructor = $this->user->getCurrentRoleData();

            $sc_lab = $this->instructor->hasPermission("Edit Lab Schedules");
            $sc_field = $this->instructor->hasPermission("Edit Field Schedules");
            $sc_clinical = $this->instructor->hasPermission("Edit Clinic Schedules");
            $schedule_permissions = array("lab" => $sc_lab, "clinical" => $sc_clinical, "field" => $sc_field);

            $sk_lab = $this->instructor->hasPermission("Edit Lab Data");
            $sk_field = $this->instructor->hasPermission("Edit Field Data");
            $sk_clinical = $this->instructor->hasPermission("Edit Clinical Data");
            $sk_view = $this->instructor->hasPermission("View All Data");
            $skills_tracker_permissions = array("lab" => $sk_lab, "clinical" => $sk_clinical, "field" => $sk_field, "view" => $sk_view);

            $admin_for = \Fisdap\EntityUtils::getRepository('SiteLegacy')->getAdminSites($program->id);
        }
        else {
            $this->student = true;
            $student_entity = $this->user->getCurrentRoleData();
            $has_skills_tracker = false;

            foreach($this->user->serial_numbers as $sn){
                if($sn->hasProductAccess(1) || $sn->hasProductAccess(10)){
                    $has_skills_tracker = true;
                }

                if($sn->hasProductAccess(11)) {
                    // this is limited scheduler - we need to pay attention to shift limits
                    $lab_limit = $student_entity->atLimit('lab');
                    $field_limit = $student_entity->atLimit('field');
                    $clinical_limit = $student_entity->atLimit('clinical');
                }
            }

            $this->has_skills_tracker = $has_skills_tracker;

            // can the student pick?
            $schedule_permissions = array("lab" => $lab, "clinical" => $clinical, "field" => $field);

            // can the student create?
            $create_lab = $program->get_can_students_create_lab();
            $create_clinical = $program->get_can_students_create_clinical();
            $create_field = $program->get_can_students_create_field();
            $skills_tracker_permissions = array("lab" => $create_lab, "clinical" => $create_clinical, "field" => $create_field);
        }

        $shift_limits = array("lab" => $lab_limit, "clinical" => $clinical_limit, "field" => $field_limit);

        $this->current_user_data = array("user" => $this->user,
            "program_id" => $program->id,
            "profession_name" => $program->profession->name,
            "profession_cert_count" => count($program->profession->certifications),
            "role_name" => $this->user->getCurrentRoleName(),
            "userContextId" => $this->userContextId,
            "view_other_students_schedules" => $program_settings->student_view_full_calendar,
            "has_skills_tracker" => $this->has_skills_tracker,
            "scheduler_permissions" => $schedule_permissions,
            "shift_limits" => $shift_limits,
            "admin_for" => $admin_for,
            "student_scheduler_permissions" => $student_scheduler_permissions,
            "skills_tracker_permissions" => $skills_tracker_permissions);
    }

    /*
     * returns a date attribute to be used on the calendar display div
     * @param string $time the text to describe the time on the attribute (month/day/year)
     * @param string $formatString the PHP date format
     * @param dateTime $date the dateTime object that will be fromatted
     * @return string
     */
    public function getDateAttrib($time, $formatString, $date)
    {
        if ($date) {
            $dateString = $date->format($formatString);
        } else {
            $dateString = "";
        }

        return 'data-' . $time . '="' . $dateString . '"';
    }

    /*
     * returns the end date attributes
     * @param dateTime $endDate the dateTime object that will be fromatted
     * @return string
    */
    public function getEndDateAttribs($endDate)
    {
        $output = $this->getDateAttrib("endMonth", "m", $endDate);
        $output .= $this->getDateAttrib("endDay", "j", $endDate);
        $output .= $this->getDateAttrib("endYear", "Y", $endDate);
        return $output;
    }

    /*
     * initializes the avail_certs_config array for this CalendarView entity
     * @param array $cert_ids the specfiied certification level ids to get the configurations for
    */
    public function getFiltersAvailCertsConfig($cert_ids)
    {
        $configs = array();
        foreach($cert_ids as $cert_id){
            $cert = \Fisdap\EntityUtils::getEntity("CertificationLevel", $cert_id);
            $configs[$cert_id] = $cert->bit_value;
        }

        $this->avail_certs_config = $configs;
        return $configs;
    }

    /*
     * gets the html for displaying hte days of the week at the top of hte calendar
     * @return string the html to be rendered
    */
    private function drawHeadings(){
        $returnContent = "";
        foreach($this->daysOfWeek as $day){
            $returnContent .= "<div class='day-name'>" . $day . "</div>";
        }
        return $returnContent;
    }

    /*
     * Exists for consistency sake!
     */
    public function getNoShiftsMsg()
    {
        return "<div class='no-shifts-msg'>No shifts. <div class='subtle-no-shifts-msg'>Either no shifts are scheduled or they have been filtered out of this view.</div></div>";
    }

}