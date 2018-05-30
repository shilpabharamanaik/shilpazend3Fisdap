<?php

use Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway;
use Fisdap\Api\Client\Students\Gateway\StudentsGateway;
use Fisdap\Entity\StudentLegacy;
use Fisdap\JBL\Authentication\JblRestApiUserAuthentication;
use Fisdap\JBL\Authentication\Exceptions\AuthenticationFailedException;
use Fisdap\Doctrine\Extensions\ColumnType\UuidType;

/**
 * Controller to handle interacting with shifts
 * @package    SkillsTracker
 * @subpackage Controllers
 */
class SkillsTracker_ShiftsController extends Fisdap_Controller_SkillsTracker_Private
{
    /**
     * @var \Fisdap\Entity\ProgramLegacy
     */
    public $program;

    /**
     * @var Zend_Session_Namespace
     */
    public $session;

    /**
     * @var StudentsGateway
     */
    protected $studentsGateway;

    /**
     * @var ShiftAttachmentsGateway
     */
    protected $shiftAttachmentsGateway;

    public function init()
    {
        parent::init();
        $this->view->user = $this->user;
        $this->view->pageTitle = "Skills & Patient Care";
        $this->studentId = $this->_getParam('studentId');

        if ($this->studentId) {
            $this->globalSession->studentId = $this->studentId;
        } elseif (!$this->studentId && $this->user) {
            $this->studentId = $this->user->getCurrentRoleData()->id;
        }

        $this->view->headLink()->appendStylesheet("/fullcalendar/fullcalendar.css")
            ->appendStylesheet("/fullcalendar/fullcalendar.print.css", "print");
        $this->view->headScript()->appendFile("/fullcalendar/fullcalendar.min.js");

        $this->session = new \Zend_Session_Namespace("ShiftsController");

        $this->studentsGateway = $this->container->make('Fisdap\Api\Client\Students\Gateway\StudentsGateway');
        $this->shiftAttachmentsGateway = $this->container->make('Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway');
    }

    public function getRoleAccessList()
    {
        $accessList = parent::getRoleAccessList();

        $accessList['instructor'] = '*';
        $accessList['student'] = '*';

        return $accessList;
    }

    public function indexAction()
    {
        $this->view->headLink()->appendStylesheet("/css/jquery.fancyFilters.css");
        $this->view->headScript()->appendFile("/js/jquery.fancyFilters.js");
        $this->view->headScript()->appendFile("/js/library/SkillsTracker/View/Helper/shift-list.js");
        $this->view->headScript()->appendFile("/js/library/SkillsTracker/View/Helper/shift-list-summary.js");

        $this->view->messages = $this->flashMessenger->getMessages();

        $this->view->shiftModal = new SkillsTracker_Form_Shift();
        $this->view->shiftLockModal = new SkillsTracker_Form_LockShift();

        // Remember previous settings from session
        $this->view->studentId = isset($this->globalSession->studentId) ? $this->globalSession->studentId : null;

        // If a specific student was passed in via querystring, add it in here...
        if ($tmpStudentId = $this->_getParam('studentId', false)) {
            $this->view->studentId = $tmpStudentId;
        }

        $this->view->filter = isset($this->globalSession->filter) ?
            $this->globalSession->filter :
            array("type" => array(),
                "attendance" => array(),
                "date" => "all",
                "pending" => null
            );
        $this->view->program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $this->user->getProgramId());

        $this->view->addType = $this->_getParam('add', false);

        $this->view->shiftFilters = new SkillsTracker_Form_ShiftFilters();
        $this->view->shiftFilters->setDefaults(array('shiftsFilters' => $this->view->filter['type'],
            "attendanceFilters" => $this->view->filter['attendance'],
            "dateFilters" => $this->view->filter['date'],
            "pending" => $this->view->filter['pending']));

        // give the view a way to talk to the MRAPI client
        $this->view->studentsGateway = $this->studentsGateway;

