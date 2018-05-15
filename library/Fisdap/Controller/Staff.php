<?php

class Fisdap_Controller_Staff extends Fisdap_Controller_Private
{
    public function preDispatch()
    {
        parent::preDispatch();
        
        // if they're not a staff member, send them away
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->_redirect('/');
        }
    }
}
