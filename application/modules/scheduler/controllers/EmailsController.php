<?php

use Fisdap\Data\ScheduleEmail\ScheduleEmailRepository;
use Fisdap\Entity\ScheduleEmail;
use Fisdap\Entity\User;


/**
 *
 * @package    Scheduler
 * @subpackage Controllers
 */
class Scheduler_EmailsController extends Fisdap_Controller_Private
{
	/**
	 * @param ScheduleEmailRepository $scheduleEmailRepository
	 */
	public function indexAction(ScheduleEmailRepository $scheduleEmailRepository)
	{
		// Check permissions
		if (!$this->user->isInstructor()) {
			$this->displayError("You don't have permission to view this page.");
			return;
		} else if (!$this->user->hasPermission("View Schedules")) {
			$this->displayPermissionError("View Schedules");
			return;
		}
		
		$this->view->headScript()->appendFile("/js/jquery.cluetip.js");
		$this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");
		
		$this->view->pageTitle = "Manage Recurring Emails";

		$this->view->program = $program = $this->user->getCurrentProgram();

		// get recurring emails
		$this->view->emails = $scheduleEmailRepository->getScheduleEmails($program->id);

		/** @var ScheduleEmail $email */
		foreach($this->view->emails as $email){
			$email->updateDependentIDsFromFilters();
		}
		
		// get the edit modal
		$this->view->schedulerPdfModal = new Scheduler_Form_PdfExportModal();
		$this->view->headLink()->appendStylesheet("/css/library/Scheduler/Form/pdf-export-modal.css");
	}


	public function updateActiveAction(ScheduleEmailRepository $scheduleEmailRepository)
	{
		$email_id = $this->_getParam('email_id');
		$active = $this->_getParam('active');
		
		/** @var ScheduleEmail $email */
		$email = $scheduleEmailRepository->getOneById($email_id);

		$email->active = ($active == 1) ? true : false;

		$userUpdated = false;

		if ($email->instructor->user->deleted === true) {
			$email->instructor = User::getLoggedInUser()->getCurrentRoleData();
			$userUpdated = true;
		}

		$scheduleEmailRepository->update($email);

		$this->_helper->json([
				'email_id' => $email->id,
				'active' => $email->active,
				'user_updated' => $userUpdated,
				'instructor_first_name' => $email->instructor->user->first_name,
				'instructor_last_name' => $email->instructor->user->last_name,
			]
		);
	}


	public function generateDeleteEmailAction(ScheduleEmailRepository $scheduleEmailRepository)
	{
	    $email_id = $this->_getParam('email_id');

		/** @var ScheduleEmail $scheduleEmail */
		$scheduleEmail = $scheduleEmailRepository->getOneById($email_id);

	    $this->_helper->json($this->generateDeleteRecurringEmailModal($scheduleEmail));
	}


	/**
	 * @param ScheduleEmail $scheduleEmail
	 *
	 * @return string
	 */
	protected function generateDeleteRecurringEmailModal(ScheduleEmail $scheduleEmail)
	{
		return "<div id='delete-modal-content'>
					Deleting
					<span id='email-title' class='dark-gray'>".$scheduleEmail->title."</span>
					will remove it from your email list and discontinue the recurring emails to the recipients.
				</div>

				<div class='delete-email-buttons'>
					<div id='cancelButtonWrapper' class='small gray-button'>
						<a href='#' id='cancel-delete-btn'>Cancel</a>
					</div>
					<div id='confirmButtonWrapper' class='small green-buttons'>
						<a href='#' id='confirm-delete-btn' data-emailid=".$scheduleEmail->id.">Confirm</a>
					</div>
				</div>";
	}


	public function processDeleteEmailAction(ScheduleEmailRepository $scheduleEmailRepository)
	{
		// get the recurring email
		$email_id = $this->_getParam('email_id');
		$email = $scheduleEmailRepository->getOneById($email_id);
		
		if ($this->user->hasPermission("View Schedules") && $this->user->getProgramId() == $email->program->id) {
			// delete email
			$scheduleEmailRepository->destroy($email);
			$this->_helper->json(true);
		}
		
		$this->_helper->json(false);
	}
}
