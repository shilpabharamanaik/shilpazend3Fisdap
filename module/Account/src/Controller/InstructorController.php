<?php

namespace Account\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;

use User\Entity\User;
use User\Entity\UserContext;
use User\Entity\UserRole;
use User\Entity\Instructor;

use Fisdap\Entity\InstructorLegacy;
use Fisdap\EntityUtils;

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
    private $objUserRole;

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
        $this->objUserRole = $this->entityManager->getRepository(UserRole::class)
                            ->findOneByUserId($this->objUser->getId());
    }


    public function editAction()
    {

        if (!$this->objUserRole->isInstructor()) {
            return new ViewModel([
                'displayError' => true,
            ]);
        }

        // Grab the instructor from either the URL or session
        $instructorSession = new Container('instructor');
        //$instructorId = $this->getParam('instructorId', $this->globalSession->instructorId);
        $instructorId = (int)$this->params()->fromRoute('instructorId', $instructorSession->instructorId);

        // If an instructor hasn't been set yet, choose the logged in instructor
        if (!$instructorId) {

            $objInstructor = $this->entityManager->getRepository(Instructor::class)
                                ->findOneByUserId($this->objUser->getId());
            $instructorId = $objInstructor->getInstructorId();
            //$instructorId = $this->currentUser->context()->getRoleData()->getId();
        }

        // Save the selected instructor in the session
        //$this->globalSession->instructorId = $instructorId;
        $instructorSession->instructorId = $instructorId;

        /** @var \Fisdap\Entity\InstructorLegacy $instructor */
        //$instructor = EntityUtils::getEntity('InstructorLegacy', $instructorId);
        $objInstructor = $this->entityManager->getRepository(Instructor::class)
                            ->findOneById($instructorId);

        // Check to make sure we can view the given instructor
                            /*
        if ($instructor->getUserContext()->getProgram()->getId() != $this->currentUser->context()->getProgram()->getId()) {
            unset($this->globalSession->instructorId);
            $this->view->instructorId = null;
        } else {
            if ($instructor->getId() != $this->currentUser->context()->getRoleData()->getId()
                && !$this->currentUser->user()->hasPermission(
                    "Edit InstructorLegacyctor Accounts"
                )
            ) {
                $this->displayError(
                    "You do not have permission to edit other instructor accounts. Please contact "
                    . $this->currentUser->context()->getProgram()->getProgramContactName() . " for more information."
                );
                unset($this->globalSession->instructorId);

                return;
            }
        }*/

        $form = new Account_Form_Instructor($instructorId);

        return new ViewModel([
            'instructorId' => $instructorId,
            'instructor' => $objInstructor,
        ]);
    }

}
