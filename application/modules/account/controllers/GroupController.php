<?php

class Account_GroupController extends Fisdap_Controller_Private
{

    public function init()
    {
        /* Initialize action controller here */
		parent::init();
    }

    public function viewAction()
    {
		//Check permissions
		if (!$this->user->isInstructor()) {
			$this->displayError("Students cannot access this page.");
			return;
		} else if (!$this->user->getCurrentRoleData()->hasPermission("Edit Program Settings")) {
			$this->displayError("You do not have permission to edit student groups. Please contact " . $this->user->getCurrentProgram()->getProgramContactName() . " for more information.");
			return;
		}
    	$programId = $this->user->getProgramId();
    	
    	$repo = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
    	
    	$active = $repo->getProgramGroups($programId, true);
    	$inactive = $repo->getProgramGroups($programId, false);
    	
    	$cleanActive = array();
    	$cleanInactive = array();
    	
    	foreach($active as $aList){
    		$cleanActive[$aList->id] = $aList->name;
    	}
    	
    	foreach($inactive as $iaList){
    		$cleanInactive[$iaList->id] = $iaList->name;
    	}
    	
    	$this->view->activeGroups = $cleanActive;
    	$this->view->inactiveGroups = $cleanInactive;
    }
    
    public function editAction()
    {
    	$groupId = $this->_getParam('gid', false);
    	
    	$group = \Fisdap\EntityUtils::getEntity('ClassSectionLegacy', $groupId);
		
		//Check permissions
		if (!$this->user->isInstructor()) {
			$this->displayError("Students cannot access this page.");
			return;
		} else if (!$this->user->getCurrentRoleData()->hasPermission("Edit Program Settings")) {
			$this->displayError("You do not have permission to edit student groups. Please contact " . $this->user->getCurrentProgram()->getProgramContactName() . " for more information.");
			return;
		}
    	
		if ($groupId > 0 && $this->user->getProgramId() != $group->program->id) {
			$this->displayError("You do not have permission to edit this student group.");
			return;
		}
    	
    	if(!$groupId){
    		$group->start_date = new \DateTime("now");
    		$group->end_date = new \DateTime("+1 year");
    	}
    	
    	$this->view->group = $group;

		// set up the student picker
		$config = null;
		$picklistOptions = array(
			'loadJSCSS' => TRUE,
			'picklistJS' => "/js/library/Account/View/Helper/student-group-multistudent-picklist.js",
			'showTotal' => TRUE,
			'includeSubmit' => TRUE,
		);
		$this->view->studentPicker = $this->view->multistudentPicklist($this->user, $config, $picklistOptions);
    }
    
    /**
     * Changing this function from a delete to a more of an inactivate action.  Instead of removing 
     * the group, it now just sets the end date for that group to yesterdays date, effectively
     * marking it as inactive.  There are lots of references that don't get cleanly deleted, and this
     * method seems to work more natually with other functionality that relies on the groups.
     */
    public function deleteAction(){
    	$groupId = $this->_getParam('group_id');
    	
    	/*
    	// There's a bug where if you delete a student group that happens to have Serial Numbers tied to it,
    	// bad things happen.  Grab all serial number records associated with the group and unset those relationships.
    	
    	$sns = \Fisdap\EntityUtils::getRepository('SerialNumberLegacy')->findByGroup($groupId);
    	
    	foreach($sns as $sn){
    		$sn->group = null;
    		$sn->save();
    	}
    	
    	$group = \Fisdap\EntityUtils::getEntity('ClassSectionLegacy', $groupId);
    	
    	$group->delete();
    	*/
    	
    	$group = \Fisdap\EntityUtils::getEntity('ClassSectionLegacy', $groupId);
    	
    	$group->end_date = new DateTime('yesterday');
    	
    	// Update the start date if it is now after the end date...
    	if($group->start_date->format('U') > $group->end_date->format('U')){
    		$group->start_date = new DateTime('-2 day');
    	}
    	
    	$group->save();
    	
    	$this->_helper->json(true);
    }
    
