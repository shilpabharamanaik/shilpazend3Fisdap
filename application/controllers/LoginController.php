<?php

use Fisdap\Doctrine\Extensions\ColumnType\UuidType;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\User;
use Fisdap\Members\Auth\CommonAuthController;
use Fisdap\JBL\Authentication\JblRestApiUserAuthentication;
use Fisdap\JBL\Authentication\Exceptions\AuthenticationFailedException;
use Fisdap\Members\Lti\Session\LtiToolProvidersSession;


/**
 * Class LoginController
 */
final class LoginController extends CommonAuthController
{
    public function preDispatch()
    {
        // If they aren't logged in, they can't logout, so that action should
        // redirect to the login form
        if (!Zend_Auth::getInstance()->hasIdentity() && 'logout' == $this->getRequest()->getActionName()) {
            $this->_helper->redirector('index', 'login', 'default');
        }
    }


    /**
     * Initialize the controller
     * Set the session and title of the page
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        $this->view->pageTitle = "Login";
    }


    /**
     * Display the login form and process and login requests
     *
     * @param JblRestApiUserAuthentication $jblAuthenticator
     *
     * @throws Zend_Exception
     * @throws Zend_Form_Exception
     */
    public function indexAction(JblRestApiUserAuthentication $jblAuthenticator)
    {
        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        if (Zend_Auth::getInstance()->hasIdentity()) {

            // We're authenticated! Redirect to the home page or URL stored in session
            if (isset($this->globalSession->requestURL)) {
                $url = $this->globalSession->requestURL;
                unset($this->globalSession->requestURL);
                $this->redirect($url);
            } else {
                $url = User::getLoggedInUser()->getRedirectionPage();
                $this->redirect($url);
            }

        }

        $this->view->form = $form = new Fisdap_Form_Login;
        $this->view->layout()->setLayout('login');

        // Tell the user their account has been deleted if we're being
        // sent back here by the controller plugin to keep out deleted users
        if ($this->hasParam("userDeleted")) {
            $this->view->form->setDescription("Your account has been deleted.");
        }

        // try to authenticate the user
        if ($request->isPost()) {
            if ($form->isValid($request->getPost())) {
                $username = $form->getValue('username');
                $password = $form->getValue('password');
                $secure = $form->getValue('ImSecure');

                // authenticate via Fisdap db
                $result = $this->authenticate($username, $password);
                if ($result->isValid()) {

                    $user = $this->userRepository->getOneByUsername($username);

                    $this->setCurrentUserContext($user);
                    $this->currentUser->setUser($user);

                    $this->processSuccessfulLogin($password, $secure);
                }

                // now try the JBL authentication
                try {
                    $jblUser = $jblAuthenticator->authenticateWithEmailPassword($username, $password);

                    // if JBL authentication was successful, get the Fisdap user using the PSG person id as the username
                    $username = UuidType::transposeUuid($jblUser->PersonId);
                    $user = $this->userRepository->getOneByUsername($username);

                    // if we have a Fisdap user associated with this JBL user, great! they're authenticated!
                    if ($user) {
                        // authenticate via Fisdap db
                        // the third argument here is saying that the password is already hashed
                        $result = $this->authenticate($user->username, $user->password, TRUE);
                        if ($result->isValid()) {
                            $this->setCurrentUserContext($user);
                            $this->currentUser->setUser($user);
                            $this->processSuccessfulLogin($password, $secure);
                        }
                    }

                    // otherwise, the JBL credentials are good, but we don't yet have a Fisdap user
                    $errorMsg = "Please set up your Fisdap account by logging in through Navigate.";
                    $form->setDescription($errorMsg);
                    return;

                } catch (Exception $e) {
                    // if we have a problem connecting to the JBL authentication server, show that message
                    if (!($e instanceof AuthenticationFailedException)) {
                        $form->setDescription($e->getMessage());
                        return;
                    }
                }

                // if we've gotten this far, they have failed to authenticate
                $errorMsg = 'The username or password you entered is incorrect. '.
                    'If you need assistance, click the "Forgot your login?" link below.';
                $form->setDescription($errorMsg);
            }
        }
    }


