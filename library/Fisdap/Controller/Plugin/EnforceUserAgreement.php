<?php

use Fisdap\Api\Users\CurrentUser\CurrentUser;


/**
 * Controller plugin to make sure every user has accepted the user agreement
 * 
 * @package    Fisdap
 * @subpackage Controllers
 */
class Fisdap_Controller_Plugin_EnforceUserAgreement extends Zend_Controller_Plugin_Abstract
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
		if ($this->_request->getControllerName() == 'error' || $this->_request->getModuleName() == 'appmon') {
			return;
		}

		/** @var CurrentUser $currentUser */
		$currentUser = Zend_Registry::get('container')->make(CurrentUser::class);

		if (Zend_Auth::getInstance()->hasIdentity()) {
			if ($currentUser->user()->hasAcceptedAgreement() === false) {
				
				//Store the requested URL in the session
				$session = new \Zend_Session_Namespace();
				$session->requestAgreementURL = $this->getRequest()->getRequestUri();
				
				//Check to see if they're not submitting the user agreement, otherwise redirect to the user agreement
				$params = $request->getParams();
				if (!($params['module'] == 'account' && $params['controller'] == 'new' && $params['action'] == 'user-agreement')) {
					$request->setControllerName('new')->setActionName('user-agreement')->setModuleName('account');					
				}
			}
		}
	}
}