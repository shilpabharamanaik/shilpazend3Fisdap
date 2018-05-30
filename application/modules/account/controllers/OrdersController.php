<?php

use Fisdap\Data\CertificationLevel\CertificationLevelRepository;
use Fisdap\Data\ClassSection\ClassSectionLegacyRepository;
use Fisdap\Data\Order\Configuration\OrderConfigurationRepository;
use Fisdap\Data\Order\OrderRepository;
use Fisdap\Data\Product\Package\ProductPackageRepository;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Data\User\UserRepository;
use Fisdap\Entity\Coupon;
use Fisdap\Entity\Order;
use Fisdap\Entity\OrderConfiguration;
use Fisdap\Entity\ProductCode;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\User;
use Fisdap\Members\Commerce\Events\CustomerWasAdded;
use Fisdap\Members\Commerce\Events\OrderWasCompleted;
use Fisdap\Service\ProductService;
use Fisdap\Controller\Plugin\IdmsToken;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Class Account_OrdersController
 */
class Account_OrdersController extends Fisdap_Controller_Private
{
    /**
     * @var integer the ID of the given order
     */
    public $orderId;

    /**
     * @var \Zend_Session_Namespace
     */
    public $session;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var CertificationLevelRepository
     */
    private $certificationLevelRepository;

    /**
     * @var ClassSectionLegacyRepository
     */
    private $classSectionLegacyRepository;


    /**
     * Account_OrdersController constructor.
     *
     * @param OrderRepository $orderRepository
     * @param CertificationLevelRepository $certificationLevelRepository
     * @param ClassSectionLegacyRepository $classSectionLegacyRepository
     */
    public function __construct(
        OrderRepository $orderRepository,
        CertificationLevelRepository $certificationLevelRepository,
        ClassSectionLegacyRepository $classSectionLegacyRepository
    ) {
        $this->orderRepository = $orderRepository;
        $this->certificationLevelRepository = $certificationLevelRepository;
        $this->classSectionLegacyRepository = $classSectionLegacyRepository;
    }


    public function init()
    {
        parent::init();

        $this->view->headLink()->appendStylesheet("/css/tabs.css");
        $this->view->headScript()->appendFile("/js/jquery.fisdap-tabs.js");
        $this->view->headScript()->appendFile("/js/jquery.maskedinput-1.3.js");
        $this->view->headScript()->appendFile("/js/jquery.formatCurrency-1.4.0.min.js");
        $this->view->headScript()->appendFile("/js/library/Account/Form/payment-method.js");

        $this->session = new Zend_Session_Namespace("OrdersController");
        $this->orderId = $this->getParam('orderId', $this->session->orderId);
        $this->session->orderId = $this->orderId;
    }


    public function indexAction()
    {
        $this->permissionsCheck();

        $this->view->pageTitle = "Order Accounts";

        if ($this->session->orderId) {
            $this->view->previousOrder = "<a href='/account/orders/cart/'>Return to order summary</a>";
        }

        $this->view->showDowngrade = $this->user->isStaff();
    }


    public function clearSessionOrdersAction()
    {
        $this->session->unsetAll();
        $this->_helper->json(true);
    }


    public function billingAction(ProductRepository $productRepository, IdmsToken $idmsToken, Dispatcher $dispatcher)
    {
        // Check permissions but allow students to see this page
        $this->permissionsCheck(true);

        /** @var Order $order */
        $this->view->order = $order = $this->orderRepository->getOneById($this->orderId);

        // Make sure we have an order and that it's not completed
        $this->requireValidOrder($order);

        $this->view->pageTitle = "Order Accounts - Billing";


        // Check to make sure a customer id has been created for this program
        if ($order->program->customer_id <= 0) {
            $order->program->generateCustomerName();

            $this->orderRepository->update($order->program);

            //Fire event to indicate that new customer was added
            $dispatcher->fire(new CustomerWasAdded($order->program->getId(), $order->program->getCustomerName(), $order->program->customer_id));
        }

        // get the order/upgrade summary
        if ($order->individual_purchase) {
            $thisStudent = $this->user->getCurrentRoleData();
            $certLevel = $thisStudent->getCertification();

            $upgradeConfig = $order->order_configurations[0]->configuration;
            $upgradedProducts = $productRepository->getProducts($upgradeConfig, true);
            $this->view->summary = $this->view->upgradeOrderSummary($upgradedProducts);
            $this->view->pageTitle = "Upgrade ".$certLevel->description." Account - Checkout";
            $this->view->breadcrumb = '<a href="/account/orders/upgrade-individual">&lt;&lt; Choose products</a>';
        } else {
            $this->view->summary = $this->view->orderSummaryTable($order, false);
            $this->view->pageTitle = "Order Accounts - Billing";
            $this->view->breadcrumb = '<a href="/account/orders/cart">&lt;&lt; Previous (cart)</a>';
        }

        // If a student is making the purchase and the order is free, process the order and take them to their receipt
        if ($order->individual_purchase && $order->total_cost == 0) {
            $order->process();
            $order->save();
            $this->redirect("/account/orders/receipt/orderId/" . $order->id);
        }

        // If this program is credit card only, set the payment method for them
        if ($order->program->order_permission->name == "Credit Card Only") {
            $order->set_payment_method(2);
        }

        switch ($order->payment_method->id) {
            case 1:
                $this->view->form = new Account_Form_OrderInvoice($this->orderRepository, $order);
                break;
            case 2:
                $this->view->form = new Account_Form_OrderCreditCard($this->orderRepository, $order);
                break;
            default:
                $this->view->form = new Account_Form_PaymentMethod($order);
                $this->view->paymentMethod = true;
        }

        /** @var Zend_Controller_Request_Http $request */
        $request = $this->getRequest();

        if (!$request->isPost()) {
            return;
        }

        if ($this->view->form->process($request->getPost(), $dispatcher) === true) {
            if ($order->completed) {
                // blow away the IDMS token so we re-authenticate with the API; that way the API knows what products we have access to now
                $idmsToken->clearIdmsToken();

                //Fire event to indicate that order was completed
                $dispatcher->fire(new OrderWasCompleted($order->id));

                $this->redirect("/account/orders/receipt/orderId/" . $order->id);
            }
            $this->view->transactionErrors = $this->view->errorContainer($order->getTransactionErrors(), "Braintree returned the following error(s):");
        }
    }


