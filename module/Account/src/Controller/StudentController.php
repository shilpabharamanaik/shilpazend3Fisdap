<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;

class StudentController extends AbstractActionController
{

    /**
     * Session manager.
     * @var Zend\Session\SessionManager
     */
    private $sessionManager;

    /**
     * Constructs the service.
     */
    public function __construct()
    {
        //$this->sessionManager = $sessionManager;
    }


    public function editAction()
    {
        $userSession = new Container('user');
        $username = $userSession->username;

        return new ViewModel();
    }

}
