<?php

/**
 * the main controller for EMS bike rides.
 *
 * @package    Fisdap
 * @subpackage Controllers
 */
class BikeRideController extends Fisdap_Controller_Base
{
    public function init()
    {
        parent::init();
        $this->session = new Zend_Session_Namespace();
        $this->session->validPass;
    }
    
    public function indexAction()
    {
        $this->view->pageTitle = "National EMS Memorial Bike Ride";
        $eventId = $this->getParam("eventId", 18);
        $this->view->form = new Fisdap_Form_BikeRide($eventId);
        $this->view->headScript()->appendFile("/js/library/Fisdap/Utils/create-pdf.js");
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            if ($riderId = $this->view->form->process($request->getPost())) {
                $this->flashMessenger->addMessage("Your bike ride was successfully recorded.");
                $this->_redirect("/bike-ride/success/?riderId=$riderId");
            }
        }
    }
    
    public function eventAction()
    {
        if (\Fisdap\Entity\User::getLoggedInUser() == null) {
            $this->_redirect('/');
        }
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->_redirect('/');
        }
        
        $this->view->pageTitle = "Setup form for the National EMS Memorial Bike Ride";
        $this->view->form = new Fisdap_Form_BikeRideEvent($this->_getParam("eventId"));
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost()) == true) {
                $this->flashMessenger->addMessage("Your event was successfully updated.");
                $this->_redirect("/bike-ride/show-event/");
            }
        }
    }
    
    public function deleteAction()
    {
        if (\Fisdap\Entity\User::getLoggedInUser() == null) {
            $this->_redirect('/');
        }
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->_redirect('/');
        }
        
        $event = \Fisdap\EntityUtils::getEntity("BikeRideEvent", $this->_getParam("eventId"));
        $event->delete();
        $this->flashMessenger->addMessage("This event was successfully deleted.");
        $this->_redirect("/bike-ride/show-event/");
    }
    
    public function createAction()
    {
        if (\Fisdap\Entity\User::getLoggedInUser() == null) {
            $this->_redirect('/');
        }
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->_redirect('/');
        }
        
        $this->view->pageTitle = "Setup form for the National EMS Memorial Bike Ride";
        $this->view->form = new Fisdap_Form_BikeRideEvent();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost()) == true) {
                $this->flashMessenger->addMessage("Your event was successfully created.");
                $this->_redirect("/bike-ride/show-event/");
            }
        }
    }
    
    public function duplicateEventAction()
    {
        $event = \Fisdap\EntityUtils::getEntity("BikeRideEvent", $this->_getParam("eventId"));
        $newEvent = clone($event);
        $newEvent->save();
        
        $this->_redirect("/bike-ride/show-event");
    }
    
    
    public function reportAction()
    {
        //Redirect the user if they're not authorized to view this page
        if ((\Fisdap\Entity\User::getLoggedInUser() == null || !\Fisdap\Entity\User::getLoggedInUser()->isStaff()) && $this->session->validPass == null) {
            $this->_redirect("/bike-ride/verify-user/");
        }
        
        $this->view->pageTitle = "Current Report for EMS Bike Riders";
        $this->view->passcode = $this->_getParam("passcode");
    }
    
    public function verifyUserAction()
    {
        $this->view->form = new Fisdap_Form_AuthorizeViewer();
        $this->view->pageTitle = "Authorization to view current report for EMS Bike Riders";
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            if ($result = $this->view->form->process($request->getPost())) {
                $this->session->validPass = true;
                $this->flashMessenger->addMessage("Your pass code was successfully authenticated.");
                $this->_redirect("/bike-ride/report/?passcode=$result");
            } else {
                $this->session->validPass = false;
                $this->flashMessenger->addMessage("Your pass code was incorrect, please try again.");
                $this->_redirect("/bike-ride/verify-user/");
            }
        }
    }
    
    public function showEventAction()
    {
        if (\Fisdap\Entity\User::getLoggedInUser() == null) {
            $this->_redirect('/');
        }
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->_redirect('/');
        }
        
        $this->view->pageTitle = "Display table for National EMS Memorial Bike Ride events.";
    }
    
    public function successAction()
    {
        $this->view->pageTitle = "EMS Bike Ride Registration";
        $this->view->rider = \Fisdap\EntityUtils::getEntity("BikeRiderData", $this->_getParam("riderId"));
        
        $payPalParams = \Zend_Registry::get("config")->payments->bikeride->toArray();
        $this->view->payPalUrl = $payPalParams['url'];
        $this->view->payPalBusiness = $payPalParams['business'];
    }
    
    public function changePaidStatusAction()
    {
        $riderId = $this->_getParam("riderid");
        $paid = $this->_getParam("paid");
        $rider = \Fisdap\EntityUtils::getEntity('BikeRiderData', $riderId);

        $rider->paid = $paid;
        
        //Save the changes and flush
        $rider->save();
        $this->_helper->json($rider->paid);
    }
}
