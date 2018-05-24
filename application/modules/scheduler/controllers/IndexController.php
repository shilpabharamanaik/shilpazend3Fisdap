<?php

use Fisdap\Data\Shift\ShiftRequestRepository;
use Fisdap\Entity\User;
use Fisdap\Members\Scheduler\Http\LimitFilters;
use Fisdap\Members\Scheduler\SchedulerHelper;


class Scheduler_IndexController extends Fisdap_Controller_Private
{
	use LimitFilters;


	/**
	 * @var Zend_Session_Namespace
	 */
	private $session;


	public function init()
    {
		parent::init();
		
		// redirect to login if the user is not logged in yet
		if (!$this->user) {
		    return;
		}
		
		$this->session = new \Zend_Session_Namespace("Scheduler");
		
		if ($this->user->getCurrentProgram()->scheduler_beta == 0 && !$this->_getParam("beta")) {
			if($this->_getParam('action') == "index"){
				$this->_redirect($this->user->getCurrentProgram()->getSchedulerUrl(), array("exit" => true));
			}
		}

        if($this->user->getCurrentRoleName() == "student" && $this->user->getCurrentRoleData()->isGraduated()){
            if($this->_getParam('action') == "index"){
                $this->_redirect("/scheduler/index/graduated");
            }
        }
    }
	
	public function joinBetaAction()
	{
		$this->view->pageTitle = "We're sorry.";
	}
        
    public function graduatedAction(){
        $this->view->pageTitle = "You've graduated!";
    }

    public function indexAction()
    {
		$this->view->pageTitle = "Scheduler";
		
		// Browser checking
		$device = Zend_Registry::get('device');
		$browser = $device->getBrowser();
		$version = intval($device->getBrowserVersion());
		$this->view->bad_browser = false;

		if ($browser == 'Internet Explorer' && $version < 9) {
			$this->view->bad_browser = true;
		}
		
		$user = User::getLoggedInUser();
		$filters = $this->getFilterSet($this->_getParam("userContextId"));
		$calendarViewType = $this->getCalendarViewType($this->_getParam("userContextId"));
		$this->view->isStudent = ($user->getCurrentRoleName() == 'instructor') ? false : true;
		
		if($calendarViewType != "month" && $calendarViewType != "month-details"){
			$previous_month_view_type = ($this->session->month_view_type) ? $this->session->month_view_type : "month";
		}
		else {
			$previous_month_view_type = $calendarViewType;
		}
		
		// Grab start and end dates from the session otherwise, default to today and null
		$startDate = isset($this->session->calendarStartDate) ? $this->session->calendarStartDate : new \DateTime();
		$endDate = isset($this->session->calendarEndDate) ? $this->session->calendarEndDate : null;
		
		$this->checkPermissions($this->view->isStudent, $user);
		$this->view->permissions = $this->getPermissionsForViews($this->view->isStudent, $user);
		
		if($this->view->isStudent){
			$this->view->studentSignupModal = $this->view->studentSignupModal();
            $calendarFeed = \Fisdap\EntityUtils::getRepository("CalendarFeed")->findOneBy(array("user_context" => $user->getCurrentUserContext()));
			$student_group_repo = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
			$student_id = $user->getCurrentRoleData()->id;
			$groups = $student_group_repo->getProgramGroups($user->getProgramId(), null, $student_id, true, true);
            $studentData = $user->getCurrentRoleData();
			$this->view->preset_data = array(
					"cert_id" => $user->getCurrentUserContext()->certification_level->id,
					"userContextId" => $user->getCurrentUserContext()->id,
					"group_ids" => implode(",",$groups),
					"student_id" => $student_id,
					"max_clinical" => $studentData->clinical_shift_limit,
					"max_field" => $studentData->field_shift_limit,
            );
		}
		else {
			$this->view->shiftHistoryModal = $this->view->shiftHistoryModal();
			$this->view->studentDropModal = $this->view->studentDropModal();
			$this->view->assignModal = new Scheduler_Form_AssignModal();
		}

        $this->view->calendarSubscriptionModal = new \Scheduler_Form_CalendarSubscriptionModal($calendarFeed->id);
        $this->view->calendarControls = $this->view->calendarControls($calendarViewType, $startDate, $previous_month_view_type);
		$this->view->calendar = $this->view->calendarView($calendarViewType, $startDate, $endDate, $filters, false);
		$this->view->filters = new Scheduler_Form_CalDisplayFilters($filters);
		
		$this->getModals($this->view);
		$schedulePdfHelper = new SchedulerHelper();
		$schedulePdfHelper->addExtraFiles($this->view);
		$this->addExtraFiles($this->view);
		
		$this->view->tour = new Fisdap_View_Helper_GuidedTourHelper();
		$this->view->tour_id = ($this->view->isStudent) ? 7 : 2;
		
    }

