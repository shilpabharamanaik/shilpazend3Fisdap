<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;
use User\Entity\User;
use User\Entity\UserContext;

class InstructorController extends AbstractActionController
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

    /**
     * Constructs the service.
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $userSession = new Container('user');
        $username = $userSession->username;

        $this->objUser = $this->entityManager->getRepository(User::class)
                    ->findOneByUsername($username);
    }


    public function editAction()
    {
        $role = $this->entityManager->getRepository(UserContext::class)
                    ->findOneByUserId($this->objUser->getId());
        var_dump($role);
        return new ViewModel([
            'user' => print_r($this->objUser, true),
        ]);
    }
}
