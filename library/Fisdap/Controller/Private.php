<?php

class Fisdap_Controller_Private extends Fisdap_Controller_Base
{
	/**
	 * List of roles/access.
	 * @var array
	 */
	protected $_accessList;

    public function init()
    {
        $redirectModule = $this->_request->getModuleName() == "mobile" ? "mobile" : "default";
        if ( ! Zend\Authentication::getInstance()->hasIdentity() ) {
			if ($this->_request->isXmlHttpRequest()) {
				$this->_helper->json(false);
				return;
			}

			$this->onFailRedirect('index', 'login', $redirectModule);
			return;
		}

		// See if the user has access to the current action.
		if ( ! $this->roleHasAccess() ){
			$this->onFailRedirect('not-allowed', 'index', 'default');
            return;
		}

        parent::init();
	}

	protected function onFailRedirect($controller, $action, $module)
	{
		$session = new Zend_Session_Namespace();

		if (!$this->_request->isXmlHttpRequest()) {
            $request = $this->_request;
			$requestAction = $request->getActionName();
			$requestController = $request->getControllerName();
			$requestModule = $request->getModuleName();

			if (!($requestModule == "my-fisdap" && $requestController == "index" && $requestAction == "index")) {
				$session->requestURL = $request->getRequestUri();
			}
		}
		$this->_helper->redirector($controller, $action, $module);
	}

	/**
	 * This function pulls up the current users role, and the current set of
	 * available actions the role can access, and determines whether the logged
	 * in role has access to the requested action.
	 *
	 * @param String $roleName Name of the role trying to access the page
	 * @param String $action Name of the action that is being accessed
	 *
	 * @return Boolean true if the user has access, false if not.
	 */
	public function roleHasAccess($roleName=null, $action=null)
	{
		// This should be caught before here, but putting it here for testing
		// cases or when this is getting called outside of this class...
		if (!Zend\Authentication::getInstance()->hasIdentity()) {
			return false;
		}

		$currentRoleName = $roleName;

		if ($roleName == null) {
			$user = \Fisdap\Entity\User::getLoggedInUser();
			$currentRoleName = $user->getCurrentRoleName();
		}

		$requestAction = $action;

		if ($action == null) {
			$requestAction = $this->getRequest()->getActionName();
		}

		$accessList = $this->getRoleAccessList();

		// First, check to see if accessors are set up for the users role-
		// if not, use the default set.
		$activeList = $accessList['default'];

		if (array_key_exists($currentRoleName, $accessList)) {
			$activeList = $accessList[$currentRoleName];
		}

		$allowed = false;

		// If $activeList is null, fail and return false.
		if ($activeList === null) {
			return false;
		}

		// Check to see if it's allowed...
		if ($activeList == '*' || in_array($requestAction, $activeList)) {
			$allowed = true;
		}

		return $allowed;
	}

	public function getRoleAccessList()
	{
		if ($this->_accessList == null) {
			$accessList = array();

			$accessList['default'] = '*';
			
			return $accessList;
		} else {
			return $this->_accessList;
		}
	}
	
	public function setRoleAccessList($newList)
	{
		$this->_accessList = $newList;
	}
	
	/**
	 * This function just delegates a call off to the logged in user
	 * to see if they have the requested permission.  Useful wrapper, 
	 * since private controllers are most likely to be the ones asking
	 * for this info.
	 * 
	 * @param mixed $permission This is one of three possible things- 
	 * - \Fisdap\Entity\Permission object representing the permission
	 * - Integer ID of the permission to check
	 * - String name of the permission to check
	 * 
	 * @param String $redirectURI URI to redirect the user to if the
	 * permission check fails.  If left out, the function will return false.
	 * 
	 * @return Boolean true if the user has the permission, false if not.
	 */
	public function userHasPermission($permission, $redirectURI=null)
	{
		$loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
		
		if ($loggedInUser) {
			if ($loggedInUser->hasPermission($permission)) {
				return true;
			} else if ($redirectURI != null) {
				$this->redirect($redirectURI);
			}
		} else {
			$this->redirect('/login');
		}
		
		return false;
	}
}
