<?php

use Fisdap\Data\Requirement\RequirementRepository;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\Requirement;
use Fisdap\Entity\User;
use Illuminate\Queue\Capsule\Manager as Queue;


class Scheduler_ComplianceController extends Fisdap_Controller_Private
{
    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var Zend_Cache_Core The Zend Cache backend to use for retrieving information about queued jobs
     */
    private $cache;

    /**
     * @var RequirementRepository
     */
    private $requirementRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var Zend_Session_Namespace
     */
    private $session;


    /**
     * @param Queue                 $queue
     * @param Zend_Cache_Core       $cache
     * @param RequirementRepository $requirementRepository
     * @param UserRepository        $userRepository
     */
    public function __construct(
        Queue $queue,
        Zend_Cache_Core $cache,
        RequirementRepository $requirementRepository,
        UserRepository $userRepository
    ) {
        $this->queue = $queue;
        $this->cache = $cache;
        $this->requirementRepository = $requirementRepository;
        $this->userRepository = $userRepository;
    }


    public function init()
    {
        parent::init();

        // redirect to login if the user is not logged in yet
        if ( ! $this->user) {
            return;
        }

        $this->session = new \Zend_Session_Namespace("Scheduler");

        if ($this->user->getCurrentProgram()->scheduler_beta == 0) {
            $this->redirect("/scheduler/index/join-beta");
        }

        $this->view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/navbar-menu.css");
        $this->view->headScript()->appendFile("/js/library/Scheduler/View/Helper/navbar-menu.js");
    }


    public function indexAction()
    {
        if(!$this->hasPermission()){
            $this->displayError("You do not have permission to view this page.");
            return;
        }
        $this->view->pageTitle = "Scheduler Compliance Tracking";
        $this->view->pickSitesModal = new Scheduler_Form_PickSitesModal();
    }


    public function settingsAction()
    {
        // Check permissions
        if ( ! $this->user->isInstructor()) {
            $this->displayError("You do not have permission to view this page.");
            return;
        } else if ( ! ($this->user->hasPermission("Edit Program Settings") && $this->hasPermission())) {
            $this->displayError("You do not have permission to view this page.");
            return;
        }

        $this->view->permissions = $this->getPermissionsForViews($this->user, "settings");

        $this->view->headScript()->appendFile("/js/jquery.cluetip.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");

        $form = new Scheduler_Form_ComplianceSettings($this->view->permissions);

        $this->view->pageTitle = "Compliance Settings";
        $this->view->form = $form;
    }


    private function hasPermission()
    {
        // do a few permissions checks. Is this an instructor who can edit compliance status at a program that is using scheduler beta?
        $user = User::getLoggedInUser();
        $has_permission = false;
        if($user->getCurrentRoleName() == "instructor"){
            $instructor = $user->getCurrentRoleData();
            if($instructor->hasPermission("Edit Compliance Status")){
                $has_permission = true;
            }
        }

        return $has_permission;
    }


    public function newRequirementAction()
    {
        $this->view->pageTitle = "Add new requirement";
        $this->view->pageTitleLinkURL = "/scheduler/compliance/manage";
        $this->view->pageTitleLinkText = "<< Back to Manage Requirements";

        $user = User::getLoggedInUser();
        $has_permission = false;
        if ($user->getCurrentRoleName() == "instructor") {
            if ($user->getCurrentRoleData()->hasPermission("Edit Program Settings") && $user->getCurrentRoleData()->hasPermission("Edit Compliance Status")) {
                $has_permission = true;
            }
        }

        if ( ! $has_permission) {
            $this->displayError("You do not have permission to add new requirements.");
            return;
        }

        $this->view->requirementForm = new Scheduler_Form_Requirement();
        $this->view->pickSitesModal = new Scheduler_Form_PickSitesModal();
        $this->view->requirementAssignModal = new Scheduler_Form_RequirementAssignModal($this->requirementRepository);
        $this->view->edit = false;

        $this->appendRequirementExternalFiles($this->view);

        $this->view->tour = new Fisdap_View_Helper_GuidedTourHelper();
        $this->view->tour_id = 10;
    }


