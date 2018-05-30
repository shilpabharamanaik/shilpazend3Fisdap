<?php

class SkillsTracker_IndexController extends Fisdap_Controller_SkillsTracker_Private
{
    public function init()
    {
        parent::init();

        if ($this->userContext->isStudent()) {
            $serialNumber = $this->userContext->getPrimarySerialNumber();
            $hasScheduler = $serialNumber->hasScheduler();
            $doesNotHaveTracking = !$serialNumber->hasSkillsTracker();

            if ($hasScheduler && $doesNotHaveTracking) {  // student has Scheduler but not Tracking
                $this->_redirect("/scheduler");
            }
        }
    }

    public function indexAction()
    {
        $roleName = $this->userContext->getRole()->getName();
        $this->view->roleName = $roleName;

        $this->view->pageTitle = "Shifts";

        $this->view->schedulerLink = "scheduler";
        $this->view->skillsPatientCareLink = "/skills-tracker/shifts";

        switch ($roleName) {
            case "student":
                break;

            case "instructor":
                break;

            default:
                break;
        }
    }

    public function graduatedAction()
    {
        $status = "graduated";

        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData();

        if ($loggedInUser->student->good_data_flag == 4) {
            $status = "left the program";
        }

        $this->view->status = $status;
    }
}
