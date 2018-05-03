<?php

class MyFisdap_Widgets_ComplianceWidget extends MyFisdap_Widgets_Base
{
	public function render(){
		$user = $this->getWidgetUser();
		
		if($user->isInstructor()){
			$html = $this->renderInstructorView();
		}
		
		return $html;
	}
	
	public function renderInstructorView(){
		
		$html = '<div id="non-compliant-list">';
		
		$user = $this->getWidgetUser();
		$repo = \Fisdap\EntityUtils::getRepository('Requirement');
		
		if($user->getCurrentRoleData()->hasPermission("Edit Compliance Status")){
			$html .= "<a href='/scheduler/compliance/edit-status' id='edit-compliance-status'>Edit compliance status</a>";
		}
		
		$attachments = $repo->getNonCompliantAttachmentsByProgram($this->getWidgetProgram()->id);
		
		$html .= "<h2 class='compliance-gray-header section-header'>" . $this->getWidgetProgram()->name . "</h2>";
		
		if(count($attachments) > 0){
			$users = $this->buildData($attachments);
			$html .= $this->displayList("Non-Compliant Students", $users['student']);
			$html .= $this->displayList("Non-Compliant Instructors", $users['instructor']);
		}
		else {
			$html .= "There are no users who are non-compliant.";
		}
		
		// for each site i am admin for, get the non-compliant attachments
		$sites = \Fisdap\EntityUtils::getRepository('SiteLegacy')->getAdminSites($this->getWidgetProgram()->id);
		
		// if this user is an admin for at least 1 site, we want to show them shared sites
		if(count($sites) > 0){
			
			$html .= "<br /><h2 id='shared-sites-title' class='compliance-gray-header section-header'>";
			$html .= "<img id='shared-site-icon' src='/images/icons/sharing-admin.png'>";
			$html .= "Shared Sites</h2>";
			
			$current_program = $this->getWidgetProgram();
			$global_network_associations = array();
			$network_program_ids = array();
			$attachments_by_site = array();
			 
			foreach($sites as $site_id){
				$site = \Fisdap\EntityUtils::getEntity('SiteLegacy', $site_id);
				$network_program_ids[$site_id] = $site->getNetworkPrograms();
				$global_network_associations = $repo->getGlobalNetworkAssocationIds($site_id, $network_program_ids[$site_id]);
				if($global_network_associations){
					$shared_attachments = $repo->getGlobalNonCompliantAttachmentsBySite($current_program->id, $global_network_associations, $network_program_ids[$site_id], $current_program->seesSharedStudents($site_id));
					$attachments_by_site[$site_id] = $shared_attachments;
				}
			}
			
			if(count($attachments_by_site) > 0){
				
				foreach($attachments_by_site as $site_id => $site_attachments){
					$site = \Fisdap\EntityUtils::getEntity('SiteLegacy', $site_id);
					$shared_users = $this->buildData($site_attachments, true, $site_id);
					$site_name = $site->name;
					
					$html .= $this->displayList($site_name, $shared_users);
				}
				
			}
			else {
				$html .= "There are no users who are non-compliant.";
			}
		}
		
		
		$html .= "</div>";
		return $html;
	}
	
	public function separateBySite($shared_attachments)
	{
		$data = array();
		
		foreach($shared_attachments as $attachment){
			
			$site_id = $attachment->getSite($this->getWidgetProgram()->id)->id;
			
			if(!$data[$site_id]){
				$data[$site_id] = array();
			}
			
			$data[$site_id][$attachment->id] = $attachment;
			
		}
		
		return $data;
	}
	
	public function buildData($attachments, $shared_section = false, $site_id = null)
	{
		$users = ($shared_section) ? array() : array("student" => array(), "instructor" => array());
		$current_program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $this->getWidgetProgram()->id);

        // get very beginning of today for date comparison purposes
        $lastDayOfPrevMonth = new \DateTime("last day of previous month");
        $lastDayOfPrevMonth->setTime(23,59,59);