    /**
     * Processes an order instantly, without additional billing details or the usual notification/invoice stuff
     */
    public function staffCompletionAction()
    {
        // Make sure only staff are accessing this order completion method
        if (!$this->user->isStaff()) {
            $this->displayError("You do not have permission to instantly complete orders. This is a tool reserved for Fisdap staff.");
            return;
        }

        // Set order properties and process it
        $order = $this->orderRepository->getOneById($this->orderId);

        // Make sure we have an order and that it's not completed
        $this->requireValidOrder($order);

        $order->order_type = 3; // instant-staff-completion order type
        $order->staff_free_order = true;
        $order->name = $this->user->first_name . ' ' . $this->user->last_name; // recording basic info about the current staff user
        $order->email = $this->user->email;

        // Process completed order
        $order->process();
        $order->save();

        // view stuff
        $this->view->pageTitle = "Order Completed Instantly";
        $this->view->order = $order;
    }


    public function historyAction()
    {
        $this->permissionsCheck();

        $this->view->pageTitle = "Order History";
        $this->view->user = $this->user;

        // Make sure the user is an instructor
        if (!$this->user->isInstructor()) {
            $this->displayError("Only instructors are allowed to view this page.");
        }

        // Make sure the instructor has permission to order accounts
        if (!$this->user->hasPermission("Order Accounts")) {
            $this->displayError("You do not have permission to view the order history. Please contact " . $this->user->getCurrentProgram()->getProgramContactName() . " for more information.");
        }
    }


    public function getOrderHistoryListAction()
    {
        $this->permissionsCheck();

        $programId = $this->user->getProgramId();
        $params = $this->getAllParams();

        $this->_helper->json($this->view->orderHistoryList($programId, $params));
    }


    public function getOrderHistoryModalAction()
    {
        $this->session->unsetAll();
        $order = $this->orderRepository->getOneById($this->orderId);
        $this->_helper->json($this->view->partial("orderHistoryModal.phtml", ["order" => $order]));
    }


    public function duplicateOrderAction()
    {
        $this->permissionsCheck();

        $order = $this->orderRepository->getOneById($this->orderId);

        if ($order->isViewable($this->user)) {
            $newOrder = clone $order;
            $newOrder->completed = false;
            $newOrder->user = $this->user;
            $newOrder->save();

            $this->flashMessenger->addMessage("This order has been duplicated from Order #" . $order->id . ".");
            $this->redirect("/account/orders/cart/orderId/" . $newOrder->id);
        }

        $this->displayError("You are not allowed to duplicate this order.");
    }


    /**
     * @param ProductRepository $productRepository
     *
     * @throws Exception
     */
    public function upgradeAction(ProductRepository $productRepository)
    {
        // Check permissions but allow students to see this page (they'll just get rerouted)
        $this->permissionsCheck(true);

        $this->view->pageTitle = "Upgrade Student Accounts";
        $this->view->form = new Account_Form_Products(null);

        if ($this->user->isStaff()) {
            $this->view->isStaff = true;
        }

        // This is page is currently only for instructors
        if (!$this->userContext->isInstructor()) {
            $this->redirect("/account/orders/upgrade-individual");
        }

        $request = $this->getRequest();

        // Look to see if we already know who and what we want to upgrade
        $users = $request->getParam("users");
        $products = $request->getParam("products");
        if (!empty($users) && !empty($products)) {
            //Transform the product IDs into entities
            foreach ($products as $i => $productId) {
                $products[$i] = $productRepository->getOneById($productId);
            }

            // Create a new order and forward to the confirmation page
            $order = new Order();
            $order->user = User::getLoggedInUser();
            $order->order_type = 1;
            $order->upgrade_purchase = true;

            foreach ($users as $user) {
                $orderConfig = new OrderConfiguration();
                $orderConfig->upgraded_user = $user;

                // Only apply products that the user doesn't have OR ones that have multiple attempts
                foreach ($products as $product) {
                    if ($product->has_multiple_attempts || !($product->configuration & $orderConfig->upgraded_user->getCurrentUserContext()->getPrimarySerialNumber()->configuration)) {
                        $orderConfig->configuration += $product->configuration;
                    }
                }

                $orderConfig->certification_level = $orderConfig->upgraded_user->getCurrentUserContext()->getRoleData()->getCertification();
                $orderConfig->quantity = 1;

                // Only add the order configuration if we have products to upgrade for this user
                if ($orderConfig->configuration > 0) {
                    $order->addOrderConfiguration($orderConfig);
                }

                $orderConfig->calculateFinalPrice();
            }

            $order->save();
            $this->redirect("/account/orders/confirm-upgrade/orderId/" . $order->id);
        }

        if ($request->isPost()) {
            if ($configuration = $this->view->form->processUpgrade($request->getPost())) {
                $this->session->upgradeConfiguration = $configuration;
                $this->redirect("/account/orders/upgrade-students");
            }
        }
    }