	private function getCalendarViewType($userContextId)
	{
		if (isset($userContextId))
		{
			return "week";
		}
		if (isset($this->session->filterSet)) {
			$filterSet = \Fisdap\EntityUtils::getEntity("SchedulerFilterSet", $this->session->filterSet);
			return $filterSet->view_type->name;
		}

		if ($filterSet = $this->getFilterSetFromDatabase()) {
			return $filterSet->view_type->name;
		}

		return "week";
	}

	public function getFilterSet($userContextId)
	{
		
		if (isset($userContextId))
		{
			return array("sites" => "all",
							 "bases" => "all",
							 "preceptors" => "all",
							 "show_avail" => 0,
							 "avail_certs" => "all",
							 "avail_groups" => "all",
							 "avail_open_window" => true,
							 "show_chosen" => 1,
							 "chosen_students" => array($userContextId));
		}
		
		$user = User::getLoggedInUser();
		$filterSet = \Fisdap\EntityUtils::getEntity("SchedulerFilterSet", $this->session->filterSet);
		
		if ($filterSet->id) {return $filterSet->filters;}
		if ($filterSet = $this->getFilterSetFromDatabase()) {return $filterSet->filters;}
		
		if($user->getCurrentRoleName() == 'instructor'){
			$filters = array("sites" => "all",
							 "bases" => "all",
							 "preceptors" => "all",
							 "show_avail" => 1,
							 "avail_certs" => "all",
							 "avail_groups" => "all",
							 "avail_open_window" => false,
							 "show_chosen" => 1,
							 "chosen_students" => "all");
		}
		else {
			$student_group_repo = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
			$userGroups = $student_group_repo->getProgramGroups($user->getProgramId(), null, $user->getCurrentRoleData()->id, true, true);
			$filters = array("sites" => "all",
							 "bases" => "all",
							 "preceptors" => "all",
							 "show_avail" => 1,
							 "avail_certs" => array($user->getCurrentUserContext()->certification_level->id),
							 "avail_groups" => $userGroups,
							 "avail_open_window" => true,
							 "show_chosen" => 1,
							 "chosen_students" => array(User::getLoggedInUser()->getCurrentUserContext()->id));
		}
		
		return $filters;
	}
	
	public function getModals($view)
	{
		$view->shiftDeleteModal = $this->view->shiftDeleteModal();
		$view->eventDeleteModal = new Scheduler_Form_EventDeleteModal();
		$whichEventsModal = new Scheduler_View_Helper_WhichEventsInSeriesModal();
		$view->whichEventsModal = $whichEventsModal->whichEventsModal($this->view);
		$view->shiftRequestModal = new Scheduler_Form_ShiftRequestModal();
		$view->studentShiftModal = new SkillsTracker_Form_Shift();
		$view->viewComplianceModal = $this->view->viewComplianceModal();
		$view->schedulerPdfModal = new Scheduler_Form_PdfExportModal();
		$view->displayOptionsModal = new Scheduler_Form_DisplayOptionsModal();
	}
	