    /**
     * Logout the user
     *
     * @param LtiToolProvidersSession $ltiToolProvidersSession
     *
     * @return null
     */
    public function logoutAction(LtiToolProvidersSession $ltiToolProvidersSession)
    {
        // remove launched LTI tools from session and redirect to tool's logout URL
        if ($this->hasParam('resourceLinkId')) {
            $ltiToolProvidersSession->removeLaunchedTool($this->getParam('resourceLinkId'));
        }

        foreach ($ltiToolProvidersSession->getLaunchedTools() as $resourceLinkId => $logoutUrl) {
            $ltiToolProvidersSession->removeLaunchedTool($resourceLinkId);
            $this->redirect($logoutUrl);
        }

        // legacy server telling us that we've already logged out
        $legacyLogout = $this->getParam('legacyLogout', false);

        // "unmask" the user on legacy
        $legacyUnmask = $this->getParam('legacyUnmask', false);

        if ($legacyUnmask == true) {
            $this->redirect(Util_HandyServerUtils::get_fisdap_members1_url_root() . 'internal/user_switcher/unmask.php');
        }

        if ($legacyLogout == false) {
            $redirect = $this->getParam('redirect');
            $code = $this->getParam('code');
            $legacyLogoutLink = Util_GetLegacyTopNavLinks::getLink(Util_GetLegacyTopNavLinks::LOGOUT, $this->view->serverUrl());
            if ($redirect) {
                $legacyLogoutLink .= "?redirect=$redirect";
            }
            if ($code) {
                $legacyLogoutLink .= "%26code=$code";
            }
            $this->redirect($legacyLogoutLink);
        }

        // Remove authorization
        if (Zend_Auth::getInstance()->hasIdentity()) {
            Fisdap_Auth_Adapter_Db::clearIdentity();
        }

        // Check to see if user is still logged in (could happen if multiple layers of masquerade are in place)
        if (Zend_Auth::getInstance()->hasIdentity()) {
            // If we're still masquerading, tell legacy who we're masking as,
            // otherwise, go to the original user's home
            if (Fisdap_Auth_Adapter_Db::isMasquerade()) {
                $username = Zend_Auth::getInstance()->getIdentity();
                $this->redirect(Util_HandyServerUtils::get_fisdap_members1_url_root() . 'internal/user_switcher/switch.php?new_username=' . $username);
            } else {
                $url = User::getLoggedInUser()->getRedirectionPage();
                $this->redirect($url);
            }

        } else {
            if ($redirect = $this->getParam('redirect')) {
                switch ($redirect) {
                    case 'serial':
                        $sn = $this->getParam('code');
                        $serial = SerialNumberLegacy::getBySerialNumber($sn);
                        if ($serial->isInstructorAccount()) {
                            $redirectUrl = "/account/new/instructor/sn/$sn";
                        } else {
                            $redirectUrl = "/account/new/student/sn/$sn";
                        }
                        break;
                    case 'product':
                        $redirectUrl = '/account/new/confirm-account/?pc=' . $this->getParam("code");
                        break;
                    case 'new':
                        $redirectUrl = '/account/new/index/code/' . $this->getParam("code");
                        break;
                    default:
                        $redirectUrl = '/login';
                }
                $this->redirect($redirectUrl);
                return;
            } else if ($this->globalSession->newProgramRedirect == true) {
                $this->redirect('/new');
                return;
            }

            // fully logged out
            $this->redirect(Util_HandyServerUtils::get_fisdap_content_url_root()); // back to login page
        }
    }


    /**
     * Display the masquerade form or process masquerade requests
     * for permissioned users
     *
     * @return null
     */
    public function masqueradeAction()
    {
        $username = $this->getParam('username');

        if (Zend_Auth::getInstance()->hasIdentity()) {

            // @todo check for staffData link for permissions
            $loggedInUser = User::getLoggedInUser();

            if ($loggedInUser->isStaff()) { //$loggedInUser->Staff::isStaff()) {
                $this->view->form = $form = new Fisdap_Form_Masquerade;

                /** @var Zend_Controller_Request_Http $request */
                $request = $this->getRequest();

                if ($request->isPost() || $username) {
                    if ($form->isValid($request->getPost()) || $username) {

                        $formValues = $form->getValues();

                        if (!$username) {
                            $username = $formValues['username'];
                        }

                        $newUser = User::getByUsername($username);
                        if ($newUser->id) {
                            if (Fisdap_Auth_Adapter_Db::masquerade($newUser)) {

                                $location = 'internal/user_switcher/switch.php?new_username=' . $username;

                                $this->_redirectToLegacy($location);

                                //$this->processSuccessfulLogin($password, $formValues['ImSecure']); // send to fisdap-old integration and redirect
                            } else {
                                $form->setDescription('masquerade failed!!!!');
                            }
                        } else {
                            $form->setDescription('Sorry, the username you entered does not match a user existing in the system.');
                        }
                    }
                }
            } else {
                // this user is not staff, then he/she is not allowed to view the page, so DENY
                $this->redirect("/index/not-allowed");
            }
        }
    }

    /**
     * @param User $user
     */
    private function setCurrentUserContext(User $user)
    {
        if ($user->hasCurrentUserContext() === false) {
            $user->setCurrentUserContext($user->getAllUserContexts()->first());
            $this->userRepository->update($user);
        }
    }
}
