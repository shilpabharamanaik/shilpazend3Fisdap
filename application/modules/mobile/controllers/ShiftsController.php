<?php

class Mobile_ShiftsController extends Mobile_Controller_SkillsTrackerPrivate
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        // action body
    }
    
    public function myShiftAction()
    {
        //Grab the shift ID from the URL or session
		$shiftId = $this->_getParam('shiftId', $this->globalSession->shiftId);
		
		//Error out if no shift is found
		if (!$shiftId) {
			$this->displayError("You've reached this page in error. No Shift ID found.");
			return;
		}
		
		$shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
		
		//Error out if this shift cannot be viewed by the current user
		if (!$shift->isViewable()) {
			$this->displayError("You do not have permission to view this shift.");
			return;
		}
		
		$loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
		
		//Set the page title and sub links
		$titleText = "My Shift";
		if($loggedInUser->getCurrentRoleName() == 'instructor'){
			$titleText = $shift->student->user->first_name . " " . $shift->student->user->last_name . "'s shift";
		}
		
		$this->view->pageTitle = $titleText;
		
		$this->view->pageTitleLinks = array(
			"Shift report" => "#",
			"Detailed shift report" => "/skills-tracker/shifts/detailed-shift-report/shiftId/" . $shiftId
			//"See what you're getting credit for" => "#"
		);
		
		//Grab boolean for shift vs. student displays
		$this->view->isInstructor = ($loggedInUser->getCurrentRoleName() == 'instructor');

		$this->globalSession->shiftId = $shiftId;
		
		////Check the permissions of the user
		//$this->checkPermissions($shiftId);
		
		//Grab the Shift entity and put it in the view
		$this->view->shift = $shift;
		
		//Stick any flash messages in the view
		$this->view->messages = $this->flashMessenger->getMessages();
	}
    
    public function deleteRunAction()
    {
        $id = $this->_getParam('runId');
        $run = \Fisdap\EntityUtils::getEntity('Run', $id);
        $shiftId = $run->shift->id;
        
		
		if ($run->shift->isEditable()) {
			$this->flashMessenger->addMessage("Patient #$id successfully deleted.");
			$run->delete();
		} else {
			$this->flashMessenger->addMessage("Patient #$id was not deleted because you do not have permission to do so.");

		}
		
        $this->_redirect("/mobile/shifts/my-shift/shiftId/" . $shiftId);
    }
}

