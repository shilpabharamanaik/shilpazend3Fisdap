<?php

/**
 * the main controller for viewing the current session and relevant info
 *
 * @package    admin
 * @subpackage Controllers
 */
class Admin_SessionController extends Fisdap_Controller_Private
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $this->view->pageTitle = "Current Session";

        $this->view->session = $this->globalSession;
        $this->view->oldsession = $_SESSION;
        $this->view->currentuser = $this->user;
        $this->view->currentcontext = $this->user->getCurrentUserContext();
    }
}
