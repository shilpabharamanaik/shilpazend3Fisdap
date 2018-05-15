<?php namespace Fisdap\Data\Event;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;
use Scheduler_View_Helper_CalendarView; //@todo someday refactor this since it will break in the standalone rest api
use Fisdap\Entity\SchedulerFilterSet;

/**
 * Class DoctrineEventLegacyRepository
 *
 * @package Fisdap\Data\Event
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineEventLegacyRepository extends DoctrineRepository implements EventLegacyRepository
{
    public $month_details_view = false;

    /**
     * @var Scheduler_View_Helper_CalendarView
     */
    public $cal_view_helper;

    /**
     * Given an array of event IDs, this function will returning an array of date time objects in order
     * @param $event_ids
     * @return array
     */
    public function getDatesByIds($event_ids)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("partial e.{id, start_datetime}")
            ->from('\Fisdap\Entity\EventLegacy', "e")
            ->andWhere($qb->expr()->in('e.id', $event_ids))
            ->orderBy("e.start_datetime, e.id");

        $res = $qb->getQuery()->getArrayResult();

        $dates = array();

        foreach ($res as $event_data) {
            $dates[] = $event_data['start_datetime'];
        }

        return $dates;
    }

    public function getAllByProgram($program_id, $offset = null, $limit = null, $preconversion = false, $unconvertedShifts = false)
    {
        $sql = "SELECT e.*, r.RelSelectType AS offset_type_start, r.ExpSelectType AS offset_type_end, ".
            "TIMESTAMP(e.StartDate, CONCAT(INSERT(LPAD(e.StartTime, 4, '0'), 3, 0, ':'), ':00')) AS start_datetime ".
            "FROM EventData e ".
            "LEFT JOIN RepeatInfo r ".
            "ON e.RepeatCode = r.Repeat_id ".
            "WHERE e.Program_id = $program_id ";

        if ($preconversion) {
            $sql .= " AND e.StartDate <= 2012-12-01 ";
        }
        if ($unconvertedShifts) {
            $sql .= " AND e.site_id IS NULL ";
        }

        if (is_numeric($offset) && is_numeric($limit)) {
            $sql .= " LIMIT $offset, $limit";
        }

        echo $sql . "\n";

        //$sql .= ($minId) ? " AND e.Event_id >= $minId" : "";
        //$sql .= ($maxId) ? " AND e.Event_id <= $maxId" : "";
        $db = \Zend_Registry::get('db');
        $arr = array();
        $res = $db->query($sql);
        $counter = 0;

        while ($row = $res->fetch()) {
            $arr[] = $row;
        }

        return $arr;
    }

    /**
     * @param $program_id
     *
     * @return bool
     * @throws \Zend_Exception
     * @todo DELETE THIS METHOD and the one page that uses it
     */
    public function programHasEvents($program_id)
    {
        $sql = "select * from EventData " .
            "where Program_id = " . $program_id . " limit 1";

        $db = \Zend_Registry::get('db');
        $arr = array();
        $res = $db->query($sql);
        $counter = 0;

        while ($row = $res->fetch()) {
            $arr[] = $row;
        }

        if ($arr) {
            return true;
        }

        return false;
    }

    /**
     * Get all events that are in the given series
     *
     * @param integer $series_id
     * @return array
     */
    public function getAllBySeries($series_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('e.start_datetime, e.id')
            ->from('\Fisdap\Entity\EventLegacy', 'e')
            ->where('e.series = ?1')
            ->setParameter(1, $series_id)
            ->orderBy('e.start_datetime');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param integer   $program
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param array     $filters
     * @param bool      $previously_shared
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getSharedEventIds($program, \DateTime $startDate, \DateTime $endDate, array $filters, $previously_shared = false)
    {
        //Format start and end dates into strings and set the end date to the end of the day
        $startDate = $startDate->format('Y-m-d H:i:s');
        $endDate = $endDate->format('Y-m-d 23:59:59');

        $sql = "SELECT e.Event_id from EventSharesData es left join EventData e on es.Event_id = e.Event_id where es.Receiving_Program_id = $program ";
        $sql .= ($startDate) ? " AND e.start_datetime > '$startDate'" : "";
        $sql .= ($endDate) ? " AND e.start_datetime < '$endDate'" : "";
        $sql .= ($previously_shared) ? " AND es.retired = 1" : " AND (es.retired is null OR es.retired = 0)";

        $conn = $this->_em->getConnection();
        $arr = array();
        $res = $conn->query($sql);

        while ($row = $res->fetch()) {
            $arr[] = $row['Event_id'];
        }

        return $arr;
    }

    /**
     * Get a single event fetch joined with other pieces
     *
     * @param integer $event_id
     * @return \Fisdap\Entity\EventLegacy
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getEvent($event_id)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("distinct partial e.{id, start_datetime, cert_levels}, partial p.{id}, partial es.{id}, partial s.{id}, partial rp.{id}, partial st.{id,name}")
            ->from('\Fisdap\Entity\EventLegacy', "e")
            ->join("e.program", "p")
            ->join("e.event_shares", "es")
            ->join("es.receiving_program", "rp")
            ->leftJoin("e.slots", "s")
            ->leftJoin("s.slot_type", "st")
            ->where("e.id = ?1")
            ->setParameter(1, $event_id);

        return $qb->getQuery()->getSingleResult();
    }

    public function getNetworkEvents($beta_scheduler, $site_id, $startDate)
    {
        if ($beta_scheduler) {
            $start = "start_datetime";
            $site = "site_id";
        } else {
            $start = "StartDate";
            $site = "AmbServ_id";
        }

        $sql = "SELECT DISTINCT E.Event_id ".
            "FROM EventData E, EventSharesData EV ".
            "WHERE E.$site = $site_id ".
            "AND E.Event_id = EV.Event_id ".
            "AND E.$start >= '$startDate' ".
            "ORDER BY $start";

        $conn = $this->_em->getConnection();
        $arr = array();
        $res = $conn->query($sql);

        while ($row = $res->fetch()) {
            $arr[] = $row['Event_id'];
        }

        return $arr;
    }

    /**
     * pretty much just for a conversion/update script
     *
     * @param $program_id
     * @return array
     */
    public function getWindowsByProgram($program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('w')
            ->from('\Fisdap\Entity\Window', 'w')
            ->where('w.program = ?1')
            ->setParameter(1, $program_id);

        return $qb->getQuery()->getResult();
    }

    public function getProgramEvents($beta_scheduler, $program_id, $site_id, $startDate)
    {
        if ($beta_scheduler) {
            $start = "start_datetime";
            $site = "site_id";
        } else {
            $start = "StartDate";
            $site = "AmbServ_id";
        }

        $sql = "SELECT Event_id ".
            "FROM EventData ".
            "WHERE $site = $site_id ".
            "AND Program_id = $program_id ".
            "AND $start >= '$startDate' ".
            "ORDER BY $start";

        $conn = $this->_em->getConnection();
        $arr = array();
        $res = $conn->query($sql);

        while ($row = $res->fetch()) {
            $arr[] = $row['Event_id'];
        }

        return $arr;
    }

    public function getStartDates($event_ids)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('partial e.{id,start_datetime}')
            ->from('Fisdap\Entity\EventLegacy', 'e')
            ->where($this->buildFiltersAndWhere("e.id", $event_ids));

        return $qb->getQuery()->getArrayResult();
    }

    public function getStudentAssignments($series_id)
    {
        $sql = "select group_concat(sa.user_role_id separator ',') as userContextIds";
        $sql .= " from fisdap2_slot_assignments sa";
        $sql .= " left join fisdap2_slots sl on sa.slot_id = sl.id";
        $sql .= " left join EventData ed on sl.event_id = ed.Event_id";
        $sql .= " where ed.series_id = " . $series_id . " and sl.slot_type_id = 1 group by ed.Event_id";

        $conn = $this->_em->getConnection();
        $arr = array();
        $res = $conn->query($sql);

        while ($row = $res->fetch()) {
            $arr[] = explode(",", $row['userContextIds']);
        }

        return $arr;
    }

    /**
     * @param $event_id
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getReceivingPrograms($event_id)
    {
        $sql = "SELECT es.Receiving_Program_id from EventSharesData es left join EventData e on es.Event_id = e.Event_id where e.Event_id = $event_id and es.retired is null";

        $conn = $this->_em->getConnection();
        $arr = array();
        $res = $conn->query($sql);

        while ($row = $res->fetch()) {
            $arr[] = $row['Receiving_Program_id'];
        }

        return $arr;
    }

    /**
     * Returns an un-parsed array of event-less shifts with lots of data.
     * Uses Doctrine's partial selectors to build a query, but the query is NOT run using doctrine.
     *
     * @param      $program   int the shift's owner's program id
     * @param      $startDate mixed the min start_datetime for the shifts
     * @param      $endDate   mixed the max start_datetime for the shifts
     * @param      $filters   array includes base_ids to filter the shifts
     *
     * @param null $limit limit the maximum results (used for pagination)
     * @param null $offset set the offset for when the results should start (used for pagination)
     *
     * @return array of un-parsed shift data
     */
    public function getQuickAddShifts($program, $startDate, $endDate = null, $filters = null, $limit = null, $offset = null)
    {
        $qb = $this->_em->createQueryBuilder();

        if ($endDate instanceof \DateTime) {
            $end_datetime = $endDate->setTime(23, 59, 59);
        } elseif ($endDate) {
            $end_datetime = new \DateTime($endDate);
            $end_datetime->setTime(23, 59, 59);
        } else {
            $end_datetime = null;
        }

        if ($startDate instanceof \DateTime) {
            $start_datetime = $startDate;
        } elseif ($startDate) {
            $start_datetime = new \DateTime($startDate);
        } else {
            $start_datetime = null;
        }

        $selectPartials  = 'partial s.{id,hours,start_datetime,end_datetime,type},';
        $selectPartials .= 'partial creator.{id},';
        $selectPartials .= 'partial role.{id,name},';
        $selectPartials .= 'partial site.{id,name},';
        $selectPartials .= 'partial base.{name, id},';
        $selectPartials .= 'partial pre.{id,first_name,last_name},';
        $selectPartials .= 'partial student.{id},';
        $selectPartials .= 'partial ur.{id},';
        $selectPartials .= 'partial sn.{id,configuration},';
        $selectPartials .= 'partial cert.{id,description},';
        $selectPartials .= 'partial user.{id, first_name, last_name}';


        $qb->select($selectPartials)
            ->from('Fisdap\Entity\ShiftLegacy', 's')
            ->leftJoin('s.site', 'site')
            ->leftJoin('s.student', 'student')
            ->leftJoin('student.user_context', 'ur')
            ->leftJoin('ur.certification_level', 'cert')
            ->leftJoin('ur.user', 'user')
            ->leftJoin('user.serial_numbers', 'sn')
            ->leftJoin('s.patients', 'patients')
            ->leftJoin('patients.preceptor', 'pre')
            ->leftJoin('s.base', 'base')
            ->leftJoin('s.creator', 'creator')
            ->leftJoin('creator.role', 'role')
            ->andWhere('student.program = ' . $program)
            ->andWhere('s.event_id = -1');

        if ($start_datetime) {
            $qb->andWhere('s.start_datetime >= ' . $start_datetime->format("'Y-m-d H:i:s'"));
        }
        if ($end_datetime) {
            $qb->andWhere('s.start_datetime <= ' . $end_datetime->format("'Y-m-d H:i:s'"));
        }

        if ($filters) {
            // the way the filter works: if there are any sites selected (and no specific bases) - all bases for that site will be included
            // so we just need to go through the bases. we dont ever need to step through sites
            if ($filters['bases'] && $filters['bases'] != "all") {
                $qb->andWhere($this->buildFiltersAndWhere("base.id", $filters['bases']));
            }
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Default clinical departments are not saved with nice names in the database,
     * this will convert them.
     *
     * @param string $name
     *
     * @return string
     */
    private function convertDefaultDepartment($name)
    {
        $defaults = array(
            "Anesthesia"	=> "Anesthesia",
            "Burn" 			=> "Burn Unit",
            "CCL" 			=> "Cardiac Cath. Lab",
            "CCU" 			=> "Cardiac Care Unit",
            "Clinic"		=> "Clinic",
            "ER" 			=> "ER",
            "ICU"			=> "ICU",
            "IVTeam"		=> "IV Team",
            "Labor"			=> "Labor & Delivery",
            "NICU"			=> "Neonatal ICU",
            "OR"			=> "OR",
            "PostOp"		=> "Post Op",
            "PreOp"			=> "Pre Op",
            "Psych"			=> "Psychiatric Unit",
            "Respiratory"	=> "Respiratory Therapy",
            "Triage"		=> "Triage",
            "Urgent"		=> "Urgent Care");

        return ($defaults[$name]) ? $defaults[$name] : $name;
    }

    /**
     * Take in the array hydrated events from Doctrine and assemble an array structure that Amy's
     * formatAndFilterEvents() method can use
     *
     * @param array $eventsToParse
     * @param array $quick_added_shifts
     * @param array $shared_event_ids
     * @param array $previously_shared_event_ids
     *
     *
     * @return array
     */
    public function getEventDataStructure(array &$eventsToParse, array &$quick_added_shifts, array $shared_event_ids, array $previously_shared_event_ids)
    {
        $events = array();
        $today = new \DateTime();
        $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
        $see_students_perms = array();
        $allRequestTypes = \Fisdap\Entity\RequestType::getAll(true);

        $site_admin_perms = array();

        foreach ($eventsToParse as $i => $event) {
            $formattedEventData = [];

            $formattedEventData['id'] 	          = $event['id'];
            $formattedEventData['site_name'] 	  = $event['site']['name'];
            $formattedEventData['site_id'] 	      = $event['site']['id'];
            $formattedEventData['series_id'] 	  = $event['series']['id'];
            $formattedEventData['base_name'] 	  = $this->convertDefaultDepartment($event['base']['name']);
            $formattedEventData['duration'] 	  = $event['duration'];

            $formattedEventData['start_datetime'] = $event['start_datetime'];
            $formattedEventData['end_datetime']   = $event['end_datetime'];
            $formattedEventData['event_name']     = $event['name'];
            $formattedEventData['event_type']     = $event['type'];
            $formattedEventData['notes'] 		  = $event['notes'];

            $formattedEventData['future'] 		  = $today < $event['start_datetime'] ? true : false;
            $formattedEventData['cert_config']	  = $event['cert_levels'];

            //Set up preceptors
            foreach ($event['preceptor_associations'] as $j => $preceptor) {
                $formattedEventData['preceptors'][$preceptor['preceptor']['id']] = $preceptor['preceptor']['id']['first_name'] . " " . $preceptor['preceptor']['last_name'];
                unset($event['preceptor_associations'][$j]);
            }

            //Can the student sign up
            $formattedEventData['students_can_sign_up'] = $this->cal_view_helper->current_user_data['student_scheduler_permissions'][$formattedEventData['event_type']];

            //Determine if we're dealing with a shared event
            $formattedEventData['shared_event'] 		   = (in_array($formattedEventData['id'], $shared_event_ids));
            $formattedEventData['previously_shared_event'] = (in_array($formattedEventData['id'], $previously_shared_event_ids));

            //Determine if the instructor viewing the calendar is a site admin
            if (!$this->cal_view_helper->isStudent && $formattedEventData['shared_event']) {

                // is this instructor a casey?
                if (!isset($site_admin_perms[$formattedEventData['site_id']])) {
                    $site_admin_perms[$formattedEventData['site_id']] = $program->isAdmin($formattedEventData['site_id']);
                }

                $formattedEventData['is_site_admin'] = $site_admin_perms[$formattedEventData['site_id']];
            }

            //Set up slots
            foreach ($event['slots'] as $k => $slot) {
                if ($slot['slot_type']['id'] == 1) {
                    foreach ($slot['assignments'] as $assignment) {
                        $see_students_names = true;
                        if ($formattedEventData['shared_event'] && ($assignment['user_context']['program']['id'] != $program->id)) {
                            if (!isset($see_students_perms[$event['site']['id']])) {
                                $see_students_perms[$event['site']['id']] = $program->seesSharedStudents($event['site']['id']);
                            }

                            $see_students_names = $see_students_perms[$event['site']['id']];
                        }

                        if ($this->month_details_view) {
                            $program_abbreviation = ($formattedEventData['shared_event']) ? $assignment['user_context']['program']['abbreviation'] . " - " : "";
                            $name = ($see_students_names) ? $program_abbreviation . $assignment['user_context']['user']['first_name'] . " " . $assignment['user_context']['user']['last_name'] . "," : $assignment['user_context']['program']['name'] . " student";
                            $cert = ($see_students_names) ? $assignment['user_context']['certification_level']['description'] : "";
                        } else {
                            $program_abbreviation = ($formattedEventData['shared_event']) ? " from " . $assignment['user_context']['program']['abbreviation'] : "";
                            $name = ($see_students_names) ? $assignment['user_context']['user']['first_name'] . " " . $assignment['user_context']['user']['last_name'] . "," : "Student from " . $assignment['user_context']['program']['name'];
                            $cert = ($see_students_names) ? $assignment['user_context']['certification_level']['description'] . $program_abbreviation : "";
                        }

                        $formattedAssignment = array();
                        $formattedAssignment['id'] = $assignment['id'];
                        $formattedAssignment['userContextId'] = $assignment['user_context']['id'];
                        $formattedAssignment['program_id'] = $assignment['user_context']['program']['id'];
                        $formattedAssignment['name'] = $name;
                        $formattedAssignment['certLevel'] = $cert;
                        $formattedAssignment['cert_id'] = $assignment['user_context']['certification_level']['id'];
                        $formattedAssignment['has_skills_tracker'] = (boolean)(($assignment['user_context']['user']['serial_numbers'][0]['configuration'] & 1) || ($assignment['user_context']['user']['serial_numbers'][0]['configuration'] & 4096));
                        $formattedAssignment['compliant'] = (is_null($assignment['compliant'])) ? null : intval($assignment['compliant']);
                        $formattedAssignment['global_site_compliant'] = (is_null($assignment['global_site_compliant'])) ? null : intval($assignment['global_site_compliant']);

                        $formattedEventData['slot_assignments'][$formattedAssignment['id']] = $formattedAssignment;
                        unset($event['slots'][$k]['assignments']);
                    }


                    $formattedEventData['slot_count'] = $slot['count'];
                    $formattedEventData['windows'] = $slot['windows'];
                } elseif ($slot['slot_type']['id'] == 2) {
                    //Loop over instructor slot assignments and build list of instructors
                    foreach ($slot['assignments'] as $assignment) {
                        $formattedEventData['instructors'][] = $assignment['user_context']['user']['first_name'] . " " . $assignment['user_context']['user']['last_name'];
                    }
                }
            }

            // swap/drop/cover permissions
            $request_perm_results = $this->cal_view_helper->getRequestPerms(
                $allRequestTypes,
                array(
                    'switch_needs_permission'=>$event['shared_preferences'][0]['student_can_switch'],
                    'student_can_switch'=>$event['shared_preferences'][0]['switch_needs_permission']),
                array(
                    'switch_needs_permission'=>$event['switch_needs_permission'],
                    'student_can_switch'=>$event['student_can_switch']
                )
            );
            $formattedEventData['request_perms'] = $request_perm_results['results'];
            $formattedEventData['show_change_request_btn'] = $request_perm_results['show_change_request_btn'];
            $formattedEventData['drop_swap_cover_display'] = $this->cal_view_helper->getDropSwapCoverDisplay($formattedEventData['request_perms']);


            //Now add the event to our return array
            $start = $formattedEventData['start_datetime'];
            $events[$start->format('Y')][$start->format('n')][$start->format('j')]['events'][$event['base']['id']][$event['id']] = $formattedEventData;

            //Remove processed event from doctrine array result set
            unset($eventsToParse[$i]);
            unset($formattedEventData);
        }

        foreach ($quick_added_shifts as $i => $quick_added_shift) {
            $formattedEventData = [];

            $creator_type = $quick_added_shift['creator']['role']['name'];

            $formattedEventData['shift_id'] 	   = $quick_added_shift['id'];
            $formattedEventData['quick_add_shift'] = true;
            $formattedEventData['slot_count'] 	   = 1;
            $formattedEventData['shared_event']    = false;

            $formattedEventData['student_creator']      = ($creator_type == "student") ? true : false;
            $formattedEventData['instructor_creator']   = ($creator_type == "instructor") ? true : false;

            $formattedEventData['students_can_sign_up']    = false;
            $formattedEventData['previously_shared_event'] = false;

            $formattedEventData['site_name'] 	  = $quick_added_shift['site']['name'];
            $formattedEventData['site_id'] 	      = $quick_added_shift['site']['id'];
            $formattedEventData['base_name'] 	  = $quick_added_shift['base']['name'];
            $formattedEventData['base_id'] 	      = $quick_added_shift['base']['id'];
            $formattedEventData['duration'] 	  = $quick_added_shift['hours'];

            $formattedEventData['start_datetime'] = $quick_added_shift['start_datetime'];
            $formattedEventData['end_datetime']   = $quick_added_shift['end_datetime'];
            $formattedEventData['event_type']     = $quick_added_shift['type'];

            $formattedEventData['future'] 		  = $today < $quick_added_shift['start_datetime'] ? true : false;

            //Make fake slot for quick add shift
            $assignment = [];
            $assignment['id'] = $quick_added_shift['id'];
            $assignment['userContextId'] = $quick_added_shift['student']['user_context']['id'];
            $assignment['program_id'] = $program->id;
            $assignment['name'] = $quick_added_shift['student']['user_context']['user']['first_name'] . " " . $quick_added_shift['student']['user_context']['user']['last_name'] . ",";
            $assignment['certLevel'] = $quick_added_shift['student']['user_context']['certification_level']['description'];
            $assignment['has_skills_tracker'] = (boolean)(($quick_added_shift['student']['user_context']['user']['serial_numbers'][0]['configuration'] & 1) || ($quick_added_shift['student']['user_context']['user']['serial_numbers'][0]['configuration'] & 4096));
            $assignment['compliant'] = null;
            $assignment['global_site_compliant'] = null;
            $formattedEventData['slot_assignments'][$assignment['id']] = $assignment;

            //Now add the event to our return array
            $start = $formattedEventData['start_datetime'];
            $events[$start->format('Y')][$start->format('n')][$start->format('j')]['events'][$formattedEventData['base_id']][$formattedEventData['shift_id']] = $formattedEventData;

            //Remove processed event from doctrine array result set
            unset($quick_added_shifts[$i]);
        }

        return $events;
    }

    /**
     * The second step of formatting/parsing windows. The data has all been collected at this point, and this
     * function completes formatting and determines if we have a 'match' for filters.
     *
     * @param $windows         array the partially-parsed window data keyed by $event_id
     * @param $event           array the individual event's array
     * @param $user_cert_level integer the current logged in user's certification level id
     * @param $user_groups     array an array of ClassSectionLegacy ids
     * @param $filters         array the standard filters array
     *
     * @return array with lots of window data
     */
    public function formatEventWindows(&$windows, &$event, $user_cert_level, $user_groups, &$filters)
    {
        $has_open_window   = false;
        $has_active_window = false;
        $user_can_see_one  = false;
        $window_match 	   = false;

        $active_window_count = 0;

        if ($windows) {
            foreach ($windows as $i => $window_data) {
                $status       = $this->cal_view_helper->getWindowStatus($window_data['start_date'], $window_data['end_date']);
                $who          = $this->cal_view_helper->getWindowWho($window_data['constraints']);
                $user_can_see = $this->cal_view_helper->canUserSeeWindow($status, $who, $user_cert_level, $user_groups, $window_data['active']);

                $window_data['who']   		 = $who['description'];
                $window_data['when'] 		 = $this->cal_view_helper->getWindowWhen($window_data['start_date'], $window_data['end_date']);
                $window_data['status'] 	     = $status;
                $window_data['user_can_see'] = $user_can_see;

                if ($status == "open") {
                    $has_open_window = true;
                }
                if ($user_can_see || !$this->cal_view_helper->student) {
                    $user_can_see_one = true;
                }

                if ($window_data['active'] && $user_can_see) {
                    $has_active_window = true;
                    $active_window_count++;
                }

                $additional_window_match_check = false;

                // if our filters for 'avail_certs' or 'avail_groups' are not 'all', we need to filter the event based on windows
                if ($filters['avail_certs'] != "all" || $filters['avail_groups'] != "all") {
                    $has_cert  = ($filters['avail_certs'] != "all") ? $this->cal_view_helper->isCertInWindow($who['certs'], $filters['avail_certs']) : true;
                    $has_group = ($filters['avail_groups'] != "all") ? $this->cal_view_helper->isGroupInWindow($who['groups'], $filters['avail_groups']) : true;

                    if ($has_cert && $has_group) {
                        $additional_window_match_check = true;
                    }
                } else {
                    $additional_window_match_check = true;
                }

                if ($additional_window_match_check) {
                    if ($filters['avail_open_window']) {
                        // we meet constraint requirements - is this window active and open?
                        if ($status == "open" && $window_data['active']) {
                            $window_match = true;
                        }
                    } else {
                        $window_match = true;
                    }
                }

                $event['has_active_window'] = $has_active_window;
                $event['user_can_see_a_window'] = $user_can_see_one;
                $event['active_window_count'] = $active_window_count;
                $event['has_open_window'] = $has_open_window;

                //replace the unformatted window data with the formatted window data
                $event['windows'][$i] = $window_data;
            }
        }

        return $window_match;
    }

    /**
     * Uses the CalendarView object to get the open/closed weebles html
     *
     * @param $event array the individual event's array
     * @return void
     */
    public function getWeebles(&$event)
    {
        $max_weebles_to_show = ($this->month_details_view) ? 1 : 5;
        $today = new \DateTime();
        $event_in_past = ($today > $event['start_datetime']) ? true : false;
        $closed_weeble_data = $this->cal_view_helper->getClosedWeebles($event['slot_assignments'], $event['id'], $event['site_id'], $event_in_past, $max_weebles_to_show);

        $event['closed_weebles'] = $closed_weeble_data['output'];
        $event['slot_assignments'] = $closed_weeble_data['return_assignments'];

        if ($this->cal_view_helper->student && !$event['has_open_window']) {
            $event['open_weebles'] = "";
        } else {
            $assignment_count = count($event['slot_assignments']);
            $event['open_weebles'] = $this->cal_view_helper->getOpenWeebles($assignment_count, $event['slot_count'], $event['id'], $event['has_open_window'], $event['has_active_window'], $event['event_type'], $max_weebles_to_show);
        }
    }

    /**
     * Determines if the event has a specified preceptor
     *
     * @param $event_data array the individual event's array
     * @param $preceptor_filters array of precetor ids
     *
     * @return bool $preceptor_match
     */
    public function filterByPreceptor(&$event_data, $preceptor_filters)
    {
        $preceptor_match = false;
        if ($preceptor_filters != "all") {
            if ($event_data['preceptors']) {
                foreach ($event_data['preceptors'] as $id => $name) {
                    if (in_array($id, $preceptor_filters)) {
                        $preceptor_match = true;
                    }
                }
            }
        } else {
            $preceptor_match = true;
        }

        return $preceptor_match;
    }

    /**
     * Determines if sign up is available for a particular event/shift based on a set of filters.
     *
     * @param $avail_open_window_filter bool the "hide events that are current invisible" flag - always true for student users
     * @param $window_match             bool did we have an open/active/qualifying window?
     * @param $event_data               array the individual event's array
     *
     * @param $avail_certs
     *
     * @return bool $sign_up_available
     */
    public function getSignUpAvailable($avail_open_window_filter, $window_match, &$event_data, $avail_certs)
    {
        $sign_up_available = false;

        // is there at least one open spot?
        $open_slot = ($event_data['open_slot_count'] > 0) ? true : false;

        // let's make sure our avail_certs fits the certifcation level bit configuration on an event level
        // in theory, there shouldn't be any windows that would allow any certifications outside of the event's configuration but it is actually possible,
        // so we're just going to double check before we continue.
        if ($avail_certs != "all") {
            $meets_event_config = false;
            foreach ($this->cal_view_helper->avail_certs_config as $config) {
                if ($config & $event_data['cert_config']) {
                    $meets_event_config = true;
                }
            }
        } else {
            $meets_event_config = true;
        }

        if ($avail_open_window_filter) {
            if ($window_match) {
                // is the global setting for student sign up on or off for this shift type?
                $settings_sign_up_on = $this->cal_view_helper->current_user_data['scheduler_permissions'][$event_data['event_type']];

                // what about our current users' shift limits? Have they reached their max? (will be false if instrcutor or unlimited account)
                $reached_shift_limit = $this->cal_view_helper->current_user_data['shift_limits'][$event_data['event_type']];

                if ($open_slot && $settings_sign_up_on && !$reached_shift_limit && $meets_event_config) {
                    $sign_up_available = true;
                }
            }
        } else {
            if ($window_match && $open_slot && $meets_event_config) {
                $sign_up_available = true;
            }
        }

        return $sign_up_available;
    }

    /**
     * Determines if there is a student assignment matching a given set of student ids
     * and if hte current logged in user is assigned to the event
     *
     * @param array $event the individual event's array
     * @param array $filters
     *
     * @return array ($has_matching_assignment/$user_is_attending/$event)
     */
    public function filterByAssignment(array &$event, array &$filters)
    {
        $has_matching_assignment = false;
        $user_is_attending = false;
        $student_filters = $filters['chosen_students'];

        if ($event['slot_assignments']) {
            foreach ($event['slot_assignments'] as $i => $assignment) {
                if ($assignment['userContextId'] == $this->cal_view_helper->current_user_data['userContextId']) {
                    $event['users_assignment_id'] = $assignment['id'];
                    $user_is_attending = true;
                }

                // The user is filtering by one of our student options.
                $filtering_by_certs = (is_array($filters['certs']));
                $filtering_by_groups = (is_array($filters['groups']));
                $filtering_by_grad_month = ($filters['gradMonth'] != "All months");
                $filtering_by_grad_year = ($filters['gradYear'] != "All years");

                if (is_array($student_filters) || ($filtering_by_certs || $filtering_by_groups || $filtering_by_grad_month || $filtering_by_grad_year)) {

                    // If they are filtering by JUST certification level, check that.
                    if ($filtering_by_certs && !$filtering_by_groups && !$filtering_by_grad_month && !$filtering_by_grad_year) {
                        if (in_array($assignment['cert_id'], $filters['certs'])) {
                            $has_matching_assignment = true;
                        }
                    } else {

                        // Otherwise, check by user role id (this means they are also filtering by student groups and/or graduation date)
                        if (is_array($student_filters)) {
                            if (in_array($assignment['userContextId'], $student_filters)) {
                                $has_matching_assignment = true;
                            }
                        } else {
                            $has_matching_assignment = true;
                        }
                    }
                } else {
                    $has_matching_assignment = true;
                }
            }
        }

        return array("has_matching_assignment"=>$has_matching_assignment, "user_is_attending"=>$user_is_attending);
    }

    /**
     * Determines if the event should be shown on the calendar
     *
     * @param $preceptor_match bool is the specified preceptor (if any) on the event
     * @param $show_chosen bool has the user filtered to show chosen shifts?
     * @param $show_avail bool has the user filtered to show available shifts?
     * @param $has_matching_assignment bool are at least the specified student ids (if any) on the event
     * @param $sign_up_available bool ... true if:
     * 			at least 1 active/open window with matching constraints
     * 			at least 1 open slot
     * 			global settings for this type of shift is turned on
     * 			user has not reached their shift limit
     *
     * @return bool $show_event
     */
    public function getShowEventBool($preceptor_match, $show_chosen, $show_avail, $has_matching_assignment, $sign_up_available)
    {
        $show_event = false;

        if ($preceptor_match) {

            // if we are looking for 'chosen_shifts'
            // an event must have a matching_assignment
            if ($show_chosen) {
                if ($has_matching_assignment) {
                    $show_event = true;
                }
            }

            if ($show_avail) {
                if ($sign_up_available) {
                    $show_event = true;
                }
            }
        }

        return $show_event;
    }

    /**
     * Initializes the "total_counts" array for a given day in our big events array
     * Increases the count for this events type
     *
     * @param $day_data   array the data for this day
     * @param $event_type string lab/clinical/field
     *
     * @return array $day_data
     */
    public function increaseDayTotalCounts(&$day_data, $event_type)
    {
        if (!$day_data['total_counts']) {
            $day_data['total_counts'] = array('lab' => array("available" => 0, "total" => 0),
                                              'clinical' => array("available" => 0, "total" => 0),
                                              'field' => array("available" => 0, "total" => 0));
        }

        $day_data['total_counts'][$event_type]['total']++;

        return $day_data;
    }

    /**
     * Initializes the "has_my_shift_today" flag for a given day in our big events array
     * Once it is set to true, it can't be set to false
     *
     * @param $day_data          array the data for this day
     * @param $user_is_attending bool is the current logged in student assigned to the shift?
     *
     * @return array $day_data
     */
    public function initHasMyShiftToday(&$day_data, $user_is_attending)
    {
        if (!isset($day_data['has_my_shift_today'])) {
            $day_data['has_my_shift_today'] = $user_is_attending;
        } else {
            if ($day_data['has_my_shift_today'] == false) {
                $day_data['has_my_shift_today'] = $user_is_attending;
            }
        }
    }

    /**
     * Finally, we have the majority of our data parsed.
     * At this point we can start filtering events and doing some
     * final formatting (data that needed to be completely collected/parsed can actually be formatted)
     *
     * @param $eventDataStructure array containing the events that we will filter and format
     * @param $filters array the standard event filters
     * @param $locations array place to store the locations of the given shifts
     *
     * @return mixed $events_data
     */
    public function formatAndFilterEvents(array &$eventDataStructure, array &$filters, array &$locations)
    {
        // set up some variables before we start looping
        if ($this->cal_view_helper->current_user_data['role_name'] == "student") {
            $student_group_repo = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
            $user_cert_level = $this->cal_view_helper->user->getCurrentUserContext()->certification_level->id;
            $user_groups = $student_group_repo->getProgramGroups($this->cal_view_helper->current_user_data["program_id"], null, $this->cal_view_helper->user->getCurrentRoleData()->id, true, true);

            $filters['avail_certs'] = array($user_cert_level);
            $filters['avail_groups'] = $user_groups;

            if (!isset($this->cal_view_helper->avail_certs_config)) {
                $this->cal_view_helper->avail_certs_config = $this->cal_view_helper->getFiltersAvailCertsConfig($filters['avail_certs']);
            }
        }

        // the keying gets real crazy here because of years/months/days/events/etc.
        foreach ($eventDataStructure as $year => $months) {
            foreach ($months as $month => $days) {
                foreach ($days as $day => $day_data) {
                    foreach ($day_data['events'] as $base_id => $base_events) {
                        if ($base_events) {
                            foreach ($base_events as $event_id => $event) {

                                // set the open slot count so we don't have to calculate in our views
                                $event['open_slot_count'] = $event['slot_count'] - count($event['slot_assignments']);

                                if ($event['open_slot_count'] < 0) {
                                    $event['open_slot_count'] = 0;
                                }

                                // filter by preceptor (if the user has specified preceptors, we'll see if they are on this shift)
                                $preceptor_match = $this->filterByPreceptor($event, $filters['preceptors']);

                                // if we don't have a preceptor match, there's no need to continue to do these calculations
                                if ($preceptor_match) {

                                    // format the windows data and find out if we have a match based on our filters
                                    if ($event['quick_add_shift']) {
                                        $sign_up_available = false;
                                    } else {
                                        $window_match = $this->formatEventWindows($event['windows'], $event, $user_cert_level, $user_groups, $filters);

                                        // find out if our current logged in user should see this event as 'available' based on their filters
                                        $sign_up_available = $this->getSignUpAvailable($filters['avail_open_window'], $window_match, $event, $filters['avail_certs']);
                                    }

                                    // now filter by student assignment based on the user's filters (will also populate the 'my_shift' field)


                                    $assignment_filter_results   = $this->filterByAssignment($event, $filters);
                                    $has_matching_assignment     = $assignment_filter_results['has_matching_assignment'];
                                    $user_is_attending           = $assignment_filter_results['user_is_attending'];


                                    // if this shift was available to the user, but they are already on it do not consider it 'available'
                                    if ($sign_up_available && $user_is_attending) {
                                        $sign_up_available = false;
                                    }
                                }

                                // we are filtering - make sure we should see this event based on all the info we just collected
                                $show_event = $this->getShowEventBool($preceptor_match, $filters["show_chosen"], $filters["show_avail"], $has_matching_assignment, $sign_up_available);

                                // if show event is good, continue formatting the data
                                if ($show_event) {

                                    // grab some html for consistent formatting across all views
                                    $start_time = $event['start_datetime']->format("Hi");
                                    $type = $event['event_type'];
                                    $site_name = $event['site_name'];
                                    $base_name = $event['base_name'];

                                    $event['sort_by'] = strtolower(preg_replace('/[^a-z0-9]+/i', '', $start_time . $type . $site_name . $base_name));
                                    $event['special_icons']  = $this->cal_view_helper->getSpecialIcon($event['quick_add_shift'], $event['shared_event'], $user_is_attending, $event['previously_shared_event'], $event['student_creator'], $event['instructor_creator']);
                                    $event['event_title'] = $this->cal_view_helper->getEventTitle($event, $event_id, $event['special_icons']);
                                    $event['start_time_duration_display'] = $this->cal_view_helper->getStartTimeDurationDisplay($event['duration'], $event['start_datetime']);
                                    $event['event_site_base_name'] = $this->cal_view_helper->getEventSiteBaseName($event);
                                    $event['preceptor_list'] = $this->cal_view_helper->getPreceptorList($event['preceptors'], $event['instructors'], $this->month_details_view);
                                    $this->getWeebles($event, $this->cal_view_helper);

                                    if ($this->month_details_view) {
                                        $event['preceptor_only_list'] = $this->cal_view_helper->preceptorOnlyList($event['preceptors'], true);
                                        $event['instructor_only_list'] = $this->cal_view_helper->instructorOnlyList($event['instructors'], true);
                                    }

                                    // set this events array to our 'new_event_data' array
                                    unset($eventDataStructure[$year][$month][$day]['events'][$base_id][$event_id]);
                                    $eventDataStructure[$year][$month][$day]['events'][$base_id][$event_id] = $event;

                                    // add to this day's available/total count and add the has my shift today flag
                                    $this->increaseDayTotalCounts($eventDataStructure[$year][$month][$day], $event['event_type']);
                                    $this->initHasMyShiftToday($eventDataStructure[$year][$month][$day], $user_is_attending);

                                    //Add this location to our array if it's not there, otherwise increment it
                                    if (array_key_exists($base_id, $locations)) {
                                        $locations[$base_id]['count']++;
                                    } else {
                                        $locations[$base_id] = array("description" => $site_name . " " . $base_name, "count" => 1);
                                    }
                                } else {
                                    // we shouldn't be seeing this event, unset it from our array
                                    unset($eventDataStructure[$year][$month][$day]['events'][$base_id][$event_id]);

                                    if (count($eventDataStructure[$year][$month][$day]['events'][$base_id]) == 0) {
                                        unset($eventDataStructure[$year][$month][$day]['events'][$base_id]);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Ah, the beast that begins it all.
     * Returns a filtered, formatted array that the Scheduler views will understand and be able to use.
     *
     * @param integer                            $program the current logged in useres program id
     * @param \DateTime                          $startDate the min start_datetime for the events/shifts we'll get
     * @param \DateTime                          $endDate \DateTime the max start_datetime for the events/shifts we'll get
     * @param null                               $filters the standard event filters
     * @param Scheduler_View_Helper_CalendarView $cal_view_helper calendar view helper that called this function
     * @param null                               $single_event_id used to return a single event, rather than a set of events
     * @param bool                               $month_details_view is the data being prepped for month details view
     *
     * @return array
     */
    public function getOptimizedEvents($program, \DateTime $startDate, \DateTime $endDate, $filters = null, \Scheduler_View_Helper_CalendarView $cal_view_helper, $single_event_id = null, $month_details_view = false)
    {
        //Insert the view helper into the repository class so that we can reference in one place
        $this->cal_view_helper = $cal_view_helper;

        //Insert this boolean into the repository class so that we can reference later
        $this->month_details_view = $month_details_view;

        //If we're not dealing with all certification, use the cal view helper to determine what certifications we are suppose to filter
        if ($filters['avail_certs'] != "all") {
            $this->cal_view_helper->avail_certs_config = $this->cal_view_helper->getFiltersAvailCertsConfig($filters['avail_certs']);
        }

        //Get the event ids of all shared shifts for this program
        $shared_event_ids = $this->getSharedEventIds($program, $startDate, $endDate, $filters);

        //I don't know what this is supposed to do
        $previously_shared_event_ids = $this->getSharedEventIds($program, $startDate, $endDate, $filters, true);

        //Either stick the single event we're supposed to look at, or merge the two shared events arrays into one to be passed to the monolithic query
        $ids = ($single_event_id) ? $single_event_id : array_merge($shared_event_ids, $previously_shared_event_ids);

        //Get all the events (fetch joined with other necessary pieces) and hydrate the results to an array
        $events = $this->getEventsWithSlotsAndWindows(array("program"=>$program, "startDate"=>$startDate, "endDate"=>$endDate, "filters"=>$filters, "ids"=>$ids), $single_event_id);

        //Get quick add shifts
        if (!$single_event_id) {
            $quick_add_shifts = $this->getQuickAddShifts($program, $startDate, $endDate, $filters);
        } else {
            $quick_add_shifts = [];
        }

        //Transpose the data structure returned from doctrine into a structure that the CalendarViewHelper can use to create the calendar
        $eventDataStructure = $this->getEventDataStructure($events, $quick_add_shifts, $shared_event_ids, $previously_shared_event_ids);

        //Create array to store all the sites in this set of events, passed by reference below
        $newLocations = array();
        $this->formatAndFilterEvents($eventDataStructure, $filters, $newLocations);

        return array("events" => $eventDataStructure, "locations" => $newLocations);
    }

    /**
     * Uses SQL's "IN" function to build an "and where" statement for various queries
     *
     * @param $selector string the name of the selector (ex. 'event_id' or 'base_id')
     * @param $options array an array of some values, most likely IDs
     *
     * @return string to include in an "and where" of a query
     */
    private function buildFiltersAndWhere($selector, $options)
    {
        $where = $selector . " IN (" . implode($options, ",") . ")";
        return $where;
    }

    /**
     * Get an array of array-hydrated events that have been fetch-joined with slots, windows, shifts etc.
     *
     * @param array $params array containing the various pieces of data to run this query
     * @param null  $single_event_id if not null, it's the ID of a single event to query
     * @param null  $limit if not null, it limits are max result set
     * @param null  $offset if not null, provides an offset for pagination
     *
     * @return array
     */
    public function getEventsWithSlotsAndWindows(array $params, $single_event_id = null, $limit = null, $offset = null)
    {
        $qb = $this->_em->createQueryBuilder();

        //Set the end date to include the whole day
        if ($params['endDate'] instanceof \DateTime) {
            $end_datetime = $params['endDate'];
            $end_datetime->setTime(23, 59, 59);
        } elseif (isset($params['endDate'])) {
            $end_datetime = new \DateTime($params['endDate']);
            $end_datetime->setTime(23, 59, 59);
        } else {
            $end_datetime = null;
        }

        if ($params['startDate'] instanceof \DateTime) {
            $start_datetime = $params['startDate'];
        } elseif (isset($params['startDate'])) {
            $start_datetime = new \DateTime($params['startDate']);
        } else {
            $start_datetime = null;
        }

        $selectPartials  = 'partial e.{id,name,duration,start_datetime,end_datetime,type,notes,student_can_switch,switch_needs_permission,cert_levels},';
        $selectPartials .= 'partial sp.{id,student_can_switch,switch_needs_permission},';
        $selectPartials .= 'partial series.{id},';
        $selectPartials .= 'partial s.{id,name},';
        $selectPartials .= 'partial sppro.{id},';
        $selectPartials .= 'partial b.{name, id},';
        $selectPartials .= 'partial psa.{id, active},';
        $selectPartials .= 'partial pba.{id, active},';
        $selectPartials .= 'partial pa.{id},';
        $selectPartials .= 'partial pre.{id,first_name,last_name},';
        $selectPartials .= 'partial pree.{id,first_name,last_name},';
        $selectPartials .= 'partial sl.{id,count},';
        $selectPartials .= 'partial slot_type.{id,name},';
        $selectPartials .= 'partial sla.{id,compliant,global_site_compliant},';
        $selectPartials .= 'partial ur.{id},';
        $selectPartials .= 'partial assignment_program.{id,name,abbreviation},';
        $selectPartials .= 'partial sn.{id,configuration},';
        $selectPartials .= 'partial cert.{id,description},';
        $selectPartials .= 'partial u.{id, first_name, last_name},';
        $selectPartials .= 'partial w.{id,start_date,end_date,active},';
        $selectPartials .= 'partial wc.{id},';
        $selectPartials .= 'partial cv.{id, value, description},';
        $selectPartials .= 'partial ct.{id, entity_name}';

        $qb->select($selectPartials)
            ->from('Fisdap\Entity\EventLegacy', 'e')
            ->leftJoin('e.series', 'series')
            ->leftJoin('e.site', 's')
            ->leftJoin('s.program_site_associations', 'psa')
            ->leftJoin('e.base', 'b')
            ->leftJoin('e.shared_preferences', 'sp', 'WITH', 'sp.program = ' . $params['program'])
            ->leftJoin('sp.program', 'sppro')
            ->leftJoin('b.program_base_associations', 'pba')
            ->leftJoin('e.preceptor_associations', 'pa')
            ->leftJoin('pa.preceptor', 'pre')
            ->leftJoin('pa.preceptor', 'pree')
            ->leftJoin('e.slots', 'sl')
            ->leftJoin('sl.slot_type', 'slot_type')
            ->leftJoin('sl.assignments', 'sla')
            ->leftJoin('sla.user_context', 'ur')
            ->leftJoin('ur.user', 'u')
            ->leftJoin('ur.program', 'assignment_program')
            ->leftJoin('u.serial_numbers', 'sn')
            ->leftJoin('ur.certification_level', 'cert')
            ->leftJoin('sl.windows', 'w', 'WITH', 'w.program = ' . $params['program'])
            ->leftJoin('w.constraints', 'wc')
            ->leftJoin('wc.values', 'cv')
            ->leftJoin('wc.constraint_type', 'ct');

        if (is_null($single_event_id)) {
            $qb->andWhere('psa.active = true')
                ->andWhere('psa.program = ' . $params['program'])
                ->andWhere('pba.active = true')
                ->andWhere('pba.program = ' . $params['program']);

            if ($start_datetime) {
                $qb->andWhere('e.start_datetime >= ' . $start_datetime->format("'Y-m-d'"));
            }

            $complete_and_where = '(e.program = ' . $params['program'];
            $complete_and_where .= $end_datetime ? ' AND e.start_datetime < ' . $end_datetime->format("'Y-m-d H:i:s'") . ')' : ')';
        }

        if (!is_null($params['ids']) && count($params['ids']) > 0) {
            $complete_and_where .= (is_null($single_event_id)) ? ' OR ' : '';
            $complete_and_where .=  '(' . $this->buildFiltersAndWhere("e.id", $params['ids']) . ')';
        }

        $qb->andWhere($complete_and_where);

        //$qb->andWhere('sl.slot_type = 2');

        if (isset($params['filters']) && is_null($single_event_id)) {
            $filters = $params['filters'];
            // the way the filter works: if there are any sites selected (and no specific bases) - all bases for that site will be included
            // so we just need to go through the bases. we dont ever need to step through sites
            if ($filters['bases'] && $filters['bases'] != "all") {
                $qb->andWhere($this->buildFiltersAndWhere("b.id", $filters['bases']));
            }
        }

        $qb->orderBy('e.start_datetime');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Given an event, get all user_context ids assigned to that event
     *
     * @param $event_id
     *
     * @return array of user_context ids
     */
    public function getAssignedUsersByEventOptimized($event_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('ur.id')
            ->from('\Fisdap\Entity\SlotAssignment', 'a')
            ->join('a.slot', 's')
            ->join('a.user_context', 'ur')
            ->where('s.event = ?1')
            ->setParameter(1, $event_id);

        $results = $qb->getQuery()->getArrayResult();
        $users = array();
        foreach ($results as $result) {
            $users[] = $result['id'];
        }
        return $users;
    }


    /**
     * @inheritdoc
     */
    public function getUpcomingEventsByUserContextId($userContextId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("e.id as event_id, MIN(e.start_datetime) as start_datetime, site.id as site_id")
            ->from('\Fisdap\Entity\SlotAssignment', 'a')
            ->join('a.slot', 's')
            ->join('s.event', 'e')
            ->join('e.site', 'site')
            ->where('a.user_context = ?1')
            ->andWhere('e.start_datetime >= ?2')
            ->setParameter(1, $userContextId)
            ->setParameter(2, date_create("now")->format("Y-m-d H:i:s"))
            ->groupBy('site.id');

        $events = $qb->getQuery()->getArrayResult();

        $eventsBySite = [];

        foreach ($events as $event) {
            $eventsBySite[$event['site_id']] = $event;
        }

        return $eventsBySite;
    }

    /**
     * @param $userContextId
     * @param $start \DateTime
     * @param $end   \DateTime
     *
     * @internal param $student_id
     * @return array
     */
    public function getStudentShifts($userContextId, $start, $end)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("e.name as event_name, s.type as event_type, site.name as site_name, base.name as base_name, site.id as site_id, base.id as base_id, e.id as event_id, s.id as shift_id, e.notes, s.start_datetime, s.end_datetime")
            ->from('\Fisdap\Entity\ShiftLegacy', 's')
            ->join('s.site', 'site')
            ->join('s.base', 'base')
            ->join('s.student', 'stu')
            ->leftJoin('s.slot_assignment', 'sa')
            ->leftJoin('sa.slot', 'slot')
            ->leftJoin('slot.event', 'e')
            ->where('stu.user_context = ?1')
            ->andWhere('s.start_datetime >= ?2')
            ->andWhere('s.end_datetime <= ?3')
            ->setParameters(array(1 => $userContextId, 2 => $start, 3 => $end->setTime(23, 59, 59)));

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * @param integer   $program_id
     * @param \DateTime $start
     * @param \DateTime $end
     * @param array     $filters
     *
     * @return array
     */
    public function getStudentEventsByProgram($program_id, \DateTime $start, \DateTime $end, array $filters = [])
    {
        $qb = $this->_em->createQueryBuilder();

        $site_ids = SchedulerFilterSet::getSiteIdsFromFilters($program_id, $filters);

        //Get the shared events
        $shared_event_ids = $this->getSharedEventIds($program_id, $start, $end, $filters);

        $qb->select('partial e.{id, name, type, notes, start_datetime, end_datetime}, partial site.{id, name}, partial base.{id, name}, partial slot.{id}, partial assignment.{id}, partial ur.{id}, partial u.{id, first_name, last_name}, partial pa.{id}, partial preceptor.{id, first_name, last_name}, partial c.{id,name,description}, partial p.{id, name}')
            ->from('\Fisdap\Entity\EventLegacy', 'e')
            ->join('e.site', 'site')
            ->join('e.base', 'base')
            ->join('e.slots', 'slot', 'WITH', 'slot.slot_type = 1')
            ->join('slot.assignments', 'assignment')
            ->join('assignment.user_context', 'ur')
            ->join('ur.user', 'u')
            ->join('ur.certification_level', 'c')
            ->join('ur.program', 'p')
            ->join('site.program_site_associations', 'psa', 'WITH', 'psa.program = ' . $program_id)
            ->join('base.program_base_associations', 'bsa', 'WITH', 'bsa.program = ' . $program_id)
            ->leftJoin('e.preceptor_associations', 'pa')
            ->leftJoin('pa.preceptor', 'preceptor')
            ->andWhere("e.start_datetime >= '" . $start->format('Y-m-d') . "'")
            ->andWhere("e.start_datetime <= '" . $end->format('Y-m-d 23:59:59') . "'")
            ->andWhere('psa.active = 1')
            ->andWhere('bsa.active = 1');

        //Let's start filtering!!
        //Start with sites
        $qb->andWhere($qb->expr()->in('e.site', $site_ids));

        //Only filter by base if they didn't choose "All" and the user actually selected a particular base
        if ($filters['bases'] != 'all' && $filters['bases_selected_by_user'] == 1) {
            $qb->andWhere($qb->expr()->in('e.base', $filters['bases']));
        }

        //Now preceptors
        if (isset($filters['preceptors']) && $filters['preceptors'] != 'all') {
            $qb->andWhere($qb->expr()->in('pa.preceptor', $filters['preceptors']));
        }

        //Now the fun part, filtering by students
        if ($filters['chosen_students'] != 'all') {
            $userContextIds = SchedulerFilterSet::getStudentUserContextIdsFromFilters($program_id, $filters);
            $qb->andWhere($qb->expr()->in('assignment.user_context', $userContextIds));
        } elseif (!empty($filters['certs'])) {
            //We now know that they didn't choose any grad date, group or specific set of students
            //So we can filter by cert level
            $qb->andWhere($qb->expr()->in('ur.certification_level', $filters['certs']));
        }

        if (!empty($shared_event_ids)) {
            $qb->andWhere($qb->expr()->orX('e.program = ' . $program_id, $qb->expr()->in('e.id', $shared_event_ids)));
        } else {
            $qb->andWhere('e.program = ' . $program_id);
        }

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Get an array of preceptor names assigned to a given event
     *
     * @param $event_id
     *
     * @return array
     */
    public function getEventPreceptorList($event_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("pre.first_name, pre.last_name")
            ->from('\Fisdap\Entity\EventPreceptorLegacy', 'e')
            ->join('e.preceptor', 'pre')
            ->where('e.event = ?1')
            ->setParameter(1, $event_id);

        $results = $qb->getQuery()->getArrayResult();
        $returnArray = array();
        foreach ($results as $result) {
            $returnArray[] = $result['first_name'] . " " . $result['last_name'];
        }

        return $returnArray;
    }

    /**
     * Get an array of instructor names assigned to a given event
     *
     * @param $event_id
     *
     * @return array
     */
    public function getEventInstructorList($event_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("u.first_name, u.last_name")
            ->from('\Fisdap\Entity\Slot', 's')
            ->join('s.assignments', 'sa')
            ->join('sa.user_context', 'ur')
            ->join('ur.user', 'u')
            ->where('s.event = ?1')
            ->andWhere('s.slot_type = 2')
            ->setParameter(1, $event_id);

        $results = $qb->getQuery()->getArrayResult();
        $returnArray = array();
        foreach ($results as $result) {
            $returnArray[] = $result['first_name'] . " " . $result['last_name'];
        }

        return $returnArray;
    }

    /**
     * Returns an array-hydrated result set from Doctrine
     *
     * @param      $program_id   int the shift's owner's program id
     * @param      $startDate mixed the min start_datetime for the shifts
     * @param      $endDate   mixed the max start_datetime for the shifts
     * @param      $filters   array includes base_ids to filter the shifts
     *
     * @param null $limit limit the maximum results (used for pagination)
     * @param null $offset set the offset for when the results should start (used for pagination)
     *
     * @return array of un-parsed shift data
     */
    public function getStudentQuickAddShiftsByProgram($program_id, $startDate, $endDate = null, $filters = null, $limit = null, $offset = null)
    {
        $qb = $this->_em->createQueryBuilder();

        if ($endDate instanceof \DateTime) {
            $end_datetime = $endDate->setTime(23, 59, 59);
        } elseif ($endDate) {
            $end_datetime = new \DateTime($endDate);
            $end_datetime->setTime(23, 59, 59);
        } else {
            $end_datetime = null;
        }

        if ($startDate instanceof \DateTime) {
            $start_datetime = $startDate;
        } elseif ($startDate) {
            $start_datetime = new \DateTime($startDate);
        } else {
            $start_datetime = null;
        }

        $selectPartials  = 'partial s.{id,hours,start_datetime,end_datetime,type},';
        $selectPartials .= 'partial creator.{id},';
        $selectPartials .= 'partial role.{id,name},';
        $selectPartials .= 'partial site.{id,name},';
        $selectPartials .= 'partial base.{name, id},';
        $selectPartials .= 'partial pre.{id,first_name,last_name},';
        $selectPartials .= 'partial student.{id},';
        $selectPartials .= 'partial ur.{id},';
        $selectPartials .= 'partial sn.{id,configuration},';
        $selectPartials .= 'partial cert.{id,description},';
        $selectPartials .= 'partial user.{id, first_name, last_name}';


        $qb->select($selectPartials)
            ->from('Fisdap\Entity\ShiftLegacy', 's')
            ->join('s.site', 'site')
            ->join('s.student', 'student')
            ->join('student.user_context', 'ur')
            ->leftJoin('ur.certification_level', 'cert')
            ->join('ur.user', 'user')
            ->leftJoin('user.serial_numbers', 'sn')
            ->leftJoin('s.patients', 'patients')
            ->leftJoin('patients.preceptor', 'pre')
            ->join('s.base', 'base')
            ->leftJoin('s.creator', 'creator')
            ->leftJoin('creator.role', 'role')
            ->join('site.program_site_associations', 'psa', 'WITH', 'psa.program = ' . $program_id)
            ->join('base.program_base_associations', 'bsa', 'WITH', 'bsa.program = ' . $program_id)
            ->andWhere('student.program = ' . $program_id)
            ->andWhere('s.event_id = -1')
            ->andWhere('psa.active = 1')
            ->andWhere('bsa.active = 1');

        if ($start_datetime) {
            $qb->andWhere('s.start_datetime >= ' . $start_datetime->format("'Y-m-d H:i:s'"));
        }
        if ($end_datetime) {
            $qb->andWhere('s.start_datetime <= ' . $end_datetime->format("'Y-m-d H:i:s'"));
        }

        if ($filters) {
            $site_ids = SchedulerFilterSet::getSiteIdsFromFilters($program_id, $filters);
            $qb->andWhere($qb->expr()->in('site.id', $site_ids));

            //Now filter by only if they're not all and they haven't
            if ($filters['bases'] && $filters['bases'] != "all") {
                $qb->andWhere($this->buildFiltersAndWhere("base.id", $filters['bases']));
            }

            //Check for chosen students
            if ($filters['chosen_students'] && $filters['chosen_students'] != "all") {
                $userContextIds = SchedulerFilterSet::getStudentUserContextIdsFromFilters($program_id, $filters);
                $qb->andWhere($this->buildFiltersAndWhere("ur.id", $userContextIds));
            }
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getArrayResult();
    }
}
