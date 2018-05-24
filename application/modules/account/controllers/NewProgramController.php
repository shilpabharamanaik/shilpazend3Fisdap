<?php
use Fisdap\Data\CertificationLevel\CertificationLevelRepository;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class Account_NewProgramController
 */
class Account_NewProgramController extends Fisdap_Controller_Base
{
    public function init()
    {
		parent::init();
		$this->view->headScript()->appendFile("/js/jquery.cluetip.js");
		$this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");
		$this->view->hideSubFooter = true;
		$this->session = new Zend_Session_Namespace("NewProgramController");

		if ($this->session->authenticated == false && $this->getRequest()->getActionName() != "index") {
			$this->redirect("/account/new-program/index", array("exit" => true));
		}

		if ($this->session->acceptedAgreement == false && !in_array($this->getRequest()->getActionName(), array("index", "agreement"))) {
			$this->redirect("/account/new-program/agreement", array("exit" => true));
		}

		if (\Zend_Auth::getInstance()->hasIdentity()) {
			$this->globalSession->newProgramRedirect = true;
			$this->redirect("/login/logout");
		}
    }


	public function indexAction()
	{
		$this->view->pageTitle = "Welcome to Fisdap!";

		$request = $this->getRequest();
		if ($request->isPost()) {
			$password = $this->getParam("password");
			if ($password === "startnewprogram") {
				$this->session->authenticated = true;
				$this->session->acceptedAgreement = true;
				$this->redirect("/account/new-program/welcome", array("exit" => true));
			} else {
				$this->flashMessenger->addMessage("The password you entered is incorrect. Please try again.");
				$this->redirect("/account/new-program");
			}
		}
	}


	public function welcomeAction(CertificationLevelRepository $certificationLevelRepository)
	{
		$this->view->pageTitle = "Welcome to Fisdap!";
		$this->view->form = new \Account_Form_BasicProgramInfo();

		$this->view->certifications = array();
		$certifications = $certificationLevelRepository->findAll();
		
		foreach($certifications as $cert) {
			$this->view->certifications[$cert->profession->id][] = $cert->description;
		}
		
		$request = $this->getRequest();
		if ($request->isPost()) {
			if ($this->view->form->isValid($request->getPost())) {
				$this->session->basicProgramValues = $this->view->form->getValues();
				$this->redirect("/account/new-program/contact-info");
			}
		}

	}


    /**
     * @param Dispatcher $dispatcher
     */
    public function contactInfoAction(Dispatcher $dispatcher)
	{
		$this->view->pageTitle = "Organization Information";

		//Redirect to welcome page if no basic info is set
		if (!isset($this->session->basicProgramValues)) {
			$this->redirect("/account/new-program/welcome", array("exit" => true));
		}

		//Determine if we should show EMS training specific info
		$basicProgramInfo = $this->session->basicProgramValues;
		if ($basicProgramInfo['orgType'] == 1 || ($basicProgramInfo['orgType'] == 2 && $basicProgramInfo['emsProviderTraining'] == 1)) {
			$showEmsInfo = true;
		} else {
			$showEmsInfo = false;
		}

		// Create form for program creation
		$this->view->form = new Account_Form_Program($this->session->programId, $showEmsInfo, $basicProgramInfo['profession']);

		$request = $this->getRequest();
		if ($request->isPost()) {
			if ($programId = $this->view->form->process($request->getPost(), $dispatcher)) {
				$this->session->programId = $programId;
				$program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
				switch($basicProgramInfo['orgType']) {
					case 1:
						$programTypes = array(1);
						break;
					case 2:
						$programTypes = $basicProgramInfo['emsProviderTraining'] == 1 ? array(1, 4) : array(4);
						break;
					case 3:
						$programTypes = array(2);
						break;
				}
				$program->setProgramTypeIds($programTypes);
				$program->save();

				$this->redirect("/account/new-program/administrator-info");
			}
		}
	}


	public function administratorInfoAction()
	{
		$this->view->pageTitle = "Your administrator account";

		//Go back to program page if a program ID is not set
		if (!isset($this->session->programId)) {
			$this->redirect("/account/new-program/contact-info", array("exit" => true));
		}

		$this->view->form = new Account_Form_Instructor(null, null, $this->session->programId);

		$request = $this->getRequest();
		if ($request->isPost()) {
			if ($userId = $this->view->form->process($request->getPost())) {
				$user = \Fisdap\EntityUtils::getEntity("User", $userId);

				//set the accepted agreement flag to true because they've already agreed to it
				$user->accepted_agreement = true;

				//Give the admin account all permissions
				$user->getCurrentRoleData()->permissions = \Fisdap\EntityUtils::getRepository("Permission")->getAllPermissionsBits();

				//Get the program to do some stuff
				$program = $user->getCurrentProgram();

				//Set this user to the primary contact for their program, also set their email to the billing email
				$program->program_contact = $user->getCurrentRoleData()->id;
				$program->billing_email = $user->email;
				$program->billing_contact = $user->getName();

				//Create demo account for this school
				$demoUserId = $program->createDemoAccount($user);

				//Create new site/base for this program
				$program->createDemoSites();

				//Create lab practice definitions for the new program
				$program->createDefaultPracticeDefinitions();

				//Create default narrative section for program
				$program->createDefaultNarrativeSection();
				
				//One last flush to make sure everything saved
				$user->save();

				//Save the two user IDs into the session, we'll use them on the last page
				$this->session->userId = $user->id;
				$this->session->demoUserId = $demoUserId;

				//Email staff about the new program
				$mail = new \Fisdap_TemplateMailer();
				$mail->addTo("support@fisdap.net")
					 ->setSubject("A new program has been created in Fisdap")
					 ->setViewParam('program', $program)
					 ->sendHtmlTemplate('new-program-created.phtml');

				$this->redirect("/account/new-program/finish");
			}
		}
	}


	public function finishAction()
	{
		$this->view->pageTitle = "Thank you. Your organization is now using Fisdap.";

		$this->view->user = \Fisdap\EntityUtils::getEntity("User", $this->session->userId);
		$this->view->demoUser = \Fisdap\EntityUtils::getEntity("User", $this->session->demoUserId);

		$this->session->unsetAll();
	}
}