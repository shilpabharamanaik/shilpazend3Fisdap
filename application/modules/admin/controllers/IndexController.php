<?php

class Admin_IndexController extends Fisdap_Controller_Staff
{
    public function init()
    {
        parent::init();

        $contextSwitch = $this->_helper->getHelper('contextSwitch');
        $contextSwitch->addActionContext('cecbems-data', 'xml')
            ->initContext();
        $this->session = new Zend_Session_Namespace("Index");
    }

    public function indexAction()
    {
        $this->view->pageTitle = "Fisdap Staff Directory";
    }

    public function referralReportAction()
    {
        $this->view->pageTitle = "Referral Report";
        $this->view->headScript()->appendFile("/js/admin/index/report-referral.js");
        $this->view->headLink()->appendStylesheet("/css/admin/index/referral-report.css");
        $this->view->headScript()->appendFile("/js/tableSorter/jquery.tablesorter.min.js");
    }

    public function getProgramsFromSearchAction()
    {

        //check for POST data
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->view->isPost = true;
            $post = $request->getPost();

            $filters = array();

            if (isset($post['startDate'])) {
                $filters['startDate'] = $post['startDate'];
            }
            if (isset($post['endDate'])) {
                $filters['endDate'] = $post['endDate'];
            }

            $start = new DateTime($filters['startDate']);
            $end = new DateTime($filters['endDate']);

            $programs = \Fisdap\EntityUtils::getRepository('ProgramLegacy')->getProgramsByCreatedRange($start, $end);
        } else {
            $this->view->isPost = false;
        }

        $returnText = "";

        $addressesOnly = $post['addressesOnly'];

