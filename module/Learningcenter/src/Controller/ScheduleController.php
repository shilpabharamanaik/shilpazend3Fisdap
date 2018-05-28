<?php

namespace Learningcenter\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\SessionManager;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Zend\Mvc\MvcEvent;

use Fisdap\Entity\UserContext;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\Product;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\Permission;
use Fisdap\Entity\PermissionCategory;
use Fisdap\Entity\PermissionHistoryLegacy;
use Fisdap\Entity\PermissionSubRole;
//use Fisdap\Entity\ScheduledTestsLegacy;
//use Fisdap\Entity\User;

use User\Entity\ScheduledTestsLegacy;
use User\Entity\User;

class ScheduleController extends AbstractActionController
{

    /**
     * Entity manager.
     * @var Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var User\Entity\User
     */
    private $objUser;


    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $userSession = new Container('user');
        $username = $userSession->username;
        $this->objUser = $this->entityManager->getRepository(User::class)
                            ->findOneByUsername($username);
    }


    public function scheduleAction()
    {
        $userContext = $this->objUser->getCurrentUserContext();

        $allRouteParams = $this->params()->fromRoute();
        $allPostParams = $this->params()->fromPost();

        //$stRepos = \Fisdap\EntityUtils::getRepository('ScheduledTestsLegacy');
        $stRepos = $this->entityManager->getRepository(ScheduledTestsLegacy::class)
                    ;//->findOneByProgramId($userContext->getProgram()->getId());

        $startDate = $endDate = '' ;
        $arrFnParam = ['programId'=>$userContext->getProgram()->getId(),];
        $stResults = $stRepos->getFilteredTests($arrFnParam);
        if (count($stResults) > 50) {
            $start = new \DateTime('-4 months');
            $end = new \DateTime('+3 months');

            if (!$startDate) {
                $arrFnParam['start_date'] = $start->format("m/d/Y");
            }

            if (!$endDate) {
                $arrFnParam['end_date'] = $end->format("m/d/Y");
            }
            $stResults = $stRepos->getFilteredTests($arrFnParam);
        }

        $arrViewData
            = [
                'isInstructor' => $userContext->isInstructor(),
                'instructorPermission' => $userContext->getRoleData()->hasPermission('Admin Exams', $this->entityManager),
                'programName' => $userContext->getProgram()->getName(),
            ] ;
        $viewModel = new ViewModel($arrViewData);
        $viewModel->setTemplate('Learningcenter/learningcenter/index-schedule');
        return $viewModel;
    }
}
