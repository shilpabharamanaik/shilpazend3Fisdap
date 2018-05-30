<?php

class MyFisdap_IndexController extends Fisdap_Controller_Private
{
    public function init()
    {
        parent::init();
        $this->loggedInUser =  $this->user;

        if ($this->user && !$this->user->getCurrentRoleData()->program->use_beta) {
            $oldFisdapHome = "/oldfisdap/redirect/?loc=" . urlencode("index.html");
            $this->_redirect($oldFisdapHome);
            return;
        }

        if ($this->user && $this->user->getRedirectionPage() != "dashboard") {
            $this->_redirect($this->user->getRedirectionPage());
            return;
        }
    }

    public function indexAction()
    {
        $this->view->pageTitle = "MyFisdap Dashboard";
        $this->view->headScript()->appendFile("/js/jquery.cluetip.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");
    }
}