    private function appendRequirementExternalFiles($view)
    {
        $view->headLink()->appendStylesheet("/css/jquery.fancyFilters.css");
        $view->headScript()->appendFile("/js/jquery.fancyFilters.js");
        $view->headLink()->appendStylesheet("/css/jquery.sliderCheckbox.css");
        $view->headScript()->appendFile("/js/jquery.sliderCheckbox.js");
        $view->headScript()->appendFile("/js/jquery.chosen.js");
        $view->headScript()->appendFile("/js/library/Scheduler/View/Helper/requirement-multistudent-picklist.js");
        $view->headLink()->appendStylesheet("/css/library/Scheduler/View/Helper/multistudent-picklist.css");
        $view->headScript()->appendFile("/js/jquery.busyRobot.js");
        $view->headLink()->appendStylesheet("/css/jquery.busyRobot.css");
    }


    public function editRequirementAction()
    {
        $this->view->pageTitle = "Edit requirement";
        $this->view->pageTitleLinkURL = "/scheduler/compliance/manage";
        $this->view->pageTitleLinkText = "<< Back to Manage Requirements";

        $user = User::getLoggedInUser();
        $has_permission = false;
        if ($user->getCurrentRoleName() == "instructor") {
            if ($user->getCurrentRoleData()->hasPermission("Edit Program Settings") && $this->hasPermission()) {
                $has_permission = true;
            }
        }

        if ( ! $has_permission) {
            $this->displayError("You do not have permission to edit requirements.");
            return;
        }

        $requirement_id = $this->_getParam('id');
        $req = $this->requirementRepository->getOneById($requirement_id);

        if ( ! $req) {
            $this->displayError("We couldn't find that requirement.");
            return;
        }

        // now make sure there is at least 1 association between the current program and the requirement
        if (count($req->getAllAssociationsByProgram($user->getProgramId())) == 0) {
            $this->displayError("Your program does not have this requirement set up yet. To add it, <a href='/scheduler/compliance/new-requirement'>click here</a>.");
            return;
        }

        $this->view->edit = true;
        $this->view->requirementForm = new Scheduler_Form_EditRequirement($req);
        $this->view->pickSitesModal = new Scheduler_Form_PickSitesModal();
        $this->view->requirementAssignModal = new Scheduler_Form_RequirementAssignModal($this->requirementRepository);
        $this->appendRequirementExternalFiles($this->view);
        $this->render('new-requirement');

        $request = $this->getRequest();

        if ($request->isPost()) {
            $this->view->requirementForm->process($request->getPost());
        }
    }


    public function saveRequirementAction()
    {
        $request = $this->getRequest();
        $data = $request->getPost();
        $user = User::getLoggedInUser();
        $program = $user->getCurrentProgram();

        // if this is a new requirement for this program, we'll need an id
        if (!$data['edit']) {
            // if this is a custom req, we need to actually create it to get an id
            if ($data['custom_requirement']) {
                $req = new Requirement();
                $req->name = $data["custom_title"];
                $req->save();
            } else {
                // if this is a global req, get the id from the form
                $req = $this->requirementRepository->getOneById($data['default_list']);
            }

            $data['requirement_id'] = $req->id;
            // associate the req with this program, so the manage page knows to look for it
            // (this association may be subsequently overwritten based on the form values
            $req->createProgramAssociation($program);
        }

        // store a cache placeholder so app knows a job has been started
        $this_userContextId = $user->getCurrentUserContext()->id;
        $cacheId = "editing_req_" . $data['requirement_id'] . "_for_user_" . $this_userContextId;
        $data['cacheId'] = $cacheId;
        $data['userContextId'] = $this_userContextId;
        $data['program_id'] = $program->id;

        $this->cache->save(array('jobs' => 1), $cacheId, array(), 0); //indefinite lifetime
        
        // we need to queue this job
        $this->queue->push('SaveRequirement', $data);

        $this->_helper->json(true);
    }


