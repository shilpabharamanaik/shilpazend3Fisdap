<?php
/*	*	*	*	*	*	*	*	*	*
 *
 *	Copyright (C) 1996-2011.  This is an unpublished work of
 *			Headwaters Software, Inc.
 *				ALL RIGHTS RESERVED
 *	This program is a trade secret of Headwaters Software, Inc.
 *	and it is not to be copied, distributed, reproduced, published,
 *	or adapted without prior authorization
 *	of Headwaters Software, Inc.
 *
 * *	*	*	*	*	*	*	*	*	*/

/**
 *	Goals report controller
 *	@author Maciej/khanson
 */
class Reports_GoalController extends Fisdap_Controller_Private
{
    protected $user;

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
        }
    }

    public function indexAction()
    {
        $this->view->pageTitleLinkURL = "/reports";
        $this->view->pageTitleLinkText = '<< Back to Reports';
        $this->view->pageTitle = "Graduation Requirements";

        $form = new Reports_Form_ReportFilter(array(
            'roleName' => $this->roleName,
            'programId' => $this->programId,
            'user' => $this->user,
        ));

        $this->view->form = $form;

        $request = $this->getRequest();

        if($request->isPost()){
            $postVals = $request->getPost();

            // was cancel pressed?
            if(isset($postVals['Cancel'])) {
                $this->_redirect($postVals['cancel_redirect_to']);
            }
            $this->formValues = $form->process($postVals);

            // for student show his goals only
            if (!$this->isInstructor) {

                // should we include classmates from the same graduation year?
                if ($this->formValues['classmatesFilter']) {
                    // query for student IDs of the same graduation year for this program
                    // and add them to $this->formValues['student']['selected_students']
                    $studentRepo = \Fisdap\EntityUtils::getEntityManager()->getRepository("\Fisdap\Entity\User");
                    if (!$this->isInstructor) {
                        // for students, get the students with the same graduation year
                        $classmates = $studentRepo->getAllStudentsByProgram($this->programId, array('graduationYear' => $this->user->getCurrentRoleData()->getGraduationDate()->format("Y")));
                    } else {
                        // for instructors with the "no names" permissions, get all students
                        $classmates = $studentRepo->getAllStudentsByProgram($this->programId);
                    }
                    $classmatesIds = array();
                    foreach($classmates as $classmate) {
                        // we append an underscore to match the input format expected by code later in the process
                        $classmatesIds[] = '_' . $classmate['id'];
                    }
                    $this->formValues['studentIDs'] = implode(',', $classmatesIds);
                } else { // just show this student
                    $this->formValues['studentIDs'] = $this->user->getCurrentRoleData()->id;
                }
            }

            if (!$this->formValues) {
                // todo: error handling
                $this->view->messages = $this->flashMessenger->getMessages();
            } else if ($this->formValues['studentIDs']) {
                // back links
                $this->view->pageTitleLinkURL = '/reports/goal';
                $this->view->pageTitleLinkText = '<< Return to "Goals Report: Pick Your Settings"';

                $this->_helper->viewRenderer('goal/display-report', null, true);
                $this->displayReportAction();
            } else {
                $this->view->noStudentsSelected = true;
            }
        }

        if ($this->isInstructor && $this->user->hasPermission('View Reports')) {
            // this has moved to front end completely FOR NOW..
            // check /library/Reports/View/Helper/StudentFilterElement.php to see how defaults are assigned.
            //$classSectionRepository = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
            //$this->view->classSectionYearOptions = $classSectionRepository->getUniqueYears($this->programId);
            //$this->view->classSectionOptions = $classSectionRepository->getNamesByProgram($this->programId);
            $this->view->selectedStudentIds = $this->_getParam('studentIDs', false);
        } else {
            $studentId = $this->user->getCurrentRoleData()->id;
            $this->view->studentId = $studentId;
        }

    }

    public function customizeAction()
    {
        if (!$this->isInstructor) {
            $this->displayError("Students are not allowed to access this page.");
            return;
        }else if (!$this->user->hasPermission("Edit Program Settings")) {
            $this->displayPermissionError("Edit Program Settings");
            return;
        }

        $this->view->headLink()->appendStylesheet("/css/library/Reports/reports.css");

        $goalSetId = $this->_getParam('goalset');

        if ($goalSetId == "new") {
            //add default goal buttonset
            $defaultGoalSet = new Fisdap_Form_Element_jQueryUIButtonset('defaultGoalSetId');
            $defaultGoalSet->setLabel('Goal Set:')
                ->setUiTheme("cupertino")
                ->setButtonWidth("90px")
                ->setUiSize("extra-small")
                ->setOptions(array(1 => "National", 2 => "Virginia", 3 => "Ohio"))
                ->setDecorators(array("ViewHelper"))
                ->setValue($this->_getParam("defaultGoalSetId", 1));
            $this->view->defaultGoalButtonSet = $defaultGoalSet;

            // go ahead and take 'em to the form
            $this->customizeGoalSet($goalSetId);

        } else if ($goalSetId) {
            $goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $goalSetId);

            // if this is a standard goal set, you can't edit it
            if ($goalSet->isStandard()) {
                $this->displayError("You are not allowed to access this page.");
                return;
            }

            // if this is goal set does not belong to your program, you can't edit it
            if ($goalSet->program->id != $this->user->getCurrentProgram()->id) {
                $this->displayError("You are not allowed to access this page.");
                return;
            }

            // go ahead and take 'em to the form
            $this->customizeGoalSet($goalSetId);
        } else {
            // if we don't have a goal set id, bail
            $this->_redirect("/reports");
            return;
        }
    }

    protected function customizeGoalSet($goalSetId)
    {
        $referer = $this->getRequest()->getHeader('referer');
        $this->view->referer = $referer;
        $goalDefs = \Fisdap\EntityUtils::getRepository('Goal')->getGoalDefsForProgram($this->programId);

        $newGoalSet = $goalSetId=='new';

        $form = new Reports_Form_CustomizeGoals($this->_getParam("defaultGoalSetId", 1), array('newGoalSet' => $newGoalSet,
            'goalSet' => $goalSetId,
            'referer' => $referer));

        // if the add/edit custom goal set form has been posted, process it
        // then send us back where we came from
        if ($this->getRequest()->isPost()) {
            $values = $form->process($this->getRequest()->getPost());
            if(is_null($values)) {
                $this->view->pageTitle = $newGoalSet ? "Create Custom Goal Set" : "Edit Custom Goal Set";
                $this->view->form = $form;
                return;
            }
            $form->save($values);
            $redirect = ($values['referring_url']) ? $values['referring_url'] : "/reports";
            $this->_redirect($redirect);
        } else {
            // otherwise, show the form!
            $this->view->pageTitle = $newGoalSet ? "Create Custom Goal Set" : "Edit Custom Goal Set";
            $this->view->form = $form;
        }
    }

    public function displayReportAction()
    {
        // test report here:
        if (!$this->formValues) {
            // test/default data options:
            $dataOptions = array(
                'startDate' => new \DateTime('2000-01-01'),
                'endDate' => new \DateTime(),
                'subjectTypes' => array(1, 2, 3, 4, 5, 6),
                'shiftTypes' => array('field', 'clinical', 'lab'),
                'auditedOrAll' => 0
            );

            $options['dataOptions'] = $dataOptions;
            $this->runTestReport($options);
            return;
        }
        $vals = $this->formValues;

        $dataOptions['startDate'] = $vals['advanced_settings']['startdate'];
        $dataOptions['endDate'] = $vals['advanced_settings']['enddate'];
        $dataOptions['subjectTypes'] = $vals['advanced_settings']['patient-types'];
        $dataOptions['shiftTypes'] = $vals['educational_setting']['shifttype'];
        $dataOptions['auditedOrAll'] = $vals['advanced_settings']['audited-or-all'];

        if (is_array($vals['studentIDs'])) {
            $students = $this->getStudents($vals['studentIDs']);
        } else {
            $students = $this->getStudents(explode(',', $vals['studentIDs']));
        }

        // for any student user, place the student at the top of the list
        // and shuffle the rest, because we are anonymizing classmate data
        // also do this for any instructor who has the "reports withotu names" permission
        if(!$this->isInstructor) {
            if (!$this->isInstructor) { // for students only
                // Get the student record that matches the user and pull it out of results
                $currentStudent = $students[$this->user->getCurrentRoleData()->id];
                unset($students[$this->user->getCurrentRoleData()->id]);
            }

            // Randomize order of other students so they are effectively anonymized.
            // get the other students' keys and shuffle them
            $otherStudentKeys = array_keys($students);
            shuffle($otherStudentKeys);
            // then rebuild an array by pulling students by shuffled order of keys
            if (!$this->isInstructor) { // put back "current student" for students only
                $reorderedStudents = array($currentStudent['id'] => $currentStudent);
            }
            foreach($otherStudentKeys as $key) {
                $reorderedStudents[$key] = $students[$key];
            }

            // replace $students with the reordered list of students
            $students = $reorderedStudents;
        }

        $this->runGoalsForStudents($students, $vals['goal'], $dataOptions);
    }

    /**
     *
     */
    public function runGoalsForStudents($students, $goalSetId, $dataOptions)
    {
        // report headings:
        $shiftSelsCount = count($dataOptions['shiftTypes']);
        $heading = new stdClass();
        $heading->studentsInfo = ''; // Field and Clinical, between 02-16-2010 and 08-15-2011
        $heading->shiftsInfo = implode(', ', $dataOptions['shiftTypes']);
        $this->view->heading = $heading;

        $this->view->headLink()->appendStylesheet("/css/library/Reports/reports.css");
        $this->view->headScript()->appendFile("/js/jquery.tablescroll.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.tablescroll.css");

        $goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $goalSetId);
        foreach ($students as $sId => $student)
        {
            $goals = new \Fisdap\Goals($sId, $goalSet, $dataOptions, $student['first_name'] . " " . $student['last_name']);
            $goalsResults[$sId] = $goals->getGoalsResults();
        }

        $this->view->goalResults = $goalsResults;

        $goal = new stdClass();
        $goal->goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $goalSetId);
        $this->view->goal = $goal;
    }
    /**
     *	@todo single student goals report
     */
    public function displayStudentReportAction()
    {
        $studentId = $this->_getParam('student');
        if (!is_null($studentId)) {
            $this->_redirect('/reports/goal/display-student-report/student/'.$studentId);
        }
    }

    /**
     * Get student entities, but also filter out the underscore character
     * because Chrome and Opera are stupid and will resort a javascript collection
     * numerically by index.
     */
    public function getStudents($studentIds) {
        $studentIds = str_replace("_", "", $studentIds);
        $studentResults = \Fisdap\EntityUtils::getRepository("User")->getAllStudentsByProgram($this->user->getProgramId(), array("includeStudentIds" => $studentIds));

        $students = array();
        foreach($studentResults as $result) {
            $students[$result['id']] = $result;
        }

        return $students;
    }



    /**
     *
     *	Dev/Demo only
     *
     */
    public function getRandomStudents($howMany=20, $programId = 287, $includeThese=null)
    {
        $students = array();
        if ($includeThese) {
            foreach ($includeThese as $studentId) {
                $students[$studentId] = \Fisdap\EntityUtils::getEntity('StudentLegacy', $studentId);
            }
        }

        $skip = array(35955, 68991, 68993, 76757, 87346, 102787);

        // pick few students
        $sql = 'select Student_id from StudentData where Program_id='.$programId;
        $allStudents = Util_Db::getSqlResults($sql);

        $count = count($allStudents);
        if ($howMany>$count) {
            $howMany = $count;
        }

        for ($i=0; $i<$howMany; $i++) {
            if ($howMany==$count) {
                $studentId = $allStudents[$i]['Student_id'];
                if(in_array($studentId, $skip)) {	// remove: for debug removing long test student names
                    continue;
                }
            } else {
                do {
                    $studentId = $allStudents[mt_rand(0, $count-1)]['Student_id'];
                } while (isset($students[$studentId]) || in_array($studentId, $skip));
            }

            $students[$studentId] = \Fisdap\EntityUtils::getEntity('StudentLegacy', $studentId);
        }

        return $students;
    }

    public function getStudentsListAction()
    {
        $options = array(0 => 'Select one...');
        $graduation = $this->_getParam('graduation');

        $program = \Fisdap\EntityUtils::getEntity('Program', $this->programId);

        $getProgramOptions = array(
            'graduationMonth' => $graduation['month'],
            'graduationYear' => $graduation['year'],
            'sectionYear' => $this->_getParam('sectionYear', 0),
            'section' => $this->_getParam('section', 0)
        );

        $students = \Fisdap\EntityUtils::getRepository('User')->getAllStudentsByProgram($program, $getProgramOptions);

        foreach ($students as $student) {
            $options[$student['id']] = $student['last_name'] . ", " . $student['first_name'];
        }
        $this->_helper->json($options);
    }

    public function getAllStudentPickerStudentsAction()
    {
        $programId = $this->_getParam('programId');
        if (is_numeric($programId) || $programId > 0) {
            $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $programId); // supplied program id's program
        } else {
            $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $this->programId); // current user's program
        }

        $ret = Fisdap_User_UserLegacyMisc::getStudentPickerValuesForProgram($program);

        $this->_helper->json($ret);
    }

    // test stuff only
    public function runTestReport($options)
    {
        $studentId = $this->_getParam('student');
        if (!is_null($studentId)) {
            //$this->_redirect('/reports/goal/display-student-report/student/'.$studentId);
            $this->view->student = $studentId;
        }

        return;

        $studentList = array(21556, 25835, 68794, 106692);
        $students = $this->getRandomStudents(11, 287, $studentList);
        //$students = $this->getStudents($studentList);	//21909, 68774, 102787, 106692));
        //$students[68794]=\Fisdap\EntityUtils::getEntity('StudentLegacy', 68794);

        $this->runGoalsForStudents($students, 1, $options['dataOptions']);

        $heading->studentsInfo = '(Class 116; EMTs and Intermediates; graduating 04-2012)';	// Field and Clinical, between 02-16-2010 and 08-15-2011
        $heading->shiftsInfo = 'Shifts Info';

        $this->view->heading = $heading;
    }

    public function deleteGoalsetAction()
    {
        $goalSetId = $this->_getParam('goalsetId');

        if ($goalSetId) {
            $goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $goalSetId);
            $name = $goalSet->name;

            $goalSet->delete();

            $updatedTable = $this->view->goalSetTable();
            $this->_helper->json(array("goalsetTable" => $updatedTable));
        }

        $this->_helper->json(false);
    }

    public function checkDefaultGoalsetsAction()
    {
        $goalSetId = $this->_getParam('goalSetId');
        $programId = $this->_getParam('programId');
        $certification = $this->_getParam('certification');

        $this->_helper->json(\Fisdap\Entity\GoalSet::defaultGoalSetExists($goalSetId, $programId, $certification));
    }

    public function generateDefaultGoalSetFormAction()
    {
        $defaultGoalSetId = $this->_getParam('defaultGoalSetId');
        $form = new \Reports_Form_CustomizeGoals($defaultGoalSetId);

        $this->_helper->json($form->__toString());
    }

}