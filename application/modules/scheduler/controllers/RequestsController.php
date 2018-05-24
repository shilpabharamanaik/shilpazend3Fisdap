<?php

/**
 *
 * @package    Scheduler
 * @subpackage Controllers
 */
class Scheduler_RequestsController extends Fisdap_Controller_Private
{
	public function init()
    {
		parent::init();
		
		// redirect to login if the user is not logged in yet
		if (!$this->user) {
		    return;
		}
		
		if ($this->user->getCurrentProgram()->scheduler_beta == 0) {
			$this->_redirect("/scheduler/index/join-beta");
		}
    }
	
	public function indexAction()
	{
		// Check permissions - any student WITH SCHEDULER can view this page, but only instructors with some kind
		// of schedule editing permission can view it
		if ($this->user->isInstructor() &&
		    !($this->user->hasPermission("Edit Field Schedules") ||
		      $this->user->hasPermission("Edit Clinic Schedules") ||
		      $this->user->hasPermission("Edit Lab Schedules"))
		    ) {
			$this->displayError("You don't have permission to view this page.");
			return;
		} else if (!$this->user->isInstructor()){
			$has_scheduler = false;
			
			foreach($this->user->serial_numbers as $sn){
				if(((boolean)($sn->configuration & 2) || (boolean)($sn->configuration & 8192))){
					$has_scheduler = true;
				} 
			}
			
			if(!$has_scheduler){
				$this->displayError("You do not have scheduler on your account.");
				return;
			}
		}
		
		$this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/shift-request-table.css");
		$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/shift-request-table.js");
		$this->view->headScript()->appendFile("/js/jquery.cluetip.js");
		$this->view->headScript()->appendFile("/js/jquery.fieldtag.js");
		$this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");
		
		$this->view->pageTitle = "Shift Requests";
		
		if ($this->user->isInstructor()) {
            $this->view->requests = \Fisdap\EntityUtils::getRepository("ShiftRequest")->getRequestsByProgram($this->user->getProgramId(), true, true);
            $this->view->requestResponseModal = new Scheduler_Form_RequestApprovalModal();
		} else {
			$this->view->requests = \Fisdap\EntityUtils::getRepository("ShiftRequest")->getRequestsByOwner($this->user->getCurrentUserContext()->id);
			$this->view->requestResponseModal = new Scheduler_Form_RequestResponseModal();
			
			$this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/request-cancel-modal.js");
			$this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/request-cancel-modal.css");
			$viewHelper = new Scheduler_View_Helper_RequestCancelModal();
			$this->view->requestCancelModal = $viewHelper->requestCancelModal();
			$this->view->swapHistoryModal = $this->view->swapHistoryModal();
		}
		
		$this->view->tour = new Fisdap_View_Helper_GuidedTourHelper();
		$this->view->tour_id = ($this->user->isInstructor()) ? 8 : 9;
	}
	
	public function generateRequestResponseFormAction(){
		$request_id = $this->_getParam('request_id');
		$state_id = $this->_getParam('state_id');
		$form = new Scheduler_Form_RequestResponseModal($request_id, $state_id);
		$this->_helper->json($form->__toString());
	}

	public function processRequestResponseAction()
	{
		$formValues = $this->_getAllParams();
		$request_id = $this->_getParam('request_id');
		$state_id = $this->_getParam('state_id');
		$form = new Scheduler_Form_RequestResponseModal($request_id, $state_id);
		$this->_helper->json($form->process($formValues));
	}
	
	public function generateRequestApprovalFormAction(){
		$request_id = $this->_getParam('request_id');
		$form = new Scheduler_Form_RequestApprovalModal($request_id);
		$this->_helper->json($form->__toString());
	}

	public function processRequestApprovalAction()
	{
		$formValues = $this->_getAllParams();
		$request_id = $this->_getParam('request_id');
		$state_id = $this->_getParam('state_id');
		$form = new Scheduler_Form_RequestApprovalModal($request_id, $state_id);
		$this->_helper->json($form->process($formValues));
	}
	
	public function generateRequestCancelModalAction()
	{
	    $request_id = $this->_getParam('request_id');
	    $user = \Fisdap\Entity\User::getLoggedInUser();
	    $request = \Fisdap\EntityUtils::getEntity("ShiftRequest", $request_id);
	    
	    if ($user->getCurrentUserContext()->id == $request->owner->id) {
		$type = $request->request_type->name;
	    }
	    
	    if ($user->getCurrentUserContext()->id == $request->recipient->id) {
		$type = "offer";
	    }
	    
	    $viewHelper = new Scheduler_View_Helper_RequestCancelModal();
	    $this->_helper->json($viewHelper->generateRequestCancel($request_id, $type));
	}
    	
	public function processRequestCancelAction()
	{
		$request_id = $this->_getParam('request_id');
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$request = \Fisdap\EntityUtils::getEntity("ShiftRequest", $request_id);
		
		if ($user->getCurrentUserContext()->id == $request->owner->id) {
			// send emails
			$mail = new \Fisdap_TemplateMailer();
			$mail->addTo($user->email)
			     ->setSubject(ucfirst($request->request_type->name)." request cancelled")
			     ->setViewParam("request", $request)
			     ->sendHtmlTemplate("shift-request-cancelled-owner.phtml");
			     
			$mail->clearRecipients();
	
			$email_list = $request->getCancelEmails();
			foreach ($email_list as $email) {
				$mail->addTo($email);
			}
			if (count($email_list) > 0) {
				$mail->sendHtmlTemplate("shift-request-cancelled-others.phtml");
			}
			
			$request->set_accepted(7);
			$request->save();
			
			$this->_helper->json(true);
		}
		
		if ($user->getCurrentUserContext()->id == $request->recipient->id) {
			$swap = $request->getCurrentSwap();
			$swap->set_accepted(7);
			$request->save();
			
			// send emails
			$mail = new \Fisdap_TemplateMailer();
			$mail->addTo($user->email)
			     ->setSubject("Swap offer cancelled")
			     ->setViewParam("request", $request)
			     ->setViewParam("swap", $request->getCurrentSwap())
			     ->setViewParam("url", \Util_HandyServerUtils::getCurrentServerRoot() . "scheduler/requests")
			     ->sendHtmlTemplate("shift-offer-cancelled-recipient.phtml");
			$mail->clearRecipients()
			     ->addTo($request->owner->user->email)
			     ->sendHtmlTemplate("shift-offer-cancelled-owner.phtml");
			     
			$this->_helper->json(true);
		}
		
		$this->_helper->json(false);
	}
	
	public function generateSwapHistoryAction()
	{
	    $request_id = $this->_getParam('request_id');
	    $viewHelper = new Scheduler_View_Helper_SwapHistoryModal();
	    $this->_helper->json($viewHelper->generateSwapHistory($request_id));
	}

    public function approveDenyRequestsAction()
    {
        $requests = $this->_getParam('checkedRequests');
        $state_id = $this->_getParam('state_id');

        foreach($requests as $request_id) {
            $form = new Scheduler_Form_RequestApprovalModal($request_id, $state_id);
            $form->process(array());
        }

        $this->_helper->json(true);
    }
	
}
