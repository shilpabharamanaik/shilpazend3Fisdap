<?php

use Fisdap\Entity\User;


class Fisdap_Auth_Adapter_Db implements Zend_Auth_Adapter_Interface
{
	//array containing authenticated user record
	protected $_resultArray;
	
	//Zend session
	protected static $sess;

	private $password;

	private $passwordHashed;

	public function __construct($username, $password, $hashed = FALSE)
	{
		// initial login password; is the password hashed or not?
		$this->password = $password;
		$this->passwordHashed = $hashed;
		
		// initiate session namespace and add identity to user's queue of mask identities
		self::getSess();
		self::addIdentity($username);
	}
	
	public function authenticate()
	{
		self::getSess();
		$username = end(self::$sess->identities);
		
		$user = User::getByUsername($username);
		if ($user->id) {
			if (User::authenticate_password($username, $this->password, $this->passwordHashed)) {
				//$this->_resultArray = $username;
				$result = new Zend_Auth_Result(Zend_Auth_Result::SUCCESS, $username, array());
			} else {
				$result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID, null, array("Authentication unsuccessful. Password doesn't match."));
			}		
		} else {
			$result = new Zend_Auth_Result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND, null, array("Authentication unsuccessful. Username not found."));			
		}
		
		if (!$result->isValid()) {
			// do not store invalid login identity;
			array_pop(self::$sess->identities);
		}
		return $result;
	}
	
	public function getResultArray($excludeFields = null)
	{
		if (!$this->_resultArray) {
			return false;
		}
		
		if ($excludeFields != null) {
			$excludeFields = (array)$excludeFields;
			foreach ($this->_resultArray as $key => $value) {
				if (!in_array($key, $excludeFields)) {
					$returnArray[$key] = $value;
				}
			}
			return $returnArray;
		} else {
			return $this->_resultArray;
		}
	}
	
	
	/*
	 * Functionality for user account masquerading via a queue of identities
	 * Inspired by http://natefactorial.com/2010/02/04/lilo-queue-auth-adapter/
	 */
	
	// establish Zend Session Namespace for storing queue of user identities
	// $sess->identities is an array of usernames
	protected static function getSess() {
		if (!isset(self::$sess)) {
			self::$sess = new Zend_Session_Namespace('fisdap.auth');
			if (!is_array(self::$sess->identities)) {
				unset(self::$sess->identities);
				self::$sess->identities = array();
			}
		}
	}
	
	// push identitiy to top of queue
	protected static function addIdentity($username) {
		self::getSess();
		self::$sess->identities[] = $username;
	}
	
	// attempt to log in with username and password, using zend_auth
	// -passing blank strings just uses the latest thing off the top of the queue
	// -because it instantiates a fisdap_auth instance, it stores new creds to queue
	protected static function forceLogin($username = '', $password = '') {
		self::getSess();
		$newcreds = strlen($username);
		if (!$newcreds) {
			if (count(self::$sess->identities) == 0) {
				return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND);
			}
			$username = array_pop(self::$sess->identities);
			$newUser = User::getByUsername($username);
			$adapter = new Fisdap_Auth_Adapter_Db($username, $newUser->password, TRUE); // third arg is TRUE because password is a hash value
		} else {
			$adapter = new Fisdap_Auth_Adapter_Db($username, $password, TRUE); // third arg is TRUE because password is a hash value
		}
		$auth = Zend_Auth::getInstance();
		return $auth->authenticate($adapter);
	}
	
	/*
	 * returns true on successful masquerade, false otherwise
	 *
	 * @param \Fisdap\Entity\User $newUser
     *
	 * @return boolean
	 */
	public static function masquerade($newUser) {
		Zend_Auth::getInstance()->clearIdentity(); //forget logged in user
		// Some users may only have legacy password set. This is a temporary issue caused by accounts redesign launch
		if (!$newUser->password && $newUser->legacy_password) {
			$password = $newUser->legacy_password;
		} else {
			$password = $newUser->password;
		}
		$result = self::forceLogin($newUser->username, $password);
		if ($result->isValid()) {
			return true;
		} else {
			//revert to old login.
			self::forceLogin(); // no valid identity means go to old creds
			return false;
		}
	}
	
	// log out
	// if there are more identities in the queue, log back in with them
	public static function clearIdentity() {
		self::getSess();
		Zend_Auth::getInstance()->clearIdentity();
		array_pop(self::$sess->identities);
        $username = '';
		if (count(self::$sess->identities) > 0) {
			// array_pop is the right thing because forceLogin will add it back to the queue
			$username = array_pop(self::$sess->identities);
		}
		if (!strlen($username)) {
			// nothing more to grab from the queue, so log out for real
			self::clearEverything();
			return;
		}
		// get user and password for this username
		$newUser = User::getByUsername($username);
		$result = self::forceLogin($username, $newUser->password);
		if (!$result->isValid()) {
			// somehow the data on the queue was wrong. This is weird. Log out.
			self::clearEverything();
		}
	}
	
	// unmask without destroying the session
    public static function unmask() {
		self::getSess();
		Zend_Auth::getInstance()->clearIdentity();
		unset(self::$sess->identities);
		self::$sess->identities = array();
    }

	/**
	 * Determine if a user is currently masquerading as another user
	 *
	 * @return boolean
	 */
	public static function isMasquerade()
	{
		self::getSess();
		return count(self::$sess->identities) > 1;
	}
	
	/**
	 * Get all logged in users
	 *
	 * @return array
	 */
	public static function getMasqueradeUsernames()
	{
		self::getSess();
		return self::$sess->identities;
	}

	// a real life log out. Clear everything, destroy everything, log out all the way.
	protected static function clearEverything() {
		self::getSess();
		unset(self::$sess->identities);
		self::$sess->identities = array();
		// ran out of creds to log back in.
		Zend_Session::destroy(true);
	}
	
}