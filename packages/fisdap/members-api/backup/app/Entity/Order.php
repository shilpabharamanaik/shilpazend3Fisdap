<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;
use Fisdap\MoodleUtils;

/**
 * Entity class for Orders
 *
 * @Entity(repositoryClass="Fisdap\Data\Order\DoctrineOrderRepository")
 * @Table(name="fisdap2_orders")
 * @HasLifecycleCallbacks
 */
class Order extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var User
     * @ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @var \Fisdap\Entity\ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @var string the program's name as entered on the billing
     * form, it defaults to the logged in user's name
     * @Column(type="string", nullable=true)
     */
    protected $program_name;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $fax;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $address1;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $address2;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $address3;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $city;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $state;

    /**
     * @var integer
     * @Column(type="string", nullable=true)
     */
    protected $zip;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $country;

    /**
     * @var \Fisdap\Entity\InvoiceDeliveryMethod
     * @ManyToOne(targetEntity="InvoiceDeliveryMethod")
     */
    protected $invoice_delivery_method;

    /**
     * @var \Fisdap\Entity\PaymentMethod
     * @ManyToOne(targetEntity="PaymentMethod")
     */
    protected $payment_method;

    /**
     * @var \Fisdap\Entity\OrderType
     * @ManyToOne(targetEntity="OrderType")
     */
    protected $order_type;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $po_number;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $paypal_transaction_id;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $completed = 0;

    /**
     * @var integer
     * @Column(type="decimal", scale=2, precision=12)
     */
    protected $total_cost = 0;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $individual_purchase = false;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $upgrade_purchase = false;

    /**
     * @var \Fisdap\Entity\Coupon
     * @ManyToOne(targetEntity="Coupon")
     */
    protected $coupon;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $order_date;

    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $staff_free_order = false;

    /**
     * @var bool|null
     * @Column(type="boolean", nullable=true)
     */
    protected $accounting_processed;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $invoice_number;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="OrderConfiguration", mappedBy="order", cascade={"persist","remove"})
     */
    protected $order_configurations;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="SerialNumberLegacy", mappedBy="order")
     */
    protected $serial_numbers;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="OrderTransaction", mappedBy="order")
     * @OrderBy({"timestamp" = "DESC"})
     */
    protected $order_transactions;

    public function __clone()
    {
        if ($this->id) {
            //Reset the fields that shouldn't be duplicated
            $this->id = null;

            //Get associations before clearing
            $order_configurations = $this->order_configurations;

            //Reinitialize associations
            $this->init();

            //Get rid of the coupone if there was one
            $this->coupon = null;

            //Re-add old associations but to the new entity
            foreach ($order_configurations as $config) {
                //Don't copy configurations that were "free"
                if (!$config->free_accounts) {
                    $newConfig = clone($config);
                    $this->addOrderConfiguration($newConfig);
                    $newConfig->calculateFinalPrice();
                }
            }
        }
    }

    public function init()
    {
        $this->order_configurations = new ArrayCollection();
        $this->order_transactions = new ArrayCollection();
        $this->serial_numbers = new ArrayCollection();
        $this->order_date = new \DateTime();
        $this->completed = false;
        $this->coupon = null;
        $this->payment_method = null;
    }

    public function set_order_type($value)
    {
        $this->order_type = self::id_or_entity_helper($value, "OrderType");
    }

    public function set_payment_method($value)
    {
        $this->payment_method = self::id_or_entity_helper($value, "PaymentMethod");
    }

    public function set_invoice_delivery_method($value)
    {
        $this->invoice_delivery_method = self::id_or_entity_helper($value, "InvoiceDeliveryMethod");
    }

    public function set_user($value)
    {
        $this->user = self::id_or_entity_helper($value, "User");
        $this->program = $this->user->getCurrentProgram();
        $this->program_name = substr($this->program->name, 0, 41);
    }

    public function set_program($value)
    {
        $this->program = self::id_or_entity_helper($value, "ProgramLegacy");
    }

    /**
     * Set the flag for whether or not this order is free
     * Also physically change the price of the order
     *
     * @param boolean $value
     */
    public function set_staff_free_order($value)
    {
        $this->staff_free_order = $value;
        if ($this->staff_free_order) {
            foreach ($this->order_configurations as $config) {
                $config->individual_cost = 0;
                $config->calculateSubtotal();
            }
        }
    }


    /**
     * @return bool|null
     */
    public function isAccountingProcessed()
    {
        return $this->accounting_processed;
    }


    /**
     * @param bool|null $accounting_processed
     */
    public function setAccountingProcessed($accounting_processed)
    {
        $this->accounting_processed = $accounting_processed;
    }


    /**
     * Set the configurations for this order
     *
     * @param $configs array with the following keys: 'configuration', 'configuration_limits', 'group', 'graduation_date', 'certification', 'quantity', 'subtotal'
     * @return \Fisdap\Entity\Order
     */
    public function set_order_configurations($configs)
    {
        $this->order_configurations->clear();

        foreach ($configs as $config) {
            $ent = new OrderConfiguration();
            $ent->configuration = $config['configuration'];
            $ent->configuration_limits = $config['configuration_limits'];
            $ent->certification_level = $config['certification'];
            $ent->group = $config['group'];
            $ent->graduation_date = $config['graduation_date'];
            $ent->quantity = $config['quantity'];
            $ent->subtotal_cost = $config['subtotal'];
            $ent->order = $this;
            $this->order_configurations->add($ent);
        }

        return $this;
    }

    /**
     * Add an order configuration to this order
     *
     * @param \Fisdap\Entity\OrderConfiguration
     * @return \Fisdap\Entity\Order
     */
    public function addOrderConfiguration(OrderConfiguration $config)
    {
        $this->order_configurations->add($config);
        $config->order = $this;

        return $this;
    }

    /**
     * Remove an order configuration from this order
     *
     * @param \Fisdap\Entity\OrderConfiguration
     * @return \Fisdap\Entity\Order
     */
    public function removeOrderConfiguration(OrderConfiguration $config)
    {
        $this->order_configurations->removeElement($config);
        $config->order = null;

        return $this;
    }

    /**
     * Apply a coupon to this order and recalculate the subtotals
     * @param integer $couponId
     * @return void
     */
    public function applyCoupon($couponId)
    {
        $this->coupon = self::id_or_entity_helper($couponId, "Coupon");
        foreach ($this->order_configurations as $config) {
            $config->calculateFinalPrice();
        }
        $this->save();
    }

    /**
     * Remove a coupon from this order and recalculate the subtotals
     * @return void
     */
    public function removeCoupon()
    {
        $this->coupon = null;
        foreach ($this->order_configurations as $config) {
            $config->calculateFinalPrice();
        }
        $this->save();
    }

    /**
     * Get the full price of this order not account for discounts or coupons of any kind
     * @return string formatted as currency
     */
    public function getFullCost()
    {
        $price = 0;

        foreach ($this->order_configurations as $config) {
            $products = EntityUtils::getRepository("Product")->getProducts($config->configuration, true);
            foreach ($products as $product) {
                $price += $product->price * $config->quantity;
            }
        }

        return number_format($price, 2, ".", ",");
    }

    /**
     * Get the price of this order not accounting for coupons
     * @return string formatted as currency
     */
    public function getSubtotal()
    {
        $price = 0;

        foreach ($this->order_configurations as $config) {
            $products = EntityUtils::getRepository("Product")->getProducts($config->configuration, true);
            foreach ($products as $product) {
                $price += $product->getDiscountedPrice($this->program->id) * $config->quantity;
            }
        }

        return number_format($price, 2, ".", ",");
    }

    /**
     * Get the cost of this order formatted as currency
     * @return string
     */
    public function getTotalCost()
    {
        return number_format($this->total_cost, 2, ".", ",");
    }

    /**
     * Get the sum of all accounts attached to this order
     * @return integer
     */
    public function getTotalQuantity()
    {
        $quantity = 0;
        foreach ($this->order_configurations as $config) {
            $quantity += $config->quantity;
        }
        return $quantity;
    }

    /**
     * Does this order have at least one serial number that has not yet been activated?
     * @return boolean
     */
    public function hasUnactivatedAccounts()
    {
        foreach ($this->serial_numbers as $serial) {
            if (!$serial->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @PreUpdate
     * @PrePersist
     */
    public function calculateTotal()
    {
        $this->total_cost = 0;

        foreach ($this->order_configurations as $config) {
            $this->total_cost += $config->subtotal_cost;
        }
    }

    /**
     * Parse a product code and add the corresponding products to this order
     *
     * @param string $code the Product Code to add
     * @return \Fisdap\Entity\Order
     */
    public function addProductCode($code)
    {
        //$pieces = preg_split("/-/", $code);
        //$programId = preg_replace("/\D/", "", $pieces[0]);
    }

    /**
     * Get the billing address formatted as HTML
     *
     * @return string
     */
    public function getBillingAddress($includeOrdererDetails = false)
    {
        $address = "";

        if ($includeOrdererDetails) {
            $address .= $this->name . "<br>";
            $address .= ($this->program_name ? $this->program_name : $this->program->name) . "<br>";
        }

        $address .= $this->address1 . "<br>";
        $address .= $this->address2 ? $this->address2 . "<br>" : "";
        $address .= $this->address3 ? $this->address3 . "<br>" : "";
        $address .= $this->city . ", " . $this->state . " " . $this->zip;

        return $address;
    }

    /**
     * Parse the name field to pull out the first name
     *
     * @return string
     */
    public function getFirstName()
    {
        //Parse the name and set it
        $namePieces = preg_split("/\s/", $this->name);
        return $namePieces[0];
    }

    /**
     * Parse the name field to pull out the middle name if one exists
     *
     * @return string
     */
    public function getMiddleName()
    {
        //Parse the name and set it
        $namePieces = preg_split("/\s/", $this->name);
        if (count($namePieces) == 3) {
            return $namePieces[1];
        }
        return null;
    }

    /**
     * Parse the name field to pull out the last name
     *
     * @return string
     */
    public function getLastName()
    {
        //Parse the name and set it
        $namePieces = preg_split("/\s/", $this->name);
        if (count($namePieces) == 2) {
            return $namePieces[1];
        } elseif (count($namePieces) == 3) {
            return $namePieces[2];
        }
        return null;
    }

    /**
     * Get the full name of the person who placed this order
     * If it was an account made by staff, just list "Fisdap Staff"
     * If there was no user account when the order was placed, return the billing name
     */
    public function getOrdererName()
    {
        if ($this->staff_free_order) {
            return "Fisdap Staff";
        }

        if ($this->user) {
            if ($this->user->isStaff()) {
                return "Fisdap Staff";
            }
            return $this->user->getName();
        }

        return $this->name;
    }

    /**
     * Check correct permissions to view a given order
     *
     * @param User $user the user to check permissions
     *
     * @return boolean
     */
    public function isViewable($user = null)
    {
        if (!$user) {
            $user = User::getLoggedInUser();
        }

        if ($user->isInstructor()) {
            //Instructors must have order accounts permissions and belong to the program for this order
            $viewable = $user->hasPermission("Order Accounts");
            $viewable = $viewable && ($user->getProgramId() == $this->program->id);
        } else {
            //Students can only look at their own order
            $viewable = $this->individual_purchase;
            $viewable = $viewable && ($this->user->id == $user->id);
        }


        return $viewable;
    }

    /**
     * Check correct permissions to edit a given order
     *
     * @param User $user the user to check permissions
     *
     * @return boolean
     */
    public function isEditable($user = null)
    {
        if (!$user) {
            $user = User::getLoggedInUser();
        }

        $editable = $this->isViewable($user);
        $editable = $editable && ($this->completed == false);

        return $editable;
    }

    /**
     * Does this order have any pilot testing accounts
     *
     * @return boolean
     */
    public function hasPilotTestingAccounts()
    {
        foreach ($this->order_configurations as $config) {
            if ($config->configuration & 16384) {
                return true;
            }
        }
        return false;
    }

    /**
     * Add an extra configuration (with just preceptor training and no cost) to this order
     * Called if a configuration has preceptor training and additional products
     * @return void
     */
    public function addPreceptorTrainingConfig($quantity)
    {
        //Create new order configuration
        $orderConfig = EntityUtils::getEntity("OrderConfiguration");
        $orderConfig->configuration = 64;
        $orderConfig->quantity = $quantity;
        // will always be 0 since it's already been paid for
        $orderConfig->individual_cost = 0;
        $orderConfig->free_accounts = true;

        //Attach this order configuration to the existing order
        $this->addOrderConfiguration($orderConfig);
        $this->save();
    }


    /**
     * Get an array of emails entered for this order
     * @return array
     */
    public function getBillingEmailArray()
    {
        $emails = str_replace(";", ",", $this->email);
        return explode(',', $emails);
    }

    /**
     * Determine if this order is consider free. To qualify as free, one of three things must be true:
     * 1). The order was marked as free by staff
     * 2). The order was account changes made by staff
     * 3). The actual total of the order was free due to discounts
     * @return bool
     */
    public function isFreeOrder()
    {
        return $this->staff_free_order || $this->order_type->id == 3 || $this->total_cost == 0;
    }

    /**
     * Process a completed order
     * @return void
     */
    public function process()
    {
        // check order configurations for funny PT case
        foreach ($this->order_configurations as $config) {
            $products = $config->getProductArray(true);
            $hasPreceptorTraining = in_array("9", $products);

            // we found preceptor training and other products
            //(we need an addtional serial number, therefor an additional config)
            if (count($products) > 1 && $hasPreceptorTraining && !$this->upgrade_purchase) {
                $this->addPreceptorTrainingConfig($config->quantity);
            }
        }

        //Mark order as completed
        $this->completed = true;
        $this->order_date = new \DateTime();

        //Either apply upgrades or generate new serial numbers
        if ($this->upgrade_purchase) {
            $this->applyUpgradesAndDowngrades();
        } else {
            $this->generateSerialNumbers();
        }

        //Send email to interested parties, unless this is an instant order by Staff
        if ($this->order_type->id != 3) {
            $this->emailSerialNumbers();
        }

        //Generate invoice number and email the invoice if appropriate
        if ($this->payment_method->id == 1 && !$this->isFreeOrder()) {
            $this->generateInvoiceNumber();
            $this->emailInvoicePdf();
        }
        
        $session = new \Zend_Session_Namespace("OrdersController");
        $session->unsetAll();
    }

    /**
     * Generate and email a PDF of this invoice to the billing email
     *
     * @return void
     */
    public function emailInvoicePdf()
    {
        $view = \Zend_Layout::getMvcInstance()->getView();
        $view->addScriptPath(__DIR__ . "/../../application/modules/account/views/scripts");

        // generate the pdf
        // @todo ALERT! This code is not gonna work in MRAPI! only in the context of Members!
        $container = \Zend_Registry::get('container');
        $pdfGenerator = $container->make('Fisdap\Service\DataExport\PdfGenerator', array(\Zend_Registry::get('logger')));
        //$pdfOptions = array();
        $pdfGenerator->setOrientation("portrait");
        //$pdfOptions['contentEncoded'] = false;
        $pdfGenerator->setFilename("invoice.pdf");
        //Obsolete: $pdfOptions['htmlHead'] = \Util_PdfGenerationHelper::formatForPdf(\Util_PdfGenerationHelper::getHtmlHead($view, array("/css/account/orders/view-invoice.css")));
        $header = $this->formatForPdf($this->getHtmlHeadForPdf($view, array("/css/account/orders/view-invoice.css")));
        //Obsolete: $pdfOptions['pdfContents'] = \Util_PdfGenerationHelper::formatForPdf($view->partial("order-invoice.phtml", array("order" => $this)));
        $pdfContents = $this->formatForPdf($view->partial("order-invoice.phtml", array("order" => $this)));
        $pdfGenerator->generatePdfFromHtmlString($pdfContents, false, $header);

        //$pdf = \Util_PdfGenerationHelper::getPdf($pdfOptions, false);
        $pdf = $pdfGenerator->getPdfContent();

        // send the mail
        $mail = new \Fisdap_TemplateMailer();
        $mail->setSubject("Fisdap Invoice #" . $this->invoice_number)
            ->addTo($this->getBillingEmailArray())
            ->setFrom("accounting@fisdap.net", "Accounting");

        $attachment = $mail->createAttachment($pdf);
        $attachment->type = 'application/pdf';
        $attachment->disposition = \Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->filename = "invoice.pdf";

        $mail->sendHtmlTemplate("email-pdf-invoice.phtml", array("order" => $this));
    }

    // take the html and make it good for pdf
    private function formatForPdf($html)
    {
        $file_path = APPLICATION_PATH . "/../public";

        // now get the absolute paths for the css and image files
        $html = str_replace("/css/", $file_path . "/css/", $html);
        $html = str_replace("/images/", $file_path . "/images/", $html);

        // turn links into spans
        $html = str_replace("<a ", "<span ", $html);
        $html = str_replace("</a>", "</span>", $html);

        return $html;
    }

    private function getHtmlHeadForPdf(\Zend_View_Interface $view, $cssFiles = array())
    {
        $view->headLink()->appendStylesheet("/css/global.css");

        if (!empty($cssFiles)) {
            foreach ($cssFiles as $file) {
                $view->headLink()->appendStylesheet($file);
            }
        }

        $header = "<head>\n";
        $header .= "<script type='text/javascript'>\n";
        $header .= "var NREUMQ=NREUMQ||[];NREUMQ.push(['mark','firstbyte',new Date().getTime()]);\n";
        $header .= "</script>\n";
        $header .= $view->headLink() . "\n";

        return $header;
    }


    public function processProductCodes()
    {
        $this->completed = true;
        $session = new \Zend_Session_Namespace("OrdersController");
        $session->unsetAll();

        $mail = new \Fisdap_TemplateMailer();
        $mail->addTo($this->user->email)
            ->setSubject("Product Code Confirmation")
            ->setViewParam("order", $this)
            ->setViewParam('urlRoot', \Util_HandyServerUtils::getCurrentServerRoot())
            ->sendHtmlTemplate('product-code-confirmation.phtml');
    }

    /**
     * Grab most recent transaction and display errors
     * @return array
     */
    public function getTransactionErrors()
    {
        $errors = [];

        $transaction = $this->order_transactions->first();
        if (!$transaction->success) {
            /** @var \Braintree_Error_ErrorCollection $result */
            $result = unserialize($transaction->response);

            if ($result->errors->deepSize() > 0) {
                foreach ($result->errors->deepAll() as $error) {
                    $errors[] = "{$error->message} (Code: {$error->code})";
                }
            } else {
                $errors[] = $result->message;
            }
        }

        return $errors;
    }

    /**
     * Create a string for the invoice number to be used in quickbooks
     * For whatever reason, we're using a letter to represent the year.
     * We started this in 2010, so a=2010, b=2011, c=2012 etc.
     * We're just subtracting a number from the date to create the
     * ASCII code for that letter of the alphabet
     *
     * We need to make sure that the number is unique
     *
     * @return string
     */
    public function generateInvoiceNumber()
    {
        $orderRepo = EntityUtils::getRepository('Order');
        $orderCount = $orderRepo->getOrderCountBeforeDate($this->program->id, $this->order_date);
        $year = chr(date('Y') - 1913);
        $month = date('m');
        $day = date('d') + $orderCount;
        if ($day < 10) {
            $day = "0" . $day;
        }
        $monthDay = $month . $day;

        $invoiceNumber = $this->program->customer_id . $year . $monthDay;

        //Make sure the generated invoice number does not already exist, if it does, increment and try again
        while ($orderRepo->findOneBy(array("invoice_number" => $invoiceNumber))) {
            $monthDay++;

            //Tack on a leading zero if we don't have 4 digits
            if ($monthDay < 1000) {
                $monthDay = "0" . $monthDay;
            }

            $invoiceNumber = $this->program->customer_id . $year . $monthDay;
        }

        $this->invoice_number = $invoiceNumber;
        return $this->invoice_number;
    }

    /**
     * Get the due date for this order. We assume it's 30 days
     * @return \DateTime the date that this invoice is due.
     * @throws \Exception when someone tries to get the due date of a credit card order
     */
    public function getInvoiceDueDate()
    {
        if ($this->payment_method->id != 1) {
            throw new \Exception("There is no due date for non-invoice orders.");
        }

        $dueDate = clone($this->order_date);
        $dueDate->add(new \DateInterval("P30D"));

        return $dueDate;
    }

    /**
     * Get order details for Staff Members
     *
     * @param string $lineBreak what text do you want to use for a line break?
     * @return string
     */
    public function getStaffOrderDetails($lineBreak = "<br>")
    {
        $summary = "";
        if ($this->user) {
            $summary .= "Ordered by " . $this->user->getName() . $lineBreak;
            $summary .= "Email: " . $this->user->email . $lineBreak;
        } else {
            $summary .= "Ordered by " . $this->name . $lineBreak;
            $summary .= "Email: " . $this->email . $lineBreak;
        }

        $summary .= "Order ID: " . $this->id . $lineBreak;
        $summary .= "Program: " . $this->program->name . $lineBreak;
        $summary .= "Program ID: " . $this->program->id . $lineBreak;
        $summary .= "Customer ID: " . $this->program->customer_id . $lineBreak;
        $summary .= "Cost: $" . $this->getTotalCost() . $lineBreak;
        if ($this->payment_method->id == 1) {
            $summary .= "Invoice: " . $this->invoice_number . $lineBreak;
        }


        $discounts = EntityUtils::getRepository('DiscountLegacy')->getCurrentDiscounts($this->program->id);
        if (count($discounts) > 0) {
            $summary .= "Current discounts for " . $this->program->name . ":" . $lineBreak;
            foreach ($discounts as $discount) {
                $summary .= $discount->getSummary() . $lineBreak;
            }
            $summary .= $lineBreak;
        }

        $summary .= "Here is the message that was sent:" . $lineBreak;
        $summary .= "----------------------------------" . $lineBreak;

        return $summary;
    }

    /**
     * Generate the serial numbers for this order
     * Loop over each configuration and generate the numbers
     */
    private function generateSerialNumbers()
    {
        foreach ($this->order_configurations as $config) {
            $config->generateSerialNumbers();
        }
    }

    /**
     * Loop over each order configuration and apply the upgrades to that user
     */
    private function applyUpgradesAndDowngrades()
    {
        foreach ($this->order_configurations as $config) {
            $serial = $config->upgraded_user->getSerialNumberForRole();

            // if serial is null - it's an instructor
            if (!$serial) {
                // generate serial
                $config->generateSerialNumbers();
                foreach ($config->serial_numbers as $serial) {
                    $config->upgraded_user->getCurrentRoleData()->activateSerialNumber($serial);
                }
                continue;
            }


            //Handle Upgrades/Added products
            if ($config->configuration > 0) {
                $products = EntityUtils::getRepository("Product")->getProducts($config->configuration, true);

                //Loop over products for this configuration and increase attempts for certain products if they already have them
                foreach ($products as $product) {
                    if (($product->configuration & $serial->configuration) && $product->has_multiple_attempts) {
                        $product->modifyTestAttempts($config->upgraded_user->id, '+');
                    }
                }
            }

            //Handle downgrades. These are products that are being COMPLETELY removed for the user
            if ($config->downgrade_configuration > 0) {
                $downgradeProducts = EntityUtils::getRepository("Product")->getProducts($config->downgrade_configuration, true);

                //Loop over products for this configuration and increase attempts for certain products if they already have them
                foreach ($downgradeProducts as $product) {
                    if (($product->configuration & $serial->configuration) && $product->has_multiple_attempts) {
                        // Remove any attempts remaining in moodle
                        foreach ($product->moodle_quizzes as $moodleTestData) {
                            MoodleUtils::setUsersQuizAttemptLimit(array($config->upgraded_user), $moodleTestData, 0);
                        }

                        // Disenroll the user from the Fisdap table that controls moodle course enrolment
                        EntityUtils::getRepository("User")->disenrollInMoodleCourse($product, $config->upgraded_user->username);

                        // @todo removal from Moodle DB?
                    }
                }
            }

            //Handle attempt reduction prodcuts. These are attempt-limited products for which the user's number of attempts should be decremented
            if ($config->reduce_configuration > 0) {
                $reduceProducts = EntityUtils::getRepository("Product")->getProducts($config->reduce_configuration, true);

                //Loop over products for this configuration and increase attempts for certain products if they already have them
                foreach ($reduceProducts as $product) {
                    if (($product->configuration & $serial->configuration) && $product->has_multiple_attempts) {
                        // Decrement attempts
                        $product->modifyTestAttempts($config->upgraded_user->id, '-');
                    }
                }
            }

            //Compute new configuration and save it
            $newConfiguration = $config->configuration | ($serial->configuration & ~$config->downgrade_configuration);
            $serial->configuration = $newConfiguration;

            //Force the serial number to apply any additional features
            //(i.e. Applying product limits or enrolling in Moodle courses)
            $serial->applyExtras();
        }
    }

    /**
     * Email the orderer a copy of the serial numbers
     */
    private function emailSerialNumbers()
    {
        if (!$this->individual_purchase) {
            $subject = $this->upgrade_purchase ? "Upgrade Confirmation" : "New "
                . $this->user->getCurrentRoleName() . " "
                . $this->payment_method->name . " order";

            $template = $this->upgrade_purchase ? "upgrade-confirmation.phtml" : "serial-numbers-multiple.phtml";

            $mail = new \Fisdap_TemplateMailer();
            $mail->addTo($this->user->email)
                ->setSubject($subject)
                ->setViewParam('order', $this)
                ->setViewParam('salesEmail', false)
                ->sendHtmlTemplate($template);

            //Send email to fisdap-sales@fisdap.net
            if (APPLICATION_ENV != "production") {
                $subject = ucfirst(APPLICATION_ENV) . " - " . $subject;
            }
            $mail = new \Fisdap_TemplateMailer();
            $mail->addTo("fisdap-sales@fisdap.net")
                ->setSubject($subject)
                ->setViewParam('order', $this)
                ->setViewParam('salesEmail', true)
                ->sendHtmlTemplate($template);

            // send out a seperate email to those who have accounts that are being upgraded
            if ($this->upgrade_purchase) {
                foreach ($this->order_configurations as $config) {
                    $mail = new \Fisdap_TemplateMailer();
                    $mail->addTo($config->upgraded_user->email)
                        ->setSubject("Your Fisdap account has been upgraded")
                        ->setViewParam('config', $config)
                        ->setViewParam('orderer', $this->user->first_name . " " . $this->user->last_name)
                        ->sendHtmlTemplate('account-upgrade.phtml');
                }
            }
        } elseif ($this->individual_purchase) {
            $subject = $this->upgrade_purchase ? "Upgrades to your Fisdap account" : "Your new Fisdap account";

            $mail = new \Fisdap_TemplateMailer();
            $mail->addTo($this->email)
                ->setSubject($subject)
                ->setViewParam('order', $this)
                ->setViewParam('urlRoot', \Util_HandyServerUtils::getCurrentServerRoot())
                ->setViewParam('salesEmail', false)
                ->sendHtmlTemplate('individual-purchase-order.phtml');

            //Send email to fisdap-sales@fisdap.net
            if (APPLICATION_ENV != "production") {
                $subject = ucfirst(APPLICATION_ENV) . " - " . $subject;
            }
            $mail = new \Fisdap_TemplateMailer();
            $mail->addTo("fisdap-sales@fisdap.net")
                ->setSubject($subject)
                ->setViewParam('order', $this)
                ->setViewParam('urlRoot', \Util_HandyServerUtils::getCurrentServerRoot())
                ->setViewParam('salesEmail', true)
                ->sendHtmlTemplate('individual-purchase-order.phtml');
        }
    }
    

    /**
     * Generate a product code
     *
     * @param integer $programId the ID of the program
     * @param integer $config the products wanted
     * @param integer $certificationId
     * @return string
     */
    public static function generateProductCode($programId, $config, $certificationId)
    {
        $code = "";

        if ($config === 0) {
            return "N/A";
        }

        $program = EntityUtils::getEntity("ProgramLegacy", $programId);
        $certification = EntityUtils::getEntity("CertificationLevel", $certificationId);

        $code .= $program->abbreviation;
        $code .= $program->id;
        $code .= "-";
        $code .= $certification->abbreviation;
        $code .= "-";
        $code .= $config;

        return $code;
    }

    public static function validateProductCode($code)
    {
        return true;
    }

    public static function getPaypalLoginParams()
    {
        $ini = \Zend_Registry::get('config');
        return $ini->paypal->params->toArray();
    }

    public function getAssociatedDiscounts()
    {
        $associatedDiscounts = array();
        foreach ($this->order_configurations as $config) {
            $discounts = unserialize($config->discounts);
            if (!$discounts) {
                continue;
            }

            $perAccount = ($config->quantity > 1) ? " per account" : "";
            foreach ($discounts as $discount) {
                $savings = number_format($discount['discount'], 2, ".", ",");
                $associatedDiscounts[] = $discount['message'] . " - save $$savings $perAccount";
            }
        }

        return $associatedDiscounts;
    }

    /**
     * Determine if this order was placed with a credit card
     *
     * @return bool
     */
    public function isCreditCardPurchase()
    {
        return $this->payment_method->id != 1;
    }
}
