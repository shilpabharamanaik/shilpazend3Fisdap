<?php

class Account_HelpController extends Fisdap_Controller_Base
{

    public function init()
    {
		parent::init();
		$this->view->headScript()->appendFile("/js/jquery.cluetip.js");
		$this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");
    }

	public function indexAction()
	{
		$this->view->pageTitle = "Account Help";
		// for now just send them to forgot
		$this->_redirect("/account/help/forgot");

	}
	
	public function forgotAction()
	{
		if(\Fisdap\Entity\User::getLoggedInUser()){
			$this->displayError("You've reached this page in error.");
			return;
		}
		$this->view->pageTitle = "Forgot Your Login?";
		$this->view->form = new Account_Form_ResetPasswordEmailCollection();
	}
	
	public function emailConfirmationAction()
	{
		$this->view->pageTitle = "Reset Password";
		$user = \Fisdap\EntityUtils::getEntity("User", $this->_getParam('user'));
		if(!$user){
			$this->_redirect("/login");
		}
		$this->view->email = $user->email;
		
		if(!$this->createPaswordReset($user)){
			echo "An error has occured";
		}
	}
	
	public function resetPasswordAction()
	{
		$this->view->pageTitle = "Reset Password";
		$code = \Fisdap\EntityUtils::getEntity('PasswordReset')->getByCode($this->_getParam('code'));
		
		if($code){
			if($code->expiration_date > new \DateTime()){
				// it's ok to be here, show the form
				$this->view->form = new Account_Form_ResetPassword($code->user->id, $code->id);
				$request = $this->getRequest();
				if ($request->isPost()){
					if (!empty($this->view->form) && $this->view->form->process($request->getPost()) === true) {
						$this->flashMessenger->addMessage("Your password has been reset.");
						$this->_redirect("/login");
					}
				}
			}
			else {
				// this code has expired
				$this->view->error = "<div class='error'>We're sorry, but this code has expired.</div><div id='goBack'>Please <a href='/account/help/forgot'>click here</a> to get another code.</div>";
			}
		}
		else {
			// not a valid code
			$this->view->error = "<div class='error'>We're sorry, but it appears that your code is not valid.</div><div id='goBack'>Please <a href='/account/help/forgot'>click here</a> to get another code.</div>";
		}
		
		

	}
	
	public function searchAccountsAction()
	{
		$email = $this->_getParam('email');
		$users = \Fisdap\EntityUtils::getEntityManager()->getRepository("\Fisdap\Entity\User")->getUsersByEmail($email);
		if($users){
			$accountsFound = array();
			foreach($users as $user){
				$accountsFound[] = $user->id;
				$returnText .= "<a class='userList' href='/account/help/email-confirmation/user/"
								. $user->id . "'>" . $user->first_name . " " . $user->last_name . " - " . $user->username . "</a>";
			}
		}
		
		// we didn't find anything with this email address
		if(strlen($returnText) == 0){
			$returnText = "none";
		}
		
		// we found exactly one account, just return its id
		if(count($accountsFound) == 1) {
			$returnText = end($accountsFound);
		}
		
		$this->_helper->json($returnText);
	}
	
	public function createPaswordReset($user)
	{
		// create a new password reset
		$pswReset = \Fisdap\EntityUtils::getEntity('PasswordReset');
		$pswReset->user = $user;
		$pswReset->code = $pswReset->generateCode();
		$pswReset->expiration_date = new \DateTime("+1 day");
		$pswReset->save();
		
		// email the url and the directions
		$pswReset->email();
				
		return true;
	}
}
