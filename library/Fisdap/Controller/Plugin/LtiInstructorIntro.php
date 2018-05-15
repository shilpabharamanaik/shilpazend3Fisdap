<?php

use Fisdap\Api\Users\CurrentUser\CurrentUser;
use Fisdap\Members\Lti\Session\LtiSession;

/**
 * Controller plugin to make sure instructors have been properly introduced to Fisdap
 *
 * @package Fisdap_Controller_Plugin
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class Fisdap_Controller_Plugin_LtiInstructorIntro extends Zend_Controller_Plugin_Abstract
{
    /**
     * Grab the logged in user, then check for the user agreement
     *
     * @param Zend_Controller_Request_Abstract $request
     *
     * @throws Zend_Exception
     */
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        if ($this->getRequest()->getControllerName() == 'error' || $this->getRequest()->getModuleName() == 'appmon') {
            return;
        }

        if (!Zend_Auth::getInstance()->hasIdentity()) {
            return;
        }

        /** @var LtiSession $ltiSession */
        $ltiSession = Zend_Registry::get('container')->make(LtiSession::class);

        if ($ltiSession->launched() === false) {
            return;
        }

        /** @var CurrentUser $currentUser */
        $currentUser = Zend_Registry::get('container')->make(CurrentUser::class);

        if ($currentUser->context()->getRole()->getName() !== 'instructor') {
            return;
        }

        if ($currentUser->user()->isDemo() === false) {
            return;
        }
        
        if (($request->getModuleName() === 'account' && $request->getControllerName() === 'lti'
            && $request->getActionName() === 'instructor-intro') === false
        ) {
            $request->setModuleName('account')->setControllerName('lti')->setActionName('instructor-intro');
        }
    }
}
