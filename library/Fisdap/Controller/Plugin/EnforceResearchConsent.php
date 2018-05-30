<?php

use Fisdap\Api\Users\CurrentUser\CurrentUser;

/**
 * Controller plugin to make sure every student has either accepted/rejected the research consent
 *
 * @package    Fisdap
 * @subpackage Controllers
 */
class Fisdap_Controller_Plugin_EnforceResearchConsent extends Zend_Controller_Plugin_Abstract
{
    /**
     * Grab the logged in user, then check for the user agreement
     *
     * @param Zend_Controller_Request_Abstract $request
     *
     * @throws Exception
     * @throws Zend_Exception
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if ($this->_request->getControllerName() == 'error' || $this->_request->getModuleName() == 'appmon') {
            return;
        }

        /** @var CurrentUser $currentUser */
        $currentUser = Zend_Registry::get('container')->make(CurrentUser::class);
        
        if (Zend_Auth::getInstance()->hasIdentity()) {
            
            // if the logged in user is a student
            if ($currentUser->context()->getRole()->getName() === 'student') {
                // if they haven't seen the research consent page before
                if (is_null($currentUser->context()->getRoleData()->research_consent)) {
                    //Store the requested URL in the session
                    $session = new \Zend_Session_Namespace();
                    $session->requestAgreementURL = $this->getRequest()->getRequestUri();
                    
                    //Check to see if they're not submitting the research-consent, otherwise redirect to the user agreement
                    $params = $request->getParams();
                    if (!($params['module'] == 'account' && $params['controller'] == 'new' && $params['action'] == 'research-consent')) {
                        $request->setControllerName('new')->setActionName('research-consent')->setModuleName('account');
                    }
                }
            }
        }
    }
}
