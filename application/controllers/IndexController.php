<?php

/**
 * the main index controller
 *
 * @package    Fisdap
 * @subpackage Controllers
 */
class IndexController extends Fisdap_Controller_Base
{
    public function indexAction()
    {
        //If the user is logged in, redirect them to their "homepage"
        if ($this->user) {
            $this->redirect($this->user->getRedirectionPage());
        } else {
            $this->redirect("/my-fisdap/index/index");
        }
    }

    public function notAllowedAction()
    {
        $session = new Zend_Session_Namespace();
        $this->view->url = $session->requestURL;
    }

    public function portfolioAction()
    {
        $this->redirect('/portfolio');
    }

    public function groupReviewAction()
    {
        $this->redirect('/oldfisdap/redirect?loc=shift/evals/groupReviewAssignments.php');
    }


    public function privacyPolicyAction()
    {
        $this->view->pageTitle = "Privacy Policy";
        $this->view->policy = file_get_contents(APPLICATION_PATH . "/../data/privacy_policy.txt");
    }
    
    public function transitionCourseMapAction()
    {
        $this->view->pageTitle = "Transition Course Map";
        $this->view->states = \Fisdap\EntityUtils::getRepository("TCMapState")->getStates();
        
        $this->view->headScript()->appendFile("/js/jquery.scrollTo-1.4.2-min.js");
        $this->view->headScript()->appendFile("/js/jquery.vmap.js");
        $this->view->headScript()->appendFile("/js/jquery.vmap.usa.js");
    }
    
    public function termsOfUseAction()
    {
        $this->view->pageTitle = "Terms of Use";
        $version = $this->_getParam("version", "current");
        
        if ($version == "old") {
            $this->view->terms = file_get_contents(APPLICATION_PATH . "/../data/user_agreement_old.txt");
        } else {
            $this->view->terms = file_get_contents(APPLICATION_PATH . "/../data/user_agreement.txt");
            $this->view->versionWarning = "<br /><br /><br />If you setup your account before March 27 2013, your terms of use can be found <a href='/terms?version=old'>here</a>.";
        }
    }
    
    public function paypalHandlerAction()
    {
        $params = $this->getAllParams();
        $this->logger->debug('paypalHandlerAction() params: ' . print_r($params, true));

        $riderId = $params['riderId'];
        
        if ($riderId) {
            $rider = \Fisdap\EntityUtils::getEntity("BikeRiderData", $riderId);
            $rider->transaction_id = $params['txn_id'];
            $rider->paid = 1;
            $rider->save();
        }
    }

    public function exportCalendarDataAction()
    {
        $auth_key = $this->_getParam("calendar", "");
        $calendarFeed = \Fisdap\EntityUtils::getRepository("CalendarFeed")->findOneBy(array("auth_key" => $auth_key));

        //Exit early if we don't find anyone
        if (!$calendarFeed->id || $auth_key == "") {
            $this->getResponse()->setHttpResponseCode(404);
            $this->_helper->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);

            echo "Calendar not found.";
            return;
        }

        //Extend the timeout period
        \Fisdap\EntityUtils::getEntityManager()->getConnection()->exec("SET SESSION wait_timeout = 28800");
        \Zend_Registry::get('db')->query("SET SESSION wait_timeout = 28800");

        //Get all of the events for this calendar feed
        $events = $calendarFeed->getEvents();
        $this->view->calendarFeed = $calendarFeed;
        $this->view->events = $events['events'];
        $this->view->quickAddShifts = $events['quick_add_shifts'];
        $this->view->baseAddresses = \Fisdap\EntityUtils::getRepository("BaseLegacy")->getBaseAddresses($calendarFeed->user_context->program->id);

        //Get timezone information
        $timezone =  $calendarFeed->user_context->program->program_settings->timezone;
        $this->view->vtimezone_definition = $timezone->icalendar_definition;
        $this->view->tzid = $timezone->icalendar_name;

        //Switch the viewscript around if we're dealing with a student calendar, since the data is slightly different, otherwise use the default viewscript.
        if ($calendarFeed->user_context->isStudent()) {
            $this->_helper->viewRenderer->setRender('export-calendar-data-students');
        }

        //Disable the layout and switch out headers for an .ICS file
        $this->_helper->layout()->disableLayout();
        header('Content-type: text/calendar; charset=utf-8');
        header('Content-Disposition: attachment; filename=test.ics');
    }
}
