<?php

/**
 *
 * @package    Reports
 * @subpackage Controllers
 */
class Reports_GoalsController extends Fisdap_Controller_Private
{
	public function init()
    {
		parent::init();
		
		// redirect to login if the user is not logged in yet
		if (!$this->user) {
		    return;
		}
    }
	
	
	public function indexAction()
	{
		$program = \Fisdap\Entity\User::getLoggedInUser()->getCurrentProgram();
		
		$this->view->headScript()->appendFile("/js/reports/goals/index.js");

		$this->view->pageTitle = "Goals";
		$this->view->role = $this->user->getCurrentUserContext();
		$this->view->user = $this->user;
		$this->view->globalStudent = $this->globalSession->studentId;
	}

	public function aboutStudentAction() {
		$options = $this->_getParam('options');
		$student_id = $options['student'];
		$this->globalSession->studentId = $student_id;
		
		//$this->_helper->layout()->disableLayout();
		$this->view->options = $options;
		$this->_helper->json($this->view->aboutStudent($options));
	}

}
