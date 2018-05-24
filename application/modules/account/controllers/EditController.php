<?php

use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\User;
use Fisdap\EntityUtils;
use Fisdap\Members\Lti\Session\LtiSession;
use Illuminate\Contracts\Events\Dispatcher;

class Account_EditController extends Fisdap_Controller_Private
{
    /**
     * @var LtiSession
     */
    private $ltiSession;


    /**
     * Account_EditController constructor.
     *
     * @param LtiSession $ltiSession
     */
    public function __construct(LtiSession $ltiSession)
    {
        $this->ltiSession = $ltiSession;
    }


    public function init()
    {
        parent::init();
        $this->view->user = $this->currentUser->user();
        $this->session = new Zend_Session_Namespace("accountEditController");
    }


    /**
     * @param Dispatcher $dispatcher
     */
    public function programAction(Dispatcher $dispatcher)
    {
        //Check permissions
        if (!$this->currentUser->user()->isInstructor()) {
            $this->displayError("Students cannot access this page.");

            return;
        } elseif (!$this->currentUser->context()->getRoleData()->hasPermission("Edit Program Settings")) {
            $this->displayError(
                "You do not have permission to edit program settings. Please contact "
                . $this->currentUser->context()->getProgram()->getProgramContactName() . " for more information."
            );

            return;
        }

        $this->view->pageTitle = "Edit Program";

        $this->view->form = new Account_Form_Program($this->currentUser->context()->getProgram()->getId());

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($programId = $this->view->form->process($request->getPost(), $dispatcher)) {
                $this->flashMessenger->addMessage("Program saved successfully.");
                $this->redirect("/account/edit/program");
            }
        }
    }


    public function studentAction()
    {
        // Check instructor permissions
        if ($this->currentUser->user()->isInstructor()
            && !$this->currentUser->context()->getRoleData()->hasPermission(
                "Edit Student Accounts"
            )
        ) {
            $this->displayError(
                "You do not have permission to edit student accounts. Please contact " . $this->currentUser->context()
                    ->getProgram()->getProgramContactName() . " for more information."
            );

            return;
        }

        // Get the student's ID
        if ($this->currentUser->context()->isInstructor()) {
            $studentId = $this->getParam('studentId', $this->globalSession->studentId);
            $this->globalSession->studentId = $studentId;

            // set up single student picker for instructors
            $config = array("student" => $studentId);
            $picklistOptions = array(
                'mode'              => 'single',
                'loadJSCSS'         => true,
                'loadStudents'      => true,
                'useSessionFilters' => true,
                'longLabel'         => true
            );
            $this->view->studentPicker = $this->view->multistudentPicklist($this->user, $config, $picklistOptions);
        } else {
            $studentId = $this->currentUser->context()->getRoleData()->getId();
        }

        $this->view->studentId = $studentId;

        /** @var StudentLegacy $student */
        $this->view->student = $student = EntityUtils::getEntity('StudentLegacy', $studentId);

        // Make sure this student is in this user's program
        if ($student && $student->getUserContext()->getProgram()->getId()
            && ($student->getUserContext()->getProgram()->getId() != $this->currentUser->context()->getProgram()->getId())
        ) {
            //reset the student id in the session and the view
            $this->globalSession->studentId = 0;
            $this->view->studentId = $studentId = null;
        }

        $this->view->pageTitle = "Edit Student Accounts";
        $this->view->form = new Account_Form_Student($studentId);

        $request = $this->getRequest();

        if ($request->isPost()) {

            if ($this->view->form->process($request->getPost()) === true) {
                $this->flashMessenger->addMessage("Your changes have been saved.");

                if ($student->getUserContext()->getUser()->getId() === $this->currentUser->user()->getId()) {
                    $this->currentUser->reload();
                }

                $this->redirect("/account/edit/student");
            }
        }
    }


    public function instructorAction()
    {
        // Check to see if the user can even view this page
        if (!$this->currentUser->user()->isInstructor()) {
            $this->displayError("Students are not allowed to view this page.");

            return;
        }

        // Grab the instructor from either the URL or session
        $instructorId = $this->getParam('instructorId', $this->globalSession->instructorId);

        // If an instructor hasn't been set yet, choose the logged in instructor
        if (!$instructorId) {
            $instructorId = $this->currentUser->context()->getRoleData()->getId();
        }

        // Save the selected instructor in the session
        $this->globalSession->instructorId = $instructorId;

        $this->view->instructorId = $instructorId;

        /** @var \Fisdap\Entity\InstructorLegacy $instructor */
        $this->view->instructor = $instructor = EntityUtils::getEntity('InstructorLegacy', $instructorId);

        // Check to make sure we can view the given instructor
        if ($instructor->getUserContext()->getProgram()->getId() != $this->currentUser->context()->getProgram()->getId()) {
            unset($this->globalSession->instructorId);
            $this->view->instructorId = null;
        } else {
            if ($instructor->getId() != $this->currentUser->context()->getRoleData()->getId()
                && !$this->currentUser->user()->hasPermission(
                    "Edit Instructor Accounts"
                )
            ) {
                $this->displayError(
                    "You do not have permission to edit other instructor accounts. Please contact "
                    . $this->currentUser->context()->getProgram()->getProgramContactName() . " for more information."
                );
                unset($this->globalSession->instructorId);

                return;
            }
        }

        $this->view->pageTitle = "Edit Instructor Accounts";
        $this->view->form = $form = new Account_Form_Instructor($instructorId);

        $request = $this->getRequest();

        if ($request->isPost()) {

            $processResult = $form->process($request->getPost());

            if ($processResult === true || $processResult > 0) {
                $this->flashMessenger->addMessage("Your changes have been saved.");

                if ($form->isSelf) {
                    $this->currentUser->reload();
                }

                $this->redirect("/account/edit/instructor");
            }
        }
    }


    /**
     * Edit a students' graduation status
     */
    public function gradStatusAction()
    {
        //Check to see if the user can even view this page
        if (!$this->user->isInstructor()) {
            $this->displayError("Students are not allowed to view this page.");
            return;
        }

        //Check instructor permissions
        if ($this->user->isInstructor() && !$this->user->getCurrentRoleData()->hasPermission("Edit Student Accounts")) {
            $this->displayError("You do not have permission to edit student accounts. Please contact " . $this->user->getCurrentProgram()->getProgramContactName() . " for more information.");
        }

        $this->view->pageTitle = "Update Graduation Settings";
        // so we don't lose selected students after posting an erro
        $request = $this->getRequest();
        $post_data = $request->getPost();
        $student_ids = $post_data['studentIDs'];

        $this->view->form = new Account_Form_GradStatus($student_ids);

        if ($request->isPost()) {
            if ($this->view->form->process($post_data) === true) {
                $change_msg = "The following students have been updated to:<ul class='changes'>";
                if ($post_data['editDateFlag']) {
                    $change_msg .= "<li>Graduation date: " . $post_data['gradDate']['month'] . "/" . $post_data['gradDate']['year'] . "</li>";
                }

                if ($post_data['editStatusFlag']) {
                    switch ($post_data['gradStatus']) {
                        case 1:
                            $status = "In progress";
                            break;
                        case 2:
                            $status = "Graduated";
                            break;
                        case 3:
                            $status = "Completed (but failed to graduate)";
                            break;
                        case 4:
                            $status = "Left program";
                            break;
                    }
                    switch ($post_data['goodData']) {
                        case 1:
                            $good_data = "good data";
                            break;
                        case 0:
                            $good_data = "poor data for research";
                            break;
                        case -1:
                            $good_data = "good data flag unset";
                            break;
                    }
                    $change_msg .= "<li>Graduation status: $status with $good_data</li>";
                }

                if ($post_data['editCertFlag']) {
                    $certification = EntityUtils::getEntity('CertificationLevel', $post_data['certLevel']);
                    $change_msg .= "<li>Certification level: " . $certification->description . "</li>";
                }

                if ($post_data['editShiftFlag']) {
                    $change_msg .= "<li>Shift Limits Updated To: " . "Field: " . $this->convertShiftLimitToString($post_data['shiftLimitField']) . ", Clinical: " . $this->convertShiftLimitToString($post_data['shiftLimitClinical']) . "</li>";
                }
                $change_msg .= "</ul><br>";

                $students = EntityUtils::getRepository('User')->getStudentNames($student_ids);
                foreach ($students as $student) {
                    $change_msg .= $student['first_name'] . " " . $student['last_name'] . "<br>";
                }

                if ($post_data['removeShiftsFlag'] && $post_data['editStatusFlag'] && $post_data['gradStatus'] == 4) {
                    $change_msg .= "<br>NOTE: All future shifts for these students have been removed.";
                }

                $this->flashMessenger->addMessage($change_msg);
                $this->redirect("/account/edit/grad-status");
            }
        }
    }


    public function getRolePermissionsAction()
    {
        $roles = $this->_getParam("roles", array());
        $permissionConfig = 0;

        foreach ($roles as $role) {
            $permissionSubRole = EntityUtils::getEntity("PermissionSubRole", $role);
            $permissionConfig = ($permissionConfig | $permissionSubRole->permission_configuration);
        }

        $this->_helper->json($permissionConfig);
    }


    public function deleteAccountAction()
    {
        $userId = $this->_getParam("userId");
        if (!$userId) {
            $this->displayError("No User ID found.");
        }

        $user = EntityUtils::getEntity("User", $userId);
        $loggedInUser = User::getLoggedInUser();

        //Check to make sure the account being deleted is in the same program and
        //they're not deleting a student account and are not a student him/herself.
        //Being a staff member will override this.
        if (($user->getProgramId() != $loggedInUser->getProgramId() || !$user->isInstructor()
                || !$loggedInUser->isInstructor())
            && !$loggedInUser->isStaff()
        ) {
            $this->displayError("You are not allowed to delete this account.");

            return;
        }

        //Check to make sure they have permission to edit instructor accounts
        if (!$loggedInUser->hasPermission("Edit Instructor Accounts")) {
            $this->displayError(
                "You do not have permission to edit instructor accounts. Please contact "
                . $this->user->getCurrentProgram()->getProgramContactName() . " for more information."
            );

            return;
        }

        //Mark this account as delete, it won't actually delete, but almost
        $user->delete();
        $this->flashMessenger->addMessage($user->getName() . "'s account has successfully been deleted.");
        unset($this->globalSession->{$user->getCurrentRoleName() . "Id"});
        $this->redirect("/account/edit/" . $user->getCurrentRoleName());
    }


    public function transferAccountAction()
    {
        // Check permissions
        if (!$this->user->isStaff()) {
            $this->displayError("You do not have permission to view this page.");

            return;
        }

        $this->view->pageTitle = "Transfer Accounts";
        $this->view->form = new Account_Form_TransferAccounts();

        $request = $this->getRequest();

        if ($request->isPost()) {

            if ($this->view->form->process($request->getPost()) === true) {
                $this->flashMessenger->addMessage("The account has been transferred.");
                $this->_redirect("/account/edit/transfer-account");
            }
        }
    }

    public function findUsersToTransferAction()
    {
        $userId = $this->getParam('id');
        $user = EntityUtils::getEntity("User", $userId);

        // see if we can find a user based on a username instead
        if (!$user) {
            $user = EntityUtils::getEntityManager()->getRepository(User::class)->getUserByUsername($userId);
        }

        if ($user) {
            $returnText = "<div class='userResponse'><h3>Is this the right account?</h3><div class='name'>"
                . $user->first_name . " " . $user->last_name;

            $returnText .= " (" . $user->username . ")</div>";
            $returnText .= "<div class='userId'>User ID #" . $user->id . "</div>";
            $returnText .= "<div class='role'>";

            $role = $user->getCurrentRoleData();
            $program = EntityUtils::getEntity("ProgramLegacy", $user->getProgramId());

            if ($user->getCurrentRoleName() != "instructor") {
                $returnText .= "<span class='cert'>" . ucfirst($role->getCertification()->name) . "</span>";
            }

            $returnText .= " " . ucfirst($user->getCurrentRoleName()) . " ";
            $returnText .= "</div>";

            $returnText .= "<div class='program'>" . $program->name . "</div>";
            $returnText .= "<div class='location'>" . $program->city . ", " . $program->state . ", " . $program->country . "</div>";

            if ($role->id == $program->program_contact) {
                $returnText .= "<div class='error'>Looks like this user is the primary contact for their program. Please change this before transfering the account.</div>";
            }

            $returnText .= "</div>";

        } else {
            $returnText = "<div class='error'>No users were found.</div>";
        }

        $this->_helper->json($returnText);

    }

    public function mergeAccountAction()
    {
        // Check permissions
        if (!$this->user->isStaff()) {
            $this->displayError("You do not have permission to view this page.");

            return;
        }

        $this->view->pageTitle = "Merge Accounts";
        $this->view->form = new Account_Form_MergeAccounts();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $retVal = $this->view->form->process($request->getPost());
            if ($retVal['code'] == 200) {
                $this->flashMessenger->addMessage("The accounts have been merged.");
                $this->_redirect("/account/edit/merge-account");
            } else {
                $this->flashMessenger->addMessage("There was an error. Please check your selections.");
            }
        }
    }

    public function findUsersToMergeAction()
    {
        $retVal = array();
        $studentId = $this->getParam('id');
        $student = EntityUtils::getEntity("StudentLegacy", $studentId);
        if($student == null) {
            $this->_helper->json(null);
            return;
        }
        $user_context = $student->getUserContext();
        $sn = $user_context->getPrimarySerialNumber();
        $user = $user_context->user;
        $program = $user_context->program;

        $em = \Fisdap\EntityUtils::getEntityManager();
        $shifts = $em->getRepository('Fisdap\Entity\ShiftLegacy')->getShiftsByStudent($studentId);

        // Grab test data.
        $testQuery = <<<EOT
SELECT
	SUM(if(attempts.state = 'finished', 1, 0)) AS 'finished_count',
	SUM(if(attempts.state = 'inprogress', 1, 0)) AS 'inprogress_count',
	SUM(if(attempts.state = 'abandoned', 1, 0)) AS 'abandoned_count'
FROM
	fismdl_quiz_attempts AS attempts
LEFT JOIN fismdl_user AS user ON (attempts.userid = user.id)
WHERE
	user.username = '{$user->getUsername()}'
GROUP BY user.id

EOT;

        $dbConnection = \Fisdap\MoodleUtils::getConnection('secure_testing', TRUE);
        $statement = $dbConnection->query($testQuery);
        $testData = $statement->fetch();

        if ($student) {
            $retVal['program_id'] = $program->getId();
            $retVal['program_name'] = $program->getName();

            $retVal['user_id'] = $user->getId();
            $retVal['user_username'] = $user->getUsername();
            $retVal['user_name'] = $user->getName();
            $retVal['user_email'] = $user->getEmail();

            $retVal['sn'] = $sn->getNumber();
            $retVal['cert'] = $user_context->getCertification();
            $retVal['grad_date'] = $user_context->getEndDate()->format('m/Y');
            $retVal['products'] = $this->view->productShields($sn->configuration, $student);

            if ($testData) {
                $retVal['test_data'] = $testData;
            }

            foreach ($shifts as $shift) {
                $shiftPartials[] = array('shift' => $shift);
            }

            $retVal['shift_count'] = sizeof($shiftPartials);


            if ($this->getParam('studentIdOther') != null) {
                $snOther = $em->getRepository('Fisdap\Entity\SerialNumberLegacy')->findOneBy(['student_id' => $this->getParam('studentIdOther')]);

                $newConfig = $sn->configuration | $snOther->configuration;

                $retVal['products_result'] = $this->view->productShields($newConfig, $student);
            }

        } else {
            $retVal = null;
        }

        $this->_helper->json($retVal);

    }


    public function getFilteredStudentListWithGradStatusAction()
    {
        $filters = $this->getAllParams();
        $staffView = User::getLoggedInUser()->isStaff();

        $repos = EntityUtils::getRepository('User');
        $programId = User::getLoggedInUser()->getProgramId();

        $students = $repos->getAllStudentsByProgram($programId, $filters);

        $returnData = [];

        if ($staffView) {
            $returnData['columns'] = array('Name', 'Graduation Date', 'Graduation Status', 'Shift Limit', 'Good Data');
        } else {
            $returnData['columns'] = array('Name', 'Graduation Date', 'Graduation Status', 'Good Data');
        }

        foreach ($students as $student_data) {

            $atom = array();
            $atom['id'] = $student_data['id'];
            $atom['Name'] = $student_data['first_name'] . " " . $student_data['last_name'];
            $atom['Graduation Date'] = $student_data['end_date']->format(
                "m/Y"
            ); //date('m/Y',strtotime($student_data['end_date']));
            $atom['Graduation Status'] = $student_data['graduation_status'];
            if ($student_data['good_data']) {
                $atom['Good Data'] = "<img  style='width: 20px;' src='/images/check.png'>";
            } else {
                if ($student_data['good_data'] === '0') {
                    $atom['Good Data'] = "<img  style='width: 20px;' src='/images/badinput.png'>";
                }
            }

            if ($staffView) {
                $atom['Shift Limit'] = "F: " . $this->convertShiftLimitToString($student_data['field_shift_limit'])
                    . ", C: " . $this->convertShiftLimitToString($student_data['clinical_shift_limit']);
            }
            $returnData['students'][] = $atom;
        }

        $this->_helper->json($returnData);
    }


    public function convertShiftLimitToString($shiftLimit)
    {
        if ($shiftLimit == "-1") {
            return "U";
        } else {
            return $shiftLimit;
        }
    }


    public function studentSearchAction()
    {
        $this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");
        $this->view->headScript()->appendFile("/js/jquery.fancyFilters.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.fancyFilters.css");
        $this->view->headScript()->appendFile("/js/jquery.chosen.relative.js");
        $this->view->headScript()->appendFile("/js/library/Fisdap/Utils/create-pdf.js");
        $this->view->headScript()->appendFile("/js/library/Fisdap/Utils/create-csv.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.chosen.css");

        // Check permissions
        if (!User::getLoggedInUser()->hasPermission("Edit Student Accounts")) {
            $this->displayError("You do not have permission to view this page.");

            return;
        }

        $this->view->search_string_from_session = $this->session->prevSearch['searchString'];
        $this->view->form = $form = new Account_Form_StudentSearch();
        $this->view->email_modal = new Account_Form_SendStudentMessageModal();
        $this->view->pageTitle = "Student Search";

        if (isset($this->session->prevSearch)) {
            $this->view->prevSearch = $this->session->prevSearch;
        } else {
            $this->view->prevSearch = "";
        }
    }


    public function sendStudentMessageAction()
    {
        $data = $this->getAllParams();
        $modal = new Account_Form_SendStudentMessageModal();
        $this->_helper->json($modal->process($data));
    }


    private function performSearch($post)
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

        $filters['searchString'] = $post['searchString'];

        $programId = User::getLoggedInUser()->getCurrentProgram()->id;
        $students = EntityUtils::getRepository('User')->getAllStudentsByProgram($programId, $filters);

        $returnText = "";


        if ($students) {
            $returnText .= $this->getUserTable($students, "Student");
        } else {
            $returnText
                .= "<div class='clear'></div><div class='grid_12 island withTopMargin' id='no-students-island'>
							<h3 class='section-header'>Student Accounts</h3><div class='error' id='no-students-error'>No students
							were found.</div></div>";
        }

        return array("table" => $returnText, "outlook" => $this->view->studentEmailsOutlook,
                     "other" => $this->view->studentEmailsOther);

    }


    public function getStudentsFromSearchCacheAction()
    {
        $values = $this->session->prevSearch;

        // if prevSearch will return something other than "all"
        if ($this->session->prevSearch) {
            $result = $this->performSearch($values);
        }

        $result = false;
        $this->_helper->json($result);
    }


    public function getStudentsFromSearchAction()
    {

        //check for POST data
        $request = $this->getRequest();

        if ($request->isPost()) {
            $this->view->isPost = true;

            if ($request->isPost()) {
                $post = $request->getPost();
                $this->session->prevSearch = $post;
            } else {
                $post = $this->session->prevSearch;
            }


            $result = $this->performSearch($post);

            $this->_helper->json($result);
        }
    }


    public function setStudentSearchSessionAction()
    {
        $post = $this->getParam('search-string');
        $this->session->prevSearch['searchString'] = $post;
        $this->_helper->json($post);
    }


    public function getUserTable($users, $accountTypes)
    {
        $emails = array();

        $returnText
            = "<div class='clear'></div>
					<div class='island withTopMargin extraLong'>
					<h3 class='section-header'>" . $accountTypes . " Accounts</h3>
					<div id='table-holder'><table id='student-table' class='tablesorter student-search-table'>";

        $returnText
            .= "<thead><tr id='head'>
						<th class='id'>ID</th>
						<th class='name'>Student Name</th>
						<th class='username'>Username</th>
						<th class='email'>Email</th>
						<th class='phone'>Cell Phone</th>
						<th class='cert'>Cert. Level</th>
						<th class='grad-date'>Grad Date</th>						
						<th class='product-access'>Products</th>
						<th class='grad-status'>Grad Status</th>
						<th class='good-data'>Good Data?</th>
						<th class='address'>Address</th>
						<th class='emergency-contact'>Emergency Contact</th>
						</tr></thead><tbody>";

        foreach ($users as $studentArray) {
            $student = EntityUtils::getEntity('StudentLegacy', $studentArray['id']);

            $emails[] = $student->email;
            $returnText .= "<tr data-userId='" . $student->user->id . "'>";

            $phone_number = $student->cell_phone;
            $numeric_phone = preg_replace("/[^a-z0-9]/i", "", $phone_number);
            $grad_date = $student->getGraduationDate();
            $grad_date = ($grad_date instanceof DateTime) ? $grad_date->format('m/Y') : $grad_date;
            //we format our displayed grad date in a way that Tablesorter can't handle, but we can cheat it by placing a grad date in a format it does understand as a hidden element first in the row
            $grad_date_hidden = $student->getGraduationDate();
            $grad_date_hidden = ($grad_date_hidden instanceof DateTime) ? $grad_date_hidden->format('Ymd')
                : $grad_date_hidden;

            if (is_numeric($numeric_phone)) {
                if (strlen($numeric_phone) == 10) {
                    $formatted_phone = "(" . substr($numeric_phone, 0, 3) . ")";
                    $formatted_phone .= " " . substr($numeric_phone, 3, 3) . "-";
                    $formatted_phone .= substr($numeric_phone, 6, 4);
                    $phone_number = $formatted_phone;
                }
            }

            $returnText .= "<td class='id'>" . $student->id . "</td>";

            if (User::getLoggedInUser()->hasPermission("Edit Student Accounts")) {
                $returnText .= "<td class='name'>" . "<a href=\"/account/edit/student/studentId/" . $student->id . "\">"
                    . $student->user->getName() . "</a></td>";
            } else {
                $returnText .= "<td class='name'>" . $student->user->getName() . "</td>";
            }

            $returnText .= "<td class='username'>" . $student->user->username . "</td>";
            $returnText .= "<td class='email'>" . "<a href='#' class='student-email-trigger'>" . $student->email . "</a></td>";
            $returnText .= "<td class='phone'>" . " " . $phone_number . "</td>";
            $returnText .= "<td class='cert'>" . $student->user_context->certification_level->description . "</td>";
            $returnText .= "<td class='grad-date'><span style='display:none'>" . $grad_date_hidden . "</span>"
                . $grad_date . "</td>";

            // product shields
            $sn = $student->getUserContext()->getPrimarySerialNumber();
            $iconText = $this->view->productShields($sn->configuration, $student);
            $products = \Fisdap\Entity\Product::getProductSummary(
                $sn->configuration, $student->program->profession->id
            );

            if (is_null($student->good_data)) {
                $good_data = "";
            } else {
                $good_data = ((int)$student->good_data == 1) ? "Yes" : "No";
            }

            $returnText .= "<td class='product-access' data-products='$products'>" . $iconText . "</td>";
            $returnText .= "<td class='grad-status'>" . $student->graduation_status->name . "</td>";
            $returnText .= "<td class='good-data'>" . $good_data . "</td>";
            $returnText .= "<td class='address'>" . $student->address . "</td>";
            $returnText .= "<td class='emergency-contact'>" . $student->contact_name . "</td>";
            $returnText .= "</tr>";

        }

        $returnText .= "</tbody></table></div></div>";

        $this->view->studentEmailsOutlook = implode(";", $emails);
        $this->view->studentEmailsOther = implode(",", $emails);

        return $returnText;

    }
}
