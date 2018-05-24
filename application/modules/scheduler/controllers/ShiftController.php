<?php

class Scheduler_ShiftController extends Fisdap_Controller_Private
{

    public function init()
    {
        /* Initialize action controller here */
		$this->session = new \Zend_Session_Namespace("Scheduler");
    }
	
    public function indexAction()
    {
		$this->view->pageTitle = "Shift";
	}
	
	private function checkPermissions($new = false, $type = null)
	{
		$user = \Fisdap\Entity\User::getLoggedInUser();
		if(!empty($user) && $user->getCurrentRoleName() == 'instructor'){
			
			if($new){
				if($type != "clinical" && $type != "field" && $type != "lab"){
					$this->displayError("Looks like something went wrong. What kind of shift would you like to create: <a href='/scheduler/shift/new/type/field'>Field</a>, <a href='/scheduler/shift/new/type/clinical'>Clinical</a>, or <a href='/scheduler/shift/new/type/lab'>Lab</a>? ");
					return false;
				}
			}
			
			$instructor = $user->getCurrentRoleData();
			$perm_shift_type = ($type == "clinical") ? "clinic" : $type;
			if(!$instructor->hasPermission("View Schedules") || !$instructor->hasPermission("Edit " . ucfirst($perm_shift_type) . " Schedules")){
				$this->displayError("You do not have permission to create Scheduler " . $type . " shifts.");
				return false;
			}
			
			// now check to see if the program has at least 1 active site with at least 1 active base
			$has_sites = false;
			
			$sites = \Fisdap\EntityUtils::getRepository('SiteLegacy')->getFormOptionsByProgram($user->getProgramId(), $type, null, null, true);
			if($sites){
				foreach($sites as $site_id => $site_name){
					$bases = \Fisdap\EntityUtils::getRepository('BaseLegacy')->getFormOptionsByProgram($user->getProgramId(), true, null, $site_id);
					if($bases){
						$has_sites = true;
					}
				}
			}
			else {
				$has_sites = false;
			}
			
			if(!$has_sites){
				$no_sites_bug_msg = "In order to create " . $type . " shifts, you must have at least one active base at one active site.";
				
				if($instructor->hasPermission("Edit Program Settings")){
					$no_sites_bug_msg .= " <a href='/account/sites'>Manage sites</a>";
				}
				
				$this->displayError($no_sites_bug_msg);
				return false;
			}
		}
		else {
			$this->displayError("Students do not have permission to view this page.");
			return false;
		}
		
		return true;
	}
	

	
	public function editAction()
	{
		$event_id = $this->_getParam('event');
		$using_session_events = false;

		if(!$event_id){
		    $uuid = $this->_getParam('event-session');

            $events = $this->session->eventsToEdit[$uuid];
			$event_id = $events[0];
			$using_session_events = true;
		}

		$event = \Fisdap\EntityUtils::getEntity('EventLegacy', $event_id);
		$this->checkPermissions(false, $event->type);

		$this->view->pageTitle = "Edit " . $event->type . " shift";
		$this->view->eventForm = new Scheduler_Form_Event(null, $event_id, $events, $using_session_events, $uuid);
		
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			// also send it session data about windows
			if ($this->view->eventForm->process($request->getPost(), $this->session->windows)) {
				//$this->flashMessenger->addMessage("Your shift has been created.");
				$this->_redirect("/scheduler/");
			}
		}
		else {
			// clear the session
			$this->session->windows = null;
		}
		
