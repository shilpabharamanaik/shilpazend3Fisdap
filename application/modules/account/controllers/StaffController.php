<?php

class Account_StaffController extends Fisdap_Controller_Private
{

    public function init()
    {
        /* Initialize controller here */
		$this->checkPerm();
    }

    public function indexAction()
    {
		$this->view->pageTitle = "Staff Tools";
    }
	
	public function tcMapAdminAction()
	{
		$this->view->pageTitle = "Transition Course Map - Admin";
		$this->view->states = \Fisdap\EntityUtils::getRepository("TCMapState")->getStates();
	}
	
	public function editStateAction()
	{
		//$this->checkPerm();
		$stateId = $this->_getParam('stateId');
		$state = \Fisdap\EntityUtils::getEntity("TCMapState", $stateId);
		$this->view->pageTitle = $state->name;
		$this->view->form = new \Account_Form_TCMapEditState($state);
		
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			if ($this->view->form->process($request->getPost()) != false) {
				$this->_redirect("/account/staff/tc-map-admin");
			}
		}
	}
	
	private function checkPerm()
	{
		if(!\Fisdap\Entity\User::getLoggedInUser()->isStaff()){
			$this->_redirect("/my-fisdap");
		}
	}


}