    public function computeComplianceAction()
    {
        $params = $this->getAllParams();
        $userContextIds = $params['userContextIds'];
        $this->requirementRepository->updateCompliance($userContextIds);
        $this->_helper->json(true);
    }


    /**
     * Check an array of requirement IDs and return any of those
     * for which background editing is still being done
     *
     * @throws Zend_Exception
     */
    public function checkQueuedEditsAction() {
        $reqIds = $this->_getParam("req_ids");
        $this->_helper->json($this->checkQueuedEdits($reqIds));
    }


    public function getFilteredMspStudentsAction()
    {
        $params = $this->getAllParams();

        if ($params['account_type'] == "students") {

            $filters = [];
            if ($params['graduationYear']){$filters['graduationYear'] = $params['graduationYear'];}
            if ($params['graduationMonth']){$filters['graduationMonth'] = $params['graduationMonth'];}
            if ($params['section']){$filters['section'] = $params['section'];}
            if ($params['certificationLevels']) {
                $filters['certificationLevels'] = [];
                foreach($params['certificationLevels'] as $certLevel){
                    $filters['certificationLevels'][] = $certLevel;
                }
            }

            if ($params['graduationStatus']) {
                $filters['graduationStatus'] = [];
                foreach($params['graduationStatus'] as $gradStatus){
                    $filters['graduationStatus'][] = $gradStatus;
                }
            }

            $session = new Zend_Session_Namespace("accountEditController");
            $session->prevSearch = $filters;

            $students = $this->userRepository->getAllStudentsByProgram(User::getLoggedInUser()->getProgramId(), $filters);

            $assignable = [];
            $hidden_students = [];

            foreach($students as $student){
                // for now, these students must have Scheduler
                $config = $student['configuration'];
                $show = ((boolean)($config & 8192) || (boolean)($config & 2));
                if($show){$assignable[$student['userContextId']] = $student['first_name'] . " " . $student['last_name'];}
                else {$hidden_students[$student['userContextId']] = $student['first_name'] . " " . $student['last_name'];}
            }

            $people = array("assignable" => $assignable, "hidden_students" => $hidden_students);
        } else {

            $instructors = $this->userRepository->getAllInstructorsByProgram(User::getLoggedInUser()->getProgramId());
            $people = [];
            $people['assignable'] = [];
            $people['hidden_students'] = [];
            foreach($instructors as $instructor){
                $people['assignable'][$instructor['userContextId']] = $instructor['first_name'] . " " . $instructor['last_name'];
            }
        }

        $this->_helper->json($people);
    }


    public function getReqSumAction()
    {
        $req_id = $this->_getParam('req_id');
        $req = $this->requirementRepository->getOneById($req_id);
        $category = $req->category->name;
        $expires = ($req->expires) ? "does" : "does not";
        $this->_helper->json(array("expiration" => $expires, "category" => $category));
    }


    public function generateRequirementAssignModalAction()
    {
        $requirement_ids = $this->_getParam('requirement_ids');
        $picklistOptions = array(
            'loadJSCSS' => FALSE,
            'helpText' => 'multistudent-picklist-help-compliance.phtml',
            'showTotal' => FALSE,
            'includeSubmit' => FALSE,
        );
        $msp = $this->view->multistudentPicklist($this->user, null, $picklistOptions, $this->view);
        $form = new Scheduler_Form_RequirementAssignModal($this->requirementRepository, $msp, $requirement_ids);
        $this->_helper->json($form->__toString());
    }