    /**
     * @param OrderRepository $orderRepository
     *
     * @throws Exception
     */
    public function upgradeStudentsAction(OrderRepository $orderRepository)
    {
        $this->permissionsCheck();

        $this->view->pageTitle = "Upgrade accounts - which students are receiving the upgrade?";

        $postData = $this->getRequest()->getPost();
        $this->view->form = new Account_Form_UpgradeAccounts($this->session->upgradeConfiguration, $postData['studentIDs']);

        // allow product upgrade/downgrade table to be fluid width (very wide) for staff
        if ($this->user->isStaff()) {
            $this->view->fluidWidth = true;
            $this->view->isStaff = true;
        }

        $request = $this->getRequest();

        // todo - reduce nested conditionals
        if ($request->isPost()) {
            $response = $this->view->form->process($request->getPost());

            if (is_array($response) && $response['orderId']) {
                // if the data was valid, we'll get a valid order
                $order = $orderRepository->getOneById($response['orderId']);
                if ($order) {
                    $url = "/account/orders/confirm-upgrade/orderId/" . $response['orderId'];
                    if ($response['warning']) {
                        $url .= '/warning/' . $response['warning'];
                    }
                    $this->redirect($url);
                }
            } else {
                $this->view->errorMessage = is_array($response) ? $response['error'] : $response;
            }
        }
    }


