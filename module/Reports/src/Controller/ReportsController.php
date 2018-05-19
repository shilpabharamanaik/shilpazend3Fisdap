<?php

namespace Reports\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;
use User\Entity\User;
use User\Entity\UserContext;
use User\Entity\ProgramLegacy;
use User\Entity\InstructorLegacy;

class ReportsController extends AbstractActionController
{
    	/**
     * Session manager.
     * @var Zend\Session\SessionManager
     */
    private $sessionManager;

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $objUser;
	
	public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $userSession = new Container('user');
        $username = $userSession->username;
        $this->objUser = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
    }

    public function splashAction()
    {
        return new ViewModel();
    }
}
