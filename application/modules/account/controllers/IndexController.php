<?php

class Account_IndexController extends Fisdap_Controller_Private
{

    public function init()
    {
        /* Initialize action controller here */
        parent::init();
    }

    public function indexAction()
    {
        $this->view->pageTitle = "Account";
        $this->view->programCanOrder = ($this->user->isStaff() || $this->userContext->getProgram()->order_permission->id != 3) ? true : false;

        if ($this->userContext->isInstructor()) {
            $instructor = $this->userContext->getRoleData();
            // deal with permissions
            $this->view->canOrder = ($instructor->hasPermission("Order Accounts")) ? true : false;
            $this->view->canEditInstructors = ($instructor->hasPermission("Edit Instructor Accounts")) ? true : false;
            $this->view->canEditProgram = ($instructor->hasPermission("Edit Program Settings")) ? true : false;
            $this->view->canEditEvals = ($instructor->hasPermission("Enter Evals")) ? true : false;
            $this->view->canEditStudents = ($instructor->hasPermission("Edit Student Accounts")) ? true : false;
            $this->view->canEditCompliance = ($instructor->hasPermission("Edit Compliance Status")) ? true : false;
            $this->view->isStaff = ($this->user->isStaff()) ? true : false;
            $this->view->instructorId = $this->user->getCurrentRoleData()->id;

            $this->render('account-instructor');
        } else {
            $this->view->transitionOnly = ($this->userContext->getPrimarySerialNumber()->hasTransitionCourse());

            $this->render('account-student');
        }
    }

    public function programEvalAction()
    {
        // action body

        $dummyStudentID = NULL; // this hook is not associated with a given user
        $dummyShiftID = NULL; // this hook is not associated with a given shift
        $dummyHookID = 120; // globl program hook

        $this->view->evalListLink = Util_GetLegacyTopNavLinks::getLink(Util_GetLegacyTopNavLinks::EVAL_LIST, $this->view->serverUrl());
        $this->view->studentID = $dummyStudentID;
        $this->view->shiftID = $dummyShiftID;
        $this->view->hookID = $dummyHookID;

    }

    public function guidedTourTestAction()
    {
        $this->view->pageTitle = "Guided tour test!";
        $this->view->headLink()->appendStylesheet("/css/library/Account/Form/site-sub-forms/accreditation.css");
        $this->view->headScript()->appendFile("/js/library/Account/Form/site-sub-forms/accreditation.js");

        $site = \Fisdap\EntityUtils::getEntity("SiteLegacy", 12389);
        $this->view->form = new Account_Form_Accreditation($site);

        $this->view->headScript()->appendFile("/js/jquery.fieldtag.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.sliderCheckbox.css");
        $this->view->headScript()->appendFile("/js/jquery.sliderCheckbox.js");
        $this->view->headScript()->appendFile("/js/jquery.busyRobot.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.busyRobot.css");
        $this->view->headScript()->appendFile("/js/jquery.maskedinput-1.3.js");

        $this->view->tour = new Fisdap_View_Helper_GuidedTourHelper();
    }


}

