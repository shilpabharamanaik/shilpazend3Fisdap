<?php

class Mobile_IndexController extends Mobile_Controller_SkillsTrackerPrivate
{
    public function init()
    {
        parent::init();
        $this->studentId = $this->_getParam('studentId');
        if ($this->studentId) {
            $this->globalSession->studentId = $this->studentId;
        } elseif (!$this->studentId && $this->user && !$this->user->isInstructor()) {
            $this->studentId = $this->user->getCurrentRoleData()->id;
        }
        $this->view->user = $this->user;
    }

    public function indexAction()
    {
        //Grab and set filter
        $filter = $this->_getParam('filter', $this->globalSession->filter);
        $this->globalSession->filter = $filter;
        $this->view->filter = ($filter) ? $filter : "pending";
        
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        //Remember previous settings from session
        $this->view->studentId = isset($this->globalSession->studentId) ? $this->globalSession->studentId : null;
        
        if ($user->getCurrentRoleName() == "student") {
            $this->view->studentId = $user->getCurrentRoleData()->id;
        }
    
        $this->view->student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $this->view->studentId);

        $allowedShiftTypes = \Fisdap\Entity\ProgramLegacy::getStudentAllowedShiftTypes($user);
        $this->view->showAddShiftButton = count($allowedShiftTypes) > 0;
        
        $this->view->shiftModal = new SkillsTracker_Form_ShiftMobile(null, $this->view->studentId, null, $allowedShiftTypes);
        $this->view->shiftLockModal = new SkillsTracker_Form_LockShift(null, "/mobile/index", "/mobile/patients/patient/runId/");
    }
    
    public function goalsReportAction()
    {
    }

    public function findStudentsAction()
    {
        $searchString = $this->_getParam('searchString');
        
        $students = \Fisdap\EntityUtils::getRepository('User')->findStudents($this->user->getProgramId(), $searchString);
        $html = "<div>";
        
        $response = array();
        foreach ($students as $student) {
            //$html .= "<a href='/mobile/index/index/studentId/{$student->id}'>" . $student->last_name . ", " . $student->first_name . " - " . $student->getCertification() . ": " . $student->getGraduationDate()->format("m/Y") . "</a><br>";
            $response[$student->id] = $student->last_name . ", " . $student->first_name . " - " . $student->getCertification()->description . ": " . $student->getGraduationDate()->format("m/Y");
        }
        
        $this->_helper->json($response);
    }
    
    public function graduatedAction()
    {
        $status = "graduated";
        
        $loggedInStudent = \Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData();
        
        if ($loggedInStudent->good_data_flag == 4) {
            $status = "left the program";
        }
        
        $this->view->status = $status;
    }
    
    public function evalsAction()
    {
        $hookIDs = explode(",", $this->_getParam('hid'));
        $this->view->hook_id = current($hookIDs);
        
        $shiftID = $this->_getParam('sid');
        
        $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftID);
        $this->view->shift = $shift;
        
        $evals = \Fisdap\EntityUtils::getRepository('EvalDefLegacy')->getEvalsByHook($hookIDs, $this->user->getProgramId());

        $this->view->evalOptions = array();
        foreach ($evals as $eval) {
            $this->view->evalOptions[$eval['id']] = $eval['name'];
        }
        
        
        // Build up a list of evals that the student has completed.
        $this->view->completedEvals = \Fisdap\Entity\EvalSessionLegacy::getUsersCompletedEvals($shiftID, $hookIDs, $shift->student->user->id);
    }

    public function redirectAction()
    {
        Zend_Layout::getMvcInstance()->setLayout('noHeaderMobile');
    }
}
