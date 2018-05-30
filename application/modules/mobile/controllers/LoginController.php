<?php

use Fisdap\Api\Users\CurrentUser\CurrentUser;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\UserContext;

class Mobile_LoginController extends Zend_Controller_Action
{
    public function init()
    {
        /* Initialize action controller here */
    }


    public function indexAction(UserRepository $userRepository, CurrentUser $currentUser)
    {
        if (\Zend_Auth::getInstance()->hasIdentity()) {
            $this->redirect("/mobile/");
        }
        
        // action body
        $this->view->form = new Mobile_Form_Login();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            if ($this->view->form->isValid($request->getPost())) {
                // Get our authentication adapter and check credentials
                $values = $this->view->form->getValues();
                $adapter = $this->getAuthAdapter($values);
                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($adapter);
    
                //if authentication failed, add error msg and re-render form
                if ($result->isValid()) {
                    if ($values['rememberMe'] == true) {
                        Zend_Session::rememberMe();
                    }

                    $user = $userRepository->getOneByUsername($values['username']);
                    
                    if ($user->hasCurrentUserContext() === false) {
                        $user->setCurrentUserContext($user->getAllUserContexts()->first());
                        $userRepository->update($user);
                    }
                    
                    $currentUser->setUser($user);

                    $this->redirect("/mobile/");
                    
                    return;
                } else {
                    // Invalid credentials
                    $this->view->form->setDescription('Authentication Failed. Please try again.');
                }
            }
        }
    }


    public function logoutAction()
    {
        //Remove authorization
        Zend_Auth::getInstance()->clearIdentity();

        //Delete any sessions
        Zend_Session::destroy(true);

        $this->_helper->redirector('index'); // back to login page
    }


    /**
     * Return the authentication adapter we want to use
     *
     * @param array $values the username/password to use
     *
     * @return Zend_Auth_Adapter_Ldap
     */
    public function getAuthAdapter($values)
    {
        $adapter = new Fisdap_Auth_Adapter_Db($values['username'], $values['password']);
        return $adapter;
    }
}