    public function saveRequirementAssignModalAction()
    {
        $request = $this->getRequest();
        $data = $request->getPost();
        $user = User::getLoggedInUser();
        $program = $user->getCurrentProgram();

        // create cache id for job
        $this_userContextId = $user->getCurrentUserContext()->id;
        $cacheId = "editing_req_" . $data['requirement_ids'][0] . "_for_user_" . $this_userContextId;
        $data['cacheId'] = $cacheId;
        $data['assigner_userContextId'] = $this_userContextId;
        $data['program_id'] = $program->id;

        $this->cache->save(array('jobs' => 1), $cacheId, array(), 0);

        // queue up the job
        $this->queue->push('SaveRequirementAssignModal', $data);

        $queuedReqs = $this->checkQueuedEdits($data['requirement_ids']);
        $this->view->queuedReqs = $queuedReqs['reqsQueued'];

        $this->_helper->json(true);
    }


    public function editStatusAction()
    {
        $user = User::getLoggedInUser();

        // do a few permissions checks. Is this an instructor who can edit compliance status at a program that is using scheduler beta?
        if ( ! $this->hasPermission()) {
            $this->displayError("You do not have permission to view this page.");
            return;
        }

        // now make sure this user's program has at least 1 requirement
        if (count($this->requirementRepository->getFormOptions($user->getProgramId())) == 0) {
            $this->displayError("You do not have any requirements set up yet.");
            return;
        }

        $this->view->permissions = $this->getPermissionsForViews($this->user, "edit");

        $this->view->headLink()->appendStylesheet("/css/jquery.sliderCheckbox.css");
        $this->view->headLink()->appendStylesheet("/css/library/Scheduler/Form/edit-compliance-status.css");
        $this->view->headScript()->appendFile("/js/jquery.sliderCheckbox.js");
        $this->view->headScript()->appendFile("/js/library/Scheduler/Form/edit-compliance-status.js");
        $this->view->headScript()->appendFile("/js/jquery.busyRobot.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.busyRobot.css");
        $this->view->headLink()->appendStylesheet("/css/jquery.flippy.css");
        $this->view->headScript()->appendFile("/js/jquery.flippy.js");

        // todo - the following comment and code doesn't make sense...this immediately gets overwritten by the session? ~bgetsug
        // throw in any values in the session?
        $selectFormValues = [
            "selection-by" => "by-requirements",
            "userContextIds" => [],
            "requirementIds" => [],
            "people_sub_filters" => [],
            "all_students" => true
        ];

        $selectFormValues = $this->session->select_form_values;
        $this->select_form_values = $selectFormValues;

        $this->view->pageTitle = "Edit Compliance Status";
        $this->view->selectionForm = new Scheduler_Form_EditComplianceStatusSelection($selectFormValues);

        if ($selectFormValues) {
            $this->view->editComplianceForm = new Scheduler_Form_EditComplianceStatus(
                $this->requirementRepository, $selectFormValues
            );
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            $this->view->editComplianceForm->process($request->getPost());
            $this->flashMessenger->addMessage("Compliance successfully updated.");

            // now reset the forms since they've had a successful save
            $this->session->select_form_values = null;

            $this->redirect("/scheduler/compliance/edit-status");
        }
    }


    public function clearStatusSessionAction()
    {
        $this->session->select_form_values = null;
        $this->redirect("/scheduler/compliance/edit-status");
    }


    /**
     * @param $student_filters
     * @param $show_instructors
     *
     * @return array
     */
    public function getRoleDataIds($student_filters, $show_instructors)
    {
        $program_id = User::getLoggedInUser()->getCurrentProgram()->id;
        $students = $this->userRepository->getAllStudentsByProgram($program_id, $student_filters, true);

        $instructors = ($show_instructors) ? $this->userRepository->getAllInstructorsByProgram($program_id, null, true) : [];

        $role_data_ids = [];

        foreach($students as $data){$role_data_ids[] = $data['id'];}
        foreach($instructors as $data){$role_data_ids[] = $data['id'];}

        return $role_data_ids;
    }