        if ($this->user->getCurrentRoleName() == 'instructor') {
            $instructor = \Fisdap\EntityUtils::getEntity("InstructorLegacy", $this->user->getCurrentRoleData()->id);
            if ($instructor->hasPermission('View All Data')) {
                // set up single student picker for instructors
                $this->view->studentPicker = $this->getStudentPicker();

                $this->render('index-instructor');
            } else {
                $this->view->contact = \Fisdap\EntityUtils::getEntity('InstructorLegacy', \Fisdap\Entity\ProgramLegacy::getCurrentProgram()->program_contact);
                $this->render('index-instructor-no-perm');
            }
        } else {
            if ($sn = $this->user->getSerialNumberForRole()) {
                if ($sn->hasSkillsTracker()) {
                    $device = Zend_Registry::get('device');
                    if ($device->getFeature('is_mobile') == true && $device->getFeature('is_tablet') == "false" && !$this->_getParam('redirected') && !$this->getRequest()->getCookie('dont_show_mobile_redirect')) {
                        $this->_redirect("/mobile/index/redirect");
                    }

                    $this->shift = \Fisdap\EntityUtils::getRepository('ShiftLegacy')->findOneByStudent($this->user->getCurrentRoleData()->id);

                    $studentId = $this->user->getCurrentRoleData()->id;
                    $this->view->studentId = $studentId;
                    $this->render('index-student');
                    return;
                }
            }

            $this->render('index-student-no-perm');
        }
    }

    /**
     * Displays the Skills tracker detailed shift report
     */
    public function detailedShiftReportAction()
    {
        $shift = \Fisdap\EntityUtils::getEntity(
            'ShiftLegacy',
            $this->_getParam('shiftId')
        );
        $student = $shift->student;

        $this->program = \Fisdap\EntityUtils::getEntity(
            'ProgramLegacy',
            $this->user->getProgramId()
        );
        $this->view->program = $this->program;

        $this->view->student = $student;

        $this->view->shift = $shift;

        $this->view->shiftModal = new SkillsTracker_Form_Shift(
            $shift->id,
            null,
            $this->user->getProgramId()
        );
        $this->view->shiftLockModal = new SkillsTracker_Form_LockShift(
            $shift->id
        );

        $this->view->allowPatientSignoff
            = $this->program->program_settings->allow_signoff_on_patient;
        $this->view->allowShiftSignoff
            = $this->program->program_settings->allow_signoff_on_shift;

        //Grab boolean for shift vs. student displays
        $this->view->isInstructor = ($this->user->getCurrentRoleName()
            == 'instructor');

        //Set link text for the shift list bread crumb
        $this->view->shiftListLinkText
            = "&lt;&lt; Back to " . ($this->view->isInstructor ?
                $shift->student->user->first_name . "'s" : 'your')
            . " shift list";


        $tableOfContents = array();
        $patientPartials = array();

        // go through all the patients for this shift and get data
        for ($i = 1; $i <= count($shift->patients); $i++) {
            $patient = $shift->patients[$i - 1];
            $tableOfContents[]
                = '<a href="#run-' . $i . '">Patient ' . $i . ' - '
                . $patient->getSummaryLine() . '</a>';

            $patientData = array('patient' => $patient, 'runNumber' => $i,
                'user' => $this->user,
                'type' => $shift->type,
                'hasPatientSignoff' => $this->view->allowPatientSignoff);

            // get attachment data for this patient verification, too
            if ($patient->run->verification->shiftAttachment) {
                $patientData['shiftAttachment'] = $this->shiftAttachmentsGateway->getOne($shift->id, $patient->run->verification->shiftAttachment->getId());
            }

            $patientPartials[] = $patientData;
        }

        // get the attachment used for shift verification
        if ($shift->verification->shiftAttachment) {
            $this->view->shiftAttachment = $this->shiftAttachmentsGateway->getOne($shift->id, $shift->verification->shiftAttachment->getId());
        }

        // If we have quick added lab skills, add a heading
        if (count($shift->getQuickAddedSkills())) {
            $tableOfContents[] = '<a href="#quick-added-skills">Quick Added Skills</a>';
        }

        // If we have practice items, add a heading for them
        if (count($shift->practice_items)) {
            $tableOfContents[] = '<a href="#practice-items">Lab Practice</a>';
        }

        // get the shift attachments and stick them in the view for use in the attachments table
        $this->view->attachments = $this->getShiftAttachments($shift);
        $this->view->attachmentsRemaining = $this->shiftAttachmentsGateway->getRemainingAllottedCount($shift->student->user_context->id);
        $tableOfContents[] = '<a href="#attachments">Attachments</a>';

        // get the evals
        $evalList = \Fisdap\Entity\EvalSessionLegacy::getUsersCompletedEvals($shift->id, null, $student->user->id);
        $this->view->evalList = $evalList;
        $tableOfContents[] = '<a href="#shift-evals">Evals from this Shift</a>';

        // if shift-level signoff is enabled, and this shift is verified, grab the signoff form to be included in PDF export
        if ($shift->verification->verified) {
            // Get the preceptor signoff form. Note that we pass 'pdf' as the $media argument
            $this->view->shiftSignoff = $shift->signoff;
            $patientList[] = '<a href="#shift-signoff">Preceptor Feedback</a>';
        } else {
            $this->view->shiftSignoff = null;
        }

        $this->view->tableOfContents = $tableOfContents;
        $this->view->patientPartials = $patientPartials;
    }

    public function myShiftAction()
    {
        $this->view->headLink()->appendStylesheet("/css/my-fisdap/widget-styles/lab-skills-report.css");

        // first see if this came from the scheduler
        $assignmentId = $this->_getParam('assignmentId');

        //Grab the shift ID from the assignment_id, the URL or the session
        if ($assignmentId > 0) {
            $assignment = \Fisdap\EntityUtils::getEntity('SlotAssignment', $assignmentId);
            $shift = $assignment->shift;
            $shiftId = $shift->id;
        } else {
            $shiftId = $this->_getParam('shiftId', $this->globalSession->shiftId);

            //Error out if STILL no shift is found
            if (!$shiftId) {
                $noShiftId = true;
            } else {
                $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
            }
        }

        if ($noShiftId || !$shift) {
            $this->displayError("You've reached this page in error. No Shift ID found.");
            return;
        }

        if (!$shift->isViewable()) {
            $this->displayError("You currently do not have permission to access the requested page.");
            return;
        }

        if (!$shift->isEditable()) {
            $this->_redirect("/skills-tracker/shifts/detailed-shift-report/shiftId/" . $shift->id, array("exit" => true));
        }

        //Do a check to see if the attendance is set, legacy shifts will not have this set after conversion.
        if (!$shift->attendence->id) {
            $shift->set_attendence(1);
            $shift->save();
        }

        //Remove empty runs
        $shift->deleteEmptyRuns();

        //Set the page title and sub links
        $titleText = "My Shift";
        if ($this->user->getCurrentRoleName() == 'instructor') {
            $titleText = $shift->student->user->getFullName() . "'s shift";
        }

        $this->view->pageTitle = $titleText;

        $this->view->pageTitleLinks = array(
            "Shift report" => "#",
            "Detailed shift report" => "/skills-tracker/shifts/detailed-shift-report/shiftId/" . $shiftId
        );

        //Grab boolean for shift vs. student displays
        $this->view->isInstructor = ($this->user->getCurrentRoleName() == 'instructor');

        $this->globalSession->shiftId = $shiftId;

        //Check the permissions of the user
        $this->checkPermissions($shiftId);

        //Grab the Shift and program entities and put them in the view
        $this->view->shift = $shift;
        $this->view->program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $this->user->getProgramId());

        //Set link text for the shift list bread crumb
        $this->view->shiftListLinkText = "&lt;&lt; Back to " . ($this->view->isInstructor ? $shift->student->user->first_name . "'s" : 'your') . " shift list";

        if ($shift->type == 'clinical') {
            //Add javascript for clinical stuff
            $this->view->headScript()->appendFile("/js/skills-tracker/shifts/my-shift-clinical.js");
        }

        //Determine if we should show practice skills
        if ($shift->type == "lab") {
            //Always show practice skills for lab shifts
            $this->view->showPracticeSkills = true;
        } else {
            $this->view->showPracticeSkills = $this->view->program->program_settings->{"practice_skills_" . $shift->type};
        }

        // Determine if we should show quick add skills form (this applies to clinical shifts only for now)
        $this->view->showQuickAddSkills = false;
        if ($shift->type == "clinical") {
            // we only have the option of showing quick add skills if they're not using practice skills
            if (!$this->view->showPracticeSkills) {
                $this->view->showQuickAddSkills = $this->view->program->program_settings->quick_add_clinical;
            }

            // but if there's QAS data, go ahead and show it regardless
            $quickAddedSkills = $shift->getQuickAddedSkills();
            if (count($quickAddedSkills) > 0) {
                $this->view->showQuickAddSkills = true;
            }
        }

        $procedureForm = new Fisdap_Form_Element_Procedures("skills[procedure]");

        $this->view->procedureOptions = $procedureForm->render();
        $this->view->headScript()->appendFile("/js/skills-tracker/shifts/my-shift-lab.js");
        $this->view->headLink()->appendStylesheet("/css/skills-tracker/shifts/my-shift-lab.css");

        $this->view->loginForm = new \Fisdap_Form_Login();

        //Grab any saved lab partners from the session
        $this->view->labPartnerShifts = array();
        if (count($this->session->labPartnerShifts[$shift->id])) {
            foreach ($this->session->labPartnerShifts[$shift->id] as $partnerShiftId) {
                $this->view->labPartnerShifts[] = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $partnerShiftId);
            }
        }

        //prepare runs to stick in view
        $runs = \Fisdap\EntityUtils::getRepository('Run')->findByShift($shiftId);

        $runPartials = array();
        foreach ($runs as $run) {
            $runPartials[] = array('run' => $run, 'patients' => $this->em->getRepository('Fisdap\Entity\Patient')->getPatientsByRun($run->id));
        }
        $this->view->runs = $runPartials;

        // get the shift attachments and stick them in the view for use in the attachments table
        $this->view->attachments = $this->getShiftAttachments($shift);
        $this->view->attachmentsRemaining = $this->shiftAttachmentsGateway->getRemainingAllottedCount($shift->student->user_context->id);

        // This checks for obsolete, old-style quickAddedSkills data. Just here for historical data purposes
        // $this->view->hasQuickAddedSkills should never be true for contemporary shifts
        $skills = \Fisdap\EntityUtils::getRepository('ShiftLegacy')->getSkillsByShift($shift->id, array('shiftOnly' => true, 'labOnly' => true));
        $this->view->hasQuickAddedSkills = count($skills);


        $hookId = 113;
        switch ($shift->type) {
            case 'field':
                $hookId = 113;
                break;
            case 'clinical':
                $hookId = 114;
                break;
            case 'lab':
                $hookId = 115;
                break;
        }

        $this->view->hookId = $hookId;

        // No runs, skills, or evals == show blank state
        $isBlankState = (count($runPartials) == 0) && (count($skills) == 0) && (count(\Fisdap\Entity\EvalSessionLegacy::getUsersCompletedEvals($shift->id, $hookId, $shift->student->user->id)) == 0);

        $this->view->isBlankState = $isBlankState;

        $this->view->modalForm = new SkillsTracker_Form_Shift($shiftId, null, $this->user->getProgramId());
        $this->view->shiftLockModal = new SkillsTracker_Form_LockShift($shiftId);

        //Add intervention modals to view
        $this->view->track_airway_management_credit = ($shift->type == 'clinical') ? true : false;
        $this->view->airwayModal = new SkillsTracker_Form_AirwayModal();
        $this->view->cardiacModal = new SkillsTracker_Form_CardiacModal();
        $this->view->ivModal = new SkillsTracker_Form_IvModal();
        $this->view->otherModal = new SkillsTracker_Form_OtherModal();
        $this->view->medModal = new SkillsTracker_Form_MedModal();
        $this->view->vitalModal = new SkillsTracker_Form_VitalModal();

        // This stuff is to let the Lab Goals Widget access the student ID whose shift this is.
        // This is necessary in the case of an instructor visiting the page- need to get the shift owners user ID stored...
        $widgetSession = new \Zend_Session_Namespace("WidgetData");

        $widgetSession->user_id = $shift->student->user->id;
    }

    public function saveDefaultDefinitionAction()
    {
        $defaultDef = \Fisdap\EntityUtils::getEntity("PracticeDefinition", $this->_getParam("selectedDefId"));
        $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $this->_getParam("shiftId"));
        $this->session->defaultDefinitions[$shift->student->id] = $defaultDef->id;
        $this->_helper->json($this->session->defaultDefinitions[$shift->student->id]);
    }

    public function createRunAction()
    {
        $shiftId = $this->_getParam('shiftId');
        $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);

        if ($shift->isFuture()) {
            $this->displayError("You cannot add data to a shift in the future.");
            return;
        }

        $run = new \Fisdap\Entity\Run();
        $shift->addRun($run);
        $shift->save();
        // all runs need a patient, too
        $patient = \Fisdap\EntityUtils::getEntity('Patient');
        $run->addPatient($patient);
        $run->save();

        $this->_redirect("skills-tracker/patients/index/runId/" . $run->id . "/new/1");
    }


    public function calendarAction()
    {
        // For students, only redirect to the scheduler if they use the scheduler...
        if ($this->user->getCurrentRoleName() == 'student') {
            // Take them to the old scheduler
            if ($this->user->getSerialNumberForRole()->hasScheduler()) {
                $redirect = true;
            } else {
                $redirect = false;
            }
            // Always redirect educators...  This should hopefully work just fine.
        } else {
            $redirect = true;
        }

        if ($redirect) {
            $this->_redirect($this->user->getCurrentProgram()->getSchedulerUrl());
        }
    }

    /**
     * Handles AJAX requests to filter the shift list based on certain params
     */
    public function filtershiftsAction()
    {
        $studentId = $this->_getParam('studentId');
        $shiftFilter = $this->_getParam('shiftFilter', array());
        $attendanceFilter = $this->_getParam('atFilter', array());
        $dateFilter = $this->_getParam('dateFilter', 'all');
        $pendingFilter = $this->_getParam('pendingFilter', null);
        $isInstructor = $this->_getParam('instructor', false);

        $filter = array("type" => $shiftFilter,
            "attendance" => $attendanceFilter,
            "date" => $dateFilter,
            "pending" => $pendingFilter);

        $this->globalSession->studentId = $studentId;
        $this->globalSession->filter = $filter;

        $student = Fisdap\EntityUtils::getEntity('StudentLegacy', $studentId);

        $patients = \Fisdap\EntityUtils::getRepository("Patient")->getPatientsForExamInterviewTool($studentId);

        $returnArray = array();

        if ($patients) {
            $returnArray['examInterviewLink'] = "/skills-tracker/shifts/exam-interview-form/studentId/" . $studentId;
        }

        $returnArray['shifts'] = $this->view->shiftList($studentId, $this->studentsGateway, $filter, $isInstructor);

        if ($student) {
            $returnArray['hasSkillsTracker'] = $student->getSerialNumber()->hasSkillsTracker();
            $returnArray['studentName'] = $student->first_name . " " . $student->last_name;
        }

        $this->_helper->json($returnArray);
    }

    /**
     * Handles AJAX requests to filter the class sections based on the selected
     * year.
     */
    public function filterClassSectionsAction()
    {
        $classSectionRepository = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');

        $classSections = $classSectionRepository->getNamesByProgram($this->user->getProgramId(), $this->_getParam('year'));

        $this->_helper->json($classSections);
    }

    /**
     * Handles AJAX requests to "delete" a shift
     * @todo this doesn't actually do anything yet.
     */
    public function deleteShiftAction()
    {
        $shiftId = $this->_getParam('shiftId');

        if ($shiftId) {
            $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);

            if (!$shift->isEditable()) {
                $this->_helper->json(false);
                return;
            }

            $shift->soft_deleted = 1;
            $shift->save();
            $this->_helper->json("<div>Shift #$shiftId successfully deleted. <a href='#' id='undo-delete-$shiftId'>Undo!</a></div>");
        }
    }

    /**
     * Handles AJAX requests to "undelete" a shift
     * @todo this doesn't actually do anything yet
     */
    public function undoDeleteShiftAction()
    {
        $shiftId = $this->_getParam('shiftId');
        if ($shiftId) {
            $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);

            if (!$shift->isEditable()) {
                $this->_helper->json(false);
                return;
            }

            $shift->soft_deleted = 0;
            $shift->save();
            $this->_helper->json(true);
        }
    }

    public function hardDeleteShiftsAction()
    {
        $studentId = $this->_getParam('studentId');
        $shifts = array();

        if ($studentId) {
            $shifts = \Fisdap\EntityUtils::getRepository('ShiftLegacy')->findBy(array("student" => $studentId, "soft_deleted" => 1));
        }

        foreach ($shifts as $shift) {
            $shift->delete();
        }

        $this->_helper->json(true);
    }

    public function deleteRunAction()
    {
        $runId = $this->_getParam('runId');

        $run = \Fisdap\EntityUtils::getEntity('Run', $runId);

        if (!$run->shift->isEditable()) {
            $this->_helper->json(false);
            return;
        }

        $run->soft_deleted = 1;
        $run->save();

        $this->_helper->json("<div>Patient #$runId successfully deleted. <a href='#' id='undo-delete-$runId'>Undo!</a></div>");
    }

    public function undoDeleteRunAction()
    {
        $runId = $this->_getParam('runId');

        $run = \Fisdap\EntityUtils::getEntity('Run', $runId);

        if (!empty($run->shift) && !$run->shift->isEditable()) {
            $this->_helper->json(false);
            return;
        }

        $run->soft_deleted = 0;
        $run->save();

        $this->_helper->json(true);
    }

    public function hardDeleteRunsAction()
    {
        $shiftId = $this->_getParam('shiftId');
        $runs = array();

        if ($shiftId) {
            $runs = \Fisdap\EntityUtils::getRepository('Run')->findByShift($shiftId);
        }

        foreach ($runs as $run) {
            if ($run->soft_deleted) {
                $run->delete();
            }
        }

        $this->_helper->json(true);
    }

    public function generateStudentListAction()
    {
        $options = array(0 => 'Select one...');
        $graduation = $this->_getParam('graduation');

        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $this->user->getProgramId());

        $getProgramOptions = array(
            'graduationMonth' => $graduation['month'],
            'graduationYear' => $graduation['year'],
            'sectionYear' => $this->_getParam('sectionYear', 0),
            'section' => $this->_getParam('section', 0)
        );

        $students = \Fisdap\EntityUtils::getRepository('User')->getAllStudentsByProgram($this->user->getProgramId(), $getProgramOptions);

        foreach ($students as $student) {
            $options[$student['id']] = $student['last_name'] . ", " . $student['first_name'];
        }
        $this->_helper->json($options);
    }

    /**
     * AJAX response to validate a new/existing shift form
     * Used by \SkillsTracker_Form_Shift
     */
    public function validateShiftAction()
    {
        $formValues = $this->_getAllParams();
        $form = new SkillsTracker_Form_Shift($formValues['shiftId'], $this->_getParam('studentId', null));

        $this->_helper->json($form->process($formValues));
    }

    /**
     * AJAX response to check if a filled out shift form will conflict with existing shifts
     * Used by \SkillsTracker_Form_Shift
     */
    public function checkConflictsAction()
    {
        $formValues = $this->_getAllParams();
        $student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $formValues['studentId']);

        $startDateTime = date_create_from_format("m/d/Y Hi", $formValues['date'] . " " . str_pad($formValues['time'], 4, '0', STR_PAD_LEFT));
        $endDateTime = is_object($startDateTime) ? clone($startDateTime) : null;
        $endDateTime->add(new \DateInterval("PT" . (int)($formValues['hours'] * 3600) . "S"));

        $hasConflict = $student->user_context->hasConflict($startDateTime, $endDateTime);

        $this->_helper->json($hasConflict);
    }

    /**
     * AJAX response to validate the lock shift form
     * Used by \SkillsTracker_Form_LockShift
     */
    public function validateLockShiftAction()
    {
        $formValues = $this->_getAllParams();

        $form = new SkillsTracker_Form_LockShift($formValues['shiftId']);

        $this->_helper->json($form->process($formValues));
    }

    public function shiftsJsonAction()
    {
        $shifts = $this->em->getRepository('Fisdap\Entity\ShiftLegacy')->getShiftsByStudent($this->studentId);

        $data = array();
        foreach ($shifts as $shift) {
            $event = array(
                'id' => $shift->id,
                'title' => $shift->site->name . ", " . $shift->base->name,
                'start' => $shift->start_datetime->format('Y-m-d'),
                'url' => '/skills-tracker/shifts/runs/shiftId/' . $shift->id,
            );
            switch ($shift->type) {
                case "field":
                    $hexColor = "#0d7c9a";
                    break;
                case "clinical":
                    $hexColor = "#6aad0a";
                    break;
                case "lab":
                    $hexColor = "#f59c19";
                    break;
            }
            $event['color'] = $hexColor;

            if ($shift->locked) {
                $event['url'] = '/skills-tracker/shifts/detailed-shift-report/shiftId/' . $shift->id;
            } else {
                $event['url'] = '/skills-tracker/shifts/my-shift/shiftId/' . $shift->id;
            }

            $data[] = $event;
        }

        $this->_helper->json($data);
    }

    public function getSkillsJsonAction()
    {
        $shiftID = $this->_getParam('shiftID');
        $studentID = $this->_getParam('studentID');
        if ($shiftID > 0 && $studentID > 0) {
            $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftID);
        }
        if ($shift) {
            $rawSkills = $shift->getAssociatedSkills($studentID, $shiftID);

            $skills['active'] = $this->parseSkills($rawSkills['active']);
            $skills['inactive'] = $this->parseSkills($rawSkills['inactive']);

            $this->_helper->json($skills);
        } else {
            $this->_helper->json(array('error' => true, 'message' => 'Shift not found. Shift ID provided was: ' . $shiftID));
        }
    }


    public function commentsAction()
    {
        $this->view->headLink()->appendStylesheet('/css/skills-tracker/shifts/my-shift.css');
        $shiftId = $this->_getParam('id');
        $this->view->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
        $this->view->pageTitle = 'Comments';

        $this->view->headScript()->appendFile('/js/library/SkillsTracker/comments.js')
            ->appendFile('/js/library/plugins/prettyComments.js');

        // css
        $this->view->headLink()->appendStylesheet('/css/library/SkillsTracker/comments.css');

        $this->view->jQuery()->addOnLoad("fiscom.startCommenting('shifts', $shiftId, null, 'page')");

        $this->view->pageTitleLinkURL = "/skills-tracker/shifts";
        $this->view->pageTitleLinkText = "<< Back to Skills &amp; Patient Care";
    }


    private function parseSkills($rawSkills)
    {
        $skills = array();

        foreach ($rawSkills as $rs) {
            $atom = array();

            $context = '';

            switch (get_class($rs)) {
                case 'Fisdap\Entity\Airway':
                    $atom['procedure'] = 'AirwayProcedure_' . $rs->procedure->id;
                    $context = 'airway';
                    break;
                case 'Fisdap\Entity\Iv':
                    $atom['procedure'] = 'IvProcedure_' . $rs->procedure->id;
                    $context = 'iv';
                    break;
                case 'Fisdap\Entity\CardiacIntervention':
                    $atom['procedure'] = 'CardiacProcedure_' . $rs->procedure->id;
                    $context = 'cardiac';
                    break;
                case 'Fisdap\Entity\OtherIntervention':
                    $atom['procedure'] = 'OtherProcedure_' . $rs->procedure->id;
                    $context = 'other';
                    break;
                case 'Fisdap\Entity\LabSkill':
                    $atom['procedure'] = 'LabAssessment_' . $rs->procedure->id;
                    $context = 'lab';
                    break;
                default:
                    // Mostly because I hate using the continue keyword...
                    // This case gets hit when an admin removes the skill from
                    // the program via the settings page.
                    $atom = false;
                    break;
            }

            if ($atom) {
                $atom['subject-name'] = $rs->subject->name;
                $atom['subject-type'] = $rs->subject->type;

                if (property_exists($rs, 'success')) {
                    $atom['successful'] = $rs->success;
                } else {
                    $atom['successful'] = false;
                }

                //$atom['order'] = $rs->order;

                $atom['procedure-name'] = $rs->procedure->name;

                $atom['hook-html'] = $this->view->skillEvalHookHelper($context, $rs, true);

                $atom['id'] = $rs->id;

                $skills[] = $atom;
            }
        }

        return $skills;
    }

    public function saveQuickSkillLabAction()
    {
        $shiftEntity = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $this->_getParam('shiftID'));

        if (!$shiftEntity->isEditable()) {
            $this->_helper->json(false);
            return;
        }

        // Delete out the old records first...
        $shiftEntity->deleteQuickSkills();

        $order = 1;
        foreach ($this->_getParam('skills', array()) as $skill) {
            // Get the Procedure Type...
            $procSplit = explode("_", $skill['procedure']);

            if (count($procSplit) == 2) {
                switch ($procSplit[0]) {
                    case 'AirwayProcedure':
                        $skillEntity = $this->getSkillEntity("Airway", $skill['skill-id']);
                        $shiftEntity->addAirway($skillEntity);
                        $skillEntity->success = isset($skill['successful']);
                        break;

                    case 'IvProcedure':
                        $skillEntity = $this->getSkillEntity("Iv", $skill['skill-id']);
                        $shiftEntity->addIv($skillEntity);
                        $skillEntity->success = isset($skill['successful']);
                        break;

                    case 'CardiacProcedure':
                        $skillEntity = $this->getSkillEntity("CardiacIntervention", $skill['skill-id']);
                        $shiftEntity->addCardiacIntervention($skillEntity);
                        break;

                    case 'OtherProcedure':
                        $skillEntity = $this->getSkillEntity("OtherIntervention", $skill['skill-id']);
                        $shiftEntity->addOtherIntervention($skillEntity);
                        $skillEntity->success = isset($skill['successful']);
                        break;

                    case 'LabAssessment':
                        $skillEntity = $this->getSkillEntity("LabSkill", $skill['skill-id']);
                        $shiftEntity->addLabSkill($skillEntity);
                        $skillEntity->success = isset($skill['successful']);
                        break;
                }

                $skillEntity->procedure = \Fisdap\EntityUtils::getEntity($procSplit[0], $procSplit[1]);
                $skillEntity->performed_by = true;

                // Add in the subject type here too...
                $subject = $this->em->getRepository('Fisdap\Entity\Subject')->findOneBy(array('name' => $skill['subject-name'], 'type' => $skill['subject-type']));
                $skillEntity->subject = $subject;

                $skillEntity->skill_order = $order;

                if ($skillEntity->isDatabaseField('attempts')) {
                    $skillEntity->attempts = 1;
                }

                $skillEntity->updated = new \DateTime();

                $skillEntity->save(false);

                $order++;
            }
        }

        $shiftEntity->save();

        if ($this->_getParam('returnMode') == 'ajax') {
            $this->_helper->json(true);
        } else {
            $this->_redirect("skills-tracker/shifts/my-shift/shiftId/" . $shiftEntity->id);
        }
    }

    public function generateLockFormAction()
    {
        $shiftId = $this->_getParam('shiftId');
        $redirectUrl = $this->_getParam('redirectUrl', '/skills-tracker/shifts/');
        $patientCareUrl = $this->_getParam('patientCareUrl', '/skills-tracker/patients/index/runId/');
        $form = new SkillsTracker_Form_LockShift($shiftId, $redirectUrl, $patientCareUrl);

        $this->_helper->json($form->__toString());
    }


    public function generateShiftFormAction()
    {
        $type = $this->_getParam('type');
        $student_id = $this->_getParam('student_id');
        $shift_id = null;
        if (is_numeric($type)) {
            $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $type);
            $shift_id = $shift->id;
            $type = $shift->type;
        }
        $form = new SkillsTracker_Form_Shift($shift_id, $student_id, null, $type);

        $this->_helper->json($form->__toString());
    }

    public function examInterviewFormAction()
    {
        $this->view->pageTitle = "Exam Interview Tool";

        $student_id = $this->_getParam('studentId');

        $this->view->form = new SkillsTracker_Form_ExamInterview($student_id);

        $request = $this->getRequest();

        if ($request->isPost()) {
            $retVal = $this->view->form->process($request->getPost());
            if ($retVal['code'] == 200) {
                $this->flashMessenger->addMessage("Changes saved");
                $this->_redirect("/skills-tracker/shifts");
            } else {
                $this->flashMessenger->addMessage("There was an error. Please check your selections.");
            }
        }
    }

    /**
     * generate the view helper for a given attachment
     */
    public function generateViewAttachmentAction()
    {
        $attachmentId = $this->_getParam('attachmentId');
        $shiftType = $this->_getParam('shiftType');
        $shiftId = $this->_getParam('shiftId');
        $attachment = $this->shiftAttachmentsGateway->getOne($shiftId, $attachmentId);

        // the attachments service provides some logic to transform the attachments
        $attachmentService = new \Fisdap\Service\AttachmentService();
        $attachment->preview = $attachmentService->getPreview($attachment);

        $content = $this->view->viewAttachment($attachment, $shiftId, "shift", $shiftType);
        $buttons  = "<div class='gray-button small modal-button-wrapper'><a class='button closeModal'>Ok</a></div>";

        $this->_helper->json(array("content" => $content, "buttons" => $buttons));
    }

    /**
     * generate the form to create/edit an attachment
     */
    public function generateShiftAttachmentFormAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $values = $request->getPost();
            $attachmentId = $values['attachmentId'] ? $values['attachmentId'] : null;
            $shiftId = $values['shiftId'] ? $values['shiftId'] : null;
            $tableType = $values['tableType'] ? $values['tableType'] : null;

            // get the info we need about the shift and the attachments count
            $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
            $attachmentsRemaining = $this->shiftAttachmentsGateway->getRemainingAllottedCount($shift->student->user_context->id);
            $attachmentForm = new \SkillsTracker_Form_ShiftAttachmentForm($this->shiftAttachmentsGateway, $shift, $attachmentId, $tableType);
            $content  = $attachmentForm->__toString();

            if ($attachmentsRemaining > 0 || $attachmentId) {
                $buttons = "<div class='gray-button small modal-button-wrapper'><a class='button closeModal'>Cancel</a></div>";
                $buttons .= "<div class='green-buttons small modal-button-wrapper'><a class='button saveAttachmentForm'>Save</a></div>";
            } else {
                // if we're trying to add an attachment but we are at the limit, the user can only close the modal
                $buttons = "<div class='gray-button small modal-button-wrapper'><a class='button closeModal'>Ok</a></div>";
            }

            $this->_helper->json(array("content" => $content, "buttons" => $buttons));
        } else {
            $this->_redirect("/skills-tracker/shifts");
        }
    }

    public function auditShiftAction()
    {
        $shiftId = $this->_getParam('shiftId');
        $audit = $this->_getParam('audit');
        $lockedFlag = $this->_getParam('lockedFlag');

        $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);

        if (!$shift->isEditable() || $this->user->getCurrentRoleName() != 'instructor') {
            $this->_helper->json(false);
            return;
        }

        $shift->audited = $audit;

        //If the locked flag is set, be sure to lock this shift
        if ($lockedFlag) {
            $shift->lockShift(true);
        }

        $shift->save();

        $this->_helper->json($shift->locked);
    }

    public function getEvaluatorListAction()
    {
        $evaluatorType = \Fisdap\EntityUtils::getEntity("EvaluatorType", $this->_getParam("evaluatorTypeId"));
        $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $this->_getParam("shiftId"));

        if ($evaluatorType->id == 1) {
            $users = \Fisdap\EntityUtils::getRepository("User")->getAllInstructorsByProgram($shift->student->program->id);
        } elseif ($evaluatorType->id == 2) {
            $users = \Fisdap\EntityUtils::getRepository("User")->getAllStudentsByProgram($shift->student->program->id, array("graduationStatus" => array(1)));
        }

        $this->_helper->json($users);
    }

    public function getPracticeFormAction()
    {
        $itemId = $this->_getParam("practiceItemId");
        $defId = $this->_getParam("practiceDefinitionId");
        $shiftId = $this->_getParam("shiftId");
        $primaryShiftId = $this->_getParam("primary_shift_id");

        $item = $labDef = $shift = null;

        if ($defId && $shiftId) {
            $labDef = \Fisdap\EntityUtils::getEntity("PracticeDefinition", $defId);
            $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
        } elseif ($itemId) {
            $item = \Fisdap\EntityUtils::getEntity("PracticeItem", $itemId);
            $labDef = $item->practice_definition;
            $shift = $item->shift;
        }

        // Send back either the practice form or the URL for the new skillsheet, depending on if one has been assigned to the def
        if ($labDef->skillsheet) {
            // Determine whether or not we have a filled out eval to load...
            if ($item->eval_session) {
                $data = array(
                        'postUrl' => $this->view->serverUrl() . '/skills-tracker/shifts/post-save-lab-eval',
                        'psid' => $primaryShiftId,
                        'esid' => $item->eval_session->id,
                        'source' => 'lab',
                        'lpii' => $item->id,
                        'pdi' => $defId
                );

                $this->view->action('get-eval-view-url', 'oldfisdap', null, $data);
            } else {
                $hookId = 0;
                // The hooks are mapped based on certification level.
                switch ($shift->student->getCertification()->id) {
                    case 1:
                        $hookId = 124;
                        break;
                    case 3:
                        $hookId = 126;
                        break;
                    case 5:
                        $hookId = 125;
                        break;
                }

                $data = array(
                        'edid' => $labDef->skillsheet->id,
                        'ehid' => $hookId,
                        'psid' => $primaryShiftId,
                        'subject' => $shift->student->user->id,
                        'sid' => $shift->id,
                        'source' => 'lab',
                        'postUrl' => $this->view->serverUrl() . '/skills-tracker/shifts/post-save-lab-eval',
                        'pdi' => $defId
                );

                // This is a bit strange, but not sure how to avoid.  The get-eval-url action
                // spits out json formatted data, which will still return to the browser.
                // Long story short, you don't need to re-pack the data into a new json call.
                $this->view->action('get-eval-url', 'oldfisdap', null, $data);
                //$this->_helper->json(array("data" => $labDef->toArray(), "link" => $evalURL));
            }
        } else {
            $form = new \SkillsTracker_Form_PracticeModal($itemId, $labDef->id, $shiftId);
            $this->_helper->json(array("data" => $labDef->toArray(), "form" => $form->__toString()));
        }
    }

    public function postSaveLabEvalAction()
    {
        $lpi = null;

        if ($lpid = $this->_getParam('lpii', false)) {
            $lpi = \Fisdap\EntityUtils::getEntity('PracticeItem', $lpid);
        } else {
            $lpi = new \Fisdap\Entity\PracticeItem();
        }

        $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $this->_getParam('sid'));

        if ($shift) {
            $lpi->set_shift($shift);
            $lpi->set_student($shift->student);
        }

        $pdi = $this->_getParam('pdi');

        if ($pdi > 0) {
            $lpi->set_practice_definition($pdi);
        }

        // Always default to a live human, since this isn't set in an eval.
        $lpi->set_patient_type(5);

        if ($evalSessionID = $this->_getParam('EvalSession_id')) {
            $lpi->set_eval_session($evalSessionID);

            $evalSession = \Fisdap\EntityUtils::getEntity('EvalSessionLegacy', $evalSessionID);

            $lpi->evaluator_id = $evalSession->evaluator_id;

            $lpi->set_evaluator_type($evalSession->evaluator_type);

            $lpi->time = $evalSession->start_time;
            $lpi->passed = $evalSession->passed;
            $lpi->confirmed = $evalSession->confirmed;
        }

        //If the evaluator is a student, add the skill credit immediately, if instructor, check confirmation status and add/remove relevant skills
        if ($lpi->evaluator_type->id == 2) {
            $lpi->confirmAttachSkills(true, $lpi->passed);
        } elseif ($lpi->evaluator_type->id == 1) {
            if ($lpi->confirmed == 1) {
                $lpi->confirmAttachSkills($lpi->confirmed, $lpi->passed);
            } elseif ($lpi->confirmed == 0) {
                $lpi->unconfirmDeleteSkills();
            }
        }

        $lpi->save();
    }

    public function validatePracticeAction()
    {
        $formValues = $this->_getAllParams();
        $form = new \SkillsTracker_Form_PracticeModal($formValues['practiceItemId'], $formValues['practiceDefinitionId'], $formValues['shiftId']);
        $this->_helper->json($form->process($formValues));
    }

    public function generateLabPracticeWidgetAction()
    {
        $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $this->_getParam("shiftId"));
        $defaultDef = \Fisdap\EntityUtils::getEntity("PracticeDefinition", $this->_getParam("selectedDefId"));

        if ($defaultDef) {
            $this->session->defaultDefinitions[$shift->student->id] = $defaultDef->id;
        }

        //If we're generating a lab partner widget, save it in the session
        if ($this->globalSession->shiftId != $shift->id) {
            $this->_helper->json($this->generateLabPartnerWidgetJson(\Fisdap\EntityUtils::getEntity("ShiftLegacy", $this->globalSession->shiftId), $shift, true, $defaultDef->id));
            return;
        }

        //Otherwise, this must be the shift that belongs to the page
        $this->_helper->json($this->view->practiceSkillTable($shift, false, false, $defaultDef->id));
    }

    /**
     * AJAX responder to validate the lab partner signin form for students
     */
    public function validateLabPartnerAction(JblRestApiUserAuthentication $jblAuthenticator)
    {
        $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $this->globalSession->shiftId);
        $username = $this->_getParam("username");
        $password = $this->_getParam("password");

        //check if the username looks like an email address
        $is_email = strpbrk($username, "@");

        //if it does, try JBL authenticator, if not, try Fisdap auth directly.
        if ($is_email) {
            if ($jblUser = $jblAuthenticator->authenticateWithEmailPassword($username, $password)) {

                // if JBL authentication was successful, get the Fisdap user using the PSG person id as the username
                $username = UuidType::transposeUuid($jblUser->PersonId);
                $userEntity = \Fisdap\EntityUtils::getRepository("User")->getOneByUsername($username);

                if ($userEntity) {
                    // Now authenticate via Fisdap db
                    // the third argument here is saying that the password is already hashed
                    if (\Fisdap\Entity\User::authenticate_password($userEntity->username, $userEntity->password, true)) {
                        $user = $userEntity;
                    }
                }
            } else {
                $this->_helper->json($this->view->errorContainer(array("The username/password does not match.  <a href='#' id='try-again'>Try again?</a>")));
            }
        } else {
            if (\Fisdap\Entity\User::authenticate_password($username, $password)) {
                $user = \Fisdap\Entity\User::getByUsername($username);
            } else {
                $this->_helper->json($this->view->errorContainer(array("The username/password does not match.  <a href='#' id='try-again'>Try again?</a>")));
            }
        }

        //Don't allow the user to add his/herself as a lab partner
        if ($shift->student->user->username == $user->username) {
            $this->_helper->json($this->view->errorContainer(array("You cannot add yourself as a lab partner. <a href='#' id='try-again'>Try again?</a>")));
        }

        //Don't allow the user to add an instructor
        if ($user->isInstructor()) {
            $this->_helper->json($this->view->errorContainer(array("You cannot add an instructor as a lab partner. <a href='#' id='try-again'>Try again?</a>")));
        }

        //Don't allow the user to add a lab partner not in their program
        if ($shift->student->program->id != $user->getCurrentRoleData()->program->id) {
            $this->_helper->json($this->view->errorContainer(array("You cannot add a lab partner whose Fisdap account is at a different school. <a href='#' id='try-again'>Try again?</a>")));
        }

        //Attempt to find any shifts for the new lab partner that occur on the same day as the given shift
        $shifts = \Fisdap\EntityUtils::getRepository("ShiftLegacy")->getShiftsByStartDate($user->getCurrentRoleData()->id, $shift->start_datetime->format('Y-m-d'), $shift->type);

        if (count($shifts) == 0) {
            //If the student has no shifts and their program allows it, create a shift for them
            $permission = "can_students_create_" . $shift->type;
            if ($user->getCurrentProgram()->$permission) {
                $newShift = $shift->copyShiftForLabPartner($user->getCurrentRoleData());
                $newShift->save();

                $this->_helper->json($this->generateLabPartnerWidgetJson($shift, $newShift));
            } else {
                //Otherwise tell them we can't do anything
                $this->_helper->json($this->view->errorContainer(array("You do no have a shift today and we cannot create one for you because of your program settings. <a href='#' id='try-again'>Try again?</a>")));
            }
        } elseif (count($shifts) == 1) {
            //If the student has exactly one shift, return the practice widget for that shift
            $this->_helper->json($this->generateLabPartnerWidgetJson($shift, $shifts[0]));
        } else {
            //if the student has more than one shift, return a list that they can choose from
            $chooseShiftsBox = "<h3 class='practice-header'>Choose a shift for " . $shifts[0]->student->user->getName() . "</h3><div class='notice'>" . $shifts[0]->student->user->getName() . " has more than one lab shift today, please choose one to add lab practice:<br /><br /><ul>";
            foreach ($shifts as $existingShift) {
                $chooseShiftsBox .= "<li><a href='#' class='pick-lab-shift' data-shiftid='" . $existingShift->id . "'>" . $existingShift->getShortSummary() . "</a></li>";
            }
            $chooseShiftsBox .= "</div>";
            $this->_helper->json($chooseShiftsBox);
        }
    }

    /**
     * Given the existing shift, and the shift that we want to add the widget for, save it
     * in the session and then return the widget in JSON.
     * @param \Fisdap\Entity\Shift $shift the shift owned by the logged in user
     * @param \Fisdap\Entity\Shift $newShift the shift practice widget being added
     * @param boolean $widgetRefresh boolean to determine if we're adding a new widget or refreshing an existing one.
     */
    private function generateLabPartnerWidgetJson($shift, $newShift, $widgetRefresh = false, $defaultDef = null)
    {
        //Don't allow a lab partner who already has a lab widget saved to add another
        if (count($this->session->labPartnerShifts[$shift->id])) {
            foreach ($this->session->labPartnerShifts[$shift->id] as $labPartnerShiftId) {
                $labPartnerShift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $labPartnerShiftId);
                if ($labPartnerShift->student->id == $newShift->student->id && !$widgetRefresh) {
                    return;
                }
            }
        }

        //Save the lab partner in the session if we're not refreshing a widget
        if (!$widgetRefresh) {
            $this->session->labPartnerShifts[$shift->id][] = $newShift->id;
        }

        //Return the view helper in JSON format
        return $this->view->practiceSkillTable($newShift, null, true, $defaultDef);
    }

    /**
     * AJAX Responder to create multiple practice widgets give a list of student IDs
     */
    public function generateMultiplePracticeWidgetsAction()
    {
        $studentIds = $this->_getParam("studentIds");
        $widgets = array();
        $thisShift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $this->globalSession->shiftId);


        foreach ($studentIds as $id) {
            //Attempt to find any shifts for the new lab partner that occur on the same day as the given shift
            $shifts = \Fisdap\EntityUtils::getRepository("ShiftLegacy")->getShiftsByStartDate($id, $thisShift->start_datetime->format('Y-m-d'), $thisShift->type);

            if (count($shifts) == 0) {
                $newShift = $thisShift->copyShiftForLabPartner(\Fisdap\EntityUtils::getEntity("StudentLegacy", $id));
                // save and flush so we have the new shift id available for immediate use
                $newShift->save();
                $widgets[] = $this->generateLabPartnerWidgetJson($thisShift, $newShift);
            } elseif (count($shifts) == 1) {
                $widgets[] = $this->generateLabPartnerWidgetJson($thisShift, $shifts[0]);
            } else {
                $chooseShiftsBox = "<h3 class='practice-header'>Choose a shift for " . $shifts[0]->student->user->getName() . "</h3><div class='notice'>" . $shifts[0]->student->user->getName() . " has more than one lab shift today, please choose one to add lab practice:<br /><br /><ul>";
                foreach ($shifts as $existingShift) {
                    $chooseShiftsBox .= "<li><a href='#' class='pick-lab-shift' data-shiftid='" . $existingShift->id . "'>" . $existingShift->getShortSummary() . "</a></li>";
                }
                $chooseShiftsBox .= "</div>";
                $widgets[] = $chooseShiftsBox;
            }
        }
        \Fisdap\EntityUtils::getEntityManager()->flush();

        $this->_helper->json($widgets);
    }

    public function removeLabPartnerWidgetAction()
    {
        $shiftId = $this->getParam("shiftId");
        if (count($this->session->labPartnerShifts[$this->globalSession->shiftId])) {
            //Loop over lab partners and remove this shift
            foreach ($this->session->labPartnerShifts[$this->globalSession->shiftId] as $i => $labPartnerShiftId) {
                if ($labPartnerShiftId == $shiftId) {
                    unset($this->session->labPartnerShifts[$this->globalSession->shiftId][$i]);
                }
            }
        }

        $this->_helper->json(true);
    }

    public function getLabPartnerStudentForm()
    {
        $this->_helper->json($this->view->labPartnerLogin());
    }

    public function deletePracticeItemAction()
    {
        $item = \Fisdap\EntityUtils::getEntity("PracticeItem", $this->_getParam("practiceItemId"));
        $item->shift->removePracticeItem($item);
        $item->shift->save();
        
        $this->_helper->json(count($item->shift->practice_items));
    }

    public function updatePracticePatientTypeAction()
    {
        $item = \Fisdap\EntityUtils::getEntity("PracticeItem", $this->_getParam("practiceItemId"));
        $item->patient_type = $this->_getParam("patientTypeId");
        $item->save();
        
        $am = $item->getAirwayManagement();
        if ($am !== false) {
            // update the existing airway management record
            $am->subject = \Fisdap\EntityUtils::getEntity('Subject', $this->_getParam("patientTypeId"));
            $am->save();
        }
        
        $this->_helper->json(true);
    }

    public function getLabPartnerStudentsAction()
    {
        $filters = $this->_getAllParams();

        $repos = \Fisdap\EntityUtils::getRepository('User');
        $programId = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
        $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $filters['shiftId']);

        //$students = $repos->getAllStudentsByShiftDate($programId, $shift->start_datetime->format("Y-m-d"), $filters);
        $students = $repos->getAllStudentsByProgram($programId, $filters);


        $returnData = array();
        $returnData['columns'] = array('Name');

        //Build an array of students that already have a widget on the page
        $existingStudents = array($shift->student->id);
        if (count($this->session->labPartnerShifts[$shift->id])) {
            foreach ($this->session->labPartnerShifts[$shift->id] as $i => $labPartnerShiftId) {
                $labPartnerShift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $labPartnerShiftId);
                $existingStudents[] = $labPartnerShift->student->id;
            }
        }

        foreach ($students as $student) {
            //Skip the students that already have a widget on the page
            if (in_array($student['id'], $existingStudents)) {
                continue;
            }

            $atom = array();
            $atom['id'] = $student['id'];
            $atom['Name'] = $student['first_name'] . " " . $student['last_name'];

            $returnData['students'][] = $atom;
        }

        $this->_helper->json($returnData);
    }

    /**
     * AJAX call to unconfirm a practice item. The current user
     * must be an instructor with edit eval permissions to do this.
     */
    public function unconfirmPracticeItemAction()
    {
        $item = \Fisdap\EntityUtils::getEntity("PracticeItem", $this->_getParam("practiceItemId"));

        if ($this->user->isInstructor() && $this->user->hasPermission("Edit Evals")) {
            $item->confirmed = false;
            $item->unconfirmDeleteSkills();
            $item->save();

            $this->_helper->json(true);
            return;
        }

        $this->_helper->json(false);
    }

    public function confirmPracticeItemsAction()
    {
        $this->view->pageTitle = "Confirm Practice Items";

        //check permissions
        $permissibleTypes = $this->getInstructorPermissibleShiftTypes();

        //No students can access this page
        if (!$this->user->isInstructor()) {
            $this->displayError("Students are not allowed to access this page");
            return;
        }

        //Only instructors with view data and at least one edit data permission
        if (!$this->user->hasPermission("View All Data") || count($permissibleTypes) == 0) {
            $this->displayError("You do not have the appropriate permissions to view this page.");
            return;
        }

        // Remember previous settings from session
        $this->view->studentId = isset($this->globalSession->studentId) ? $this->globalSession->studentId : null;
        $evaluator = isset($this->session->evaluator) ? $this->session->evaluator : 0;

        //get the student entity
        $this->view->student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $this->view->studentId);

        // set up single student picker for instructors
        $this->view->studentPicker = $this->getStudentPicker();

        // set up evaluator buttonset
        $instructorId = $this->user->getCurrentRoleData()->id;
        $buttonset = new Fisdap_Form_Element_jQueryUIButtonset('evaluator');
        $buttonset->setOptions([$instructorId => 'Me', 0 => 'Any instructor'])
            ->setDecorators(['ViewHelper'])
            ->setValue($evaluator)
            ->setUiTheme("")
            ->setUiSize("extra-small");
        $this->view->evaluatorButtonset = $buttonset;

        //get practice item table
        if ($this->view->student) {
            $this->view->confirmationTableHelper = $this->getPracticeItemConfirmationTable($this->view->student, $permissibleTypes, $evaluator);
        }
    }

    public function confirmPracticeItemsAjaxAction()
    {
        $itemIds = $this->getParam('itemIds', []);

        //Javascript is stupid and passes booleans as strings, so I'm converting back to booleans here
        $confirmed = filter_var($this->getParam('confirmed'), FILTER_VALIDATE_BOOLEAN);

        //Getting entities here to update because we need to also update attached eval sessions and attach associated skills once confirmed.
        foreach ($itemIds as $itemId) {
            $item = \Fisdap\EntityUtils::getEntity('PracticeItem', $itemId);
            $item->confirmed = $confirmed;
            $passed = $item->passed;
            if ($confirmed == true) {
                $item->confirmAttachSkills($confirmed, $passed);
            } elseif ($confirmed == false) {
                $item->unconfirmDeleteSkills();
            }
            $item->save(false);
        }

        \Fisdap\EntityUtils::getEntityManager()->flush();
        $this->_helper->json(true);
    }

    public function generatePracticeItemConfirmationTableAction()
    {
        $student = Fisdap\EntityUtils::getEntity('StudentLegacy', $this->getParam('studentId'));
        $evaluatorId = $this->getParam('evaluator');
        $this->session->evaluator = $evaluatorId;

        $permissibleTypes = $this->getInstructorPermissibleShiftTypes();

        $this->_helper->json($this->getPracticeItemConfirmationTable($student, $permissibleTypes, $evaluatorId));
    }

    /**
     * Get an array of shift types that the logged in user is allowed to edit
     *
     * @return array
     */
    private function getInstructorPermissibleShiftTypes()
    {
        $permissibleTypes = [];
        foreach (['Field', 'Clinical', 'Lab'] as $type) {
            if ($this->user->hasPermission("Edit " . $type . " Data")) {
                $permissibleTypes[] = strtolower($type);
            }
        }

        return $permissibleTypes;
    }

    /**
     * @param StudentLegacy $studentLegacy
     * @param array         $permissibleShiftTypes
     * @param null          $evaluatorId
     *
     * @return string HTML rendering of the view helper
     */
    private function getPracticeItemConfirmationTable(StudentLegacy $studentLegacy, array $permissibleShiftTypes, $evaluatorId = null)
    {
        //get practice items
        $instructorId = $evaluatorId ? $evaluatorId : null;
        $items = \Fisdap\EntityUtils::getRepository('PracticeItem')->getItemsByStudentEvaluatorShiftTypes($studentLegacy, $permissibleShiftTypes, $instructorId);

        return $this->view->practiceItemConfirmationTable($studentLegacy, $items, $permissibleShiftTypes);
    }

    private function getSkillEntity($type, $id)
    {
        $skillEntity = \Fisdap\EntityUtils::getEntity($type, $id);
        if ($skillEntity) {
            return $skillEntity;
        } else {
            $fullEntName = "\\Fisdap\\Entity\\" . $type;
            return new $fullEntName();
        }
    }

    /**
     * Check to see if a shift has been locked
     * @param integer $shiftId the ID of the shift
     */
    private function checkPermissions($shiftId)
    {
        $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);

        if ($this->user->getCurrentRoleName() == 'student') {
            if ($shift->locked) {
                $this->displayError("This shift has been locked. In order to add patient care information, please contact your instructor in order to unlock it.");
            }
        }
    }

    /**
     * Get an instance of the student picker with some default settings
     *
     * @param array $config
     * @param array $picklistOptions
     * @return Fisdap_View_Helper_MultistudentPicklist
     */
    private function getStudentPicker($config = [], $picklistOptions = [])
    {
        if (empty($config)) {
            $config = array("student" => $this->view->studentId);
        }

        if (empty($picklistOptions)) {
            $picklistOptions = array(
                'mode' => 'single',
                'loadJSCSS' => true,
                'loadStudents' => true,
                'useSessionFilters' => true,
                'longLabel' => true
            );
        }

        return $this->view->multistudentPicklist($this->user, $config, $picklistOptions);
    }

    /**
     * Get the shift attachments for this shift and prepare/sort them them for use in the attachments table
     * @param $shift
     */
    private function getShiftAttachments($shift)
    {
        // prepare attachments to stick in the view
        // use the api client to get the students shifts, keyed by id, with attachment info
        try {
            $rawAttachments = $this->shiftAttachmentsGateway->get($shift->id, null, array("verifications"));
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            return array();
        }

        // the attachments service provides some logic to transform the attachments
        $attachmentService = new \Fisdap\Service\AttachmentService();

        // prep the attachments for the view and sort them in chronological order
        $attachments = array();
        if ($rawAttachments) {
            foreach ($rawAttachments as $attachmentEntity) {
                $date = new \DateTime($attachmentEntity->created);
                $attachmentEntity->preview = $attachmentService->getPreview($attachmentEntity);
                $attachmentEntity->usedForSignoff = (count($attachmentEntity->verification_ids) > 0);
                $attachments[$date->format('U')] = array("attachment" => $attachmentEntity, "associatedEntityId" => $shift->id, "titleClass" => $shift->type);
            }
            ksort($attachments);
        }
        return $attachments;
    }
}
