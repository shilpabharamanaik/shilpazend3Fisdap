<?php

/**
 *
 * @package    Scheduler
 * @subpackage Controllers
 */
class Scheduler_SettingsController extends Fisdap_Controller_Private
{
    public function init()
    {
        parent::init();
        
        // redirect to login if the user is not logged in yet
        if (!$this->user) {
            return;
        }
        
        if ($this->user->getCurrentProgram()->scheduler_beta == 0) {
            $this->_redirect("/scheduler/index/join-beta");
        }
    }
    
    
    public function indexAction()
    {
        //Check permissions
        if (!$this->user->isInstructor()) {
            $this->displayError("You don't have permission to view this page.");
            return;
        } elseif (!$this->user->hasPermission("Edit Program Settings")) {
            $this->displayPermissionError("Edit Program Settings");
            return;
        }
        
        $this->view->headScript()->appendFile("/js/jquery.cluetip.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");
        
        $form = new Scheduler_Form_Settings();
        
        if ($this->getRequest()->isPost()) {
            $form->process($this->getRequest()->getPost());
            //$this->flashMessenger->addMessage("Your changes have been saved.");
            $this->_redirect("/skills-tracker/settings/");
        } else {
            $this->view->pageTitle = "Scheduler Settings";
            $this->view->form = $form;
        }
    }
    
    public function autosaveAction()
    {
        $formData = $this->_getParam('form');
        $data = array();
        
        foreach ($formData as $element) {
            $data[$element['name']] = $element['value'];
        }
        
        $form = new Scheduler_Form_Settings();
        $form->process($data);
        $this->_helper->json('Your settings have been saved.');
    }

    public function subscriptionsAction()
    {
        $this->view->pageTitle = "Manage Calendar Subscriptions";
        $this->view->calendarFeeds = \Fisdap\EntityUtils::getRepository("CalendarFeed")->findBy(array("user_context" => $this->user->getCurrentUserContext()));
        $this->view->programId = $this->user->getCurrentProgram()->id;
    }

    public function processDeleteSubscriptionAction()
    {
        $calendarId = $this->getParam("calendarId");
        $calendarFeed = \Fisdap\EntityUtils::getEntity("CalendarFeed", $calendarId);

        if ($calendarFeed->user_context->id == \Fisdap\Entity\User::getLoggedInUser()->getCurrentUserContext()->id) {
            $calendarFeed->delete();
            $this->_helper->json(true);
        } else {
            $this->_helper->json(false);
        }
    }
}
