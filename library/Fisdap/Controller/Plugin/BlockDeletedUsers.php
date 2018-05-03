<?php

use Fisdap\Api\Users\CurrentUser\CurrentUser;


/**
 * Controller plugin to kick out deleted users
 * 
 * @package    Fisdap
 * @subpackage Controllers
 */
class Fisdap_Controller_Plugin_BlockDeletedUsers extends Zend_Controller_Plugin_Abstract
{
	/**
	 * Grab the logged in user, then make sure they're not deleted
	 *
	 * @param Zend_Controller_Request_Abstract $request
	 *
	 * @throws Zend_Exception
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request)
	{
		if ($this->_request->getControllerName() == 'error' || $this->_request->getModuleName() == 'appmon') {
			return;
		}

		/** @var CurrentUser $currentUser */
		$currentUser = Zend_Registry::get('container')->make(CurrentUser::class);

		if (Zend_Auth::getInstance()->hasIdentity()) {
			
			if ($currentUser->user()->deleted) {
                //Clear the session including their identity
				Fisdap_Auth_Adapter_Db::clearIdentity();

                //Redirect to the login page with a flag to tell the form to display a helpful message about their account being deleted
                $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper('redirector');
                $redirector->gotoUrl('/login?userDeleted=1')->redirectAndExit();
                return;
            }
		}
	}
}