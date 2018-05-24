<?php

use Doctrine\ORM\EntityManager;
use Fisdap\Entity\VideoView;
use Fisdap\EntityUtils;
use Fisdap\Api\Users\CurrentUser\CurrentUser;
use Fisdap\Entity\NotificationUserView;


/**
 * Class to handle all AJAX requests for the moment
 * @package Fisdap
 */
class AjaxController extends Fisdap_Controller_Base
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $db;


    public function init()
    {
        parent::init();
    }

    public function usersearchAction()
    {
        $users = array();
        $term = $this->_getParam('term');
        $role = $this->_getParam('role', 'student');
        if ($role == "student") {
            $students = \Fisdap\EntityUtils::getRepository('User')->findStudents($this->user->getProgramId(), $term, 10);

            foreach ($students as $student) {
                $users[] = array('value' => $student->getLongName(), 'id' => $student->id);
            }
        } else if ($role == "instructor") {
            $instructors = \Fisdap\EntityUtils::getRepository('User')->findInstructors($this->user->getProgramId(), $term, 10);

            foreach ($instructors as $instructor) {
                $users[] = array('value' => $instructor['last_name'] . ", " . $instructor['first_name'], 'id' => $instructor['id']);
            }
        }

        $this->_helper->json($users);
    }

    public function usernamesearchAction()
    {
        $options = array();
        $term = $this->_getParam('term');
        $usernames = \Fisdap\EntityUtils::getRepository('User')->findUsers(null, $term, 20);

        foreach ($usernames as $user) {
            $options[] = array('value' => $user->username, 'id' => $user->username);
        }

        $this->_helper->json($options);
    }

    public function studentPickerListAction()
    {
        $studentSearch = $this->_getParam('studentSearch');
        $students = \Fisdap\EntityUtils::getRepository('User')->findStudents($this->user->getProgramId(), $studentSearch);

        $baseURL = $this->_getParam('baseURL');

        // Add a trailing slash if necessary...
        if (!(substr($baseURL, -1) == '/' || substr($baseURL, -1) == '\\')) {
            $baseURL .= '/';
        }

        $html = "<ul>";

        foreach ($students as $student) {
            $html .= "<li><a href='" . $baseURL . $student->id . "'>" . $student->user->last_name . ", " . $student->user->first_name . " - " . ucwords($student->getCertification()) . ": " . $student->getGraduationDate()->format('m/Y') . "</a></li>";
        }

        $html .= "</ul>";

        $this->_helper->json($html);
    }

    public function instructorPickerListAction()
    {
        $instructorSearch = $this->_getParam('instructorSearch');
        $instructors = \Fisdap\EntityUtils::getRepository('User')->findInstructors($this->user->getProgramId(), $instructorSearch);

        $baseURL = $this->_getParam('baseURL');

        // Add a trailing slash if necessary...
        if (!(substr($baseURL, -1) == '/' || substr($baseURL, -1) == '\\')) {
            $baseURL .= '/';
        }

        $html = "<ul>";

        foreach ($instructors as $instructor) {
            $html .= "<li><a href='" . $baseURL . $instructor['id'] . "'>" . $instructor['last_name'] . ", " . $instructor['first_name'] . "</a></li>";
        }

        $html .= "</ul>";

        $this->_helper->json($html);
    }

    public function getBasesAction()
    {
        $siteId = $this->_getParam('siteId');
        $shiftId = $this->_getParam('shiftId');

        //IE likes to send AJAX params as arrays apparently
        if (is_array($siteId)) {
            $siteId = $siteId[0];
        }
        
        //IE likes to send AJAX params as arrays apparently
        if (is_array($shiftId)) {
            $shiftId = $shiftId[0];
        }
        
        $_SESSION['shiftId'] = $shiftId;

        $this->_helper->json(\Fisdap\Entity\BaseLegacy::getBases($siteId, \Fisdap\Entity\ProgramLegacy::getCurrentProgram()->id));
    }

    
    public function getStatesAction()
    {
        $country = $this->_getParam('countryId');

        //IE likes to send AJAX params as arrays apparently
        if (is_array($country)) {
            $country = $country[0];
        }

        $element = new \Fisdap_Form_Element_States("state");
        $element->setCountry($country);
        $states = $element->getMultiOptions();

        $this->_helper->json($states);
    }

    public function markVideoAsViewedAction()
    {
        $videoID = $this->getParam('vid');
        $userID = (int) $this->getParam('uid');
        
        // Check to see if the user has already marked this video as viewed...
        /** @var \Fisdap\Data\Repository\DoctrineRepository $vidViewRepo */
        $vidViewRepo = $this->em->getRepository(VideoView::class);
        $vidView = $vidViewRepo->findOneBy(['user' => $userID, 'video_key' => $videoID]);

        // If it already exists, update the view_time on it...
        if ($vidView) {
            $vidView->view_time = new \DateTime();
        } else {
            $vidView = new VideoView;

            $vidView->user = $this->currentUser->getWritableUser();
            $vidView->video_key = $videoID;
            $vidView->view_time = new \DateTime();
        }

        $vidViewRepo->store($vidView);

        $this->_helper->json(['success' => "User $userID viewed video $videoID"]);
    }

    /**
     * AJAX handler to validate the student edit and activation form
     *
     * @return JSON array containing any error messages.
     */
    public function validateStudentFormAction()
    {
        $formValues = $this->getAllParams();

        $form = new Account_Form_Student($formValues['studentId'], $formValues['snId']);
        $form->isValid($formValues);

        $this->_helper->json($form->getMessages());
    }

    /**
     * AJAX handler to validate the instructor edit and activation form
     *
     * @return JSON array containing any error messages.
     */
    public function validateInstructorFormAction()
    {
        $formValues = $this->getAllParams();

        $form = new Account_Form_Instructor($formValues['instructorId'], $formValues['snId']);
        $form->isValid($formValues);

        $this->_helper->json($form->getMessages());
    }

    /**
     * AJAX handler to validate the program creation form
     *
     * @return JSON array containing any error messages.
     */
    public function validateProgramFormAction()
    {
        $formValues = $this->getAllParams();

        $form = new Account_Form_Program($formValues['programId']);
        $form->isValid($formValues);

        $this->_helper->json($form->getMessages());
    }

    public function getFilteredStudentListAction()
    {
        $filters = $this->getAllParams();

        $repos = \Fisdap\EntityUtils::getRepository('User');
        $programId = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();

        $students = $repos->getAllStudentsByProgram($programId, $filters);

        $returnData = array();

        $returnData['columns'] = array('Name');

        $returnData['students'] = array();

        foreach ($students as $student) {
            $atom = array();
            $atom['id'] = $student['id'];
            $atom['Name'] = $student['first_name'] . " " . $student['last_name'];

            $returnData['students'][] = $atom;
        }

        $this->_helper->json($returnData);
    }

    /**
     * Get an array of students keyed by id based on a given set of filters
     * Store the filters in a given session namespace, if applicable
     *
     * Used by the multistudentPicklist view helper (called in js)
     */
    public function getFilteredStudentPicklistAction()
    {
        $params = $this->getAllParams();

        // construct the filters array based on the given parameters
        $filters = array();
        if ($params['graduationYear']) {
            $filters['graduationYear'] = $params['graduationYear'];
        }
        if ($params['graduationMonth']) {
            $filters['graduationMonth'] = $params['graduationMonth'];
        }
        if ($params['section']) {
            $filters['section'] = $params['section'];
        }
        if ($params['certificationLevels']) {
            $filters['certificationLevels'] = array();
            foreach ($params['certificationLevels'] as $certLevel) {
                $filters['certificationLevels'][] = $certLevel;
            }
        }
        if ($params['graduationStatus']) {
            $filters['graduationStatus'] = array();
            foreach ($params['graduationStatus'] as $gradStatus) {
                $filters['graduationStatus'][] = $gradStatus;
            }
        }

        // save the filters to the session, if applicable
        if (isset($params['sessionNamespace'])) {
            $session = new \Zend_Session_Namespace($params['sessionNamespace']);
            $session->selectedCertifications = $filters['certificationLevels'];
            $session->selectedStatus = $filters['graduationStatus'];
            $session->selectedGradMonth = $filters['graduationMonth'];
            $session->selectedGradYear = $filters['graduationYear'];
            $session->selectedSection = $filters['section'];

            // set the flag so we know this namespace has been written to at least once
            $session->activated = TRUE;
        }

        $students = \Fisdap\EntityUtils::getRepository('User')->getAllStudentsByProgram(\Fisdap\Entity\User::getLoggedInUser()->getProgramId(), $filters);

        $list = array();
        foreach ($students as $student) {
            $label = $student['first_name'] . ' ' . $student['last_name'];
            if ($params['longLabel']) {
                $label .= ", " . $student['cert_description'] . ": " . $student['graduation_month'] . "/" . $student['graduation_year'];
            }
            $list[$student['id']] = $label;
        }

        $this->_helper->json($list);
    }

    public function getFilteredStudentListWithTestAttemptsAction()
    {
        $filters = $this->getAllParams();

        $repos = \Fisdap\EntityUtils::getRepository('User');
        $programId = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();

        $students = $repos->getAllStudentsByProgramWithProductData($programId, $filters);

        $moodleQuizId = $filters['moodleQuizId'];
        $moodleTestData = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $moodleQuizId);

        $returnData = array();
        $returnData['columns'] = array('Name', 'Attempts left');

        // get current Moodle Quiz attempt status for these students on this moodle quiz
        if ($moodleQuizId) {
            $attemptInfo = \Fisdap\MoodleUtils::getUsersQuizAttemptLimitInfo($students, $moodleTestData);

            // Also Get the moodle test's minimum default atteempts
            $defaultMaxAttempts = \Fisdap\MoodleUtils::getQuizDefaultMaxAttempts($moodleTestData);
        }

        foreach ($students as $student) {
            $atom = array();

            $atom['id'] = $student['id'];
            $atom['Name'] = $student['first_name'] . " " . $student['last_name'];

            // if student has access to the product, but no moodle account yet, the student probably still has the
            // default maximum number of attempts left (but just hasn't accessed moodle yet)
            if ($moodleQuizId) {
                // check if the user matches against at least one of the products associated with this quiz
                $hasProductAccess = FALSE;
                if (count($moodleTestData->products) > 0) {
                    foreach ($moodleTestData->products as $product) {
                        if ($student['configuration'] & $product->configuration) {
                            $hasProductAccess = TRUE;
                        }
                    }
                } else {
                    // there is no product associated wtih the moodletestdata entity, so we assume everyone has access
                    $hasProductAccess = TRUE;
                }

                if ($hasProductAccess) {
                    if (isset($attemptInfo[$student['user_id']]['remaining'])) {
                        // user has a Moodle account already
                        $atom['Attempts left'] = $attemptInfo[$student['user_id']]['remaining'];
                        if ($atom['Attempts left'] == 0) {
                            $atom['Attempts left'] = "<span style='color:red;'>" . $atom['Attempts left'] . "</span>";
                        }
                    } else {
                        // we can assume that the user has access to the default max number of attempts for htis moodle quiz
                        // they don't have a moodle account yet, but probably just because they haven't tried to log in
                        $atom['Attempts left'] = $defaultMaxAttempts;
                    }
                } else {
                    // student does not have product access.
                    $atom['Attempts left'] = "<span style='color:red;'>0</span>";
                }
            }

            $returnData['students'][] = $atom;
        }

        $this->_helper->json($returnData);
    }

    public function changeProgramAction()
    {
        $programId = $this->_getParam("id");
        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $programId);

        if ($programId && \Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            \Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData()->user_context->program = $programId;
            \Fisdap\Entity\User::getLoggedInUser()->save();

            // for the sake of legacy...
            $_SESSION['i_am_beta'] = $program->use_beta;

            //reset the selected student, too
            $this->globalSession->studentId = 0;

            // reset the portfolio student id
            $portfolioNamespace = new Zend_Session_Namespace("portfolioVars");
            $portfolioNamespace->studentId = null;

        }
        $this->_helper->json(true);
    }

    public function generateStudentListAction()
    {
        $options = array(0 => 'Select one...');
        $graduation = $this->_getParam('graduation');


        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
        $getProgramOptions = array(
            'graduationMonth' => $graduation['month'],
            'graduationYear' => $graduation['year'],
            'sectionYear' => $this->_getParam('sectionYear', 0),
            'section' => $this->_getParam('section', 0)
        );

        $students = \Fisdap\EntityUtils::getRepository('User')->getAllStudentsByProgram(\Fisdap\Entity\User::getLoggedInUser()->getProgramId(), $getProgramOptions);

        foreach ($students as $student) {
            $options[$student['id']] = $student['last_name'] . ", " . $student['first_name'];
        }

        $this->_helper->json($options);
    }


    public function getStudentScoresTableAction()
    {
        if (extension_loaded('newrelic')) {
            /** @noinspection PhpUndefinedFunctionInspection */
            newrelic_disable_autorum();
        }

        $this->_helper->layout->disableLayout();

        $post = $this->getRequest()->getPost();

        $selectedTestIds = $this->_getParam('test_id', array());

        $scheduledTestId = $this->_getParam('stid', null);

        $this->view->scheduledTestId = $scheduledTestId;

        $testResults = array();
        $sections = array();
        $sectionDisplayOptions = $overallDisplayOptions = array();

        foreach ($selectedTestIds as $selectedTestId) {
            $test = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $selectedTestId);

            $cleanSections = array();

            $studentIDs = $this->_getParam('studentIDs', array());

            $sectionInfo = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy')->getQuizSections($selectedTestId);
            $sections[$selectedTestId] = $sectionInfo['sections'];
            // set display info for any sections that have it, based on data from TestBPSections
            if (isset($sectionInfo['displayOptions'])) {
                foreach ($sectionInfo['displayOptions'] as $section => $options) {
                    if (isset($options['score_display']) && $options['score_display']) {
                        $sectionDisplayOptions[$selectedTestId][$section] = $options['score_display'];
                    }
                }
            }

            // Set some test results display options based on values from MoodleTestData
            if ($test->help_text != '' && $test->help_text != NULL) {
                $overallDisplayOptions[$selectedTestId]['customHelpBubble'] = $test->help_text;
            }
            $overallDisplayOptions[$selectedTestId]['showTotals'] = $test->show_totals;

            if (count($studentIDs) > 0) {
                $studentNames = array();

                foreach ($studentIDs as $studentId) {
                    $studentEnt = \Fisdap\EntityUtils::getEntity('StudentLegacy', $studentId);
                    // Need to account for some bad data where the user entity doesn't have username filled in or no user associated with the student record
                    if ($studentEnt->user != null && $studentEnt->user->username != null) {
                        $studentNames[$studentId] = $studentEnt->user->username;
                    } else {
                        $studentNames[$studentId] = $studentEnt->username;
                    }
                }

                if ($scheduledTestId) {
                    $scheduledTest = \Fisdap\EntityUtils::getEntity('ScheduledTestsLegacy', $scheduledTestId);

                    if ($scheduledTest->start_date->format('Ymd') > 0) {
                        $post['start_date'] = $scheduledTest->start_date->format('Y-m-d');
                    }
                    if ($scheduledTest->end_date->format('Ymd') > 0) {
                        $post['end_date'] = $scheduledTest->end_date->format('Y-m-d');
                    }
                }

                $results = $test->getScoreRecords($studentNames, $post);

                if ($results['results']) {
                    usort($results['results'], $this->buildSortFunction($this->_getParam('sort_field', 'last_name')));
                }
            } else {
                $results = array('results' => array(), 'groupData' => array());
            }


            $results['start_date'] = $post['start_date'];
            $results['end_date'] = $post['end_date'];
            $testResults[$selectedTestId] = $results;
        }

        $this->view->sections = $sections;
        $this->view->sectionDisplayOptions = $sectionDisplayOptions;
        $this->view->overallDisplayOptions = $overallDisplayOptions;
        $this->view->results = $testResults;
    }

    public function hasSeenVideoAction()
    {
        if (!$this->user->id) {
            $this->_helper->json(false);
            return;
        }

        $videoId = $this->_getParam('videoId');
        $jsonpCallback = $this->_getParam('jsonp_callback');
        $views = \Fisdap\EntityUtils::getRepository("VideoView")->findBy(array("user" => $this->user->id, "video_key" => $videoId));

        $this->_helper->layout->disableLayout();
        header('Content-type: application/javascript');
        echo $jsonpCallback . "(" . json_encode(array("viewCount" => count($views), "username" => $this->user->username)) . ");";
        return;
    }

    public function markVideoAsSeenAction()
    {
        $jsonpCallback = $this->getParam('jsonp_callback');

        if (Zend_Auth::getInstance()->hasIdentity()) {
            $videoId = $this->getParam('videoId');
            $videoView = new VideoView;
            $videoView->user = $this->currentUser->getWritableUser();
            $videoView->video_key = $videoId;
            $videoView->view_time = new \DateTime;
            $this->em->getRepository(VideoView::class)->store($videoView);
        }

        $this->_helper->layout->disableLayout();
        header('Content-type: application/javascript');
        echo $jsonpCallback . "(" . json_encode(true) . ");";
        return;

    }

    public function buildSortFunction($field)
    {
        return function ($a, $b) use ($field) {
            return strnatcmp($a[$field] . "_" . $a['attempt_number'], $b[$field] . "_" . $b['attempt_number']);
        };
    }

    public function populatePracticeDefinitionsAction()
    {
        $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $this->_getParam("programId"));
        $success = 1;

        //Check to make sure the program exists and does not already have definitions
        if (!$program->id) {
            $success = 0;
        } else if (count($program->practice_definitions)) {
            $success = 0;
        } else {
            $populator = new Util_PracticePopulator();
            try {
                $populator->populatePracticeDefinitions($program);
                \Fisdap\EntityUtils::getEntityManager()->flush();
            } catch (\Exception $e) {
                $success = 0;
            }
        }

        $this->_helper->json($success);
    }

    public function completeGuidedTourAction()
    {
        $user_context = $this->user->getCurrentUserContext();
        $guided_tour = \Fisdap\EntityUtils::getEntity("GuidedTour", $this->_getParam("tour_id"));

        // create a history record if there isn't already one
        if (!$guided_tour->userHasCompleted($user_context->id)) {
            $history_record = new \Fisdap\Entity\GuidedTourHistory;
            $history_record->guided_tour = $guided_tour;
            $history_record->user_context = $user_context;
            $history_record->save();
        }

        unset($this->globalSession->guided_tour_progress[$guided_tour->id]);

        $this->_helper->json(true);
    }

    public function updateTourProgressAction()
    {
        $guided_tour_id = $this->_getParam("tour_id");
        $step_id = $this->_getParam("step_id");

        $this->globalSession->guided_tour_progress[$guided_tour_id] = $step_id;
        $this->_helper->json(true);
    }

    /**
     * Send an email to the testing team, triggered from the TestItemAnalysis Report
     */
    public function sendTestItemAnalysisFlagEmailAction()
    {
        // Make sure we have a valid user: don't send emails unless someone is logged in
        if (!$this->user instanceof \Fisdap\Entity\User) {
            $this->_helper->json(array('error' => true, 'message' => 'You must be logged in to send a Test Item Analysis Flag email'));
        } else {
            // Get the recipient of the TIA Flag email from the application config
            $allConfig = Zend_Registry::get('config');
            $recipient = $allConfig->fisdap->emailrecipients->sendTestItemAnalysisFlagEmail;

            $testId = $this->_getParam("test_id");
            $test = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $testId);

            // If we have proper information, send the email
            if ($test instanceof \Fisdap\Entity\MoodleTestDataLegacy && $this->_getParam("item_id")) {

                // Compose and send the message
                $name = $this->user->getName();
                $mail = new \Fisdap_TemplateMailer();
                $mail->setViewParams(array(
                    'date' => date("Y-m-d - h:m"),
                    'name' => $name,
                    'itemId' => $this->_getParam("item_id"),
                    'test' => $test,
                    'table' => $this->_getParam("table"),
                    'message' => $this->_getParam("message"),
                ))
                    ->addTo($this->user->email)
                    ->addTo($recipient)
                    ->setSubject('Test Item Flagged by ' . $name)
                    ->sendHtmlTemplate('test-item-analysis-flag.phtml');

                $this->_helper->json(true);
            } else {
                $this->_helper->json(array('error' => true, 'message' => 'Missing required fields'));
            }
        }
    }

    /**
     * Mark a notification as viewed
     */
    public function markNotificationViewedAction(CurrentUser $currentUser)
    {
        /** @var Doctrine\ORM\EntityManager $em */
        $em = Zend_Registry::get("doctrine")->getEntityManager();
        $notificationUserViewRepository = $em->getRepository(NotificationUserView::class);

        //get user_view_id parameter from js
        $userViewId = $this->_request->getParam('userViewId');

        //get entity NotificationUserView
        $notificationUserView = $this->view->notifications = EntityUtils::getEntity('NotificationUserView', $userViewId);
        if ($currentUser->context()->id == $notificationUserView->user_context->id) {
            //modify viewed row for corresponding $userViewId from false to true and save changes
            $notificationUserView->viewed = true;
            $notificationUserViewRepository->store($notificationUserView);
        }

        //return success as true to ajax
        $this->_helper->json(true);
    }

    public function updateGoalsetDefinitionsSummaryAction()
    {
        $response = array("success" => false);
        $selectedGoalsetId = $this->getParam('selectedGoalset');
        $selectedGoalset = \Fisdap\EntityUtils::getEntity('GoalSet', $selectedGoalsetId);

        if ($selectedGoalset) {
            $response['success'] = true;
            $response['html'] = $this->view->partial('goalset-definitions.phtml', "reports", array("goalset" => $selectedGoalset));
        }

        $this->_helper->json($response);
    }

    public function updateAgeDefinitionsSummaryAction()
    {
        $response = array("success" => false);
        $selectedGoalsetId = $this->getParam('selectedGoalset');
        $selectedGoalset = \Fisdap\EntityUtils::getEntity('GoalSet', $selectedGoalsetId);

        if ($selectedGoalset) {
            $response['success'] = true;
            $response['html'] = $this->view->partial('age-definitions.phtml', "reports", array("goalset" => $selectedGoalset));
        }

        $this->_helper->json($response);
    }

    public function updateCurrentContextAction()
    {
        $response = array("success" => false);

        $contextId = $this->getParam('newContext');

        // sanity check on the context
        if (!is_numeric($contextId) || $contextId < 1) {
            $response['message'] = "Invalid context.";
            $this->_helper->json($response);
            return;
        }

        $this->currentUser->setContextFromId(intval($contextId));
        $response['success'] = true;

        $this->_helper->json($response);
    }

    /**
     * Given an email, get the Fisdap Gravatar markup
     */
    public function getGravatarAction()
    {
        $email = $this->getParam('email');
        $this->_helper->json($this->view->fisdapGravatar($email));
    }

}