        if ($programs) {
            $returnText .= $this->getProgramsTable($programs, $addressesOnly);
        } else {
            $returnText .= "<div class='clear'></div><div class='withTopMargin'>
                <h3 class='section-header'>Programs</h3>
                <div class='error'>No programs were found</div>
                </div>";
        }
        $this->_helper->json(array("table" => $returnText));
    }

    public function getProgramsTable($programs, $addressesOnly)
    {
        $returnText = "<div class='clear'></div>
            <div class='withTopMargin extraLong'>
            <h3 class='section-header'>Programs</h3>
            <div id='programs-holder'><table id='program-table' class='tablesorter program-search-table'>";

        $returnText .= "<thead><tr id='head'>
            <th class='id'>ID</th>
            <th class='name'>Program Name</th>
            <th class='contactfn'>Contact First Name</th>
            <th class='contactln'>Contact Last Name</th>";

        if ($addressesOnly) {
            $returnText .= "<th class='address'>Addr. 1</th>
                <th class='address'>Addr. 2</th>
                <th class='address'>Addr. 3</th>
                <th class='address'>City</th>
                <th class='address'>State</th>
                <th class='address'>Zip</th>
                </tr></thead><tbody>";
        } else {
            $returnText .= "<th class='referral'>Referral</th>
                <th class='referral-desc'>Referral Description</th>
                </tr></thead><tbody>";
        }

        foreach ($programs as $program) {
            $returnText .= "<tr>";

            $returnText .= "<td class='id'>" . $program->id . "</td>";
            $returnText .= "<td class='name'>" . $program->name . "</td>";
            $returnText .= "<td class='contactfn'>" . $program->getProgramContact()->first_name . "</td>";
            $returnText .= "<td class='contactln'>" . $program->getProgramContact()->last_name . "</td>";

            if ($addressesOnly) {
                $returnText .= "<td class='address'>" . $program->address . "</td>";
                $returnText .= "<td class='address'>" . $program->address2 . "</td>";
                $returnText .= "<td class='address'>" . $program->address3 . "</td>";
                $returnText .= "<td class='address'>" . $program->city . "</td>";
                $returnText .= "<td class='address'>" . $program->state . "</td>";
                $returnText .= "<td class='address'>" . $program->zip . "</td>";
            } else {
                $returnText .= "<td class='referral'>" . $program->referral . "</td>";
                $returnText .= "<td class='referral-desc'>" . $program->ref_description . "</td>";
            }

            $returnText .= "</tr>";
        }

        $returnText .= "</tbody></table></div></div>";

        return $returnText;
    }

    public function cecbemsDataAction()
    {
        $this->view->pageTitle = "CECBEMS Transition Course XML Export";

        $startDate = new \DateTime($this->_getParam("startDate"));
        $endDate = new \DateTime($this->_getParam("endDate"));

        $scores = \Fisdap\MoodleUtils::getTransitionCourseCompletions($startDate, $endDate);
        $this->view->cecbemsData = array();

        foreach ($scores as $score) {
            $data = array();
            $user = \Fisdap\Entity\User::getByUsername($score['username']);
            $program = $user->getCurrentProgram();

            //Hack to exclude dummy users from getting reported to CECBEMS
            if ($user->license_number == "fisdap" || is_null($user->id)) {
                continue;
            }

            $data['email'] = $user->email;
            $data['certificationLevel'] = $user->getCurrentRoleData()->getCertification()->description;
            $data['licenseState'] = \Fisdap_Form_Element_States::getAbbreviation($user->license_state);
            $data['stateLicenseNumber'] = $user->state_license_number;
            $data['stateLicenseExpirationDate'] = $user->state_license_expiration_date->format("m-d-Y");
            $data['completitionDate'] = date("m-d-Y", $score['timecreated']);
            $data['provider'] = "FISD6625";
            $data['city'] = $user->city ? $user->city : $program->city;
            $data['lastName'] = $user->last_name;
            $data['firstName'] = $user->first_name;
            $data['address'] = $user->address;
            $data['licenseExpirationDate'] = $user->license_expiration_date->format("m-d-Y");
            $data['state'] = $user->state ? $user->state : $program->state;
            $data['zip'] = $user->zip;
            $data['licenseNumber'] = $user->license_number;
            $data['phone'] = $user->cell_phone;

            $transitionCourseInfo = \Fisdap\Entity\Product::getTransitionCourseInfo($score['coursename']);
            $data['courseNumber'] = $transitionCourseInfo['courseNumber'];
            $data['courseHours'] = $transitionCourseInfo['courseHours'];
            $data['courseType'] = $transitionCourseInfo['courseType'];

            $this->view->cecbemsData[] = $data;
        }

        if ($this->_getParam('format') == "xml") {
            $this->_helper->contextSwitch()->initContext('xml');
            $filename = "trans_" . $startDate->format("mdY") . "_" . $endDate->format("mdY") . ".xml";
            $this->getResponse()->setheader('Content-Disposition', "attachment; filename=\"$filename\"");
        }
    }

    public function moodleGroupsAction()
    {
        $this->view->pageTitle = "Moodle Groups";
        $this->view->form = new \Fisdap_Form_MoodleGroup();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost())) {
                $this->redirect("/admin/index/moodle-groups");
            }
        }
    }

    /**
     * Management page to turn ethnio screeners on and off
     */
    public function manageEthnioScreenersAction()
    {
        $this->view->pageTitle = "Ethnio Screener Placement";
        $this->view->screenersForm = new Admin_Form_ManageEthnioScreeners();

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($this->view->screenersForm->process($request->getPost())) {
                $this->flashMessenger->addMessage("Ethnio Screeners saved.");
                $this->redirect("/admin/index/manage-ethnio-screeners");
            }
        }
    }

    public function switchProgramToOldSchedulerAction()
    {
        $this->view->pageTitle = "Scheduler 1.0 Switcher";

        // if we are a staff account browsing to this page, we'll check the current logged in program.
        // if this current logged in program is currently using Scheduler 2.0 and DOES NOT have an events,
        // their 'scheduler_beta' flagged can be switch off.
        if (\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $program = \Fisdap\Entity\User::getLoggedInUser()->getProgram();
            if (!$program->scheduler_beta) {
                $this->displayError("This program is already using Scheduler 1.0.");
                return;
            } else {
                if (!\Fisdap\EntityUtils::getRepository('EventLegacy')->programHasEvents($program->id)) {
                    $program->scheduler_beta = 0;
                    $program->save();
                    $this->view->program_name = $program->name;
                } else {
                    $this->displayError("This program already has events in Scheduler 2.0. They cannot be converted back to 1.0.");
                    return;
                }
            }
        } else {
            $this->displayError("You do not have permission to view this page.");
            return;
        }
    }

    public function ordersAdminAction()
    {
        $this->view->pageTitle = "Fisdap Orders";
        $orders = \Fisdap\EntityUtils::getRepository("Order")->getAllOrders(array("startDate" => date_create("-1 month")->format("Y-m-d"), "endDate" => date_create()->format("Y-m-d")));
        $this->view->orderPartials = array();
        foreach ($orders as $order) {
            $this->view->orderPartials[] = array("order" => $order);
        }
    }

    public function userSearchAction()
    {
        // Check permissions
        if (!\Fisdap\Entity\User::getLoggedInUser()->isStaff()) {
            $this->displayError("You do not have permission to view this page.");
            return;
        }

        $this->view->form = $form = new Fisdap_Form_UserSearch;
        $this->view->pageTitle = "User Search";

        //check for POST data
        $request = $this->getRequest();
        if ($request->isPost()) {
            $this->view->isPost = true;
            $post = $request->getPost();

            // make sure form is valid
            if ($form->isValid($post)) {
                $this->view->users = $form->process($post);
            }
        } else {
            $this->view->isPost = false;
        }
    }

    public function getUsersFromSearchAction()
    {
        $search = $this->_getParam('searchString');
        $users = \Fisdap\EntityUtils::getRepository('User')->searchUsers($search);
        $returnText = "";
        if ($users) {
            $instructors = array();
            $students = array();

            foreach ($users as $user) {
                if ($user->getCurrentRoleName() == "instructor") {
                    $instructors[] = $user;
                } else {
                    $students[] = $user;
                }
            }
        }

        if ($students) {
            $returnText .= $this->getUserTable($students, "Student");
        } else {
            $returnText .= "<div class='clear'></div><div class='grid_12 island withTopMargin'><h3 class='section-header'>Student Accounts</h3><div class='error'>No students were found</div></div>";
        }

        if ($instructors) {
            $returnText .= $this->getUserTable($instructors, "Instructor");
        } else {
            $returnText .= "<div class='clear'></div><div class='grid_12 island withTopMargin'><h3 class='section-header'>Instructor Accounts</h3><div class='error'>No instructors were found</div></div>";
        }

        $this->_helper->json($returnText);
    }


    public function getUserTable($users, $accountTypes)
    {
        $returnText = "<div class='clear'></div>
            <div class='island withTopMargin extraLong'>
            <h3 class='section-header'>" . $accountTypes . " Accounts</h3>
            <table class='user-search-table'>";

        $returnText .= "<tr id='head'>
            <td class='id'>ID</td>
            <td class='username'>Username</td>
            <td class='ac'>Activation Code</td>
            <td class='name'>First/Last Name</td>
            <td class='email'>Email</td>
            <td class='program'>Program</td>
            <td class='home'>Home #</td>
            <td class='cell'>Cell #</td>
            <td class='po'>PO #</td>
            <td class='dist'>Dist Method</td>
            <td class='products'>Products</td>";

        if ($accountTypes != "Instructor") {
            $returnText .= "<td class='grad'>Grad Date</td>";
        }

        $returnText .= "
            <td class='customer'>Cust. ID</td>
            </tr>";

        foreach ($users as $user) {
            // for now we're only going to show data for the current context
            $userContext = $user->getCurrentUserContext();
            $roleData = $userContext->getRoleData();
            $returnText .= "<tr>";
            $program = $userContext->getProgram();
            $sn = $userContext->getPrimarySerialNumber();
            $distMethod = ($sn->dist_method) ? $sn->dist_method : $sn->order->paypal_transaction_id;

            if ($userContext->isStudent()) {
                if ($userContext->end_date) {
                    $gradDate = $userContext->end_date->format('F Y');
                }
            }

            $returnText .= "<td class='id'>" . $user->id . "</td>";
            $returnText .= "<td class='username'><a class='masq' href='/login/masquerade/username/" . $user->username . "'><span class='usernameText'>" . $user->username . "</span><span class='imgWrapper'><img src='/images/masquerade-small.png'></span></a></td>";
            $returnText .= "<td class='ac'>" . $sn->number . "</td>";
            $returnText .= "<td class='name'>" . $user->getFullName() . "</td>";
            $returnText .= "<td class='email'>" . $user->getEmail() . "</td>";
            $returnText .= "<td class='program'><a href='#' onclick='change_program(" . $program->id . ")'>#" . $program->getId() . " " . $program->getName() . "</a></td>";
            $returnText .= "<td class='home'>" . $user->getHomePhone() . "</td>";
            $returnText .= "<td class='cell'>" . $user->getCellPhone() . "</td>";
            $returnText .= "<td class='po'>" . $sn->purchase_order . "</td>";
            $returnText .= "<td class='dist'>" . $distMethod . "</td>";
            $returnText .= "<td class='products'>" .
                \Fisdap\EntityUtils::getRepository("Product")->getProducts($sn->configuration, true, true) . "</td>";

            if ($userContext->isStudent()) {
                $returnText .= "<td class='grad'>" . $gradDate . "</td>";
            }

            $returnText .= "<td class='customer'>" . $program->customer_id . "</td>";

            $returnText .= "</tr>";
        }

        $returnText .= "</table></div>";

        $returnText .= '<script type="text/javascript">
            function change_program(prog_id){
                blockUi(true);
                $.post("/ajax/change-program",
            {"id": prog_id},
            function(response){
                location.reload();
    },
        "json"
    );

    }
            </script>';

        return $returnText;
    }
}