	public function addExtraFiles($view)
	{
		$this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/modal-imports.css");
		//$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/calendar-js-includes.js");
		
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/student-presets.js");
		$view->headScript()->appendFile("/js/library/Scheduler/View/Helper/shift-assign-multistudent-picklist.js");
		$view->headScript()->appendFile("/js/library/Scheduler/View/Helper/multipick-cal.js");
		$view->headScript()->appendFile("/js/jquery.busyRobot.js");
		$view->headLink()->appendStylesheet("/css/jquery.busyRobot.css");
		$this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/navbar-menu.css");
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/navbar-menu.js");
	}

	public function getFilterSetFromDatabase()
	{
		$filterSet = \Fisdap\EntityUtils::getRepository("SchedulerFilterSet")->findOneBy(array("user_context" => $this->user->getCurrentUserContext()->id, "active" => 1));
		$this->session->filterSet = $filterSet->id;

		return $filterSet;
	}

	public function getCalendarAction()
	{
		$month = $this->_getParam('month');
		$year = $this->_getParam('year');
		$day = $this->_getParam('day');
		$endMonth = $this->_getParam('endMonth');
		$endYear = $this->_getParam('endYear');
		$endDay = $this->_getParam('endDay');
		$type = $this->_getParam('type');
		
		$filters = $this->getParam("filters");
		
		// make sure the filters are limited for students with different permissions
		$filters = $this->limitFilters($filters);
		
		//See if the filters have changed, if so, save them
		if (isset($this->session->filterSet)) {
			$filterSet = \Fisdap\EntityUtils::getEntity("SchedulerFilterSet", $this->session->filterSet);
			if ($filters != $filterSet->filters || $type != $filterSet->view_type->name) {
				$filterSet->filters = $filters;
				$filterSet->setViewTypeByName($type);
				$filterSet->save();
			}
		} else {
			$filterSet = \Fisdap\EntityUtils::getEntity("SchedulerFilterSet");
			$filterSet->user_context = $this->user->getCurrentUserContext();
			$filterSet->setViewTypeByName($type);
			$filterSet->filters = $filters;
			$filterSet->save();
			$this->session->filterSet = $filterSet->id;
		}
		
		$date = new DateTime($year . "/" . $month . "/" . $day);
			
		if($endYear && $endDay && $endMonth){
			$endDate = new DateTime($endYear . "/" . $endMonth . "/" . $endDay);
		}
		else {
			$endDate = null;
		}
		
		//Save the start and end date in the session
		$this->session->calendarStartDate = $date;
		$this->session->calendarEndDate = $endDate;
		
		if($type == "month" || $type == "month-details"){
			$this->session->month_view_type = $type;
		}
		
		$returnText = $this->view->calendarView($type, $date, $endDate, $filters);
		
		// we have some invalid UTF-8 characters in our database, which breaks the view for affected schools. The following line should scrub out the bad characters
		$returnText = iconv('UTF-8', 'UTF-8//IGNORE', $returnText);
        
		$this->_helper->json($returnText);
	}


	public function multistudentPickerTestAction()
	{
		$this->view->pageTitle = "Picklist Multistudent Picker";
		$config = null;
		$picklistOptions = array(
			'mode' => 'single',
			'useSessionFilters' => TRUE,
			'loadJSCSS' => TRUE,
			'loadStudents' => TRUE
		);

		$this->view->studentPicker = $this->view->multistudentPicklist($this->user, $config, $picklistOptions);

	}

	public function selectBoxTestAction()
	{
		//$this->view->pageTitle = "Picklist Multistudent Picker";
	}
	
	public function generateDisplayOptionsModalAction()
	{
		$filterSet = \Fisdap\EntityUtils::getEntity("SchedulerFilterSet", $this->session->filterSet);
		$form = new Scheduler_Form_DisplayOptionsModal($filterSet);
	    $this->_helper->json($form->__toString());
	}
	
