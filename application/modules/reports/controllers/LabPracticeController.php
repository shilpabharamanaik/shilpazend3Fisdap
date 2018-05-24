<?php
/*	*	*	*	*	*	*	*	*	*
 *
 *	Copyright (C) 1996-2013.  This is an unpublished work of
 *			Headwaters Software, Inc.
 *				ALL RIGHTS RESERVED
 *	This program is a trade secret of Headwaters Software, Inc.
 *	and it is not to be copied, distributed, reproduced, published,
 *	or adapted without prior authorization
 *	of Headwaters Software, Inc.
 *
 * *	*	*	*	*	*	*	*	*	*/

/**
 *	Lab Practice report controller
 *	@author jmortenson and ahammond
 */
class Reports_LabPracticeController extends Fisdap_Controller_Private
{
	protected $user;
	protected $formValues;

	/**
	 * @var string
	 */
	private $roleName;

	public function init()
	{
		parent::init();

		if ($this->user) {
			$this->programId = $this->user->getProgramId();
			$this->roleName = $this->user->getCurrentRoleName();
			$this->isInstructor = ($this->roleName == 'instructor');
			$this->reportTitle = $this->user->getCurrentProgram()->hasSkillsPractice() ? "Skills Practice Report" : "Lab Practice Report";
		} else {
			
		}
	}

	// the lab practice report!
	public function indexAction()
	{
		// Set some page view values
		$this->view->pageTitleLinkURL = Util_HandyServerUtils::get_fisdap_members1_url_root()."reports/index.html";
		$this->view->pageTitleLinkText = '<< Back to Reports';
		$this->view->pageTitle = $this->reportTitle;
		$this->view->errorMessage = "";
		
		// get the report filters form
		$this->view->form  = new Reports_Form_LabPracticeReportFilter(array(
				'roleName' => $this->roleName,
				'programId' => $this->programId,
				'user' => $this->user,
		));
		
		$request = $this->getRequest();
		
		if($request->isPost()){
			$this->view->selectedStudentIds = $this->_getParam('studentIDs', false);
			$postVals = $request->getPost();

			$this->formValues = $this->view->form->process($postVals);
			
			if($this->isInstructor){
				$this->setUpInstructorReport($postVals['reportType'], $postVals['studentIDs'], $postVals['certLevel']);
			}
			else {
				$this->setUpStudentReport($postVals['reportType'], $postVals['includeClassmates']);
			}
			
			$this->formValues['reportType'] = $postVals['reportType'];
			$this->formValues['start_date'] = $postVals['start_date'];
			$this->formValues['end_date'] = $postVals['end_date'];
				
			// do we have valid data? if so, render the report
			if ($this->formValues['studentIDs']) {
				$this->view->pageTitleLinkURL = '/reports/lab-practice';
				$this->view->pageTitleLinkText = '<< Return to "' . $this->reportTitle . ': Pick Your Settings"';
				
				$viewHelper = new SkillsTracker_View_Helper_EurekaModal();
				$this->view->eurekaModal = $viewHelper->eurekaModal();
				
				$this->view->pageTitle = $this->reportTitle;
				$this->_helper->viewRenderer('lab-practice/display-lp-report', null, true);
				$this->displayReportAction();
			}
			else {
				// display the only error possible
				$this->view->errorMessage = "<div class='error'>Please choose at least one student.</div>";
			}
		}
	}
	
	/*
	 * gets the params ready to display the lab practice report
	 */
	public function displayReportAction()
	{
		$vals = $this->formValues;
		
		$dataOptions['reportType'] = $vals['reportType'];
		$dataOptions['start_date'] = $vals['start_date'];
		$dataOptions['end_date'] = $vals['end_date'];
		
		// create a keyed array of student IDs
		$students = array();
		if(!$this->isInstructor && $dataOptions['reportType'] != "summary"){
			$student = \Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData();
			$students[$student->id] = $student->id;
		}
		else {
			foreach($vals['studentIDs'] as $studentID) {
				$students[$studentID] = $studentID;
			}
		}
		
		$this->getLabGoalsReport($students, $vals['certLevel'], $dataOptions);
	}
	
	/*
	 * Actaully builds the array, then calls a view helper to display the tables
	 * @param $students the students that were selected from the form
	 * @param $certLevel the id of the cert level also chosen from the form
	 * @param $dataOptions other options (such as start/end date)
	 */
	public function getLabGoalsReport($students, $certLevel, $dataOptions)
	{
		$loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
		$this->addExternalFilesForEureka($this->view);
		
		if($certLevel) {
			$cert = \Fisdap\EntityUtils::getEntity('CertificationLevel', $certLevel);
		}
		else {
			$cert = $loggedInUser->getCurrentRoleData()->getCertification();
		}
		
		$this->view->certLevel = $cert->description;
		$this->view->detailedReport = ($dataOptions['reportType'] == 'summary') ? false : true;
		$this->view->dateRange = $dataOptions['start_date'];
		
		if($dataOptions['start_date'] != $dataOptions['end_date']){
			$this->view->dateRange .= " - " . $dataOptions['end_date'];
		}
		
		// if it's a detailed report we just care about 1 student, grab it from our array
		if($this->view->detailedReport){
			$student = \Fisdap\EntityUtils::getEntity("StudentLegacy", array_shift(array_values($students)));
			$this->view->pageTitle = $this->reportTitle . " for " . $student->user->getName();
		}
		// if it's a 'summary' include the 'summary table'
		else {
			$this->view->summary = $this->view->labPracticeReport(true, $certLevel, $this->anonymizeStudentIds($students),
																  true, $dataOptions);
		}
		
		// organize the rest by active/inactive for both detailed and sumamry reports
		$this->view->activeData = $this->view->labPracticeReport(true, $certLevel, $students, false, $dataOptions);
		
		// only include inactive data if an instructor is the user
		if($loggedInUser->isInstructor()){
			$this->view->isInstructor = true;
			$this->view->inactiveData = $this->view->labPracticeReport(false, $certLevel, $students, false, $dataOptions);
		}
	}
	
