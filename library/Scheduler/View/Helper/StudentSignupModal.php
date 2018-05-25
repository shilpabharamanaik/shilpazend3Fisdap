<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This helper will display a modal so a student can sign up for a shift
 */

/**
 * @package Scheduler
 */
class Scheduler_View_Helper_StudentSignupModal extends Zend_View_Helper_Abstract 
{
	
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	// will create an empty modal
	public function studentSignupModal()
	{
		
		// set up our modal
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/student-signup-modal.js");
		$this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/student-signup-modal.css");
		
		$this->_html =  "<div id='studentSignupDialog'>";
		$this->_html .= 	"<div id='signup-modal-content'></div>";
		$this->_html .= "</div>";
		
		return $this->_html;
	}
	
	// generates the content for the modal
	public function generateStudentSignup($event_id)
	{
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$event = \Fisdap\EntityUtils::getEntity("EventLegacy", $event_id);
		
		// the student can't sign up if the limit for this shift type has been reached
		if ($user->getCurrentRoleData()->atLimit($event->type)) {
			$returnContent = "<div>
						You have a limited scheduler account and cannot sign up for any more ".$event->type." shifts.
					</div>
					<div class='signup-buttons'>
						<div id='cancelButtonWrapper' class='small gray-button'>
							<a href='#' id='signup-cancel-btn'>Ok</a>
						</div>
					</div>";
			return $returnContent;
		}
		
		// make sure there are still slots open for sign up
		if (!$event->hasOpenStudentSlot()) {
			$returnContent = "<div>
						Looks like this shift is already full. Try refreshing your page to get the most recent changes.
					</div>
					<div class='signup-buttons'>
						<div id='cancelButtonWrapper' class='small gray-button'>
							<a href='#' id='signup-cancel-btn'>Ok</a>
						</div>
					</div>";
			return $returnContent;
		}
		
		$returnContent = "<div id='main-signup-content'>
					<div>
						You have chosen to sign up for this shift:
					</div>
					<div id='shift-div'>	
						<img id='site-icon' class='icon' src='/images/icons/".$event->type."SiteIconColor.png'>
						<h4 class='site-desc ".$event->type."'>".$event->getDetailViewDate()."</h4>
						<h4 class='header' style='margin: 0 0 5px 30px'>".$event->getLocation()."</h4>
						<div class='preceptors dark-gray '>".$event->getPreceptorText()."</div>
					</div>";
				
		if ($user->getCurrentUserContext()->hasConflict($event->start_datetime, $event->end_datetime)) {
			$returnContent .= "<div class='notice shift-conflict'>
						Note: You already have a shift scheduled during this time.
					</div>";
		}
		
		$returnContent .= "</div>
				<div class='signup-buttons'>
					<div id='cancelButtonWrapper' class='small gray-button'>
						<a href='#' id='signup-cancel-btn'>Cancel</a>
					</div>
					<div id='signupButtonWrapper' class='small green-buttons'>
						<a href='#' id='signup-btn' data-eventid=".$event->id.">Confirm</a>
					</div>
				</div>";		

		return $returnContent;
	}

}