    /**
     * Get the post values from the Scheduler_Form_EditComplianceStatusSelection form,
     * then format the values into an array that can be used by the Requirement repo to retrieve attachments
     *
     * @return array an array that can be used by the Requirement repo to retrieve attachments
     */
    private function getSelectFormValues()
    {
        $program_id = User::getLoggedInUser()->getCurrentProgram()->id;
        $selection_by = $this->_getParam('selection_by');
        $all_students = true;

        if ($selection_by == "by-requirements") {

            $default_filters = array('graduationStatus' => array(1), 'show_instructors' => 1);

            // format sub-filters for the students
            $filters = [];

            if ($this->_getParam('graduationMonth')) {
                $filters['graduationMonth'] = $this->_getParam('graduationMonth');
            }
            if ($this->_getParam('graduationYear')) {
                $filters['graduationYear'] = $this->_getParam('graduationYear');
            }
            if ($this->_getParam('certs')) {
                $filters['certificationLevels'] = $this->_getParam('certs');
            }
            if ($this->_getParam('status')) {
                $filters['graduationStatus'] = $this->_getParam('status');
            }
            if ($this->_getParam('groups')) {
                $filters['section'] = $this->_getParam('groups');
            }
            $filters['show_instructors'] = $this->_getParam('show_instructors');

            // if the filters are different than our standard set
            if ($filters != $default_filters) {
                $all_students = false;
            } else {
                $all_students = true;
            }

            if ($this->_getParam('all_students')) {
                $all_students = true;
                $filters = $default_filters;
            }
            $userContextIds = $this->getRoleDataIds($filters, $this->getParam('show_instructors'));
            $requirement_ids = array_unique($this->_getParam('requirement_ids'));
        } else {
            $userContextIds = $this->getParam('userContextIds');
            $requirement_ids = $this->requirementRepository->getRequirements($program_id, true, true, true, true);
        }

        $selectFormValues = [
            "selection-by" => $selection_by,
            "userContextIds" => $userContextIds,
            "requirementIds" => $requirement_ids,
            "people_sub_filters" => $filters,
            "all_students" => $all_students
        ];

        return $selectFormValues;
    }


    /**
     * return the html for the edit compliance status form
     */
    public function getEditComplianceFormAction()
    {
        $this->session->select_form_values = $selectFormValues = $this->getSelectFormValues();

        if(!$selectFormValues['userContextIds']){
            $this->_helper->json("No user roles");
            return;
        }

        if(!$selectFormValues['requirementIds']){
            $this->_helper->json("No requirements");
            return;
        }

        $form = new Scheduler_Form_EditComplianceStatus($this->requirementRepository, $selectFormValues);
        $this->_helper->json($form->__toString());
    }


    /**
     * return the number of attachments associated with the selected people & requirements
     */
    public function getAttachmentCountAction()
    {
        $selectFormValues = $this->getSelectFormValues();

        if ( ! $selectFormValues['userContextIds']) {
            $this->_helper->json("No user roles");
            return;
        }

        if ( ! $selectFormValues['requirementIds']) {
            $this->_helper->json("No requirements");
            return;
        }

        $attachments = $this->requirementRepository->getRequirementAttachmentsByUserContexts(
            $selectFormValues['userContextIds'], $selectFormValues['requirementIds']
        );

        $this->_helper->json(count($attachments));
    }


    public function generateSitePickerAction()
    {
        $site_ids = $this->getParam('site_ids');
        $req_name = $this->getParam('requirement');
        $form = new Scheduler_Form_PickSitesModal($site_ids, $req_name);
        $this->_helper->json($form->__toString());
    }