		$this->addEventFormFiles();
	}
		
	public function saveEventIdsToSessionAction()
	{
		$event_ids = $this->_getParam('event_ids');
		$uuid = uniqid("event-");
		$this->session->eventsToEdit[$uuid] = $event_ids;
		$this->_helper->json($uuid);
	}
	
	private function addEventFormFiles()
	{
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/shift-assign-multistudent-picklist.js");
		$this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/multistudent-picklist.css");
		$this->view->headScript()->appendFile("/js/jquery.fancyFilters.js");
		$this->view->headScript()->appendFile("/js/jquery.busyRobot.js");
		$this->view->headLink()->appendStylesheet("/css/jquery.busyRobot.css");
		$this->view->headLink()->appendStylesheet("/css/jquery.fancyFilters.css");
	}

	public function newAction()
	{
		$type = $this->getParam('type');
		
		if(!$this->checkPermissions(true, $type)){
			return;
		}
		
		$this->view->pageTitle = "Add " . $type . " shift";
		
		$this->view->eventForm = new Scheduler_Form_Event($type);
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			// also send it session data about windows
			if ($this->view->eventForm->process($request->getPost(), $this->session->windows)) {
				//$this->flashMessenger->addMessage("Your shift has been created.");
				$this->_redirect("/scheduler/");
			}
		}
		else {
			// clear the session
			$this->session->windows = null;
		}
		
		$this->addEventFormFiles();
		
		$this->view->tour = new Fisdap_View_Helper_GuidedTourHelper();
		$this->view->tour_id = 6;
		
	}
	
	public function saveShiftFormAction()
	{
		$edit = ($this->_getParam('edit') == 1) ? true : false;
		
		if($edit) {
			$event_id = $this->_getParam('event_id');
			$using_session_events = false;
			
			if(!$event_id){
			    $formData = $this->_getParam('form_values');
				$events = $this->session->eventsToEdit[$formData['edit_event_session_id']];
				$event_id = $events[0];
				$using_session_events = true;
			}
		}
		else {
			$type = $this->_getParam('type');
		}
		
		$this->view->eventForm = ($edit) ? new Scheduler_Form_Event(null, $event_id, $events, $using_session_events) : new Scheduler_Form_Event($type);
		$process_result = $this->view->eventForm->process($this->_getParam('form_values'), $this->_getParam('window_data'));
		
		$this->_helper->json($process_result);
	}
	
	public function addNewWindowAction()
	{
		$window_id = $this->_getParam('window_id');
		
		if($this->_getParam('new_window') == 1){
			$new_window = $window_id;
		}
		
		$window_form_elements = new Scheduler_Form_WindowSubForm($new_window, $window_id, false, $this->_getParam('shift_type'));
		$this->_helper->json(array("form" => $window_form_elements->__toString(), "id" => $window_form_elements->id, "data" => $this->session->windows[0]));
	}

	public function getBaseOptionsAction()
	{
		$siteId = $this->_getParam("siteId");
		$baseOptions = \Fisdap\EntityUtils::getRepository('BaseLegacy')->getFormOptionsByProgram(\Fisdap\Entity\User::getLoggedInUser()->getProgramId(), true, null, $siteId);
		foreach($baseOptions as $val => $option){
			$html .= "<option value='" . $val . "'>" . $option . "</option>";
		}
		$this->_helper->json($html);
	}
	
	public function getPreceptorOptionsAction()
	{
		$siteId = $this->_getParam("siteId");
		$preceptorOptions = \Fisdap\EntityUtils::getRepository('PreceptorLegacy')->getPreceptorFormOptions(\Fisdap\Entity\User::getLoggedInUser()->getProgramId(), $siteId);
		
		foreach($preceptorOptions as $val => $option){
			$html .= "<option value='" . $val . "'>" . $option . "</option>";
		}
		
		$this->_helper->json($html);
	}

	public function detailsAction()
	{
		$eventId = $this->_getParam('event');
		$event = \Fisdap\EntityUtils::getEntity("EventLegacy", $eventId);
		$this->view->pageTitle = "Scheduler";
		
		if(!$event){
			$this->displayError("We couldn't find the event you're looking for.");
			return false;
		}
		
		$user = \Fisdap\Entity\User::getLoggedInUser();
		if(!empty($user)){
			$current_program_id = $user->getProgramId();
		}
		else{
			$current_program_id = '';
		}
		$program_has_permission = false;
		$user_has_permission = false;
		$no_perm_msg = "";
		if($event->program->id != $current_program_id){
			
			// this isn't their program, make sure they should be looking at these details
			$recieving_programs = \Fisdap\EntityUtils::getRepository('EventLegacy')->getReceivingPrograms($event->id);
			
			if($recieving_programs){
				foreach($recieving_programs as $program_id){
					if($program_id == $current_program_id){
						$program_has_permission = true;
					}
				}
			}
			
			
		}
		else {
			$program_has_permission = true;
		}
		
		if($program_has_permission) {
			if($user->getCurrentRoleName() == 'student'){
				if($event->studentCanViewDetails($user)){
					$user_has_permission = true;
				}
				else {
					$no_perm_msg = "You do not have permission to view this shift's details.";
				}
			}
			else {
				$user_has_permission = true;
			}
		}
		else {
			$no_perm_msg = "You do not have permisson to view this shift's details. This shift was not created by your program and is not shared with you.";
		}
		
		if(!$user_has_permission){
			$this->displayError($no_perm_msg);
			return false;
		}
		
		// ew, i know...
		$this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/modal-imports.css");

		$this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/calendar-view.css");
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/calendar-modal-triggers.js");
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/calendar-controls.js");
		
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/shift-assign-multistudent-picklist.js");
		$this->view->headScript()->appendFile("/js/scheduler/index/index.js");
		
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/get-filter-values.js");

		$this->view->headScript()->appendFile("/js/jquery.fancyFilters.js");
		$this->view->headLink()->appendStylesheet("/css/jquery.fancyFilters.css");
		
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/multipick-cal.js");
		
		$this->view->headScript()->appendFile("/js/jquery.busyRobot.js");
		$this->view->headLink()->appendStylesheet("/css/jquery.busyRobot.css");
		
		$repo = \Fisdap\EntityUtils::getRepository('EventLegacy');
		
		$calViewHelper = new \Scheduler_View_Helper_CalendarView();
		$calViewHelper->setVars("day");
		
		$filters = $this->getStandardFilters();

		$data = $repo->getOptimizedEvents(\Fisdap\Entity\User::getLoggedInUser()->getProgramId(), $event->start_datetime, $event->end_datetime, $filters, $calViewHelper, array($eventId));
		
		$shared = false;
		if($event->event_shares){
			$shared = true;
		}
		
		// now just format this sucker to a single event
		foreach($data['events'] as $year => $month){foreach($month as $month => $day){foreach($day as $day => $event){foreach($event['events'] as $baseId => $event){
			foreach($event as $eventId => $eventData){
				
				$this->view->event = $eventData;
				
				if($shared){
					$this->view->event['shared_event'] = true;
				}
			}
		}}}}
		

		
		$this->view->eventId = $eventId;
		$this->view->current_user_data = $calViewHelper->current_user_data;
		
		if($this->view->current_user_data['role_name'] == "student"){
			$this->view->studentSignupModal = $this->view->studentSignupModal();
			$this->view->studentShiftModal = new SkillsTracker_Form_Shift();
		}
		else {
			$this->view->shiftHistoryModal = $this->view->shiftHistoryModal();
			$this->view->studentDropModal = $this->view->studentDropModal();
			$this->view->assignModal = new Scheduler_Form_AssignModal();
		}
		
		$this->view->shiftDeleteModal = $this->view->shiftDeleteModal();
		$this->view->shiftRequestModal = new Scheduler_Form_ShiftRequestModal();
		$this->view->eventDeleteModal = new Scheduler_Form_EventDeleteModal();
		$whichEventsModal = new Scheduler_View_Helper_WhichEventsInSeriesModal();
		$this->view->whichEventsModal = $whichEventsModal->whichEventsModal($this->view);
		$this->view->shiftRequestModal = new Scheduler_Form_ShiftRequestModal();
		$this->view->viewComplianceModal = $this->view->viewComplianceModal();
	}
	
	public function getStandardFilters()
	{
		return array("sites" => "all",
				 "bases" => "all",
				 "preceptors" => "all",
				 "show_avail" => 1,
				 "avail_certs" => "all",
				 "avail_groups" => "all",
				 "avail_open_window" => false,
				 "show_chosen" => 1,
				 "chosen_students" => "all");
	}
	
	public function getSharingOptionsAction()
	{
		$site_id = $this->_getParam("site_id");
		$event_id = $this->_getParam("event_id");
		$current_program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
		$sharing = false;
		
		if($event_id){
			// find out who is recieving this event
			$recieving_programs = \Fisdap\EntityUtils::getRepository('EventLegacy')->getReceivingPrograms($event_id);
		}
		
		if($current_program->isAdmin($site_id)){
			// get all of the shareable programs
			$associated_programs = \Fisdap\EntityUtils::getEntity('SiteLegacy', $site_id)->getAssociatedPrograms();
			$prorgam_options = array();
			if($associated_programs){
				$sharing = true;

				foreach ($associated_programs as $associated_program) {
					$program_name = $associated_program['name'];
					
					if ($associated_program['shared']) {
						$id = $associated_program['id'];
						if($id != $current_program->id){

							$selected_row = "";
							$checked = "";
							
							if($event_id){
								if(in_array($id, $recieving_programs)){
									$checked = 'data-isSharedWith="true" checked="checked"';
									$selected_row = 'class="selected-row"';
								}
							}
							else {
								$checked = 'data-isSharedWith="true" checked="checked"';
								$selected_row = 'class="selected-row"';
							}
							
							$prorgam_options[] = '<tr ' . $selected_row . ' data-checkboxid="' . $id . '"><td class="sharing-prorgam-checkbox-cell"><input ' . $checked .' type="checkbox" name="' . $id . '-program-checkbox" value="' . $id . '"><label for="' . $id . '-program-checkbox" class="program-label">' . $program_name . '</label></td></tr>';
						}
					}
				}
			}
			else {
				$sharing = false;
			}
			
			$results = $prorgam_options;
			
		}
		else {
			$results = false;
		}
		
		$this->_helper->json(array("programs" => $results, "sharing" => $sharing));
	}
}