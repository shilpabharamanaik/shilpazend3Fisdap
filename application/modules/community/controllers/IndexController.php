<?php

class Community_IndexController extends Fisdap_Controller_Private
{
    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body

        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
        if (!empty($loggedInUser)) {
            $this->view->roleName = $loggedInUser->getCurrentRoleName();

            $this->view->pageTitle = "Community";
        
            switch ($loggedInUser->getCurrentRoleName()) {

            case "student":

                    $this->view->ResearchLink = "http://www.".Util_HandyServerUtils::get_server().".net/research";
                    $this->view->ResourcesLink = Util_GetLegacyTopNavLinks::getLink(Util_GetLegacyTopNavLinks::RESOURCES, $this->view->serverUrl());
                    $this->view->consentFormLink = Util_GetLegacyTopNavLinks::getLink(Util_GetLegacyTopNavLinks::CONSENT_FORM, $this->view->serverUrl());
    
            break;

            case "instructor":

                    $this->view->researchLink = "http://www.".Util_HandyServerUtils::get_server().".net/research";
                    $this->view->whatIsNewLink = "http://www.".Util_HandyServerUtils::get_server().".net/whats_new";
                    $this->view->acreditationCentralLink = "http://www.".Util_HandyServerUtils::get_server().".net/accreditation_central";

                    $this->view->recourcesLink = Util_GetLegacyTopNavLinks::getLink(Util_GetLegacyTopNavLinks::RESOURCES, $this->view->serverUrl());
                    $this->view->trainingVideoLink = Util_GetLegacyTopNavLinks::getLink(Util_GetLegacyTopNavLinks::TRAINING_VIDEO, $this->view->serverUrl());
                    $this->view->submitTestOnlineLink = Util_GetLegacyTopNavLinks::getLink(Util_GetLegacyTopNavLinks::SUBMIT_TEST_ONLINE, $this->view->serverUrl());

            break;

            default:

            break;

        }
        }
    }
}
