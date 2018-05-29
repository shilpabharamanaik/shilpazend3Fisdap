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
use User\Traits\Reports;
use User\Entity\InstructorLegacy;
use Fisdap\Api\Programs\Entities\Traits;
class ReportsController extends AbstractActionController
{
	use Reports;
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
	/**
	 * Index page displays an interface for navigating to various reports
	 */
	public function indexAction()
	{
		//$this->checkPermissions();
		
		// List the reports available
		//echo $this->objUser->first_name;exit;
		 $userContext = $this->objUser->getCurrentUserContext();
		 //var_dump($userContext); exit;
		$program = $userContext->program;
		$pageTitle = "All Reports";
		$categories = $program->profession->report_categories;
		// get the reports for this user
		$reports = $program->getActiveReports($this->entityManager);
		//print_r($reports); exit;
		$visible_reports = array();
		foreach ($reports as $report) {
			$reportClass = 'Fisdap_Reports_' . $report->class;
            //if (class_exists($reportClass) && $reportClass::hasPermission($this->userContext)) {
				$visible_reports[] = $report;
			//}
		}
		$reports = $visible_reports;
		
		
		// stuff we need for plugins
		//$this->view->headScript()->appendFile("/js/jquery.fieldtag.js");
		
		//$this->view->tour = new Fisdap_View_Helper_GuidedTourHelper();
		//$this->view->tour_id = ($this->user->getCurrentRoleName() == 'instructor') ? 11 : 12;
		$arrViewData = [
                    'program' => $program,
                    'categories' => $categories, 'reports' => $reports, 'userobj' => $this->objUser] ;

            $viewModel = new ViewModel($arrViewData);
            return $viewModel;
	}
	
}