        foreach($attachments as $attachment){
			
			$user = $attachment->user_context->user;
			$role_data = $user->getCurrentRoleData();
			$role_name = $user->getCurrentRoleName();
			
			// m1119, MAINT-1838 - don't show non-active students, and don't show staff members- just step over them here.
			if($user->isStaff() || ($role_name == "student" && (($attachment->user_context->end_date < $lastDayOfPrevMonth) || ($attachment->user_context->studentRoleData->graduation_status->id != 1)))) {
				continue;
			}
			
			if(!$shared_section) {
				if(!isset($users[$role_name])){
					$users[$role_name] = array();
				}
			}
			
			if((!$shared_section && !isset($users[$role_name][$role_data->id])) || ($shared_section && !isset($users[$role_data->id]))){
				
				$instructor = ($role_name == "instructor") ? true : false;
				
				if(($shared_section) && (!$current_program->seesSharedStudents($site_id) && ($role_name != "instructor"))){
					// hide the student name
					$name = $attachment->user_context->certification_level->description . " student from " . $attachment->user_context->program->abbreviation;
				}
				else {
					$name  = ($shared_section || $instructor) ? "" : "<a href='/portfolio/index/about/studentId/" . $role_data->id . "'>";
					$name .= $user->first_name . " " . $user->last_name;
					$name .= ($shared_section || $instructor) ? "" : "</a>";
					$name .= ($instructor) ? "" : " " . $attachment->user_context->certification_level->description;
					$name .= ($shared_section && $instructor) ? " Instructor" : "";
					$name .= ($shared_section) ? " from " . $attachment->user_context->program->abbreviation : "";
				}
				
				if($shared_section){
					$users[$role_data->id] = array("name" => $name, "count" => 0);
				}
				else {
					$users[$role_name][$role_data->id] = array("name" => $name, "count" => 0);
				}
			}
			
			if($shared_section){
				$users[$role_data->id]['count']++;
			}
			else {
				$users[$role_name][$role_data->id]['count']++;
			}
			
		}
		
		return $users;
	}
	
	public function displayList($list_name, $people)
	{
		if(count($people) > 0){
			$html .= "<h2 class='compliance-gray-header non-compliant-list-name'>" . $list_name . "</h2>";
			foreach($people as $id => $person){
				$plural = ($person['count'] == 1) ? "" : "s"; 
				$html .= "<div class='non-compliant-list-item'>" . $person['name'] . " has <b>" . $person['count'] . "</b> non-compliant requirement" . $plural . ".</div>";
			}
		}
		
		return $html;
	}
	
	public function renderStudentView(){
		$user = $this->widgetData->user;
		return $html;
	}
	
	public function getDefaultData(){
		return array();
	}
	
	public static function userCanUseWidget($widgetId){
		$user = \Fisdap\EntityUtils::getEntity('MyFisdapWidgetData', $widgetId)->user;
		
		// User has to:
		// Be an instructor who has permission to view schedules and have at least 1 student who uses Scheduler
		if($user->isInstructor() && $user->hasPermission("View Schedules") && $user->getCurrentProgram()->scheduler_beta){
			//$has_student = $this->hasStudentUsingScheduler($user);
		
			// This query returns a 1 if a student exists in the instructors program that uses scheduler, or 0 if it doesn't...
			$sql = "SELECT IF(COUNT(*) > 0, 1, 0) as result FROM StudentData sd INNER JOIN SerialNumbers sn ON sn.Student_id = sd.Student_id WHERE sd.Program_id = " . $user->getProgramId() . " AND ((sn.Configuration & 2) = 1);";
			
			$conn = \Fisdap\EntityUtils::getEntityManager()->getConnection();
			$result = $conn->query($sql);
			
			$row = $result->fetch();
			
			if($row !== false){
				if($row['result'] == 1){
					$result = true;
				}else{
					$result = false;
				}
			}
			
			return true;
		}
		
		return false;
	}
}