    public function upgradeInstructorsAction()
    {
        $this->permissionsCheck();

        $this->view->pageTitle = "Upgrade Instructor Accounts";
        $this->view->form = new Account_Form_UpgradeInstructorAccounts(1572928);

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost())) {
                $this->redirect("/account/orders/cart");
            }
        }
    }


    /**
     * @param UserRepository $userRepository
     * @param ProductRepository $productRepository
     *
     * @throws Exception
     * @todo clean up/refactor ... too many nested conditionals
     */
    public function getUpgradeStudentListAction(UserRepository $userRepository, ProductRepository $productRepository)
    {
        $filters = $this->getAllParams();

        $programId = User::getLoggedInUser()->getProgramId();

        $students = $userRepository->getAllStudentsByProgram($programId, $filters);

        $returnData = [];
        $returnData['columns'] = array('Name');

        // is the current user a staff member?
        $isStaff = $this->user->isStaff();

        // Get products to display
        $products = $productRepository->getProducts($filters['configuration'], true);
        $cellNames = [];

        foreach ($products as $product) {
            if ($isStaff) {
                $returnData['columns'][] = $cellNames[$product->id] = $product->getShortName();
            } else {
                $returnData['columns'][] = $cellNames[$product->id] = $product->getFullName();
            }
        }

        foreach ($students as $student) {
            $atom = [];

            $atom['id'] = $student['id'];
            $atom['Name'] = $student['first_name'] . " " . $student['last_name'];

            foreach ($products as $product) {
                if ($isStaff) {
                    $downgradeCheckbox = "<span class='downgradeProduct'>" . $this->view->formCheckbox("downgradeProducts_" . $student['id'] . "[]", $product->configuration) . "</span>";
                    $reduceAttemptsCheckbox = "<span class='reduceAttemptProduct'>" . $this->view->formCheckbox("reduceAttemptProducts_" . $student['id'] . "[]", $product->configuration) . "</span>";
                }

                if (($product->configuration & $student['configuration']) && $product->has_multiple_attempts) {
                    // The student has this product already, and the product has multiple attempts
                    $productCell = "<span class='hasProduct'>" . $this->view->formCheckbox("products_" . $student['id'] . "[]", $product->configuration, array("checked" => true)) . '<img class="mini-checkmark" src="/images/check.png"></span>';
                    if ($isStaff) {
                        $productCell .= $downgradeCheckbox . $reduceAttemptsCheckbox;
                    }
                } elseif ($product->configuration & $student['configuration']) {
                    $productCell = '<img style="width: 1em;" src="/images/check.png">';
                    if ($isStaff) {
                        $productCell .= $downgradeCheckbox;
                    }
                } elseif ($product->configuration & $student['configuration_blacklist']) {
                    $productCell = "N/A";
                } else {
                    $productCell = $this->view->formCheckbox("products_" . $student['id'] . "[]", $product->configuration, array("checked" => true));
                }

                if ($isStaff) {
                    $atom[$cellNames[$product->id]] = "<div class='productInnerCell downgradable'>$productCell</div>";
                } else {
                    $atom[$cellNames[$product->id]] = "<div class='productInnerCell'>$productCell</div>";
                }
            }
            $returnData['students'][] = $atom;
        }

        $this->_helper->json($returnData);
    }


    public function upgradeIndividualAction(ProductRepository $productRepository, ProductService $productService)
    {
        // Check permissions and allow students to see this page
        $this->permissionsCheck(true);

        // This is page is currently only for students
        if ($this->userContext->isInstructor()) {
            $this->redirect("/account/orders/upgrade");
        }

        // Check to make sure this is not a transition course account
        $this->transitionCourseCheck();

        $thisStudent = $this->userContext->getRoleData();
        $certLevel = $thisStudent->getCertification();

        $this->view->pageTitle = "Upgrade " . $certLevel->description . " Account";

        $request = $this->getRequest();

        // if the form has been posted
        if ($request->isPost()) {
            $values = $request->getPost();
            $mode = $values['upgrade_mode'];
            $configuration = $values['upgradeConfig'];
            $couponCode = $values['appliedCoupon'];

            if ($mode == "choose") {
                $this->processUpgradeOrder($configuration, $couponCode);
            } else {
                if ($values['upgradeCodeType'] == "serial") {
                    $this->processSerialUpgrade($values['upgradeCode'], $productRepository);
                } else {
                    $this->processUpgradeOrder($configuration, $couponCode);
                }
            }
        } else {
            // if the form has not been posted, get some info to set up the form
            $currentConfig = $this->userContext->getPrimarySerialNumber()->configuration;
            $this->view->purchasedProducts = $productRepository->getProducts($currentConfig, true);

            // exclude black listed products, and transition course from the list of available products
            $excludedProductsConfig = ($certLevel->configuration_blacklist | 64);
            $availableProducts = $productRepository->getProducts($excludedProductsConfig, false, false, false, false, $thisStudent->program->profession->id);
            // just grab the product names and descriptions
            $productDetails = array();
            foreach ($availableProducts as $product) {
                $productDetails[] = array(
                    "name" => $product->name,
                    "description" => $product->description);
            }
            $this->view->availableProducts = $productDetails;

            // now get info about the products you can actually upgrade to
            $orphanStudyTools = ($thisStudent->program->id == 688);
            $this->view->upgradeableProducts = $productService->sortProductsForUpgrade($availableProducts, $this->user, $currentConfig, $orphanStudyTools);

            // get info about available packages
            $this->view->availablePackages = $productRepository->getAvailablePackages($certLevel, $currentConfig);

            // if the url contained an activation code, pass it to the view
            // we'll use javascript to give them the activation code tab, post code validation
            $this->view->activationCode = $this->_getParam("code");
        }
    }

    public function confirmUpgradeAction()
    {
        //Check permissions but allow students to see this page
        $this->permissionsCheck(true);

        $this->view->pageTitle = "Confirm Upgrade";
        $this->view->orderId = $this->orderId;
        $this->view->order = $order = $this->orderRepository->getOneById($this->orderId);
        $this->view->instructor = $this->userContext->isInstructor();
        $this->view->is_staff = $this->userContext->getUser()->isStaff();

        $warning = $this->_getParam('warning', false);
        if ($warning) {
            $this->view->warningText = $warning;
        }

        if ($this->user->isStaff()) {
            $this->view->enableInstantOrderCompletion = true;
        } else {
            $this->view->enableInstantOrderCompletion = false;
        }
    }

    public function upgradeConfirmationAction(ProductRepository $productRepository)
    {
        // Check permissions and allow students to see this page
        $this->permissionsCheck(true);

        // This is page is currently only for students
        if ($this->userContext->isInstructor()) {
            $this->redirect("/account/orders/upgrade");
        }

        $thisStudent = $this->userContext->getRoleData();
        $certLevel = $thisStudent->getCertification();

        $this->view->pageTitle = "Upgrade " . $certLevel->description . " Account";
        $this->view->user = $this->user;

        $currentConfig = $this->userContext->getPrimarySerialNumber()->configuration;
        $this->view->products = $productRepository->getProducts($currentConfig, true);
        $this->view->error = $this->getParam('error', false);
    }


    public function accountHoldersAction(ProductRepository $productRepository)
    {
        $this->permissionsCheck();

        $this->view->pageTitle = "Account Holders";
        $this->view->orderId = $this->orderId;
        $this->view->form = new Account_Form_AccountHolders($this->orderRepository, $productRepository, $this->orderId);

        if ($this->session->accountHoldersFormValues) {
            $this->view->form->populate($this->session->accountHoldersFormValues);
        }

        $request = $this->getRequest();

        // todo - reduce nested conditionals
        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost()) === true) {

                // Redirect directly to the cart if preceptor accounts are being purchased
                if ($this->view->form->isWizardDone()) {

                    // Clear the session and save the order ID
                    $orderId = $this->session->orderId;
                    $this->session->unsetAll();
                    $this->session->orderId = $orderId;

                    $this->redirect("/account/orders/cart");
                } else {
                    // Otherwise redirect to package selection
                    $this->redirect("/account/orders/packages");
                }
            }
        }
    }


    /**
     * @param ProductPackageRepository $productPackageRepository
     */
    public function packagesAction(ProductPackageRepository $productPackageRepository)
    {
        $this->permissionsCheck();

        $this->view->pageTitle = "Products: packages";
        $this->view->orderId = $this->orderId;
        $this->view->form = new Account_Form_Packages($this->orderId, $this->session->accountHoldersFormValues['certification']);

        // Redirect to account holders page if we haven't chosen any yet
        if (!$this->session->accountHoldersFormValues) {
            $this->redirect("/account/orders/account-holders");
        }

        // Redirect to products page if there are no packages available for this certification
        $packages = $productPackageRepository->findByCertification($this->session->accountHoldersFormValues['certification']);

        if (count($packages) == 0) {
            $this->redirect("/account/orders/products");
        }

        $this->view->studentsDescription = $this->getAccountHoldersDescription();

        // Get form values from the session
        if ($this->session->packagesFormValues) {
            $this->view->form->populate($this->session->packagesFormValues);
        }

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost()) === true) {
                $this->redirect("/account/orders/products");
            }
        }
    }


    /**
     * @param ProductPackageRepository $productPackageRepository
     */
    public function productsAction(ProductPackageRepository $productPackageRepository)
    {
        $this->permissionsCheck();

        $this->view->pageTitle = "Products: a la carte";
        $this->view->orderId = $this->orderId;

        // Figure out if a package has been chosen and display accordingly
        if ($this->session->packagesFormValues['packageId']) {
            $package = $productPackageRepository->getOneById($this->session->packagesFormValues['packageId']);
            $this->view->directions = "<p>You have already selected the {$package->name}. You can add any of the following products.</p>";
        } else {
            $this->view->directions = "You can use this page to create a custom package for your students.";
        }

        $this->view->form = new Account_Form_Products($this->session->accountHoldersFormValues['certification'], $package->configuration, $this->orderId);

        // Redirect to account holders page if we haven't chosen any yet
        if (!$this->session->accountHoldersFormValues) {
            $this->redirect("/account/orders/account-holders", array("exit" => true));
        }

        //If there are no products to display, complete order configuration and go to cart
        //if (empty($this->view->form->products)) {
        //	$this->_redirect("/account/orders/cart", array("exit" => true));
        //}

        // Add the current account holder selection to the view
        $this->view->studentsDescription = $this->getAccountHoldersDescription();

        $request = $this->getRequest();

        if ($request->isPost()) {
            if ($this->view->form->process($request->getPost()) === true) {
                $this->redirect("/account/orders/cart");
            }
        }
    }


    /**
     * Display an order summary of what's been chosen so far
     */
    public function cartAction()
    {
        $this->permissionsCheck();

        $this->view->order = $order = $this->orderRepository->getOneById($this->orderId);

        // Make sure we have an order and that it's not completed
        $this->requireValidOrder($order);

        $this->view->pageTitle = "Order Summary";
        $this->view->orderId = $this->orderId;
        $this->view->user = $this->user;
    }


    /**
     * Cancels a user's order
     *
     * Grab the order from the session, then delete it from the database.
     * Then clear out the session and redirect to the order landing page.
     */
    public function cancelOrderAction()
    {
        $this->permissionsCheck();

        $order = $this->orderRepository->getOneById($this->orderId);

        if (!$order->isEditable()) {
            $this->displayError("You are not allowed to cancel this order.");
            return;
        }

        if ($order->id) {
            $order->delete();
            $this->session->unsetAll();
            $this->flashMessenger->addMessage("Your order has been cancelled.");
        }
        $this->redirect("/account/orders/");
    }


    /**
     * Display a receipt-like confirmation of the product codes that have been generated.
     * Mark the order as completed and process the the "order"
     */
    public function productCodeConfirmationAction()
    {
        $this->permissionsCheck();

        $this->view->pageTitle = "Product Code Confirmation";
        $this->view->order = $this->orderRepository->getOneById($this->orderId);
        $this->session->unsetAll();

        $this->view->modal = new Account_Form_SendProductCodeModal();

        //Process the product codes if they haven't been already
        if ($this->view->order->completed == false) {
            $this->view->order->processProductCodes();
            $this->view->order->save();
        }
    }


    /**
     * Display a receipt of the order, then clear out the session since this order is done.
     */
    public function receiptAction()
    {
        // Check permissions but allow students to see this page
        $this->permissionsCheck(true);

        $this->view->pageTitle = "Receipt";
        $this->view->order = $this->orderRepository->getOneById($this->orderId);
        $this->session->unsetAll();

        // Make sure the user is allowed to view this receipt
        if (!$this->view->order->isViewable()) {
            $this->displayError("You are not allowed to view this receipt.");
            return;
        }
    }


    public function inventoryAction()
    {
        // Do not allow students to see this page, but allow the appropriate instructors in, even if their program can't order
        $this->permissionsCheck(false, true);

        $this->view->pageTitle = "Inventory";
        $this->view->form = new Account_Form_Inventory();
        $this->view->emailModal = new Account_Form_EmailActivationCodesModal();
        $this->view->certModal = new Account_Form_EditCertificationModal();
        $this->view->gradGroupModal = new Account_Form_EditGradGroupModal();
    }


    public function updateGradGroupsAction()
    {
        $formValues = $this->getAllParams();
        $data = $formValues['data'];
        $codes = $formValues['codes'];
        $form = new \Account_Form_EditGradGroupModal();
        $this->_helper->json($form->process($data, $codes));
    }
    public function deleteGradGroupsAction()
    {
        $formValues = $this->getAllParams();
        $data = $formValues['data'];
        $codes = $formValues['codes'];
        foreach ($codes as $code) {
            $serial = SerialNumberLegacy::getBySerialNumber($code);
            $serial->delete();
        }
        $this->_helper->json(true);
    }

    public function sendEmailsAction()
    {
        $params = $this->getAllParams();
        $serials = $params['data'];

        foreach ($serials as $serial) {
            $email = $serial['email'];
            $serial = SerialNumberLegacy::getBySerialNumber($serial['sn']);
            $serial->distribution_date = new \DateTime();
            $serial->distribution_email = $email;
            $serial->save();

            $mail = new \Fisdap_TemplateMailer();
            $upgradeable = !($serial->hasTransitionCourse() || $serial->isInstructorAccount());
            $mail->addTo($email)
                ->setSubject("Invitation to create a Fisdap account")
                ->setViewParam('serial', $serial)
                ->setViewParam('orderer', $programId = User::getLoggedInUser())
                ->setViewParam('urlRoot', Util_HandyServerUtils::getCurrentServerRoot())
                ->setViewParam('message', $params['message'])
                ->setViewParam('upgradeable', $upgradeable)
                ->sendHtmlTemplate('create-account-invitation.phtml');
        }

        $this->_helper->json(true);
    }


    /**
     * @param ProductRepository $productRepository
     */
    public function generateModalAction(ProductRepository $productRepository)
    {
        $params = $this->getAllParams();
        $codes = $params['codes'];
        $returnArray = [];

        foreach ($codes as $code) {
            $codeObject = SerialNumberLegacy::getBySerialNumber($code);
            $products = $productRepository->getProducts($codeObject->configuration, true, true);
            $returnArray[] = [
                "number" => $code,
                "products" => $products
            ];
        }

        $this->_helper->json($returnArray);
    }


    public function getInventoryAction()
    {
        $params = $this->getAllParams();
        $filters = $params['formValues'];

        $filters['gradYear'] = ($filters['gradYear'] == '0') ? null : $filters['gradYear'];
        $filters['gradMonth'] = ($filters['gradMonth'] == '0') ? null : $filters['gradMonth'];
        $filters['section'] = ($filters['section'] == '0') ? null : $filters['section'];
        $filters['status'] = [
            "available" => $filters['available'],
            "activated" => $filters['activated'],
            "distributed" => $filters['distributed']
        ];

        $this->_helper->json($this->view->inventoryList($filters));
    }


    private function getAccountHoldersDescription()
    {
        if (!$this->session->accountHoldersFormValues) {
            return null;
        }

        $accountValues = $this->session->accountHoldersFormValues;
        $certification = $this->certificationLevelRepository->getOneById($accountValues['certification']);
        $descPieces = array($certification->description . ($accountValues['quantity_students'] > 1 ? "s" : ""));

        if ($accountValues['gradDate']['year'] && $accountValues['gradDate']['month']) {
            $descPieces[] = "graduating " . $accountValues['gradDate']['month'] . "/" . $accountValues['gradDate']['year'];
        }

        if ($accountValues['group']['id']) {
            $group = $this->classSectionLegacyRepository->getOneById($accountValues['group']['id']);
            $descPieces[] = "in " . $group->name;
        }

        return implode(", ", $descPieces);
    }


    /**
     * AJAX handler to output preview text for an email with product code info.
     * This function is used in the cartAction()
     */
    public function previewProductCodeEmailAction(ProductService $productService)
    {
        $params = $this->getAllParams();
        $productCode = ProductCode::getByProductCode($params['productCode']);
        $configuration = $productCode->order_configuration->configuration;
        $upgradeable = !($configuration == $productService::PRECEPTOR_TRAINING_CONFIG ||
            $configuration == $productService::PARAMEDIC_TRANSITION_CONFIG ||
            $configuration == $productService::EMTB_TRANSITION_CONFIG ||
            $configuration == $productService::AEMT_TRANSITION_CONFIG);
        $viewParams = [
            "productCode" => $productCode,
            "orderer" => $productCode->order_configuration->order->user,
            "urlRoot" => Util_HandyServerUtils::getCurrentServerRoot(),
            "message" => $params['message'],
            "upgradeable" => $upgradeable
        ];

        $this->view->addScriptPath(APPLICATION_PATH . "/views/scripts/email-templates/");
        $invite = $this->view->partial("purchase-account-invitation.phtml", $viewParams);

        // Add the email signature to the email
        $mail = new \Fisdap_TemplateMailer();
        $invite .= $mail->getDefaultSignature("<br>");
        $this->_helper->json("<div class='preview-email'>" . $invite . "</div>");
    }


    public function processPaymentMethodAction()
    {
        $params = $this->getAllParams();

        $order = $this->orderRepository->getOneById($params['orderId']);
        $order->payment_method = $params['paymentMethod'];
        $order->save();

        $this->_helper->json(true);
    }


    /**
     * @param OrderConfigurationRepository $orderConfigurationRepository
     */
    public function deleteOrderConfigurationAction(OrderConfigurationRepository $orderConfigurationRepository)
    {
        $configId = $this->getParam('orderConfigurationId');

        /** @var OrderConfiguration $orderConfig */
        $orderConfig = $orderConfigurationRepository->getOneById($configId);

        $order = $orderConfig->order;

        $order->removeOrderConfiguration($orderConfig);
        $order->calculateTotal();
        $orderConfig->delete();

        $this->_helper->json(true);
    }


    /**
     * @param OrderConfigurationRepository $orderConfigurationRepository
     */
    public function updateOrderConfigurationAction(OrderConfigurationRepository $orderConfigurationRepository)
    {
        $quantity = $this->getParam('quantity');
        $configId = $this->getParam('orderConfigurationId');

        $orderConfig = $orderConfigurationRepository->getOneById($configId);
        $orderConfig->quantity = $quantity;
        $orderConfig->calculateSubtotal();
        $orderConfig->save();

        $this->_helper->json(true);
    }


    /**
     * @param ProductPackageRepository $productPackageRepository
     */
    public function calculatePackagePriceAction(ProductPackageRepository $productPackageRepository)
    {
        $params = $this->getAllParams();

        $package = $productPackageRepository->getOneById($params['packageId']);
        $price = $package->getPrice($params['certification'], $params['limited']);
        $config = $package->getConfiguration($params['certification'], $params['limited']);


        $this->_helper->json(['price' => $price, 'configuration' => $config]);
    }


    /**
     * @param ProductRepository $productRepository
     */
    public function calculateProductPriceAction(ProductRepository $productRepository)
    {
        $params = $this->getAllParams();
        $product = $productRepository->findOneByConfiguration($params['configuration']);

        $this->_helper->json($product->getDisplayPrice($params['limited']));
    }

    public function generateProductSubformAction()
    {
        $mode = $this->getParam('mode', 'productCode');
        $counter = $this->getParam('counter', 1);

        $form = new Account_Form_Products(null, $mode, $counter);

        $this->_helper->json($form->__toString());
    }


    public function validateCouponAction()
    {
        $code = $this->getParam('code');
        $order = $this->orderRepository->getOneById($this->session->orderId);

        if ($coupon = Coupon::isValidCoupon($code)) {
            $order->applyCoupon($coupon->id);
            $order->save();
            $this->flashMessenger->addMessage('Coupon successfully applied.');
            $this->_helper->json(true);
        } else {
            $this->_helper->json(false);
        }
    }

    public function isCouponValidAction()
    {
        $code = $this->_getParam("code");
        $this->_helper->json(\Fisdap\Entity\Coupon::isValidCoupon($code));
    }

    public function sendProductCodeAction()
    {
        $formValues = $this->getAllParams();
        $form = new \Account_Form_SendProductCodeModal();
        $this->_helper->json($form->process($formValues));
    }


    public function viewInvoiceAction()
    {
        $this->view->pageTitle = 'View Invoice';
        $order = $this->orderRepository->getOneById($this->orderId);

        $this->permissionsCheck();

        // Check to make sure this user "owns" this invoice.
        if ($this->user->getCurrentProgram()->id != $order->program->id) {
            $this->displayError('You do not have permission to view this invoice.');
            return;
        }

        if (!$order->invoice_number) {
            $order->generateInvoiceNumber();
            $order->save();
        }

        $this->view->invoice = $this->view->partial("order-invoice.phtml", ["order" => $order]);
    }


    /**
     * @param Order|null $order
     */
    private function requireValidOrder(Order $order = null)
    {
        if (is_null($order)) {
            $this->redirect("/account/orders/");
        }

        if (!$order->isEditable()) {
            $this->session->unsetAll();
            $this->redirect("/account/orders/");
        }
    }


    /**
     * Make sure this user can view this page
     *
     * @param bool $studentsAllowed allow students to see this page
     * @param bool $allProgramsAllowed all users from programs that cannot order accounts to see this page
     * @throws Exception
     */
    private function permissionsCheck($studentsAllowed = false, $allProgramsAllowed = false)
    {
        // Don't allow students onto this page unless explicitly stated
        if (!$this->userContext->isInstructor() && !$studentsAllowed) {
            $this->displayError("Students are not allowed to view this page.");
            return;
        }

        // Check program permissions, if applicable; staff members can order accounts for programs that otherwise can't
        if (!$allProgramsAllowed &&
            !$this->user->isStaff() &&
            $this->userContext->getProgram()->order_permission->name == "Cannot Order Accounts"
        ) {
            $this->displayError("You do not have permission to view or place orders.");
            return;
        }

        // Check instructor permissions
        if ($this->userContext->isInstructor() && !$this->userContext->getRoleData()->hasPermission("Order Accounts")) {
            $this->displayError("You do not have permission to view or place orders. Please contact " . $this->userContext->getProgram()->getProgramContactName() . " for more information.");
            return;
        }
    }

    /**
     * Make sure this is not a transition course user
     *
     */
    private function transitionCourseCheck()
    {
        if ($this->userContext->getPrimarySerialNumber()->hasTransitionCourse()) {
            $code = $this->_getParam("code");
            if ($code) {
                $link = "/account/new/index/code/$code";
                $errorMsg = "That activation code cannot be used with a Transition Course account, but you can use it ".
                    "to <a href='$link'>create a new account</a>.";
            } else {
                $link = "/account/new";
                $errorMsg = "Transition Course accounts cannot be upgraded, but you can ".
                    "<a href='$link'>create a new account</a>.";
            }
            $this->displayError($errorMsg);
            return;
        }
    }

    /**
     * Validate a product code or serial number for a given student to use to upgrade their account.
     *
     * @param ProductRepository $productRepository
     * @param ProductService    $productService
     */
    public function validateActivationCodeAction(ProductRepository $productRepository, ProductService $productService)
    {
        $code = $this->_getParam("code");
        $thisStudent = $this->userContext->getRoleData();
        $accountData = false;
        $error = true;

        // if it's a valid serial number
        if (SerialNumberLegacy::isSerialFormat($code) && SerialNumberLegacy::getBySerialNumber($code)) {
            $serial = SerialNumberLegacy::getBySerialNumber($code);

            if ($serial->isActive()) {
                // make sure this serial number hasn't been used
                $html = "Uh-oh! '$code' is already in use. Please ask your instructor for a new activation code.";
            } else {
                $accountData = $serial->getAccountDetails();
                $codeType = "serial";
            }
        } elseif (ProductCode::getByProductCode($code) || ProductCode::isLegacyProductCode($code)) {
            // if it's a valid product code
            $productCode = ProductCode::getByProductCode($code);

            // get account data about this code
            if ($productCode) {
                $accountData = $productCode->getAccountDetails();
            } else {
                $accountData = ProductCode::getAccountFromLegacyProductCode($code);
            }
            $codeType = "product";
        } else {
            // it's not a valid code
            $html = "Uh-oh! That activation code isn't valid. Please make sure you entered it correctly, or talk to your instructor.";
        }

        // if we've gotten an account associated with this code, continue validation to make sure it works for this student
        if ($accountData) {
            $thisCert = $thisStudent->getCertification();
            $thisProgram = $thisStudent->program;

            // in case we need to redirect to create a new account
            $logoutLink = "/login/logout?redirect=$codeType&code=$code";

            // make sure the programs match
            if ($thisProgram->id != $accountData['programId']) {
                $action = "<a href='$logoutLink'>create a new account</a>";

                $html = "We're sorry! That activation code cannot be used with an account at " . $thisProgram->name . ", ";
                $html .= "but you can use it to $action at " . $accountData['programName'] . ".";
            } elseif ($thisCert->id != $accountData['certId']) {
                // make sure the cert levels match
                $newCert = ($accountData['cert']) ? $accountData['cert'] : "instructor";
                $action = "<a href='$logoutLink'>create a new $newCert account</a>";

                $html = "We're sorry! That activation code cannot be used with your " . $thisCert->description . " account, ";
                $html .= "but you can use it to $action.";
            } elseif (
                ($accountData['configuration'] & $productService::PARAMEDIC_TRANSITION_CONFIG) ||
                ($accountData['configuration'] & $productService::EMTB_TRANSITION_CONFIG) ||
                ($accountData['configuration'] & $productService::AEMT_TRANSITION_CONFIG)) {
                // make sure this is not a transition course code
                $action = "<a href='$logoutLink'>create a new ".$accountData['cert']." Transition Course account</a>";

                $html = "We're sorry! That activation code cannot be used with your account, ";
                $html .= "but you can use it to $action.";
            } else {
                $data = $accountData;

                $productService = new ProductService();
                $upgradedProducts = $productRepository->getProducts($accountData['configuration'], true);
                $currentConfig = $this->userContext->getPrimarySerialNumber()->configuration;
                $sortedProducts = $productService->sortProducts($upgradedProducts, $this->user, $currentConfig);
                $showPrice = ($codeType == "product");

                if ($sortedProducts['upgradeableCount'] > 0) {
                    $html = $this->view->upgradeSummary($sortedProducts['products'], $showPrice);

                    if ($codeType == "serial") {
                        $html .= "<div class='extra-small green-buttons bottom-link'><a id='applySerial' href='#'>Upgrade account</a></div>";
                    }

                    $error = false;
                } else {
                    $html = "Oops! Your account already has access to all the products included in this code. ";
                    $html .= "Please check with your instructor to see if you need a new one.<br>";
                }
            }
        }

        $response['error'] = $error;
        $response['data'] = $data;
        $response['html'] = $html;
        $response['codeType'] = $codeType;
        $this->_helper->json($response);
    }

    /**
     * @param $configuration
     * @param $couponCode
     * @throws Exception
     */
    private function processUpgradeOrder($configuration, $couponCode)
    {
        // Create new order for upgrade
        $order = new Order();
        $order->user = $this->user;
        $order->payment_method = 2;
        $order->upgrade_purchase = true;
        $order->individual_purchase = true;
        $order->save();

        // Create order config for upgraded user
        $orderConfig = new OrderConfiguration();
        $orderConfig->upgraded_user = $order->user;
        $orderConfig->configuration = $configuration;
        $orderConfig->certification_level = $this->userContext->getRoleData()->getCertification();
        $orderConfig->quantity = 1;

        // add coupon, if any
        if ($coupon = Coupon::isValidCoupon($couponCode)) {
            $order->applyCoupon($coupon->id);
        }

        // Add order config to order, calculate price, and save
        $order->addOrderConfiguration($orderConfig);
        $orderConfig->calculateFinalPrice();
        $order->save();

        // make this order the one in the session
        $this->session->orderId = $order->id;

        // Redirect to confirmation page
        $this->redirect("/account/orders/billing");
    }

    /**
     * Use a serial number to upgrade a student's account
     *
     * @param                   $serialNumber
     * @param ProductRepository $productRepository
     *
     * @throws Zend_Mail_Exception
     */
    private function processSerialUpgrade($serialNumber, ProductRepository $productRepository)
    {
        // if it's a valid serial number
        if (SerialNumberLegacy::isSerialFormat($serialNumber) && SerialNumberLegacy::getBySerialNumber($serialNumber)) {
            $upgradeSerial = SerialNumberLegacy::getBySerialNumber($serialNumber);

            // make sure it's not in use before doing the upgrade
            if (!$upgradeSerial->isActive()) {
                $originalSerial = $this->userContext->getPrimarySerialNumber();

                // first go through and do any exam attempt increases
                $upgradeProducts = $productRepository->getProducts($upgradeSerial->configuration, true);
                foreach ($upgradeProducts as $product) {
                    if (($product->configuration & $originalSerial->configuration) && $product->has_multiple_attempts) {
                        $product->modifyTestAttempts($this->user->id, '+');
                    }
                }

                // get some info for the confirmation email
                $productService = new ProductService();
                $upgradedProducts = $productRepository->getProducts($upgradeSerial->configuration, true);
                $sortedProducts = $productService->sortProducts($upgradedProducts, $this->user, $originalSerial->configuration);

                // then merge the new serial number into the old one
                $error = ($originalSerial->mergeSerialNumbers($upgradeSerial)) ? false : true;
                $errorUrl = ($error) ? "error/true" : "";

                // if we were successful, send the email
                if (!$error) {
                    $mail = new \Fisdap_TemplateMailer();
                    $mail->addTo($this->user->email)
                        ->setSubject("Your Fisdap account has been upgraded")
                        ->setViewParam('name', $this->user->first_name)
                        ->setViewParam('products', $sortedProducts['products'])
                        ->sendHtmlTemplate('account-upgrade-serial.phtml');
                }

                $this->redirect("/account/orders/upgrade-confirmation/$errorUrl");
            }
        }
        $this->redirect("/account/orders/upgrade-confirmation/error/true");
    }
}
