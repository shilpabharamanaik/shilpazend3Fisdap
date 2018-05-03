<?php

class Fisdap_Controller_SkillsTracker_Private extends Fisdap_Controller_Private
{
    public function preDispatch()
    {
		parent::preDispatch();

		//Redirect users to graduated message if they're graduated
		if($this->userContext->isStudent() && $this->userContext->getRoleData()->isGraduated()) {
			$this->_redirect("/skills-tracker/graduated/");
		}
    }
}