    public function manageAction()
    {
        // Check permissions
        if ( ! $this->user->isInstructor()) {
            $this->displayError("You do not have permission to view this page.");
            return;
        } else if ( ! ($this->user->getCurrentRoleData()->hasPermission("Edit Program Settings") && $this->hasPermission())) {
            $this->displayError("You do not have permission to view this page.");
            return;
        }

        $this->view->permissions = $this->getPermissionsForViews($this->user, "manage");

        $this->view->pageTitle = "Manage Requirements";
        $this->view->program = $this->user->getCurrentProgram();

        $this->appendRequirementExternalFiles($this->view);
        $this->view->headLink()->appendStylesheet("/css/jquery.flippy.css");
        $this->view->headScript()->appendFile("/js/jquery.flippy.js");
        $this->view->headScript()->appendFile("/js/jquery.fieldtag.js");

        $this->view->requirementFilters = new \Scheduler_Form_RequirementFilters();
        $this->view->autoAssignModal = new Scheduler_Form_AutoAssignModal();
        $this->view->notificationsModal = new Scheduler_Form_NotificationsModal();
        $this->view->requirementAssignModal = new Scheduler_Form_RequirementAssignModal($this->requirementRepository);

        $this->view->tour = new Fisdap_View_Helper_GuidedTourHelper();
        $this->view->tour_id = 5;
        //Need to tell getRequirements to include inactive ones explicitly, in the case that we hit this action with a pending edit on an inactive req
        $filters['active'] = false;
        $reqIds = $this->requirementRepository->getRequirements($this->user->getProgramId(), true, true, true, true, $filters);
        $queuedReqs = $this->checkQueuedEdits($reqIds);
        $this->view->queuedReqs = $queuedReqs['reqsQueued'];

    }


    public function toggleRequirementAction()
    {
        $params = $this->getAllParams();
        //Need to tell getRequirements to include inactive ones explicitly.
        $filters['active'] = false;
        $reqIds = $this->requirementRepository->getRequirements($this->user->getProgramId(), true, true, true, true, $filters);

        $this->requirementRepository->toggleRequirement($this->user->getProgramId(), $params['requirementId'], $params['active']);
        $userContextIds = $this->requirementRepository->getUserContextIdsByRequirement($params['requirementId'], $this->user->getProgramId());
        $data['userContextIds'] = $userContextIds;

        //Adding placeholder ID to cache
        $cacheId = "editing_req_" . $params['requirementId'] . "_for_user_" . $this->user->getCurrentUserContext()->id;
        $data['cacheId'] = $cacheId;

        $this->cache->save(array('jobs' => 1), $cacheId, array(), 0); //indefinite lifetime

        // Queue up the compliance calculation as a result of the toggle.
        $this->toggleRequirementBatching($cacheId, $data);

        $queuedReqs = $this->checkQueuedEdits($reqIds);
        $this->view->queuedReqs = $queuedReqs['reqsQueued'];

        $this->_helper->json($this->view->manageRequirementsTable($this->user->getCurrentProgram(), array(), $queuedReqs['reqsQueued']));
    }


    private function toggleRequirementBatching($cacheId, $data)
    {
        $compute_compliance_userContextIds = $data['userContextIds'];

        // now we queue up the users in batches for compliance recalculation
        $batch_size = 100;
        $batch = array();

        foreach ($compute_compliance_userContextIds as $userContextId) {
            $batch[] = $userContextId;

            // when we get a full batch, queue the job
            if (count($batch) >= $batch_size) {
                $this->queue->push('UpdateCompliance', ["cacheId" => $cacheId, "userContextIds" => $batch]);
                $batch = [];
            }
        }

        // queue up the stragglers, too
        if (count($batch) > 0) {
            $this->queue->push('UpdateCompliance', ["cacheId" => $cacheId, "userContextIds" => $batch]);
        }

    }


    public function filterRequirementsAction()
    {
        $filtersForm = new \Scheduler_Form_RequirementFilters();
        $filters = $filtersForm->process($this->getAllParams());

        // check to see if any of these reqs have pending edits
        $reqIds = $this->requirementRepository->getRequirements($this->user->getProgramId(), true, true, true, true);
        $pendingEdits = $this->checkQueuedEdits($reqIds);

        $this->_helper->json($this->view->manageRequirementsTable($this->user->getCurrentProgram(), $filters, $pendingEdits['reqsQueued']));
    }


