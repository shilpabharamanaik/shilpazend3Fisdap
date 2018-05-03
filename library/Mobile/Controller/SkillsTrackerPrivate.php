<?php

class Mobile_Controller_SkillsTrackerPrivate extends Mobile_Controller_Private
{
    public function preDispatch()
    {
        parent::preDispatch();
		
		$loggedInUser = \Fisdap\Entity\User::getLoggedInUser();

		//Redirect users to graduated message if they're
		if($loggedInUser->getCurrentRoleName() == "student" && $loggedInUser->getCurrentRoleData()->isGraduated() && $this->getRequest()->getActionName() != 'graduated') {
			$this->redirect("/mobile/index/graduated/");
		}
    }
}