	public function saveDisplayOptionsModalAction()
	{
		/** @var SchedulerFilterSet $filterSet */
		$filterSet = \Fisdap\EntityUtils::getEntity("SchedulerFilterSet", $this->session->filterSet);
		$event_id = $this->_getParam('event_id');
		
		if($filterSet){
			$filterSet->show_student_names = ($this->_getParam('student_names') === 'true');
			$filterSet->show_instructor_names = ($this->_getParam('instructor_names') === 'true');
			$filterSet->show_preceptor_names = ($this->_getParam('preceptor_names') === 'true');
			$filterSet->show_weebles = ($this->_getParam('weebles') === 'true');
			$filterSet->show_totals = ($this->_getParam('totals') === 'true');
			$filterSet->show_site_names = ($this->_getParam('site_names') === 'true');
			$filterSet->show_base_names = ($this->_getParam('base_names') === 'true');
			$filterSet->save();
		}
		
		$this->_helper->json(true);
	}

	public function generateShiftHistoryAction()
	{
	    $id = $this->_getParam('id');
        $quickAdd = $this->_getParam('quick_add');
	    $viewHelper = new Scheduler_View_Helper_ShiftHistoryModal();
	    $this->_helper->json($viewHelper->generateShiftHistory($id, $quickAdd));
	}

	public function generateStudentSignupAction()
	{
	    $event_id = $this->_getParam('event_id');
	    $viewHelper = new Scheduler_View_Helper_StudentSignupModal();
	    $this->_helper->json($viewHelper->generateStudentSignup($event_id));
	}

	public function processStudentSignupAction()
	{

		$event_id = $this->_getParam('event_id');
		$user = User::getLoggedInUser();
		$event = \Fisdap\EntityUtils::getEntity("EventLegacy", $event_id);
		$student = $user->getCurrentRoleData();
		
		// make sure this event still has available slots
		if (!$event->hasOpenStudentSlot()) {
			$this->_helper->json("full");
		    return;
		}
		
		// make sure this student can actually sign up for this shift
		if (!$student->program->program_settings->{'student_pick_'.$event->type}) {
		    $this->_helper->json(false);
		    return;
		}

		$student_group_repo = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
		$userGroups = $student_group_repo->getProgramGroups($user->getProgramId(), null, $student->id, true, true);
		
		if (!$event->isAvailableTo($student->getCertification()->id, $userGroups, $user->getProgramId(), true, $user)) {
		    $this->_helper->json(false);
		    return;
		}

		$this->_helper->json(($event->assign($user->getCurrentUserContext()) ? true : false));
	}

	public function generateStudentDropAction()
	{
	    $assignment_id = $this->_getParam('assignment_id');
	    $viewHelper = new Scheduler_View_Helper_StudentDropModal();
	    $this->_helper->json($viewHelper->generateStudentDrop($assignment_id));
	}

	public function processStudentDropAction()
	{
	    $assignment_id = $this->_getParam('assignment_id');
	    $assignment = \Fisdap\EntityUtils::getEntity("SlotAssignment", $assignment_id);

	    $this->_helper->json($assignment->remove());

	}

	public function generateShiftDeleteAction()
	{
	    $shift_id = $this->_getParam('shift_id');
	    $viewHelper = new Scheduler_View_Helper_ShiftDeleteModal();
	    $this->_helper->json($viewHelper->generateShiftDelete($shift_id));
	}

	public function processShiftDeleteAction()
	{
		$shift_id = $this->_getParam('shift_id');
		$user = User::getLoggedInUser();
		$shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $shift_id);

		// make sure this user can actually delete this shift
		if (!$shift->isEditable()) {
		    $this->_helper->json(false);
		    return;
		}

		$shift->delete();