    public function saveSettingsAction(){
        $settingsForm = new Scheduler_Form_ComplianceSettings();
        $this->_helper->json($settingsForm->process($this->_getAllParams()));
    }


    public function generateAutoAssignAction()
    {
        $requirement_ids = $this->getParam('requirement_ids');
        $form = new Scheduler_Form_AutoAssignModal($requirement_ids);
        $this->_helper->json($form->__toString());
    }


    public function processAutoAssignAction()
    {
        $formValues = $this->getAllParams();
        $requirement_ids = explode(',', $this->getParam('req_ids'));
        $form = new Scheduler_Form_AutoAssignModal($requirement_ids);
        $form->process($formValues);
        $this->_helper->json('Your settings have been saved.');
    }


    public function generateNotificationsModalAction()
    {
        $requirement_ids = $this->getParam('requirement_ids');
        $form = new Scheduler_Form_NotificationsModal($requirement_ids);
        $this->_helper->json($form->__toString());
    }


    public function processNotificationsModalAction()
    {
        $formValues = $this->getAllParams();
        $requirement_ids = explode(',', $this->getParam('req_ids'));
        $form = new Scheduler_Form_NotificationsModal($requirement_ids);
        $this->_helper->json($form->process($formValues));
    }


    /**
     * @param User $user
     * @param      $page
     *
     * @return array
     */
    private function getPermissionsForViews(User $user, $page)
    {
        $instructor = $user->getCurrentRoleData();
        $settings = $instructor->hasPermission("Edit Program Settings");
        $edit_compliance = $instructor->hasPermission("Edit Compliance Status");
        $scheduler = $instructor->hasPermission("View Schedules");
        $lab = $instructor->hasPermission("Edit Lab Schedules");
        $field = $instructor->hasPermission("Edit Field Schedules");
        $clinical = $instructor->hasPermission("Edit Clinic Schedules");

        return ["lab" => $lab,
            "field" => $field,
            "clinical" => $clinical,
            "scheduler" => $scheduler,
            "settings" => $settings,
            "edit_compliance" => $edit_compliance,
            "page" => $page
        ];
    }


    /**
     * @param array $reqIds a list of requirement IDs to check
     * @return array keyed array of requirements queued, requirements finished, and wait status
     */
    protected function checkQueuedEdits(array $reqIds)
    {
        $user = User::getLoggedInUser();

        // check for presence of each in cache.
        $reqsQueued = array();
        $reqsUpdated = array();
        foreach ($reqIds as $req_id) {
            $cacheId = "editing_req_" . $req_id . "_for_user_" . $this->user->getCurrentUserContext()->id;

            $cachedResults = $this->cache->load($cacheId);

            // figure out if the req is still queued
            if ($cachedResults['jobs'] > 0) {
                $reqsQueued[] = $req_id;
            } else {
                $requirement = $this->requirementRepository->getOneById($req_id);
                $attachmentInfo = $this->requirementRepository->getAttachmentSummariesByRequirement($req_id, $user->getProgramId());

                $reqsUpdated[$req_id] = $this->view->partial("manageRequirementRow.phtml",
                    array("requirement" => $requirement,
                        "attachmentInfo" => $attachmentInfo,
                        "pendingEdits" => false
                    ));
            }
        }

        // are we still waiting for some edits?
        if (count($reqsQueued) > 0) {
            $waiting = TRUE;
        } else {
            $waiting = FALSE;
        }

        // return array of requirements for which we are still performing edits
        return array('reqsQueued' => $reqsQueued,
            'reqsUpdated' => $reqsUpdated,
            'waiting' => $waiting);
    }
}