<?php

use AscendLearning\Lti\Entities\ToolProvider;
use Fisdap\Api\Products\Queries\Specifications\MatchingUserContext;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Entity\Product;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Service\ProductService;
use Happyr\DoctrineSpecification\Spec;
use Illuminate\Support\Collection;

/**
 * Class LearningCenter_IndexController
 */
class LearningCenter_IndexController extends Fisdap_Controller_Private
{
    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Collection
     */
    private $toolProvidersByContext;


    /**
     * LearningCenter_IndexController constructor.
     *
     * @param ProductRepository $productRepository
     */
    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }


    public function indexAction()
    {
        $this->view->pageTitle = "Learning Center";
        
        $launchableProducts = $this->getLaunchableProductsForCurrentUserContext();

        $this->setToolProvidersByContext($launchableProducts);
        
        $serialNumber = $this->currentUser->context()->getPrimarySerialNumber();

        $this->view->roleName = $roleName = $this->currentUser->context()->getRole()->getName();
        switch ($roleName) {
            case "student":
                $this->setupStudentIndexView($launchableProducts, $serialNumber);
                $this->render('index-student-lti');
                break;
            case "instructor":
                if ($serialNumber && $serialNumber->hasProductAccess('preceptor_training')) {
                    $this->view->hasPreceptorTraining = true;
                }
                $this->setupInstructorIndexView();
                $this->render('index-instructor');
                break;
            default:
                break;
        }
        if ($serialNumber) {
            $this->addMedrillsToView($serialNumber);
        }
    }


    /**
     * @return Collection
     */
    private function getLaunchableProductsForCurrentUserContext()
    {
        return Collection::make(
            $this->productRepository->match(
                Spec::andX(
                    new MatchingUserContext($this->currentUser->context()->getRole()->getFullEntityClassName()),
                    Spec::eq('id', $this->currentUser->context()->getId(), 'userContext'),
                    Spec::isNotNull('moodle_course_id')
                )
            )
        );
    }


    /**
     * @param Collection $launchableProducts
     */
    private function setToolProvidersByContext(Collection $launchableProducts)
    {
        $this->toolProvidersByContext = Collection::make(
            $this->em->getRepository(ToolProvider::class)->findByContextId(
                $launchableProducts->map(function (Product $product) {
                    return $product->getMoodleCourseId();
                })->toArray()
            )
        )->keyBy(
            function (ToolProvider $toolProvider) {
                return $toolProvider->getResourceLinkTitle() . '-' . $toolProvider->getContextId();
            }
        );
    }


    /**
     * @param Collection         $launchableProducts
     * @param SerialNumberLegacy $serialNumber
     */
    private function setupStudentIndexView(Collection $launchableProducts, SerialNumberLegacy $serialNumber)
    {
        // Secure Testing
        $this->view->secureTestingIcon = ProductService::COMPREHENSIVE_EXAM_ICON;

        $this->view->secureTestingToolProviders = $launchableProducts->filter(
            function (Product $product) {
                return $product->getMoodleContext() === 'secure_testing';
            }
        )->map(
            function (Product $product) {
                return $this->getToolProviderForProduct($product);
            }
        );

        ### BEGIN OLD TESTING LINK
        /*
        if ($serialNumber->hasProductAccess("secure_testing")) {
            $this->view->hasSecureTesting = TRUE;
        }
        $this->view->secureTestingLink = \Fisdap\MoodleUtils::getUrl("secure_testing");
        */
        ### END OLD TESTING LINK

        // Study Tools
        $this->view->studyToolsIcon = ProductService::STUDY_TOOLS_ICON;

        $this->view->studyToolsLink = "http://www." . Util_HandyServerUtils::get_server()
            . ".net/what_we_make/study_tools";
        
        $this->view->studyToolsToolProviders = $launchableProducts->filter(
            function (Product $product) {
                return $product->getMoodleContext() === 'study_tools';
            }
        )->map(
            function (Product $product) {
                return $this->getToolProviderForProduct($product);
            }
        );

        ### BEGIN OLD STUDY TOOLS LINKS
        /*
        if ($serialNumber->hasStudyTools()) {
            $this->view->hasStudyTools = TRUE;
        }

        if ($this->view->hasStudyTools) {
            $this->view->studyToolsLink = \Fisdap\MoodleUtils::getUrl("study_tools");
        } else {
            $this->view->studyToolsLink = "https://www.".Util_HandyServerUtils::get_server().".net/what_we_make/study_tools";
        }
        */
        ### END OLD STUDY TOOLS LINKS

        
        $this->view->studentScoresLink = Util_GetLegacyTopNavLinks::getLink(
            Util_GetLegacyTopNavLinks::STUDENT_SCORES
        );
        
        $this->view->openAirWaysLink = "http://www." . Util_HandyServerUtils::get_server()
            . ".net/whats_new/open_airways";
        
        $this->view->howToSucceedLink = "https://testing-instructions.s3.amazonaws.com/how_to_succeed.pdf";
        
        $this->view->research101Link = \Fisdap\MoodleUtils::getUrl("research101");

        // Pilot Testing
        $this->view->hasPilotTesting = true;
        $this->view->pilotTestingLink = \Fisdap\MoodleUtils::getUrl('pilot_testing');

        // Transition Course
        if ($serialNumber->hasTransitionCourse()) {
            $this->view->hasTransitionCourse = true;
            $this->view->transitionCourseLink = \Fisdap\MoodleUtils::getUrl('transition_course');
        }
    }


    /**
     * @param Product $product
     *
     * @return ToolProvider
     * @throws Exception
     */
    private function getToolProviderForProduct(Product $product)
    {
        $toolProvider = $this->toolProvidersByContext->get($product->getMoodleContext() . '-' . $product->getMoodleCourseId());

        if ($toolProvider === null) {
            throw new \Exception("Missing LTI Tool Provider configuration for {$product->getFullName()}");
        }

        return $toolProvider;
    }

    private function setupInstructorIndexView()
    {
        if ($this->view->hasPreceptorTraining) {
            $this->view->preceptorTrainingLink = \Fisdap\MoodleUtils::getUrl("preceptor_training");
        }
        $this->view->scheduleASecureExam = Util_GetLegacyTopNavLinks::getLink(
            Util_GetLegacyTopNavLinks::SCHEDULE_A_SECURE_EXAM,
            $this->view->serverUrl()
        );
        $this->view->reviewStudentScoreAndLearningPerscription = "/learning-center/index/retrieve";
        $this->view->trainingVideo = Util_GetLegacyTopNavLinks::getLink(
            Util_GetLegacyTopNavLinks::TRAINING_VIDEO,
            $this->view->serverUrl()
        );
        $this->view->recordNREMTResults = "/learning-center/index/submit-scores";
        $this->view->testingFAQs = "/learning-center/index/faq";
        $this->view->visitFisdapsTestBank = Util_GetLegacyTopNavLinks::getLink(
            Util_GetLegacyTopNavLinks::VISIT_FISDAPS_TEST_BANK,
            $this->view->serverUrl()
        );
        $this->view->viewRewardsPointsBalance = Util_GetLegacyTopNavLinks::getLink(
            Util_GetLegacyTopNavLinks::VIEW_REWARDS_POINTS_BALANCE,
            $this->view->serverUrl()
        );
        $this->view->submitATestOnLine = Util_GetLegacyTopNavLinks::getLink(
            Util_GetLegacyTopNavLinks::SUBMIT_A_TEST_ITEM_ONLINE,
            $this->view->serverUrl()
        );
        $this->view->research101Link = \Fisdap\MoodleUtils::getUrl("research101");
        $this->view->testItemAnalysisLink = "/reports/index/display/report/TestItemAnalysis";
        $this->view->emsReferenceLink = "https://www.emsreference.com/";
    }


    /**
     * @param SerialNumberLegacy $serialNumber
     * @deprecated
     */
    private function addMedrillsToView(SerialNumberLegacy $serialNumber)
    {
        if ($serialNumber && $serialNumber->hasMedrills()) {
            $this->view->hasMedrills = true;

            if ($serialNumber->hasProductAccess(41) && $serialNumber->hasProductAccess(42)) {
                $this->view->medrillsHeader = "Medrills";
                $this->view->showAllMedrills = true;
            } else {
                $this->view->showAllMedrills = false;

                if ($serialNumber->hasProductAccess(41)) {
                    $this->view->medrillsHeader
                        = "<a href='/learning-center/medrills/index/product/41'>EMT Medrills</a>";
                } else {
                    if ($serialNumber->hasProductAccess(42)) {
                        $this->view->medrillsHeader
                            = "<a href='/learning-center/medrills/index/product/42'>Paramedic Medrills</a>";
                    }
                }
            }
        }
    }
    

    public function faqAction()
    {
        $this->view->pageTitle = "Frequently Asked Questions";
    }

    public function testScoresAction()
    {
        $this->view->headScript()->appendFile("/js/jquery.cluetip.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");
        $this->view->headLink()->appendStylesheet("/css/my-fisdap/widget-styles/fisdap-exams.css");

        $hasTesting = $this->userContext->getPrimarySerialNumber()->hasProductAccess("all_testing");
        $this->view->user = $this->user;
        $this->view->roleName = $this->userContext->getRole()->getName();
        $this->view->pageTitle = "Test Scores";

        if ($this->userContext->isInstructor()) {
            $this->redirect("/learning-center/index/retrieve");
        } else {
            if (!$hasTesting) {
                $this->redirect("/learning-center");
            }
        }
    }

    public function scheduleAction()
    {
        $this->permissionsCheck();

        $this->view->pageTitle = "Test Schedule";

        $this->view->filters = $this->getAllParams();

        $stRepos = \Fisdap\EntityUtils::getRepository('ScheduledTestsLegacy');
        // if there are more than 50 scheduled tests program-wide, limit the date range
        if (count($stRepos->getFilteredTests(array())) > 50) {
            $start = new DateTime('-1 month');
            $end = new DateTime('+3 months');

            if (!$this->view->filters['start_date']) {
                $this->view->filters['start_date'] = $start->format("m/d/Y");
            }

            if (!$this->view->filters['end_date']) {
                $this->view->filters['end_date'] = $end->format("m/d/Y");
            }
        }

        $this->view->filtersForm = new LearningCenter_Form_TestFilters($this->view->filters);

        // get program info
        $role = $this->userContext->getRoleData();
        $this->view->programName = $this->userContext->getProgram()->getName();
    }

    public function filterTestsAction()
    {
        $params = $this->getAllParams();
        $this->_helper->json($this->view->scheduledTestList($params));
    }

    public function retrieveAction()
    {
        $this->permissionsCheck();

        $this->view->pageTitle = "Retrieve Scores";

        // this needs to be included here even though it is duplicative of HelpBubble view helper
        // but HelpBubble is being used in DHTML context, so too late to load headscripts
        // boo
        $this->view->headScript()->appendFile("/js/jquery.cluetip.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");

        $this->view->headLink()->appendStylesheet("/css/library/Fisdap/View/Helper/testScoresResults.css");
        $this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");
        $this->view->headScript()->appendFile("/js/jquery-select_accordion.js");

        $request = $this->getRequest();

        $moodleRepos = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy');
        $options = $moodleRepos->getMoodleTestList(
            array('extraGroups' => array('pilot_tests', 'retired')),
            'productArray'
        );

        //This is only temporary, I swear. Please don't hate me.
        if ($this->userContext->getProgram()->getId() != 2077) {
            if ($options['Australian Comprehensive Exams']) {
                unset($options['Australian Comprehensive Exams']);
            }
        }

        $test = new Zend_Form_Element_Select("test_id");
        $test->setLabel('')
            ->setMultiOptions($options)
            ->setAttrib("multiple", "multiple");

        //See if there's a student being passed in
        $student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $this->_getParam("studentId"));
        if ($student->id) {

            // Make sure this user can view this student's data
            if (!$student->dataCanBeViewedBy()) {
                $this->displayError("You do not have permission to view this student's test scores.");

                return;
            }

            $this->view->students = array($student->id);
            $moodle_quiz_ids = array();
            $products = $student->getSerialNumber()->getProducts();
            foreach ($products as $product) {
                $moodle_quiz_ids = array_merge($moodle_quiz_ids, $product->getMoodleQuizIds());
            }

            $test->setValue($moodle_quiz_ids);
        }

        $this->view->tests = $test;

        // Now for some fun stuff.  If a scheduled test ID comes in to this page, we need to just
        // load up the results for that one test.  Renders in the
        if ($stid = $this->_getParam('stid', false)) {
            $scheduledTest = \Fisdap\EntityUtils::getEntity('ScheduledTestsLegacy', $stid);

            // make sure this user is even allowed to see this exam
            if ($scheduledTest->program_id != $this->userContext->getProgram()->getId()) {
                $this->displayError("You do not have permission to view these test scores.");

                return;
            }

            // Building this up to mimic what is normally being sent in the ajax call.
            $postData = array();

            foreach ($scheduledTest->students as $student) {
                $postData['studentIDs'][] = $student->id;
            }

            $postData['test_id'] = array($scheduledTest->test->moodle_quiz_id);
            $postData['stid'] = $stid;

            $this->view->jsonPost = Zend_Json::encode($postData);

            // This viewscript takes the postdata, sends off an ajax call, and immediately shows a
            // resultset when it comes back, avoiding the whole form element.
            $this->render('retrieve-single');
        }
    }

    public function submitScoresAction()
    {
        $this->permissionsCheck();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
        }


        $this->view->pageTitle = "Record NREMT Results";

        $this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");
        $this->view->headScript()->appendFile("/js/jquery.fancyFilters.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.fancyFilters.css");
        $this->view->headScript()->appendFile("/js/jquery.chosen.relative.js");

        // Get students for the program
        $programId = $this->userContext->getProgram()->getId();
        $users = $this->getStudents($programId, $post);

        // get already reported scores to populate the form
        $testScoreRepo = \Fisdap\EntityUtils::getRepository('TestScoreLegacy');
        $programScores = $testScoreRepo->getNremtScoresByProgram($programId);

        $this->view->scoresForm = new LearningCenter_Form_SubmitScores($users, $programScores);

        //process submitted form
        if ($request->isPost() && isset($this->view->scoresForm)) {
            if ($this->view->scoresForm->process($request->getPost()) == true) {
                $this->flashMessenger->addMessage("Your students' NREMT results have been saved.");
                // reload the page
                $this->_redirect("/learning-center/index/submit-scores");
            }
        }
    }

    public function getSubmitScoresFormAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $post = $request->getPost();
        }

        $programId = $this->userContext->getProgram()->getId();
        $users = $this->getStudents($programId, $post);

        // get already reported scores to populate the form
        $testScoreRepo = \Fisdap\EntityUtils::getRepository('TestScoreLegacy');
        $programScores = $testScoreRepo->getNremtScoresByProgram($programId);

        $form = new LearningCenter_Form_SubmitScores($users, $programScores, $post);
        $this->_helper->json($form->__toString());
    }

    private function getStudents($program_id, $post)
    {
        $filters = array();

        if (isset($post['graduationMonth'])) {
            $filters['graduationMonth'] = $post['graduationMonth'];
        }
        if (isset($post['graduationYear'])) {
            $filters['graduationYear'] = $post['graduationYear'];
        }
        if (isset($post['certificationLevels'])) {
            $filters['certificationLevels'] = $post['certificationLevels'];
        }
        if (isset($post['gradStatus'])) {
            $filters['graduationStatus'] = $post['gradStatus'];
        }
        if (isset($post['section'])) {
            $filters['section'] = $post['section'];
        }

        $students = \Fisdap\EntityUtils::getRepository('User')->getAllStudentsByProgram($program_id, $filters);

        return $students;
    }

    private function permissionsCheck($studentsAllowed = false)
    {
        //Don't allow students onto this page unless explicitly stated
        if (!$this->userContext->isInstructor() && !$studentsAllowed) {
            $this->displayError("Students are not allowed to view this page.");

            return;
        }
        //Check instructor permissions
        if ($this->userContext->isInstructor() && !$this->userContext->getRoleData()->hasPermission("Admin Exams")) {
            $this->displayError(
                "You do not have permission to schedule exams or retrieve scores. Please contact "
                . $this->userContext->getProgram()->getProgramContactName() . " for more information."
            );

            return;
        }
    }

    public function editScoreAjaxAction()
    {
        $score_record_id = $this->getParam('score_record_id');
        $nremt_score = \Fisdap\EntityUtils::getEntity('TestScoreLegacy', $score_record_id);
        $score = $nremt_score->pass_or_fail;
        $nremt_score->delete();
        $this->_helper->json($score);
    }
}
