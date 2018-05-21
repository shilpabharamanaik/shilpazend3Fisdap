<?php

namespace Learningcenter\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;
use User\Entity\User;
use User\Entity\UserContext;
use User\Entity\ProgramLegacy;
use User\Entity\InstructorLegacy;
use User\Entity\Product;
use User\Entity\SerialNumberLegacy;

class LearningcenterController extends AbstractActionController
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
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Collection
     */
    private $toolProvidersByContext;

    
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $userSession = new Container('user');
        $username = $userSession->username;
        $this->objUser = $this->entityManager->getRepository(User::class)->findOneByUsername($username);
    }

    public function indexAction()
    {
        $userContext = $this->objUser->getCurrentUserContext();
        $serialNumber = $userContext->getPrimarySerialNumber();

        $roleName = $userContext->getRole()->getName();
        switch ($roleName) {
            case "student":
                //$this->setupStudentIndexView($launchableProducts, $serialNumber);
                $viewModel = new ViewModel();
                $viewModel->setTemplate('Learningcenter/learningcenter/index-student-lti');
                return $viewModel;
                break;
            case "instructor":
                // $arrViewData = ['hasPreceptorTraining' => $hasPreceptorTraining] ;
                 $viewModel = new ViewModel();
                 $viewModel->setTemplate('Learningcenter/learningcenter/index-instructor');
                return $viewModel;
                break;
            default:
                break;
        }
    }
}
