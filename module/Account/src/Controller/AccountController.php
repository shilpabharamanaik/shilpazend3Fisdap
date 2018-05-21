<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;

use User\Entity\User;
use User\Entity\UserContext;
use User\Entity\ProgramLegacy;
use User\Entity\InstructorLegacy;

class AccountController extends AbstractActionController
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

    public function indexAction()
    {
        $userContext = $this->objUser->getCurrentUserContext();
        $programCanOrder
            = (
                $this->objUser->isStaff() ||
                $userContext->getProgram()->order_permission->id != 3
            ) ? true : false;

        if ($userContext->isInstructor()) {
            $instructor = $userContext->getRoleData();
            // deal with permissions
            $canOrder = ($instructor->hasPermission("Order Accounts", $this->entityManager)) ? true : false;
            $canEditInstructors = ($instructor->hasPermission("Edit Instructor Accounts", $this->entityManager)) ? true : false;
            $canEditProgram = ($instructor->hasPermission("Edit Program Settings", $this->entityManager)) ? true : false;
            $canEditEvals = ($instructor->hasPermission("Enter Evals", $this->entityManager)) ? true : false;
            $canEditStudents = ($instructor->hasPermission("Edit Student Accounts", $this->entityManager)) ? true : false;
            $canEditCompliance = ($instructor->hasPermission("Edit Compliance Status", $this->entityManager)) ? true : false;
            $isStaff = ($this->objUser->isStaff()) ? true : false;
            $instructorId = $this->objUser->getCurrentRoleData()->id;

            $arrViewData = [
                    'isStaff' => $this->objUser->isStaff(),
                    'canOrder' => $canOrder,
                    'canEditInstructors' => $canEditInstructors,
                    'canEditProgram' => $canEditProgram,
                    'canEditEvals' => $canEditEvals,
                    'canEditStudents'=> $canEditStudents,
                    'canEditCompliance' => $canEditCompliance,
                    'instructorId' => $instructorId
                ] ;

            $viewModel = new ViewModel($arrViewData);
            $viewModel->setTemplate('Account/account/account-instructor');
            return $viewModel;
        } else {
        }
    }
}