	// just adds of the addition css/js files (moslty for the eureka graph)
	private function addExternalFilesForEureka($view){
                //Need to have jquery migrate loaded before the jqplot stuff or we get errors.
            
                $view->headScript()->appendFile("/js/jquery-migrate-1.2.1.js");
            
		$view->headLink()->appendStylesheet("/css/library/Reports/reports.css");
		$view->headScript()->appendFile("/js/jquery.tablescroll.js");
		$view->headLink()->appendStylesheet("/css/jquery.tablescroll.css");
		
		$view->headScript()->appendFile("/js/library/Fisdap/Utils/create-pdf.js");
		$view->headScript()->appendFile("/js/jquery.printElement.min.js");
		
		$view->headScript()->appendFile("/js/jquery.eurekaGraph.js");
		$view->headScript()->appendFile("/js/library/SkillsTracker/View/Helper/eureka-modal.js");
		$view->headScript()->appendFile("/js/jquery.jqplot.min.js");
		$view->headScript()->appendFile("/js/syntaxhighlighter/scripts/shCore.min.js");
		$view->headScript()->appendFile("/js/syntaxhighlighter/scripts/shBrushJScript.min.js");
		$view->headScript()->appendFile("/js/syntaxhighlighter/scripts/shBrushXml.min.js");
		
		// gross collection of stylesheet we need for the graphing plugin
		$view->headLink()->appendStylesheet("/css/jquery.jqplot.min.css");
		$view->headLink()->appendStylesheet("/css/jquery.eurekaGraph.css");
		$view->headLink()->appendStylesheet("/js/syntaxhighlighter/styles/shCoreDefault.min.css");
		$view->headLink()->appendStylesheet("/js/syntaxhighlighter/styles/shThemejqPlot.min.css");
	}
	
	// Will anonymize the student IDs
	// used ONLY for the 'summary' table on the summary report.
	// The rest of the IDs are anonymized using js
	private function anonymizeStudentIds($students)
	{
		if(!$this->isInstructor) {
			if (!$this->isInstructor) {
				// Get the student record that matches the user and pull it out of results
				$currentStudent = $students[$this->user->getCurrentRoleData()->id];
				unset($students[$this->user->getCurrentRoleData()->id]);
			}
			
			// Randomize order of other students so they are effectively anonymized.
			$otherStudentKeys = array_keys($students);
			shuffle($otherStudentKeys);
			
			// then rebuild an array by pulling students by shuffled order of keys
			if (!$this->isInstructor) { // put back "current student" for students only
				$reorderedStudents = array($currentStudent->id => $currentStudent);
			}
			
			foreach($otherStudentKeys as $key) {
				$reorderedStudents[$key] = $students[$key];
			}
			
			// replace $students with the reordered list of students
			$students = $reorderedStudents;
		}
		
		return $students;
	}
	
	// gets the other classmates when a student choses to include anoynmized data in their summary report
	// gets active students with the same grad year and cert level
	private function setUpIncludedClassmates()
	{
		$loggedInStudent = $this->user->getCurrentRoleData();
		$studentRepo = \Fisdap\EntityUtils::getEntityManager()->getRepository("\Fisdap\Entity\User");
		$classmates = $studentRepo->getAllStudentsByProgram($this->programId,
															array(
																  'graduationStatus' => array(1),
																  'graduationYear' => $loggedInStudent->getGraduationDate()->format("Y"),
																  'certificationLevels' => $loggedInStudent->getCertification()->id
																  )
															);
		$classmatesIds = array();
		foreach($classmates as $classmate) {
			$this->formValues['studentIDs'][$classmate['id']] = $classmate['id'];
		}
	}
	
	// handles variable prep for student users
	private function setUpStudentReport($reportType, $includeClassmates)
	{
		$loggedInStudent = $this->user->getCurrentRoleData();
							
		// we've got a student, handle the summary case
		if($reportType == 'summary'){
			if($includeClassmates){
				$this->setUpIncludedClassmates();
				$this->formValues['includeClassmates'] = $includeClassmates;
			}
		}
		
		// if we don't have any studentIDs yet, we'll just used the logged in user
		if(!$this->formValues['studentIDs']){
			$this->formValues['studentIDs'][$loggedInStudent->id] = $loggedInStudent->id;
		}
		
		$this->formValues['certLevel'] = $loggedInStudent->getCertification()->id;
	}
	
	// handles variable prep for instructor users
	private function setUpInstructorReport($reportType, $studentIds, $certLevel)
	{
		if($reportType == "summary"){
			$this->formValues['studentIDs'] = $studentIds;
		}
		else {
			if($studentIds[0]){
				$onlyStudent = $studentIds[0];
				$this->formValues['studentIDs'][$onlyStudent] = $onlyStudent;
			}
		}
		
		$this->formValues['certLevel'] = $certLevel;
	}
	
}
