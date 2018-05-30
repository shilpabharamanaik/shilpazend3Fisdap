<?php

class SkillsTracker_GraduatedController extends Fisdap_Controller_Private // Zend_Controller_Action
{
    public function init()
    {
    }
    
    public function indexAction()
    {
        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData();
        
        $grad_status_id = $loggedInUser->graduation_status->id;
        switch ($grad_status_id) {
            case 2:
                $status = "graduated";
                break;
            case 3:
                $status = "have completed your program";
                break;
            case 4:
                $status = "have left your program";
                break;
            default:
                $status = "have reached this page in error";
        }
        
        $this->view->status = $status;
    }
}
