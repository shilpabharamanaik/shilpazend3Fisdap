<?php

use Fisdap\Data\Event\EventLegacyRepository;
use Fisdap\Data\Requirement\RequirementRepository;
use Fisdap\Data\Slot\SlotAssignmentRepository;
use Fisdap\Entity\SiteLegacy;
use Fisdap\Service\ProductService;

class Portfolio_IndexController extends Fisdap_Controller_Private
{
    public function init()
    {
        parent::init();
        $this->preDispatch(); // todo - do we really need this here?

        $this->user = \Fisdap\Entity\User::getLoggedInUser();

        // redirect to login if the user is not logged in yet
        if (!$this->user) {
            return;
        }

        $this->view->user = $this->user;
        $this->view->beta_scheduler = $this->user->getCurrentProgram()->scheduler_beta;
        $this->view->headScript()->appendFile("/js/portfolio/index/index.js");
        $this->view->headLink()->appendStylesheet("/css/portfolio/index/index.css");

        // set up single student picker for instructors
        $picklistOptions = array(
            'mode' => 'single',
            'loadJSCSS' => true,
            'loadStudents' => true,
            'useSessionFilters' => true,
            'longLabel' => true
        );

        // Used for exporting...
        $this->view->contentOnly = $this->_getParam('contentOnly', false);

        // figure out who's portfolio we are looking at
        if ($this->user->isInstructor()) {
            if (!$this->user->getCurrentRoleData()->hasPermission("View All Data")) {
                $this->view->errorMessage = "You do not have permission to access the requested page.";
                $this->render('error');
                return;
            } else {
                if ($this->getParam('studentId') > 0) {
                    // look in the get first
                    $this->student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $this->_getParam('studentId'));
                } elseif ($this->getParam('userContextId') > 0) {
                    $this->student = \Fisdap\EntityUtils::getEntity("UserContext", $this->_getParam('userContextId'))->getRoleData();
                } elseif ($this->globalSession->studentId > 0) {
                    // otherwise, the student might be in the session
                    $this->student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $this->globalSession->studentId);
                } else {
                    // if this is an instructor but no student has been picked,
                    // set up single student picker for instructors with no one picked, render the index page and bail
                    $this->view->studentPicker = $this->view->multistudentPicklist($this->user, null, $picklistOptions);
                    return;
                }
            }
        } else {
            $this->student = $this->user->getCurrentRoleData();
        }

        // If the student isn't null
        if ($this->student != null) {
            // make sure the user matches the students program
            if ($this->user->getProgramId() != $this->student->program->id) {
                $this->view->errorMessage = "You do not have permission to access the requested page.";
                $this->render("error");
                return;
            // If the student doesn't have skills tracker OR beta scheduler, show an error.
            } elseif (!$this->student->getSerialNumber()->hasSkillsTracker() &&
                !($this->student->getSerialNumber()->hasScheduler() && $this->view->beta_scheduler)
            ) {
                if ($this->view->beta_scheduler) {
                    $scheduler_msg = " or Fisdap Scheduler";
                }

                // set up single student picker for instructors
                $config = array("student" => $this->student->id);
                $this->view->studentPicker = $this->view->multistudentPicklist($this->user, $config, $picklistOptions);

                $this->view->errorMessage = $this->student->first_name . " does not use the Fisdap Skills Tracker" . $scheduler_msg . ".<br>" .
                    "If you want to use the portfolio feature, you need to <a href='/account/orders/upgrade'>upgrade</a> " . $this->student->first_name . "'s account.";
                $this->render("index");
                $this->render("error");
                return;
            }

            $this->view->student = $this->student;
            $this->view->student_name = $this->student->user->first_name;

            if ($this->view->beta_scheduler && $this->student->getSerialNumber()->hasScheduler()) {
                $this->view->compliance = true;
            } else {
                $this->view->compliance = false;
            }

            $this->view->skillstracker = $this->student->getSerialNumber()->hasSkillsTracker();

            // remember this student in the session
            $this->globalSession->studentId = $this->student->id;
        } else {
            $this->render('index');
            return;
        }

        // set up single student picker for instructors
        $config = array("student" => $this->globalSession->studentId);
        $this->view->studentPicker = $this->view->multistudentPicklist($this->user, $config, $picklistOptions);
    }

    public function indexAction()
    {
        $this->view->user = $this->user;

        // if we have a student reroute to the about page
        if ($this->student->id > 0) {
            $this->_redirect('/portfolio/index/about/');
        }
    }

    public function aboutAction()
    {
        $this->view->reports_link = Util_HandyServerUtils::get_fisdap_members1_url_root() . "reports";

        // format contact info
        if ($this->student->contact_name) {
            $this->view->contact_info = $this->student->contact_name;
            if ($this->student->contact_relation) {
                $this->view->contact_info .= " (" . $this->student->contact_relation . ")";
            }
        }

        // if the student has skillstracker, get internship progress info
        if ($this->view->skillstracker) {
            $shiftRepos = \Fisdap\EntityUtils::getRepository('ShiftLegacy');
            $labShifts = $shiftRepos->getShiftsByStudent($this->student->id, array('type' => 'lab'));
            $labSummaries = \Fisdap\Shifts::getShiftsSummaries($labShifts);
            $clinicalShifts = $shiftRepos->getShiftsByStudent($this->student->id, array('type' => 'clinical'));
            $clinicalSummaries = \Fisdap\Shifts::getShiftsSummaries($clinicalShifts);
            $fieldShifts = $shiftRepos->getShiftsByStudent($this->student->id, array('type' => 'field'));
            $fieldSummaries = \Fisdap\Shifts::getShiftsSummaries($fieldShifts);

            $this->view->lab_hours = $labSummaries['Hours']['Attended'];
            $this->view->clinical_hours = $clinicalSummaries['Hours']['Attended'];
            $this->view->field_hours = $fieldSummaries['Hours']['Attended'];
            $this->view->total_hours = $labSummaries['Hours']['Attended'] + $clinicalSummaries['Hours']['Attended'] + $fieldSummaries['Hours']['Attended'];
            $this->view->clinical_patients = $clinicalSummaries['Patient Care']['Clinical patients'];
            $this->view->field_patients = $fieldSummaries['Patient Care']['Field patients'];
            $this->view->total_patients = $clinicalSummaries['Patient Care']['Clinical patients'] + $fieldSummaries['Patient Care']['Field patients'];
        }
    }

    public function saveDescriptionAction()
    {
        $portfolioDetails = \Fisdap\EntityUtils::getRepository('PortfolioDetails')->findOneBy(array('student' => $this->student->id));

        if ($portfolioDetails == null) {
            $portfolioDetails = \Fisdap\EntityUtils::getEntity('PortfolioDetails');
            $portfolioDetails->student = $this->student;
        }

        $portfolioDetails->portfolio_description = $this->_getParam('description', '');

        $portfolioDetails->save();

        $this->_helper->json(true);
    }

    public function internshipRecordsAction()
    {
        $this->checkProducts("skills-tracker");

        $this->view->headScript()->appendFile("/js/jquery.cluetip.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");

        if ($this->user->isInstructor()) {
            $this->view->subject = $this->student->user->first_name;
            $this->view->possessive = $this->student->user->first_name . "'s";
            $this->view->is = "is";
            $this->view->has = "has";
        } else {
            $this->view->subject = "you";
            $this->view->possessive = "your";
            $this->view->is = "are";
            $this->view->has = "have";
        }
        $this->view->student_id = $this->student->id;

        $shiftRepos = \Fisdap\EntityUtils::getRepository('ShiftLegacy');
        $labShifts = $shiftRepos->getShiftsByStudent($this->student->id, array('type' => 'lab'));
        $labSummaries = \Fisdap\Shifts::getShiftsSummaries($labShifts);
        $clinicalShifts = $shiftRepos->getShiftsByStudent($this->student->id, array('type' => 'clinical'));
        $clinicalSummaries = \Fisdap\Shifts::getShiftsSummaries($clinicalShifts);
        $fieldShifts = $shiftRepos->getShiftsByStudent($this->student->id, array('type' => 'field'));
        $fieldSummaries = \Fisdap\Shifts::getShiftsSummaries($fieldShifts);

        $this->view->lab_hours = $labSummaries['Hours']['Attended'];
        $this->view->clinical_hours = $clinicalSummaries['Hours']['Attended'];
        $this->view->field_hours = $fieldSummaries['Hours']['Attended'];

        $this->view->skills_report_link = "/oldfisdap/redirect/?loc=" . urlencode("reports/new_skills_report.php");

        // Get the counts for the various records for this student...
        $em = \Fisdap\EntityUtils::getEntityManager();

        $this->view->ventilations = $em->createQuery("SELECT count(s) FROM \Fisdap\Entity\Airway s WHERE s.student = ?1 AND s.procedure = 28 AND s.performed_by = 1 AND s.subject = 1")->setParameter(1, $this->student->id)->getSingleScalarResult();

        $this->view->ivs = $em->createQuery("SELECT count(s) FROM \Fisdap\Entity\Iv s WHERE s.student = ?1 AND s.procedure IN (1, 8) AND s.success = 1 AND s.performed_by = 1 AND s.subject = 1")->setParameter(1, $this->student->id)->getSingleScalarResult();

        $this->view->medIvs = $em->createQuery("SELECT count(s) FROM \Fisdap\Entity\Med s WHERE s.student = ?1 AND s.route IN (13, 17, 32) AND s.performed_by = 1 AND s.subject = 1")->setParameter(1, $this->student->id)->getSingleScalarResult();

        $this->view->medImSc = $em->createQuery("SELECT count(s) FROM \Fisdap\Entity\Med s WHERE s.student = ?1 AND s.route IN (1, 2) AND s.performed_by = 1 AND s.subject = 1")->setParameter(1, $this->student->id)->getSingleScalarResult();

        $this->view->bloodGlucose = $em->createQuery("SELECT count(s) FROM \Fisdap\Entity\Vital s WHERE s.student = ?1 AND s.blood_glucose IS NOT NULL AND s.performed_by = 1 AND s.subject = 1")->setParameter(1, $this->student->id)->getSingleScalarResult();

        $this->view->twelveLead = $em->createQuery("SELECT count(s) FROM \Fisdap\Entity\CardiacIntervention s WHERE s.student = ?1 AND s.rhythm_performed_by = 1 AND s.subject = 1")->setParameter(1, $this->student->id)->getSingleScalarResult();

        $this->view->ets = $em->createQuery("SELECT count(s) FROM \Fisdap\Entity\Airway s WHERE s.student = ?1 AND s.procedure in (5, 6, 10) AND s.performed_by = 1 AND s.subject = 1")->setParameter(1, $this->student->id)->getSingleScalarResult();
    }

    public function affectiveEvaluationsAction()
    {
        $this->checkProducts("skills-tracker");

        $this->view->user = $this->user;

        $completedEvals = \Fisdap\EntityUtils::getRepository('EvalDefLegacy')->getStudentEvals($this->student->id, false, true);

        $cleanEvalList = array();

        foreach ($completedEvals as $eval) {
            $cleanEvalList[$eval['EvalTitle']]['scored_points'] += $eval['scored_points'];
            $cleanEvalList[$eval['EvalTitle']]['total_points'] += $eval['total_points'];
        }

        $oldURL = "/oldfisdap/redirect?loc=" . urlencode("index.html?target_pagename=shift/evals/listAllEvalSessions.html?firstloaded=1");

        $this->view->reportURL = $oldURL;

        $this->view->evalList = $cleanEvalList;

        // Get a list of the available shifts and their late/absent/absent with perm. counts...
        $attendanceCounts = array();
        foreach ($this->student->shifts as $shift) {
            $attendanceCounts[$shift->attendence->name]++;
        }

        $this->view->attendanceCounts = $attendanceCounts;
    }

    public function skillSheetsAction()
    {
        $this->checkProducts("skills-tracker");

        $this->view->user = $this->user;

        $labShifts = \Fisdap\EntityUtils::getRepository('ShiftLegacy')->getShiftsByStudent($this->student->id, array('type' => 'lab'));
        $labSummaries = \Fisdap\Shifts::getShiftsSummaries($labShifts);

        $this->view->labShiftCounts = $labSummaries['Attendance']['Total shifts'] - ($labSummaries['Attendance']['Absences'] + $labSummaries['Attendance']['Absent w/ permission']);
        $this->view->labShiftHours = $labSummaries['Hours']['Attended'];

        //Get an array of all passed practice items and their counts
        $passedPracticeItems = \Fisdap\EntityUtils::getRepository("PracticeItem")->getItemPassCounts($this->student->id);
        $this->view->passedItems = array();
        foreach ($passedPracticeItems as $item) {
            $this->view->passedItems[$item['name']] += $item['pass_count'];
        }

        $scenarioTopicAreas = \Fisdap\EntityUtils::getRepository("EvalDefLegacy")->getScenarioTopicAreas($this->student->id);
        $this->view->topicAreas = array();
        foreach ($scenarioTopicAreas as $topic => $count) {
            $this->view->topicAreas[$topic] = $count;
        }

        $this->view->reportURL = Util_HandyServerUtils::get_fisdap_members1_url_root() . "index.html?target_pagename=shift/evals/listAllEvalSessions.html?firstloaded=1";
    }

    public function competencyAction()
    {
        $this->checkProducts("skillstracker");

        $request = $this->getRequest();

        $certs = \Fisdap\EntityUtils::getRepository('CourseCertification')->findAll();
        $studentCertsRepos = \Fisdap\EntityUtils::getRepository('StudentCourseCertifications');

        $portfolioOptions = \Fisdap\EntityUtils::getRepository('PortfolioOptions')->findOneBy(array('student' => $this->student->id));

        if ($portfolioOptions == null) {
            $portfolioOptions = \Fisdap\EntityUtils::getEntity('PortfolioOptions');
            $portfolioOptions->student = $this->student;
            $portfolioOptions->save();
        }

        if ($request->isPost() && !$this->_getParam('contentOnly', false)) {
            $post = $request->getPost();

            // Start assigning everything to this optionset and student...

            $portfolioOptions->all_requirements_date = ($post['all_requirements_completed']) ? new \DateTime($post['all_requirements_completed']) : null;
            $portfolioOptions->written_exams_completed = isset($post['written_id_cb']);
            $portfolioOptions->practical_skill_sheets_completed = isset($post['practical_skill_sheets_cb']);
            $portfolioOptions->clinical_tracking_records_completed = isset($post['clinical_tracking_records_cb']);
            $portfolioOptions->field_internship_records_completed = isset($post['field_internship_records_cb']);
            $portfolioOptions->affective_learning_eval_completed = isset($post['affective_learning_eval_cb']);
            $portfolioOptions->student_counseling_completed = isset($post['student_counseling_cb']);
            $portfolioOptions->passed_national_registry_date = ($post['passed_national_registry_date']) ? new \DateTime($post['passed_national_registry_date']) : null;
            $portfolioOptions->passed_national_registry_completed = isset($post['passed_national_registry_cb']);
            $portfolioOptions->employed_date = ($post['employed_date']) ? new \DateTime($post['employed_date']) : null;
            $portfolioOptions->employed_date_completed = isset($post['employed_date_cb']);
            $portfolioOptions->employer_survey_date = ($post['employer_survey_date']) ? new \DateTime($post['employer_survey_date']) : null;
            $portfolioOptions->employer_survey_completed = isset($post['employer_survey_cb']);
            $portfolioOptions->graduate_survey_date = ($post['graduate_survey_date']) ? new \DateTime($post['graduate_survey_date']) : null;
            $portfolioOptions->graduate_survey_completed = isset($post['graduate_survey_cb']);

            // Save down the different certifications and their dates...
            foreach ($certs as $cert) {
                $studentCert = $studentCertsRepos->findOneBy(array('student' => $this->student->id, 'course_certification' => $cert->id));

                if ($studentCert == null) {
                    $studentCert = \Fisdap\EntityUtils::getEntity('StudentCourseCertifications');
                    $studentCert->course_certification = $cert;
                    $studentCert->student = $this->student;
                }

                if (isset($post['student_cert_na_' . $cert->id])) {
                    $studentCert->not_applicable = true;
                } else {
                    $studentCert->not_applicable = false;
                }

                if ($post['student_cert_date_' . $cert->id] != '') {
                    $certDate = $post['student_cert_date_' . $cert->id];
                    $studentCert->certification_date = new \DateTime($cert->formatDate($certDate));
                } else {
                    $studentCert->certification_date = null;
                }

                if ($post['student_cert_expiration_' . $cert->id] != '') {
                    $exprDate = $post['student_cert_expiration_' . $cert->id];
                    $studentCert->expires = new \DateTime($cert->formatDate($exprDate));
                } else {
                    $studentCert->expires = null;
                }

                $studentCert->save(false);
            }

            // Save down the completed exams
            // Strip out quote characters from the post- otherwise they'll mess
            // up the javascript when being output...
            $exams = array();

            foreach ($post['completed_exams'] as $exam) {
                $exams[] = str_replace(array('"', "'"), array('', '',), $exam);
            }
            $portfolioOptions->completed_exams = serialize($exams);

            $portfolioOptions->save();

            $this->_redirect("/portfolio/index/competency");
        } else {
            $studentCerts = array();

            foreach ($certs as $cert) {
                // Check to see what the status of the students certifications is
                $studentCerts[$cert->name] = array('id' => $cert->id, 'entity' => $studentCertsRepos->findOneBy(array('student' => $this->student->id, 'course_certification' => $cert->id)));
            }

            $this->view->studentCerts = $studentCerts;
            $this->view->portfolioOptions = $portfolioOptions;
        }
    }

    public function attachmentsAction()
    {
        $this->checkProducts("skills-tracker");

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();

            $upload = \Fisdap\EntityUtils::getEntity('PortfolioUploads');
            $upload->processStudentFile($_FILES['file'], $post['description'], $this->student->id);

            $this->_redirect("/portfolio/index/attachments");
        } else {
            $form = new Portfolio_Form_AttachmentForm();
            $this->view->form = $form;

            $tmpUploads = \Fisdap\EntityUtils::getRepository('PortfolioUploads')->getUploadedFilesForStudent($this->student->id);

            // Reverse the order of the uploads- order by date descending (newest first)
            usort($tmpUploads, function ($a, $b) {
                if ($a->created < $b->created) {
                    return 1;
                } elseif ($a->created > $b->created) {
                    return -1;
                } else {
                    return 0;
                }
            });

            $this->view->uploads = $tmpUploads;

            // Find out if the logged in user can delete attached items...
            if ($this->user->isInstructor() && !$this->user->hasPermission("Edit Portfolio")) {
                $this->view->canDelete = false;
            } else {
                $this->view->canDelete = true;
            }
        }
    }

    public function downloadAttachmentAction()
    {
        $id = $this->_getParam('docId');

        $attachment = \Fisdap\EntityUtils::getEntity('PortfolioUploads', $id);

        $attachment->getFile();
    }

    public function deleteAttachmentAction()
    {
        $id = $this->_getParam('docId');

        $attachment = \Fisdap\EntityUtils::getEntity('PortfolioUploads', $id);

        $attachment->delete();

        $this->_redirect("/portfolio/index/attachments");
    }

    public function examsAction()
    {
        // for now, we don't care about what kind of products this student has
        // beyond skills tracker and beta scheduler
        $this->checkProducts("skills-tracker");

        if ($this->_getParam('contentOnly')) {
            // This file contains anything to make the learning RX page not look like utter crap
            $this->view->headLink()->appendStylesheet("/css/portfolio/index/learning-rx-patch.css");
        }

        $contexts = array('secure_testing', 'study_tools', 'pilot_testing');

        $this->view->learningRxContents = array();

        // get exam attempts for each context
        foreach ($contexts as $context) {
            $moodle_attempts = \Fisdap\MoodleUtils::getQuizAttempts($this->student->username, $context);
            $moodle_modifier = \Fisdap\Entity\MoodleTestDataLegacy::getModifier($context);

            // create an array to store these attempts
            ${$context} = array();

            // for each attempt, parse the data and get it ready for the view
            foreach ($moodle_attempts as $i => $attempt) {

                // make 'timestart' the key for each attempt, so they can be sorted later
                $key = "ts" . $attempt['timestart'];

                // get moodle test data
                $moodle_quiz_id = $attempt['quiz'] + $moodle_modifier;
                $moodle_test_data = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy')->findOneBy(array('moodle_quiz_id' => $moodle_quiz_id));


                // get the name
                if (empty($moodle_test_data)) {
                    ${$context}[$key]['name'] = $attempt['name'];
                } else {
                    ${$context}[$key]['name'] = $moodle_test_data->test_name;
                }

                // configure the date
                $date = date('m-d-Y', $attempt['timestart']);
                $mysqldate = date('Y-m-d', $attempt['timestart']);
                ${$context}[$key]['date'] = $date;

                // Only show test score if the exam is set to "show_totals = 1"
                // because this table just displays test score totals
                if ($moodle_test_data->show_totals) {
                    // figure out who gets to see what
                    if ($this->user->isInstructor()) {
                        // show any score/rx to instructors
                        $published = true;
                        $rx_url = Util_HandyServerUtils::get_fisdap_members1_url_root() . "testing/scoreDetails.html?student=" . $this->student->id . "&attempt_id=" . $attempt['uniqueid'] . "&test_id=$moodle_quiz_id";
                    } else {
                        // only show published scores and rxs to students
                        $published = \Fisdap\MoodleUtils::attemptIsPublished($moodle_quiz_id, $this->student, $mysqldate);
                        $rx_url = Util_HandyServerUtils::get_fisdap_members1_url_root() . "testing/stuScoreDetails.html?attempt=" . $attempt['uniqueid'] . "&testid=$moodle_quiz_id";
                    }

                    // format scores and rx based on publication status
                    if ($published) {
                        ${$context}[$key]['score'] = round(($attempt['score'] / $attempt['possible']) * 100, 0);
                        ${$context}[$key]['learning_rx'] = "<a href='$rx_url'><img src='/images/icons/icon_rx.png'></a>";
                    } else {
                        ${$context}[$key]['score'] = 'not published';
                        ${$context}[$key]['learning_rx'] = 'not published';
                    }

                    // figure out if we should show a learning prescription
                    if (empty($moodle_test_data) || $moodle_test_data->show_details == 0) {
                        $show_rx = false;
                    } else {
                        $show_rx = true;
                    }

                    // don't show a rx if there's not one available
                    if (!$show_rx) {
                        ${$context}[$key]['learning_rx'] = 'N/A';
                    }

                    if ($show_rx && $this->_getParam('contentOnly', false) && $this->_getParam('include-learning-prescriptions', false)) {
                        $this->view->learningRxContents[] = $this->getLearningRxContent($this->student->id, $attempt['uniqueid'], $moodle_quiz_id);
                    }
                } else {
                    if ($attempt['possible'] && empty($moodle_test_data)) {
                        ${$context}[$key]['score'] = round(($attempt['score'] / $attempt['possible']) * 100, 0);
                        ${$context}[$key]['learning_rx'] = 'N/A';
                    } else {
                        ${$context}[$key]['score'] = 'N/A';
                        ${$context}[$key]['learning_rx'] = 'N/A';
                    }
                }
            }
        }

        // add the pilot exams to the secure exams array
        // NOTE: these variables show up red in PhpStorm because they were named above as variable variable names (${$context})
        $secure_testing = array_merge($secure_testing, $pilot_testing);
        krsort($secure_testing); // re-order by descending chronological order

        $attempts = array(
            'secure_testing' => array(
                'title' => 'Secure Exams',
                'subtitle' => '(Fisdap Testing)',
                'image' => '<img src="'.ProductService::COMPREHENSIVE_EXAM_ICON.'" class="shield">',
                'moodle_attempts' => $secure_testing
            ),
            'study_tools' => array(
                'title' => 'Practice Exams and Quizzes',
                'subtitle' => '(Fisdap Study Tools)',
                'image' => '<img src="'.ProductService::STUDY_TOOLS_ICON.'" class="shield">',
                'moodle_attempts' => $study_tools)
        );
        $this->view->attempts = $attempts;

        // stuff for scores link
        if ($this->student->getSerialNumber()->hasProductAccess('all_testing')) {
            $this->view->hasTesting = true;
        }
        $this->view->testing_link = "/learning-center/index/retrieve";
    }

    private function getLearningRxContent($studentId, $attemptId, $quizId)
    {
        $locString = "testing/scoreDetails.html?student=" . $this->student->id . "&attempt_id=" . $attemptId . "&test_id=$quizId&source=f2&server=members";
        $baseURL = $this->view->serverUrl() . "/oldfisdap/redirect/?loc=" . urlencode($locString) . "&forceUsername=1&username=" . Zend_Auth::getInstance()->getIdentity();

        $tracker = array();

        $result = \Fisdap\OldFisdapUtils::getLegacyPage($baseURL, $tracker);

        $bodyString = $result['body'];

        $bodyString = str_replace('<div id="header">', '<div id="header" style="display: none;">', $bodyString);

        return $bodyString;
    }

    public function exportContentsAction()
    {
        // Figure out what actions should be shown based on the post vars...
        $actions = array(
            'include-about-page' => 'about',
            'include-competency-page' => 'competency',
            'include-compliance-page' => 'compliance',
            'include-exams-page' => 'exams',
            'include-lab-practice-page' => 'skill-sheets',
            'include-internship-summary-page' => 'internship-records',
            'include-affective-eval-page' => 'affective-evaluations'
        );

        $this->view->includeBase = true;

        $this->view->noHeader = true;
        $this->view->noFooter = true;

        $this->view->actions = array();

        foreach ($actions as $option => $action) {
            if ($this->_getParam($option, false)) {
                $this->view->actions[] = $action;
            }
        }
    }

    public function exportOptionsAction()
    {
    }


    /**
     * @param RequirementRepository $requirementRepository
     * @param EventLegacyRepository $eventLegacyRepository
     *
     * @throws Zend_View_Exception
     */
    public function complianceAction(RequirementRepository $requirementRepository, EventLegacyRepository $eventLegacyRepository)
    {
        $this->checkProducts("compliance");

        $userComplianceAccordion = new Portfolio_View_Helper_UserComplianceAccordion(
            $requirementRepository,
            $eventLegacyRepository,
            $this->view
        );
        $this->view->registerHelper($userComplianceAccordion, 'userComplianceAccordion');

        if ($this->user->isInstructor()) {
            $this->view->permissions = $this->getPermissionsForComplianceMenus($this->user, "portfolio");
            $this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/navbar-menu.css");
            $this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/navbar-menu.js");
        } else {
            $this->view->permissions = array("menu" => false);
        }

        $this->view->user = $this->user;
        $userContextId = $this->student->user_context->id;

        //Get an array of all requirement associations for the program
        $req_associations = $requirementRepository->getRequirementAssociations($this->student->program->id);
        $this->view->req_associations = $req_associations;

        // get the stuff for the email
        $this->view->pending_attachments = $requirementRepository->getAttachments($userContextId, 'pending', true);
        $this->view->non_comp_attachments = $requirementRepository->getAttachments($userContextId, 'non-compliant-only', true);

        // get stuff for the site list
        $sites = \Fisdap\Entity\SiteLegacy::getSites($this->student->program->id);
        $compliant_attachments = $requirementRepository->getAttachments($userContextId, 'compliant-only', true);
        $compliant_reqs = array();
        foreach ($compliant_attachments as $attachment) {
            $compliant_reqs[] = $attachment->requirement->id;
        }
        // go through all the requirements for the program and see if the student has them
        foreach ($req_associations as $req_id => $info) {
            // if this requirement is associated with sites and the user is non-compliant,
            // take those sites out of the site array
            if ($info['site'] && array_search($req_id, $compliant_reqs) === false) {
                foreach ($info['site'] as $type) {
                    foreach ($type as $site_id => $site) {
                        unset($sites[$site_id]);
                    }
                }
            }
        }
        $this->view->sites = array_keys($sites);

        $this->view->tour = new Fisdap_View_Helper_GuidedTourHelper();
        $this->view->tour_id = ($this->user->isInstructor()) ? 3 : 4;
    }


    /**
     * @param RequirementRepository    $requirementRepository
     * @param EventLegacyRepository    $eventLegacyRepository
     * @param SlotAssignmentRepository $slotAssignmentRepository
     */
    public function filterRequirementsTableAction(
        RequirementRepository $requirementRepository,
        EventLegacyRepository $eventLegacyRepository,
        SlotAssignmentRepository $slotAssignmentRepository
    ) {
        $filterBy = $this->getParam('value');
        $type = $this->getParam('type');

        if ($type == 'accordion') {
            $req_associations = $requirementRepository->getRequirementAssociations($this->view->student->program->id);
            $user_context = \Fisdap\EntityUtils::getEntity("UserContext", $this->view->student->user_context->id);
            $viewHelper = new Portfolio_View_Helper_UserComplianceAccordion($requirementRepository, $eventLegacyRepository, $this->view);

            $html = $viewHelper->userComplianceAccordion($user_context, $filterBy, $req_associations);
        }

        if ($type == 'table') {
            $assignment = $slotAssignmentRepository->getOneById($this->getParam('assignment_id'));
            $event = $assignment->slot->event;
            $student = $assignment->user_context->getRoleData();

            $reqHelper = new Portfolio_View_Helper_UserSiteComplianceTable();
            if ($this->user->getProgramId() == $student->program->id) {
                $attachments = $requirementRepository->getAttachmentsBySite($assignment->user_context->id, $event->site->id, $filterBy, true, true);
            } else {
                $attachments = $requirementRepository->getGlobalAttachmentsBySite($assignment->user_context->id, $event->site->id, $filterBy, true);
            }
            $html = $reqHelper->userSiteComplianceTable($assignment->user_context, $event->site, $attachments, $filterBy, $event->isShared());
        }

        $this->_helper->json($html);
    }

    public function generateComplianceHistoryAction()
    {
        $attachmentId = $this->_getParam("attachmentId");
        $this->_helper->json($this->view->complianceHistoryModal($attachmentId));
    }

    public function generateComplianceEmailAction()
    {
        $userContextId = $this->_getParam('userContextId');
        $email = $this->createComplianceEmail($userContextId);
        $modal = new Portfolio_View_Helper_ComplianceEmailModal();
        $this->_helper->json($modal->generateComplianceEmail($email, $userContextId));
    }

    public function sendComplianceEmailAction()
    {
        $userContextId = $this->_getParam('userContextId');
        $email = $this->createComplianceEmail($userContextId);
        return $email->sendHtmlTemplate('compliance-email.phtml');
    }

    public function createComplianceEmail($userContextId)
    {
        $this_user = \Fisdap\Entity\User::getLoggedInUser();
        $recipient = \Fisdap\EntityUtils::getEntity("UserContext", $userContextId);
        $pending_attachments = \Fisdap\EntityUtils::getRepository("Requirement")->getAttachments($recipient->id, 'pending', true);
        $non_comp_attachments = \Fisdap\EntityUtils::getRepository("Requirement")->getAttachments($recipient->id, 'non-compliant-only', true);
        if (count($pending_attachments) + count($non_comp_attachments) == 1) {
            $language['requirements'] = "requirement";
            $language['need'] = "needs";
            $language['these'] = "this";
        } else {
            $language['requirements'] = "requirements";
            $language['need'] = "need";
            $language['these'] = "these";
        }

        $mail = new \Fisdap_TemplateMailer();
        $mail->addTo($recipient->user->email)
            ->setSubject("Compliance Warning")
            ->setViewParam("recipient_name", $recipient->user->getName())
            ->setViewParam("sender_name", $this_user->getName())
            ->setViewParam("language", $language)
            ->setViewParam("pending_attachments", $pending_attachments)
            ->setViewParam("non_comp_attachments", $non_comp_attachments);

        return $mail;
    }

    public function generateSitesModalAction()
    {
        // get the site entities and order them
        $site_ids = explode(',', $this->_getParam('site_ids'));
        $sites = array();
        foreach ($site_ids as $site_id) {
            $site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
            $sites[$site->name] = $site;
        }
        ksort($sites);

        $userContextId = $this->_getParam('userContextId');
        $modal = new Portfolio_View_Helper_ClearedSitesModal();
        $this->_helper->json($modal->generateClearedSites($sites, $userContextId));
    }

    public function checkProducts($product)
    {
        if ($product == "skills-tracker") {
            if (!empty($this->student) && !$this->student->getSerialNumber()->hasSkillsTracker() &&
                $this->student->getSerialNumber()->hasScheduler()
            ) {
                $this->_redirect("/portfolio/index/compliance");
                return;
            }
        }

        if ($product == 'compliance') {
            if (!empty($this->student) && !$this->student->getSerialNumber()->hasScheduler() &&
                $this->student->getSerialNumber()->hasSkillsTracker()
            ) {
                $this->_redirect("/portfolio/index/competency");
                return;
            }
        }

        if ($product == 'competency') {
            if (!empty($this->student) && $this->student->getSerialNumber()->hasScheduler()) {
                $this->_redirect("/portfolio/index/compliance");
                return;
            }
        }
    }

    private function getPermissionsForComplianceMenus($user, $page)
    {
        $instructor = $user->getCurrentRoleData();
        $settings = $instructor->hasPermission("Edit Program Settings");
        $edit_compliance = $instructor->hasPermission("Edit Compliance Status");
        $scheduler = $instructor->hasPermission("View Schedules");
        $lab = $instructor->hasPermission("Edit Lab Schedules");
        $field = $instructor->hasPermission("Edit Field Schedules");
        $clinical = $instructor->hasPermission("Edit Clinic Schedules");
        $page = $page;
        return array("lab" => $lab,
            "field" => $field,
            "clinical" => $clinical,
            "scheduler" => $scheduler,
            "settings" => $settings,
            "edit_compliance" => $edit_compliance,
            "page" => $page,
            "menu" => true
        );
    }
}
