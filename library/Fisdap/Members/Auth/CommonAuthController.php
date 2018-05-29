<?php namespace Fisdap\Members\Auth;

use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\User;
use Fisdap_Auth_Adapter_Db;
use Fisdap_Controller_Base;
use Zend\Authentication;
use Zend_Session_Namespace;


/**
 * Class CommonAuthController
 *
 * @package Fisdap\Members\Auth
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class CommonAuthController extends Fisdap_Controller_Base
{
    /**
     * @var UserRepository
     */
    protected $userRepository;


    /**
     * CommonAuthController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    /**
     * @param string   $username
     * @param string   $password
     * @param boolean  $hashed
     *
     * @return \Zend_Auth_Result
     */
    protected function authenticate($username, $password, $hashed = false)
    {
        // Get our authentication adapter and check credentials
        $adapter = new Fisdap_Auth_Adapter_Db($username, $password, $hashed);
        $auth = Zend\Authentication::getInstance();
        $result = $auth->authenticate($adapter);

        return $result;
    }


    /**
     * Process a successful login, handling SSO with Legacy, and redirect
     *
     * @param string   $password
     * @param int|bool $ImSecure
     * @param string   $redirectUrl
     */
    protected function processSuccessfulLogin($password, $ImSecure, $redirectUrl = null)
    {
        $this->initMultiStudentPicker();

        // Store whether or not the user is secure in the session.
        $loginNamespace = new Zend_Session_Namespace('loginVars');
        $loginNamespace->isSecure = ($ImSecure == 1) ? 1 : 0;
        $this->globalSession->isSecure = ($ImSecure == 1) ? 1 : 0;

        // todo - fix Legacy SSO in OldfisdapController, which depends on this
        $loginNamespace->password = $password;

        // Set a cookie to save the checkbox value...
        setcookie('secureLogin', $loginNamespace->isSecure);

        if (isset($this->globalSession->requestURL)) {
            $url = $this->globalSession->requestURL;
            unset($this->globalSession->requestURL);
        } else {
            if ($redirectUrl === null) {
                $url = User::getLoggedInUser()->getRedirectionPage();
            } else {
                $url = $redirectUrl;
            }

            $flashMessenger = $this->_helper->getHelper('FlashMessenger');
            $flashMessenger->addMessage('Login Successful!  Welcome to Fisdap!');
        }

        // Force login on Legacy server, then redirect back to specified url
        $this->_redirectToLegacy($url, $loopback = 1);
    }


    protected function initMultiStudentPicker()
    {
        $mspNamespace = new Zend_Session_Namespace("MultiStudentPicker");
        $mspNamespace->selectedStatus = array(1); // default to viewing active students only
    }
}