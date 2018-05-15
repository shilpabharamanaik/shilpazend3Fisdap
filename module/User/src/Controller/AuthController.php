<?php

namespace User\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\Result;
use Zend\Uri\Uri;
use User\Form\LoginForm;
use User\Entity\User;

/**
 * This controller is responsible for letting the user to log in and log out.
 */
class AuthController extends AbstractActionController
{
    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * Auth manager.
     * @var User\Service\AuthManager
     */
    private $authManager;

    /**
     * User manager.
     * @var User\Service\UserManager
     */
    private $userManager;

    /**
     * Constructor.
     */
    public function __construct($entityManager, $authManager, $userManager)
    {
        $this->entityManager = $entityManager;
        $this->authManager = $authManager;
        $this->userManager = $userManager;
    }

    /**
     * Authenticates user given email address and password credentials.
     */
    public function loginAction()
    {
        // Retrieve the redirect URL (if passed). We will redirect the user to this
        // URL after successfull login.
        $redirectUrl = (string)$this->params()->fromQuery('redirectUrl', '');
        if (strlen($redirectUrl)>2048) {
            throw new \Exception("Too long redirectUrl argument passed");
        }

        // Create login form
        $form = new LoginForm();
        $form->get('redirect_url')->setValue($redirectUrl);
       
        $isLoginError = false;
        if ($this->getRequest()->isPost()) {
            // Fill in the form with POST data
            $data = $this->params()->fromPost();
            $form->setData($data);
            $result = $this->authManager->login($data['username'], $data['password'], $data['remember_me']);
            if ($result->getCode() == Result::SUCCESS) {
                //Get redirect URL.
                $redirectUrl = $this->params()->fromPost('redirect_url', '');

                if (!empty($redirectUrl)) {
                    // The below check is to prevent possible redirect attack
                    // (if someone tries to redirect user to another domain).
                    $uri = new Uri($redirectUrl);
                    if (!$uri->isValid() || $uri->getHost()!=null) {
                        throw new \Exception('Incorrect redirect URL: ' . $redirectUrl);
                    }
                }

                // If redirect URL is provided, redirect the user to that URL;
                // otherwise redirect to Home page.
                if (empty($redirectUrl)) {
                    return $this->redirect()->toRoute('my-fisdap');
                } else {
                    $this->redirect()->toUrl($redirectUrl);
                }
            } else {
                $isLoginError = true;
            }
        }

        return new ViewModel([
            'form' => $form,
            'isLoginError' => $isLoginError,
            'redirectUrl' => $redirectUrl
        ]);
    }

    /**
     * The "logout" action performs logout operation.
     */
    public function logoutAction()
    {
        $this->authManager->logout();

        return $this->redirect()->toRoute('login');
    }
}