    public function getFilteredStudentsAction()
    {
    	$params = $this->_getAllParams();
    	
    	$filters = array();
    	if($params['graduationYear']){
    		$filters['graduationYear'] = $params['graduationYear'];
    	}
    	if($params['graduationMonth']){
    		$filters['graduationMonth'] = $params['graduationMonth'];
    	}
    	if($params['section']){
    		$filters['section'] = $params['section'];
    	}
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

    	$assignable = array();
    	$assigned = array();
    	
    	// If we're looking for either students or TA's, load up a listing of students to return
		$program_id = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
    	if($params['assignment_type'] == 'students' || $params['assignment_type'] == 'tas'){
	    	$students = \Fisdap\EntityUtils::getRepository('User')->getAllStudentsByProgram($program_id, $filters);
	    
	    	foreach($students as $student){
	    		$assignable[$student['id']] = $student['first_name'] . " " . $student['last_name'];
	    	}
			
    	} else {
    		$instRepo = \Fisdap\EntityUtils::getRepository('InstructorLegacy');
    		$instructors = $instRepo->findByProgram($program_id);
    		
    		foreach($instructors as $instructor){
    			$assignable[$instructor->id] = $instructor->first_name . " " . $instructor->last_name;
    		}

    	}
    	
    	// There's an array of "assigned" users that comes with the request for this page; this is what we care about
		// instead of what's in the database because of the way the page is set up.
    	// Loop through that array and manually assign those users to the right side.
    	// This is because the assignment happens only on the page level, and doesn't get saved until they save on the page.
    	if(is_array($params['chosen_users'])){
	    	foreach($params['chosen_users'] as $id){
	    		// Easy case - if it's in the assignable list, just pull the name from there
	    		if(array_key_exists($id, $assignable)){
	    			$assigned[$id] = $assignable[$id];
	    		} else {
	    			// If it's no longer assignable (because of filtering and whatnot), pull it manually from the DB.
	    			if($params['assignment_type'] == 'instructors') {
	    				$inst = \Fisdap\EntityUtils::getEntity('InstructorLegacy', $id);
	    				$assigned[$id] = $inst->first_name . " " . $inst->last_name;
	    			} else {
	    				$stud = \Fisdap\EntityUtils::getEntity('StudentLegacy', $id);
	    				$assigned[$id] = $stud->first_name . " " . $stud->last_name;
	    			}
	    		}
	    	}
    	}
    	
    	$users = array("assignable" => $assignable, "assigned" => $assigned);

    	$this->_helper->json($users);
    }
    
    public function saveAction()
    {
    	$data = $this->_getAllParams();
    	
    	// Init the arrays for the group stuff here- things break if no people are assigned to a bucket
    	if(!is_array($data['assigned_students'])){
    		$data['assigned_students'] = array();
    	}
    	if(!is_array($data['assigned_instructors'])){
    		$data['assigned_instructors'] = array();
    	}
    	if(!is_array($data['assigned_tas'])){
    		$data['assigned_tas'] = array();
    	}
    	
    	// Start applying the stuff to the model...
    	$groupId = $this->_getParam('group_id', false);
    	$group = \Fisdap\EntityUtils::getEntity('ClassSectionLegacy', $groupId);
    	
    	if(!$groupId){
    		$group->program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
    	}
    	
    	$group->start_date = new \DateTime($data['group_start_date']);
    	$group->end_date = new \DateTime($data['group_end_date']);
    	
    	$group->year = $group->start_date->format('Y');
    	
    	$group->generate_emails = ($data['group_event_notifications']=='true');
    	
    	// Unset all removed student associations...
    	// There doesn't seem to be a better way to do this- loop through all current associations and only clear out the people who are no
    	// longer part of the different groups.
    	if($group->section_student_associations){
		    foreach($group->section_student_associations as $assoc){
	    		if(!in_array($assoc->student->id, $data['assigned_students'])){
	    			$group->removeStudent(\Fisdap\EntityUtils::getEntity('StudentLegacy', $assoc->student->id));
	    		}
	    	}
    	}
    	
    	if($group->section_instructor_associations){
	    	foreach($group->section_instructor_associations as $assoc){
	    		if(!in_array($assoc->instructor->id, $data['assigned_instructors'])){
	    			$group->removeInstructor(\Fisdap\EntityUtils::getEntity('InstructorLegacy', $assoc->instructor->id));
	    		}
	    	}
    	}
    	
	    if($group->section_ta_associations){
	    	foreach($group->section_ta_associations as $assoc){
	    		if(!in_array($assoc->ta_student->id, $data['assigned_tas'])){
	    			$group->removeTa(\Fisdap\EntityUtils::getEntity('StudentLegacy', $assoc->ta_student->id));
	    		}
	    	}
	    }
    	
    	// Assign all of the student/TA/instructor associations here.
    	foreach($data['assigned_students'] as $id){
    		$group->addStudent(\Fisdap\EntityUtils::getEntity('StudentLegacy', $id));
    	}
    	
    	foreach($data['assigned_instructors'] as $id){
    		$group->addInstructor(\Fisdap\EntityUtils::getEntity('InstructorLegacy', $id));
    	}
    	
    	foreach($data['assigned_tas'] as $id){
    		$group->addTa(\Fisdap\EntityUtils::getEntity('StudentLegacy', $id));
    	}
    	
    	$oldGroupName = $group->name;
    	
    	$group->name = $data['group_name'];
    	
    	$group->save();
    	
    	// m781 fix- if the name changed, we need to iterate through the window constraint values
    	// to update descriptions as well...
    	// This needs to happen after the group->save() call so that we have a groupId to pass in.
    	if($oldGroupName != $data['group_name']){
    		$group->name = $data['group_name'];
    	
    		// Find all window constraints for this group...
    		$repo = \Fisdap\EntityUtils::getRepository('Window');
    	
    		$repo->updateWindowConstraintValueDescriptions($group->id,  $group->name);
    	}
    	
    	$this->_helper->json($group->id);
    }
}

