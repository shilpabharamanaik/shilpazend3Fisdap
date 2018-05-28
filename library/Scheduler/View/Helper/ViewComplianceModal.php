<?php
use Fisdap\Entity\User;
use Fisdap\EntityUtils;

/**
 * This helper will display a modal to display information about compliance for a given assignment
 *
 * @package Scheduler
 */
class Scheduler_View_Helper_ViewComplianceModal extends Zend_View_Helper_Abstract 
{
	
	/**
	 * @var string the html to be rendered
	 */
	protected $_html;
	
	// will create an empty modal
	public function viewComplianceModal()
	{
		
		// set up our modal
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/view-compliance-modal.js");
		
		$this->_html =  "<div id='viewComplianceDialog'>";
		$this->_html .= 	"<div id='view-compliance-modal-content' class='container_12'></div>";
		$this->_html .= "</div>";
		
		return $this->_html;
	}
	
	// generates the content for the modal
	public function generateViewComplianceModal($assignment_id)
	{
		$user = User::getLoggedInUser();
		$assignment = EntityUtils::getEntity("SlotAssignment", $assignment_id);
		$event = $assignment->slot->event;
		$student = $assignment->user_context->getRoleData();
		
		$aboutHelper = new Portfolio_View_Helper_AboutStudent();
		$aboutSection = $aboutHelper->aboutStudent(array(
                    'student' => $student->id,
                    'helpers' => array(
                        'profile-pic' => array(
                            'options' => array(
                                'size' => '150',
                                'class' => 'grid_3'
                            )
                        ),
                        'info',
                        'contact-info',
                    )
		));

		$reqHelper = new Portfolio_View_Helper_UserSiteComplianceTable();
		$filterBy = 'all-reqs';
		
		// if the SITE is part of the shared network, we care about shared requirements
		$getShared = $student->program->sharesSite($event->site->id);
		
		if ($user->getProgramId() == $student->program->id) {
			$attachments = EntityUtils::getRepository("Requirement")->getAttachmentsBySite($assignment->user_context->id, $event->site->id, $filterBy, $getShared, true);
		} else {
			$attachments = EntityUtils::getRepository("Requirement")->getGlobalAttachmentsBySite($assignment->user_context->id, $event->site->id, $filterBy, true);
		}
		$reqTable = $reqHelper->userSiteComplianceTable($assignment->user_context, $event->site, $attachments, $filterBy, $getShared);
				
		$returnContent = "<div id='shift-desc-div'>	
							<img id='site-icon' class='icon' src='/images/icons/".$event->type."SiteIconColor.png'>
							<h4 class='site-desc ".$event->type."'>".$event->getDetailViewDate()."</h4>
							<h4 class='header' style='margin: 0 0 5px 30px'>".$event->getLocation()."</h4>
						</div>";

		// only instructors with the correct permissions get this button
		if ($user->getCurrentRoleName() == 'instructor' && $user->getProgramId() == $student->program->id  && $user->getCurrentRoleData()->hasPermission("Edit Compliance Status")) {
		$returnContent .=               "<div id='cta-buttons' class='extra-small orange-button'>
							<a href='/scheduler/compliance/edit-status' id='edit-status-btn'>Edit compliance status</a>
						</div>";
		}
				
		$returnContent .= 		"<div class='clear'></div>";
		
		// if this user does not belong to the current logged in user's program, check to see if they have permission to view student names
		$see_about = false;
		if($user->getProgramId() == $assignment->user_context->program->id){
			$see_about = true;
		}
		else {
			$see_about = EntityUtils::getEntity("ProgramLegacy", $user->getProgramId())->seesSharedStudents($assignment->slot->event->site->id);
		}
		
		if($see_about) {
			$returnContent .= "				
							<div class='about-student'>
								$aboutSection
							</div>";
		}
		else {
			$returnContent .= "<h2 class='page-title'>Student from " . $student->program->name . "</h2><br />";
		}
		$returnContent .= "
						<div id='status-filter'>
							<input type='radio' id='all-reqs' name='status_type' checked='checked'><label for='all-reqs'>All</label>
							<input type='radio' id='pending' name='status_type'><label for='pending'>In Progress</label>
							<input type='radio' id='compliant-only' name='status_type'><label for='compliant-only'>Compliant</label>
							<input type='radio' id='non-compliant-only' name='status_type'><label for='non-compliant-only'>Non-Compliant</label>
						</div>
						<h3 class='section-header'>Requirements</h3>
						<div class='clear'></div>

						<div id='attachmentsTable'>
							$reqTable	
						</div>

						<div id='closeButtonWrapper' class='small gray-button'>
							<a href='#' id='close-btn'>Ok</a>
						</div>";		

		return $returnContent;
	}

}
