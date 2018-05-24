<?php

use Fisdap\Controller\Plugin\IdmsToken;
use Fisdap\Data\CertificationLevel\CertificationLevelRepository;
use Fisdap\Data\Order\Configuration\OrderConfigurationRepository;
use Fisdap\Data\Order\OrderRepository;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Data\Program\ProgramLegacyRepository;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\Coupon;
use Fisdap\Entity\Order;
use Fisdap\Entity\OrderConfiguration;
use Fisdap\Entity\ProductCode;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\User;
use Fisdap\Members\Commerce\Events\CustomerWasAdded;
use Fisdap\Members\Commerce\Events\OrderWasCompleted;
use Illuminate\Contracts\Events\Dispatcher;


/**
 * Class Account_NewController
 */
class Account_NewController extends Fisdap_Controller_Base
{
    /**
     * @var Zend_Session_Namespace
     */
    private $session;


    public function init()
    {
        parent::init();
        $this->view->user = $this->user;
        $this->view->headScript()->appendFile("/js/jquery.cluetip.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");
        $this->session = new Zend_Session_Namespace("NewController");
    }

    public function indexAction()
    {
        $this->view->pageTitle = "Create an Account";
        $this->view->form = new \Account_Form_Activate();

        $code = $this->_getParam('code');
        $request = $this->getRequest();

        // make sure they're logged out first, then bring them back here
        if (User::getLoggedInUser()) {
            $logoutLink = "/login/logout?redirect=new&code=$code";
            $this->redirect($logoutLink);
            return;
        }

        // Make sure to use the POST values if present, otherwise default to params
        $params = ($request->isPost()) ? $request->getPost() : $request->getParams();

        // Validate the form if either POST data or an activation code is present
        if ($code || $request->isPost()) {
            if ($this->view->form->isValid($params)) {

                //Redirect to the create acct page if we have a serial number
                if (SerialNumberLegacy::isSerialFormat($params['code'])) {
                    // send 'em on
                    $serial = SerialNumberLegacy::getBySerialNumber($params['code']);
                    if (!empty($serial) && $serial->isInstructorAccount()) {
                        $this->redirect("/account/new/instructor/sn/" . $params['code']);
                    } else {
                        $this->redirect("/account/new/student/sn/" . $params['code']);
                    }
                }

                //Redirect to the acct confirmation page if we have a product code
                // also checks to see if it's a valid legacy product code
                else if (ProductCode::getByProductCode($params['code']) || ProductCode::isLegacyProductCode($params['code'])) {
                    $this->redirect("/account/new/confirm-account/?pc=" . $params['code']);
                } else {
                    // if we got here something went wrong. the form should handle the validation
                    $this->displayError("You've reached this page in error.");
                    return;
                }
            }
        }
    }

    /**
     * Have the user confirm that this is the account they want to create: correct program, products, and cert level
     */
    public function confirmAccountAction()
    {
        // make sure we have a product code of some kind
        if ($this->getParam('pc')) {
            $code = $this->getParam('pc');
        } else if ($this->getParam('productCode')) {
            $code = $this->getParam('productCode');
        } else {
            $this->displayError("You've reached this page in error.");
            return;
        }

        $this->view->pageTitle = "Create an Account";
        $productCode = ProductCode::getByProductCode($code);

        // if the user is already logged in, log 'em out first
        if (User::getLoggedInUser()) {
            $logoutLink = "/login/logout?redirect=product&code=$code";
            $this->redirect($logoutLink);
            return;
        }

        // see if we have a valid product code
        if (!$productCode) {
            // see if it's a legacy code; if so, get the details about this account
            if (ProductCode::isLegacyProductCode($code)) {
                $legacy = true;
                $this->view->accountDetails = ProductCode::getAccountFromLegacyProductCode($code);
            } else {
                $this->displayError("'$code' is not a valid product code.");
                return;
            }
        } else {
            // double check to make sure this code is active
            if (!$productCode->isValid()) {
                $this->displayError("'$code' is not a valid product code.");
                return;
            }
        }

        // if this isn't a legacy code, we still need to get the account details for the view
        if (!$legacy) {
            $this->view->accountDetails = $productCode->getAccountDetails();
        }

        // get a coupon if there is one
        if ($this->session->couponId) {
            $this->view->couponId = $this->session->couponId;
        }
    }


