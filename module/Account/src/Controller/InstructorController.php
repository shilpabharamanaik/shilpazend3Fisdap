<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;
use Zend\Http\Response;

use User\Entity\User;
use User\Entity\UserContext;
use User\Entity\UserRole;

use User\Entity\Instructor;
use User\Form\InstructorForm;
use Account\Form\NewInstructorForm;

use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\EntityUtils;

//use User\Entity\ProgramLegacy;
//use User\Entity\InstructorLegacy;




class InstructorController extends AbstractActionController
{

    /**
     * Session manager.
     * @var Zend\Session\SessionManager
     */
    private $sessionManager;

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $objUser;
    private $objUserRole;
    private $username;

    /**
     * Constructs the service.
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $userSession = new Container('user');

        $this->username = $userSession->username;

        $this->objUser = $this->entityManager->getRepository(User::class)
                            ->findOneByUsername($this->username);
        /*$this->objUserRole = $this->entityManager->getRepository(UserRole::class)
                            ->findOneByUserId($this->objUser->getId()); */
    }


    public function editAction()
    {
        if (!$this->objUserRole->isInstructor()) {
            return new ViewModel([
                'displayError' => true,
            ]);
        }

        // Grab the instructor from either the URL or session
        $instructorSession = new Container('instructor');
        //$instructorId = $this->getParam('instructorId', $this->globalSession->instructorId);
        $instructorId = (int)$this->params()->fromRoute('instructorId', $instructorSession->instructorId);

        // If an instructor hasn't been set yet, choose the logged in instructor
        if (!$instructorId) {
            $objInstructor = $this->entityManager->getRepository(Instructor::class)
                                ->findOneByUserId($this->objUser->getId());
            $instructorId = $objInstructor->getInstructorId();
            //$instructorId = $this->currentUser->context()->getRoleData()->getId();
        }

        // Save the selected instructor in the session
        //$this->globalSession->instructorId = $instructorId;
        $instructorSession->instructorId = $instructorId;

        /** @var \Fisdap\Entity\InstructorLegacy $instructor */
        //$instructor = EntityUtils::getEntity('InstructorLegacy', $instructorId);
        $objInstructor = $this->entityManager->getRepository(Instructor::class)
                            ->findOneById($instructorId);

        // Check to make sure we can view the given instructor
        /*
        if ($instructor->getUserContext()->getProgram()->getId() != $this->currentUser->context()->getProgram()->getId()) {
            unset($this->globalSession->instructorId);
            $this->view->instructorId = null;
        } else {
            if ($instructor->getId() != $this->currentUser->context()->getRoleData()->getId()
                && !$this->currentUser->user()->hasPermission(
                    "Edit InstructorLegacyctor Accounts"
                )
            ) {
                $this->displayError(
                    "You do not have permission to edit other instructor accounts. Please contact "
                    . $this->currentUser->context()->getProgram()->getProgramContactName() . " for more information."
                );
                unset($this->globalSession->instructorId);

                return;
            }
        }*/

        // Create instructor form
        $form = new InstructorForm('update', $this->entityManager, $objInstructor);

        //$tmpForm = new Account_Form_Instructor($instructorId);

        return new ViewModel([
            'instructorId' => $instructorId,
            'instructor' => $objInstructor,
            'form' => $form,
            'username' => $this->username,
        ]);
    }
	
	 public function newinstructorAction(){
	  //If we have a SN, we're activating an account with products
        /*if ($this->hasParam("sn")) {
            $this->view->pageTitle = "Activate Your Account";
            $serial = SerialNumberLegacy::getBySerialNumber($this->_getParam("sn"));

            //Display an error if the given serial number is already in use
            if ($serial->isActive()) {
                $this->displayError("This serial number has already been activated by " . $serial->user->getName() . " on " . $serial->activation_date->format("m-d-Y"));
                return;
            }

            $this->view->accountDetails = $serial->getAccountDetails();*/
             $form = new NewInstructorForm();
        /*} else {
            //Otherwise, we're already logged in, creating a free account
            $pageTitle = "Create a new Instructor";

            //Display an error if the user does not have permission to create instructor accounts
            if (!$this->objUser->hasPermission("Edit Instructor Accounts")) {
                $this->displayError("You do not have permission to create instructor accounts. Please contact " . $this->objUser->getCurrentProgram()->getProgramContactName() . " for more information.");
                return;
            }
             $form = new NewInstructorForm();

        }
/*
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($userId = $this->view->form->process($request->getPost())) {
                if ($this->hasParam("sn")) {
                    $this->redirect("/account/new/user-agreement/userId/" . $userId);
                } else {
                    $this->flashMessenger->addMessage("Instructor Account successfully created.");
                    $this->redirect("/account/new/instructor/");
                }
            }
        }
		 */
		  return new ViewModel([
            'form' => $form,
        ]);  
	 }
	
}