		$this->_helper->json(true);
	}
	
	public function generateEventDeleteAction()
	{
	    $event_ids = $this->_getParam('event_ids');
	    $form = new Scheduler_Form_EventDeleteModal($event_ids);
	    $this->_helper->json($form->__toString());
	}
	
	public function generateWhichEventsInSeriesAction()
	{
	    $series_id = $this->_getParam('series_id');
	    $event_id = $this->_getParam('event_id');
	    $event_action = $this->_getParam('event_action');
		
	    $viewHelper = new Scheduler_View_Helper_WhichEventsInSeriesModal();
	    $this->_helper->json($viewHelper->generateWhichEvents($series_id, $event_id, $event_action, $this->view));
	}

	public function processEventDeleteAction()
	{
		$formValues = $this->_getAllParams();
		$events = explode(',', $formValues['event_ids']);
		$form = new Scheduler_Form_EventDeleteModal($events);
		$this->_helper->json($form->process($formValues));
	}

	public function generateShiftRequestFormAction(){
		$assignment_id = $this->_getParam('assignment_id');
		$form = new Scheduler_Form_ShiftRequestModal($assignment_id);
		$this->_helper->json($form->__toString());
	}

	public function processShiftRequestAction(ShiftRequestRepository $shiftRequestRepository)
	{
		$user = User::getLoggedInUser();
		$formValues = $this->getAllParams();
		$assignment_id = $this->getParam('assignment_id');
		$form = new Scheduler_Form_ShiftRequestModal($assignment_id);
		
		$response = array();
		$response['success'] = $form->process($formValues);

		if ($user->getCurrentRoleName() == 'student') {
			$requests = $shiftRequestRepository->getPendingRequestCountByOwner($user->getCurrentUserContext()->id);
		} else {
			$requests = $shiftRequestRepository->getPendingRequestCountByProgram($user->getProgramId());
		}
		
		$response['request_count'] = $requests;
		$this->_helper->json($response);
	}


	public function generateAssignModalAction()
	{
		$event_id = $this->_getParam('event_id');
		$config = null;
		$picklistOptions = array(
		    'loadJSCSS' => FALSE,
		    'helpText' => 'multistudent-picklist-help-scheduler.phtml',
		    'showTotal' => TRUE,
		    'includeSubmit' => FALSE,
		);
		
		$msp = $this->view->multistudentPicklist($this->user, $config, $picklistOptions);
		
		if($event_id){
			$event = \Fisdap\EntityUtils::getEntity("EventLegacy", $event_id);
			$form = new Scheduler_Form_AssignModal($event_id, $msp);
		}
		else {
			$form_data = $this->_getParam('data');
			$form = new Scheduler_Form_AssignModal(null, $msp, $form_data);
		}
		
		$this->_helper->json($form->__toString());
	}
	
	public function generateViewComplianceAction()
	{
	    $assignment_id = $this->_getParam('assignment_id');
	    $viewHelper = new Scheduler_View_Helper_ViewComplianceModal();
	    $this->_helper->json($viewHelper->generateViewComplianceModal($assignment_id));
	}


	public function getFilteredStudentsAction()
	{
		$params = $this->_getAllParams();
		$event = \Fisdap\EntityUtils::getEntity("EventLegacy", $params['event_id']);

		$filters = array();
		if($params['graduationYear']){$filters['graduationYear'] = $params['graduationYear'];}
		if($params['graduationMonth']){$filters['graduationMonth'] = $params['graduationMonth'];}
		if($params['section']){$filters['section'] = $params['section'];}
		if($params['certificationLevels']){
			$filters['certificationLevels'] = array();
			foreach($params['certificationLevels'] as $certLevel){
				$filters['certificationLevels'][] = $certLevel;
			}
		}

		if($params['graduationStatus']){
			$filters['graduationStatus'] = array();
			foreach($params['graduationStatus'] as $gradStatus){
				$filters['graduationStatus'][] = $gradStatus;
			}
		}
		
		if($event){
			$students = $event->getAssignableStudents(User::getLoggedInUser()->getProgramId(), $filters, true);
		}

		if(!$event){
			// now we've got to get creative.
			$event_type = $params['event_type'];
			
			$students = \Fisdap\EntityUtils::getRepository('User')->getAllStudentsByProgram(User::getLoggedInUser()->getProgramId(), $filters);
			$program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", User::getLoggedInUser()->getProgramId());
			
			$assignable = array();
			$assigned = array();
			$assigned_different_program = array();
			$hidden_students = array();
			$hasData = array();
			
			foreach($students as $student){
				$show = false;
				$show = \Fisdap\Entity\EventLegacy::hasProductForSignUpByEventType( $student['configuration'], $student['id'], $event_type);
				if($show){$assignable[$student['id']] = $student['first_name'] . " " . $student['last_name'];}
				else {$hidden_students[$student['id']] = $student['first_name'] . " " . $student['last_name'];}
			}
			
			$students = array("assignable" => $assignable, "assigned" => $assigned, "hidden_students" => $hidden_students, "different_program_students" => $assigned_different_program, "has_data" => $hasData);
			
		}
		
		
		$this->_helper->json($students);
	}

	public function assignStudentsAction()
	{
		$form = new Scheduler_Form_AssignModal();
        $params = $this->getAllParams();

        $processResults = $form->process($params);

        if ($processResults === true) {
            $this->_helper->json($processResults);
        } else {
            $this->_helper->json($this->view->partial("assignConflictsWarning.phtml", array("conflicts" => $processResults)));
        }
	}

    public function generateCalendarSubscriptionAction()
    {
        $values = $this->_getAllParams();
        $form = new \Scheduler_Form_CalendarSubscriptionModal($values['calendarId']);
        $this->_helper->json($this->view->partial("calendar-subscription-confirmation.phtml", array("auth_key" => $form->process($values))));
    }

	private function getPermissionsForViews($is_student, User $user)
	{
		if($is_student){
			$program = $user->getCurrentUserContext()->program;
			$lab = $program->can_students_create_lab;
			$field = $program->can_students_create_field;
			$clinical = $program->can_students_create_clinical;
			$settings = false;
			$edit_compliance = false;
			$this->view->studentSignupModal = $this->view->studentSignupModal();
			$this->view->studentShiftModal = new SkillsTracker_Form_Shift();
			$this->view->viewComplianceModal = $this->view->viewComplianceModal();
		}
		else {
			$instructor = $user->getCurrentRoleData();
			$settings = $instructor->hasPermission("Edit Program Settings");
			$edit_compliance = $instructor->hasPermission("Edit Compliance Status");
			$lab = $instructor->hasPermission("Edit Lab Schedules");
			$field = $instructor->hasPermission("Edit Field Schedules");
			$clinical = $instructor->hasPermission("Edit Clinic Schedules");
			$this->view->shiftHistoryModal = $this->view->shiftHistoryModal();
			$this->view->studentDropModal = $this->view->studentDropModal();
		}

		return array("lab" => $lab,
					 "field" => $field,
					 "clinical" => $clinical,
					 "settings" => $settings,
					 "edit_compliance" => $edit_compliance
					);
	}

	private function checkPermissions($is_student, $user)
	{
		$can_view_scheduler = false;

		// does the student have scheduler?
		// does the instructor have permission to be here?
		if($is_student){

			foreach($user->serial_numbers as $sn){
				if(((boolean)($sn->configuration & 2) || (boolean)($sn->configuration & 8192))){
					$has_scheduler = true;
				}
			}

			if($has_scheduler){
				$can_view_scheduler = true;
			}
			else {
				$error_msg = "Your account does not include access to the Scheduler.";
			}
		}
		else {
			$instructor = $user->getCurrentRoleData();
			if($instructor->hasPermission("View Schedules")){
				$can_view_scheduler = true;
			}
			else {
				$error_msg = "Currently you do not have permission to view schedules.";
			}
		}

		if(!$can_view_scheduler){
			$this->displayError($error_msg);
			return;
		}
	}

}