    /**
     * @param UserRepository $userRepository
     */
    public function userAgreementAction(UserRepository $userRepository)
    {
        $userId = $this->getParam('userId', User::getLoggedInUser()->id);
        $user = $userRepository->getOneById($userId);

        if (!$user) {
            $this->displayError("You've reached this page in error.");
            return;
        }

        $this->view->pageTitle = "User Agreement";
        $this->view->form = new Account_Form_UserAgreement($userId);

        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost()) === true) {
                //Is there a URL to remember from being redirected here?
                if (isset($this->globalSession->requestAgreementURL)) {
                    $url = $this->globalSession->requestAgreementURL;
                    unset($this->globalSession->requestAgreementURL);
                } else {
                    //Otherwise redirect to the welcome page
                    $url = "/account/new/welcome/userId/" . $userId;
                }

                $this->redirect($url);
            }
        }
    }


    /**
     * @param UserRepository $userRepository
     */
    public function welcomeAction(UserRepository $userRepository)
    {
        if (User::getLoggedInUser()) {
            $userId = User::getLoggedInUser()->id;
        } else {
            $userId = $this->getParam('userId');
        }

        $this->view->user = $userRepository->getOneById($userId);
        if (!$this->view->user) {
            $this->displayError("You've reached this page in error.");
            return;
        }

        $serial = $this->view->user->serial_numbers->first();

        // avoid repeating tutorials
        $testingFlag = false;
        $studyToolsFlag = false;

        // step through this user's products and get corresponding tutorials
        $products = $serial->getProducts();
        $tutorialArray = array();

        foreach ($products as $product) {
            if ($product->category->id == 2 && !$testingFlag) {
                $tutorialArray = array_merge($tutorialArray, $product->getTutorials());
                $testingFlag = true;
            } else if ($product->category->id == 3 && !$studyToolsFlag) {
                $tutorialArray = array_merge($tutorialArray, $product->getTutorials());
                $studyToolsFlag = true;
            } else if ($product->category->id != 2 && $product->category->id != 3) {
                if ($product->getTutorials()) {
                    $tutorialArray = array_merge($tutorialArray, $product->getTutorials());
                }
            }
        }

        $this->view->tutorials = $tutorialArray;
        $this->view->pageTitle = "Welcome";
    }


    public function studentAction()
    {
        $this->view->pageTitle = "Activate Your Account";

        if (User::getLoggedInUser()) {
            $this->displayError("You've reached this page in error.");
            return;
        }

        $sn = SerialNumberLegacy::getBySerialNumber($this->getParam("sn"));
        if ($sn) {
            // has it already been activated?
            if ($sn->student_id == -15 || !$sn->user) {
                if ($sn->order->id) {
                    $this->view->accountDetails = $sn->getAccountDetails();
                }
            } else {
                $this->displayError("This account has already been activated. Please <a href='/login/'>log in</a> to edit your account details.");
                return;
            }
        } else {
            $this->displayError("You've reached this page in error.");
            return;
        }

        $this->view->form = new Account_Form_Student(null, $sn->id);
        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost()) === true) {
                $this->redirect("/account/new/user-agreement/userId/" . $this->view->form->userId);
            }
        }
    }


    public function researchConsentAction()
    {
        if (User::getLoggedInUser()->getCurrentRoleName() == 'instructor') {
            $this->view->pageTitle = "Sample Research Consent Form";
        } else {
            $this->view->pageTitle = "Research Consent";
        }

        $this->view->form = new Account_Form_ResearchConsentForm();

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost()) === true) {
                //Is there a URL to remember from being redirected here?
                if (isset($this->globalSession->requestAgreementURL)) {
                    $url = $this->globalSession->requestAgreementURL;
                    unset($this->globalSession->requestAgreementURL);
                } else {
                    //Otherwise redirect to my fisdap
                    $url = "/my-fisdap";
                }

                $this->redirect($url);
            }
        }
    }


    /**
     * Display a form to create a new instructor account.
     * This can be done by another instructor account with the right permissions, or
     * with a serial number.
     */
    public function instructorAction()
    {
        //If we have a SN, we're activating an account with products
        if ($this->hasParam("sn")) {
            $this->view->pageTitle = "Activate Your Account";
            $serial = SerialNumberLegacy::getBySerialNumber($this->_getParam("sn"));

            //Display an error if the given serial number is already in use
            if ($serial->isActive()) {
                $this->displayError("This serial number has already been activated by " . $serial->user->getName() . " on " . $serial->activation_date->format("m-d-Y"));
                return;
            }

            $this->view->accountDetails = $serial->getAccountDetails();
            $this->view->form = new Account_Form_Instructor(null, $serial->id);
        } else {
            //Otherwise, we're already logged in, creating a free account
            $this->view->pageTitle = "Create a new Instructor";

            //Display an error if the user does not have permission to create instructor accounts
            if (!$this->user->hasPermission("Edit Instructor Accounts")) {
                $this->displayError("You do not have permission to create instructor accounts. Please contact " . $this->user->getCurrentProgram()->getProgramContactName() . " for more information.");
                return;
            }
            $this->view->form = new Account_Form_Instructor();
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($userId = $this->view->form->process($request->getPost())) {
                if ($this->hasParam("sn")) {
                    $this->redirect("/account/new/user-agreement/userId/" . $userId);
                } else {
                    $this->flashMessenger->addMessage("Instructor Account successfully created.");
                    $this->redirect("/account/new/instructor/");
                }
            }
        }
    }


    /**
     * This page presents a landing page after an order is placed that gives an
     * instructor information on how to activate accounts.
     *
     * @param OrderRepository $orderRepository
     */
    public function activateAccountsOptionsAction(OrderRepository $orderRepository)
    {
        $this->view->pageTitle = "Activate Fisdap Accounts";

        $orderId = $this->getParam('orderId', $this->session->orderId);
        $this->session->orderId = $orderId;

        $this->checkOrderPermissions($orderRepository->getOneById($orderId));
        $this->view->order = $orderRepository->getOneById($orderId);

    }


    private function getGroupDescription($config)
    {
        $productSummary = substr($config->getProductSummary(), 29, (strlen($productSummary) - 6));
        $includeThe = ($productSummary == "Preceptor Training") ? " " : "the ";
        $certLevel = $config->certification_level->id ? $config->certification_level->description : "instructor";
        $groupRowTitle = $config->quantity . ' ' . $certLevel . ' accounts with ' . $includeThe . '' . $productSummary . ' ';

        $descriptionLength = strlen($groupRowTitle);
        $dashesToInclude = 150 - $descriptionLength;
        $beginningDashes = "-";
        for ($i = 0; $i < $dashesToInclude / 2; $i++) {
            $beginningDashes .= "-";
        }
        for ($i = 0; $i < $dashesToInclude / 2; $i++) {
            $groupRowTitle .= "-";
        }

        return $beginningDashes . ' ' . $groupRowTitle;
    }


    private function getGroupFields($config)
    {

        $fields = array('First Name', 'Last Name', 'Username', 'Password', 'Email');

        if (!$config->onlyTransitionCourse() && !$config->onlyPreceptorTraining()) {
            // include grad date
            $fields[] = 'Grad Month (mm)';
            $fields[] = 'Grad Year (yyyy)';
        }

        if ($config->onlyTransitionCourse()) {
            //Add specific fields if these accounts are EMS providers
            $fields[] = 'NREMT License';
            $fields[] = 'License Expiration (mm/dd/yyyy)';
            $fields[] = 'License State (full name)';
            $fields[] = 'State License';
            $fields[] = 'State License Expiration (mm/dd/yyyy)';
        }

        return $fields;

    }


    /**
     * @param OrderRepository $orderRepository
     */
    public function generateCsvTemplateAction(OrderRepository $orderRepository)
    {
        $orderId = $orderRepository->getOneById($this->session->orderId);
        $this->session->orderId = $orderId;

        $order = $orderRepository->getOneById($orderId);
        $list = array(
            array('Set up Accounts'),
            array('Fill out the table below with the account details. Then save the document and upload to Fisdap. All fields are required.'),
            array(''),
        );

        // fetch the data
        $configs = $order->order_configurations;

        $count = 1;

        foreach ($configs as $config) {

            $list[] = array($this->getGroupDescription($config));
            $list[] = $this->getGroupFields($config);

            // set the default grad date if there is one
            $serialNumbers = $config->serial_numbers;

            if ($config->graduation_date) {
                $month = $config->graduation_date->format("m");
                $year = $config->graduation_date->format("Y");
            } else {
                $month = "";
                $year = "";
            }

            foreach ($serialNumbers as $sn) {
                $list[] = array("", "", "", "", "", $month, $year);
            }

            $count++;
        }

        // create a file pointer connected to the output stream
        $output = fopen('php://output', 'w');

        foreach ($list as $fields) {
            fputcsv($output, $fields);
        }

        $this->template = $output;

        $today = new DateTime();
        $filename = $orderId . "-account-set-up-template-" . $today->format("m-d-y");

        // output headers so that the file is downloaded rather than displayed
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=" . $filename . ".csv");
        die;
    }


    /**
     * This page presents a form that allows an instructor to
     * create accounts from an order en masse.
     *
     * @param OrderRepository $orderRepository
     */
    public function activateAccountsAction(OrderRepository $orderRepository)
    {
        $this->view->pageTitle = "Set Up Student Accounts";

        $orderId = $this->_getParam('orderId', $this->session->orderId);
        $this->session->orderId = $orderId;
        $this->checkOrderPermissions($orderRepository->getOneById($orderId));

        $order = $orderRepository->getOneById($orderId);
        $this->view->onlyStudentAccounts = true;
        foreach ($order->order_configurations as $config) {
            if ($config->onlyPreceptorTraining() || $config->onlyTransitionCourse()) {
                $this->view->onlyStudentAccounts = false;
                break;
            }
        }

        $this->view->form = new \Account_Form_ActivateAccounts($orderId);
        $this->view->modal = new Account_Form_UploadStudentAccountsModal($orderId);

        //header('Content-Type: text/csv; charset=utf-8');
        //header('Content-Disposition: attachment; filename=data.csv');
        //$this->getRequest()
        // ->setHeader('Content-Type', 'text/csv')
        //->setHeader('Content-Disposition', 'attachment; filename=student-account-activation-template.csv');

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($_FILES) {
                $data = $request->getPost();
                $fileName = ($_FILES['file']['name']);
                $numberOfStudents = $this->view->form->numberOfStudents();
                $order = $orderRepository->getOneById($orderId);

                foreach ($order->order_configurations as $config) {
                    $configurations[] = $config;
                }

                $csv = new \Util_CsvIterator(file($_FILES['file']['tmp_name']), $configurations);
                $this->view->form->populateFromFile($csv, $data['override']);

                $this->view->params = array(
                    'fileName' => $fileName,
                    'additionalStudents' => $csv->getNumberOfStudentsNotAdded(),
                    'rowsWithData' => $this->view->form->rowsWithData,
                    'availableStudents' => $numberOfStudents,
                    'rowsAvailable' => $numberOfStudents - $this->view->form->rowsWithData,
                    'override' => $data['override']
                );
            } else {
                if ($this->view->form->process($request->getPost())) {
                    $this->flashMessenger->addMessage("The accounts have been created.");
                    $this->redirect("/account/orders/index");
                }
            }
        }
    }


    /**
     * This page presents a form that allows an instructor
     * to invite students to create a new Fisdap account
     *
     * @param OrderRepository $orderRepository
     */
    public function inviteAction(OrderRepository $orderRepository)
    {
        $this->view->pageTitle = "Activate Fisdap Accounts";

        $orderId = $this->getParam('orderId', $this->session->orderId);
        $this->session->orderId = $orderId;

        $this->checkOrderPermissions($orderRepository->getOneById($orderId));

        $this->view->form = new \Account_Form_SendActivationCodes($orderId);

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost())) {
                $this->flashMessenger->addMessage("The activation codes from your order have been emailed.");
                $this->redirect("/account/orders/index");
            }
        }
    }


    /**
     * AJAX handler to output preview text for an invitation to create an account.
     * This function is used in the inviteAction()
     *
     * @param OrderConfigurationRepository $orderConfigurationRepository
     */
    public function previewInvitationAction(OrderConfigurationRepository $orderConfigurationRepository)
    {
        $params = $this->getAllParams();
        $orderConfiguration = $orderConfigurationRepository->getOneById($params['orderConfigurationId']);
        $serial = $orderConfiguration->serial_numbers->first();
        $upgradeable = !($serial->hasTransitionCourse() || $serial->isInstructorAccount());
        $viewParams = array(
            "orderer" => $orderConfiguration->order->user,
            "serial" => $serial,
            "urlRoot" => Util_HandyServerUtils::getCurrentServerRoot(),
            "message" => $params['message'],
            "upgradeable" => $upgradeable
        );

        $this->view->addScriptPath(APPLICATION_PATH . "/views/scripts/email-templates/");
        $invite = $this->view->partial("create-account-invitation.phtml", $viewParams);

        //Add the email signature to the email
        $mail = new \Fisdap_TemplateMailer();
        $invite .= $mail->getDefaultSignature("<br>");
        $this->_helper->json("<div class='preview-email'>" . $invite . "</div>");
    }


    public function studyToolsAction()
    {
        $this->view->pageTitle = "Create an Account";
        $this->view->form = new Account_Form_StudyTools();
    }


    /**
     * @param ProductRepository $productRepository
     */
    public function orderIndividualProductsAction(ProductRepository $productRepository)
    {
        $user = $this->getParam('users');
        $email = $this->getParam('email');
        $howWeGotHere = $this->getParam('save');
        $orderId = $this->getParam('orderId');
        $configuration = $this->getParam('configuration', 1573008);
        $this->view->showBack = true;

        $products = $productRepository->getProducts($configuration, true, false, true, false);

        $this->view->pageTitle = "Create an Account";
        $this->view->form = new Account_Form_OrderIndividualProducts($products, $user, $orderId);

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($howWeGotHere == "Purchase more attempts >" || $howWeGotHere == "Upgrade account >") {
                $this->redirect("/account/orders/upgrade-individual");
            } else if ($howWeGotHere != "Purchase a New Account >") {
                if ($this->view->form->process($request->getPost()) === true) {
                    $this->redirect("/account/new/student-purchase/orderId/" . $this->view->form->orderId);
                }
            }
        } else {
            if ($email) {
                $this->view->showBack = true;
            } else {
                $this->view->showBack = false;
            }
        }
    }


    /**
     * @param OrderRepository $orderRepository
     */
    public function receiptAction(OrderRepository $orderRepository)
    {
        $orderId = $this->getParam('orderId');
        $this->view->pageTitle = "Receipt";
        $this->view->order = $orderRepository->getOneById($orderId);
    }


    /**
     * @param OrderRepository   $orderRepository
     * @param ProductRepository $productRepository
     * @param IdmsToken         $idmsToken
     * @param Dispatcher        $dispatcher
     */
    public function studentPurchaseAction(OrderRepository $orderRepository, ProductRepository $productRepository, IdmsToken $idmsToken, Dispatcher $dispatcher)
    {
        // throw an error if we have a logged in user
        if (User::getLoggedInUser()) {
            $this->displayError("Please <a href='/login/logout'>logout</a> of your current account before buying a new account.");
            return;
        }

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();
        $code = $this->getParam('code');
        $this->view->orderId = $this->getParam('orderId');

        /** @var Order $order */
        $this->view->order = $order = $orderRepository->getOneById($this->view->orderId);
        $this->view->orderConfig = $this->view->order->order_configurations->first();

        // Check to make sure a customer id has been created for this program
        if ($order->program->customer_id <= 0) {
            $order->program->generateCustomerName();

            $orderRepository->update($order->program);

            //Fire event to indicate that new customer was added
            $dispatcher->fire(new CustomerWasAdded($order->program->getId(), $order->program->getCustomerName(), $order->program->customer_id));
        }

        // make sure we have an incomplete order
        if (!$order || $order->completed) {
            $this->redirect("/account/new/");
        }

        // validate the code & get the account details
        $invalidPC = false;

        if ($code) {
            $invalidPC = true;
            $productCode = ProductCode::getByProductCode($code);

            if ($productCode || ProductCode::isLegacyProductCode($code)) {
                $legacy = ($productCode) ? false : true;
                if ($this->view->orderConfig->compareToProductCode($code, $legacy)) {
                    $this->view->accountDetails = ($legacy) ? ProductCode::getAccountFromLegacyProductCode($code) : $productCode->getAccountDetails();
                    $invalidPC = false;
                }
            }
        } else {
            // since we don't have a code, make sure this order belongs to the orphan program
            if ($order->program->id == 688) {
                $this->view->orphanAccount = true;
            } else {
                $this->displayError("You have reached this page in error.");
                return;
            }
        }

        if ($invalidPC) {
            $this->displayError("You have an invalid product code. Please enter it again <a href='/account/new/'>here</a>.");
            return;
        }

        $this->view->pageTitle = "Create an Account";
        $this->view->form = new Account_Form_OrderCreditCard($orderRepository, $order);

        $products = $productRepository->getProducts($this->view->orderConfig->configuration, true);
        $this->view->summary = $this->view->upgradeOrderSummary($products, $order->program->id);

        if (!$request->isPost()) return;

        if ($this->view->form->process($request->getPost()) === true) {
            if ($order->completed) {

                $this->session->unsetAll();
                $idmsToken->clearIdmsToken();

                $dispatcher->fire(new OrderWasCompleted($order->id));

                $this->redirect("/account/new/receipt/orderId/" . $order->id);
            }
            $this->view->transactionErrors = $this->view->errorContainer($order->getTransactionErrors(), "PayPal returned the following error(s):");
        }
    }


    /**
     * @param OrderRepository $orderRepository
     */
    public function validateCouponAction(OrderRepository $orderRepository)
    {
        $code = $this->getParam("code");
        $orderId = $this->getParam("orderId");
        $order = $orderRepository->getOneById($orderId);
        if ($coupon = Coupon::isValidCoupon($code)) {
            if ($order->id) {
                $order->applyCoupon($coupon->id);
                $order->save();
                $this->flashMessenger->addMessage("Coupon successfully applied.");
            } else {
                $this->session->couponId = $coupon->id;
                $this->flashMessenger->addMessage("Coupon successfully applied.");
            }
            $this->_helper->json(true);
        } else {
            $this->_helper->json(false);
        }
    }


    /**
     * @param OrderConfigurationRepository $orderConfigurationRepository
     * @param ProgramLegacyRepository $programLegacyRepository
     * @param CertificationLevelRepository $certificationLevelRepository
     *
     * @throws Exception
     */
    public function createOrderFromProductCodeAction(
        OrderConfigurationRepository $orderConfigurationRepository,
        ProgramLegacyRepository $programLegacyRepository,
        CertificationLevelRepository $certificationLevelRepository
    )
    {
        $code = $this->getParam('code');
        $productCode = ProductCode::getByProductCode($code);

        if ($productCode) {
            // get the configuration for this product code
            $config = $orderConfigurationRepository->getOneById($productCode->order_configuration->id);
            $productConfiguration = $config->configuration;
            $cost = $config->individual_cost;
            $certLevel = $config->certification_level;
            $program = $config->order->program;
        } else {
            // it's a legacy product code - oh boy!
            $accountDetails = ProductCode::getAccountFromLegacyProductCode($code);
            $productConfiguration = $accountDetails['configuration'];
            $cost = $accountDetails['cost'];
            $certLevel = $certificationLevelRepository->getOneById($accountDetails['certId']);
            $program = $programLegacyRepository->getOneById($accountDetails['programId']);
        }

        $orderConfig = new OrderConfiguration();

        $order = new Order();

        // Setup our configuration
        $orderConfig->configuration = $productConfiguration;
        $orderConfig->quantity = 1;
        $orderConfig->individual_cost = $cost;
        $orderConfig->set_group($config->group);
        $orderConfig->graduation_date = $config->graduation_date;
        $orderConfig->set_certification_level($certLevel);
        $order->individual_purchase = 1;
        $order->program = $program;

        $order->addOrderConfiguration($orderConfig);
        $orderConfig->calculateFinalPrice();

        // Add coupon if one is saved
        if ($this->session->couponId) {
            $order->applyCoupon($this->session->couponId);
        }

        $order->save();
        $this->redirect("/account/new/student-purchase?orderId=" . $order->id . "&code=" . $code);
    }


    /**
     * @param UserRepository $userRepository
     */
    public function searchStudyToolsAction(UserRepository $userRepository)
    {
        $this->view->pageTitle = "Create an Account";
        $email = $this->getParam('email');

        if ($email) {
            $users = $userRepository->getUsersByEmail($email);
        }

        // if we found users we now want to display them as radio buttons
        if ($users) {
            $returnArray = [];
            foreach ($users as $user) {
                foreach ($user->getAllUserContexts() as $context) {
                    if (!$context->isInstructor()) {
                        // if the program is "FISDAP Study Tools", we have an orphan account and there's no need to display these
                        if ($context->getProgram()->id != 688) {
                            $sn = $context->getPrimarySerialNumber();
                            if ($sn) {
                                $products = $sn->getProducts();
                                $userProducts = array();
                                foreach ($products as $product) {
                                    array_push($userProducts, $product->name);
                                }
                            }


                            $thisContext = array(
                                "id" => $context->id,
                                "firstName" => $user->first_name,
                                "lastName" => $user->last_name,
                                "program" => $context->getProgram()->name,
                                "certLevel" => $context->certification_level->description,
                                "products" => $userProducts
                            );

                            $returnArray[$context->id] = $thisContext;
                        }
                    }
                }
            }

            $this->view->form = new Account_Form_SearchStudyTools($returnArray, $email);
        } else {
            if (!$email) {
                $email = 0;
            }
            $this->redirect("/account/new/order-individual-products/email/" . $email);
        }
    }


    /**
     * @param OrderConfigurationRepository $orderConfigurationRepository
     */
    public function updateOrderConfigurationAction(OrderConfigurationRepository $orderConfigurationRepository)
    {
        $productConfig = $this->getParam("productConfiguration");
        $configId = $this->getParam("orderConfigurationId");
        $productCost = $this->getParam("productCost");

        $orderConfig = $orderConfigurationRepository->getOneById($configId);
        $orderConfig->configuration = $orderConfig->configuration - $productConfig;
        $orderConfig->individual_cost = $orderConfig->individual_cost - $productCost;
        $orderConfig->subtotal_cost = $orderConfig->subtotal_cost - $productCost;
        $orderConfig->order->total_cost = $orderConfig->individual_cost;

        // we also have to deal with coupons, if any
        if ($orderConfig->order->coupon->id > 0) {
            $orderConfig->order->applyCoupon($orderConfig->order->coupon->id);
        }

        $orderConfig->save();
        $orderConfig->order->save();
        $this->_helper->json(true);
    }


    /**
     * Verify that the logged in user has permission to view order information.
     * Also make sure that they're not trying to tamper with another program's order.
     *
     * @param Order $order
     */
    private function checkOrderPermissions(Order $order)
    {
        if ($this->user->getCurrentProgram()->id != $order->program->id) {
            $this->displayError("This order was not placed by your program. You do not have permission to see it.");
            return;
        }

        if (!$this->user->getCurrentRoleData()->hasPermission('Order Accounts')) {
            $this->displayError("You do not have permission to view or place orders.");
            return;
        }
    }
}
