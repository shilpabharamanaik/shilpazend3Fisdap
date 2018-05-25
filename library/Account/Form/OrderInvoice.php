<?php

use Fisdap\Data\Order\OrderRepository;
use Fisdap\Entity\Order;
use Fisdap\Entity\User;
use Fisdap\Members\Commerce\Events\CustomerWasUpdated;
use Illuminate\Contracts\Events\Dispatcher;


/**
 * Form for ordering accounts via invoice
 *
 * @package Account
 */
class Account_Form_OrderInvoice extends Fisdap_Form_Base
{
	/**
	 * @var array the decorators for the form
	 */
	private static $_formDecorators = array(
		'FormErrors',
		'PrepareElements',
		array('ViewScript', array('viewScript' => "forms/orderInvoiceForm.phtml")),
		array('Form', array('class' => 'billing-form')),
	);

	/**
	 * @var OrderRepository
	 */
	private $orderRepository;

	/**
	 * @var Order
	 */
	public $order;
	
	/**
	 * @var User
	 */
	public $user;
	
	/**
	 * @var boolean
	 * Has this program placed an order before?
	 */
	private $firstOrder;


	/**
	 * @param OrderRepository $orderRepository
	 * @param Order           $order
	 * @param mixed           $options additional Zend_Form options
	 */
	public function __construct(OrderRepository $orderRepository, Order $order, $options = null)
	{
		$this->orderRepository = $orderRepository;
		$this->order = $order;
		$this->user = User::getLoggedInUser();
		
		$this->firstOrder = $orderRepository->getProgramOrderCount($this->user->getCurrentProgram()) == 0;
		
		parent::__construct($options);
	}


	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();
		
		$this->setDecorators(self::$_formDecorators);
		
        //add js file to do cool input masking
        $this->addJsFile("/js/jquery.maskedinput-1.3.js");

		$this->addJsOnLoad('
			 $("#phone").mask("999-999-9999? x99999");
			');
        
        $name = new Zend_Form_Element_Text("name");
        $name->setLabel("Contact Name:")
              ->setRequired(true)
              ->setDescription("(required)")
			  ->addErrorMessage("Please enter a name.");
        $this->addElement($name);
        
        $email = new Fisdap_Form_Element_Email("email");
        $email->setLabel("Email:")
              ->setRequired(true)
              ->setDescription("(required)")
			  ->addErrorMessage("Please enter a valid email address.")
              ->addValidator(new \Fisdap_Validate_MultipleEmails())
              ->removeValidator("EmailAddress");
        $this->addElement($email);
        
        $phone = new Zend_Form_Element_Text("phone");
        $phone->setLabel("Phone:")
              ->setRequired(true)
              ->setDescription("(required)")
			  ->addErrorMessage("Please enter a phone number.");
        $this->addElement($phone);
        
        $addressLineOne = new Zend_Form_Element_Text("addressLineOne");
        $addressLineOne->setLabel("Street Address (line 1):")
                       ->setRequired(true)
                       ->setDescription("(required)")
					   ->addValidator("StringLength", false, array("max" => 41))
        			   ->addErrorMessage("Please enter a street address less than 42 characters.");
		$this->addElement($addressLineOne);
        
        $addressLineTwo = new Zend_Form_Element_Text("addressLineTwo");
        $addressLineTwo->setLabel("Street Address (line 2):")
					   ->addValidator("StringLength", false, array("max" => 41))
        			   ->addErrorMessage("Please choose a street address less than 42 characters.");
        $this->addElement($addressLineTwo);
        
		$programName = new Zend_Form_Element_Text("programName");
		$programName->setLabel("Program Name:")
					->setRequired(true)
					->setDescription("(required)")
					->addValidator("StringLength", false, array("max" => 41))
					->addErrorMessage("Please enter a program name less than 42 characters.");
		$this->addElement($programName);
		
//        $addressLineThree = new Zend_Form_Element_Text("addressLineThree");
//        $addressLineThree->setLabel("Street Address (line 3):")
//						 ->addValidator("StringLength", false, array("max" => 41))
//						 ->addErrorMessage("Please choose a street address less than 42 characters.");
//        $this->addElement($addressLineThree);
        
        $city = new Zend_Form_Element_Text("city");
        $city->setLabel("City:")
             ->setRequired(true)
			 ->addValidator("StringLength", false, array("max" => 31))
			 ->addErrorMessage("Please enter a city less than 32 characters.");
        $this->addElement($city);
        
		$billingCountry = new Fisdap_Form_Element_Countries("billingCountry");
        $billingCountry->setLabel("Country:")
                ->setRequired(true)
				->setStateElementName("billingState")
                ->addErrorMessage("Please choose a billing country.");
        $this->addElement($billingCountry);

        $billingState = new Fisdap_Form_Element_States("billingState");
        $billingState->setLabel("State/Provence:")
              ->addValidator(new \Fisdap_Validate_States("billingCountry"));
        $this->addElement($billingState);

		if ($this->order->program->id) {
			$billingState->setCountry($this->order->program->billing_country);
		} else {
			$billingState->setCountry($billingCountry->getValue());
		}
        
        $zip = new Zend_Form_Element_Text("zip");
        $zip->setLabel("Zip:")
            ->setRequired(true)
            ->addValidator('Digits')
			->addValidator('LessThan', false, array('max' => '99999'))
			->setAttrib('size', 5)
			->addErrorMessage("Please enter a valid zip code.");
        $this->addElement($zip);
        
        $poNumber = new Zend_Form_Element_Text("po");
        $poNumber->setLabel("Purchase Order:")
				 ->addValidator("StringLength", false, array("max" => 25))
				 ->addErrorMessage("Please choose a PO number less than 26 characters");
        $this->addElement($poNumber);
		
		if ($this->user->getCurrentProgram()->requires_po) {
			$poNumber->setRequired()
					 ->setDescription("(required)")
					 ->addErrorMessage("Please enter a PO number.");
		}
		
		$saveBillingInfo = new Zend_Form_Element_Checkbox("saveBillingInfo");
		$saveBillingInfo->setLabel("Save billing information for next time.");
		
		if ($this->user->hasPermission("Edit Program Settings") && !$this->firstOrder) {
			$this->addElement($saveBillingInfo);			
		}
		
		$staffFreeOrder = new Zend_Form_Element_Checkbox("staffFreeOrder");
		$staffFreeOrder->setLabel("Do NOT charge for this invoice (Staff Only Option)");
		
		//If this order has pilot testing accounts, disable the option
		//to make this order free, because it's going to be free no matter what
		if ($this->order->hasPilotTestingAccounts()) {
			$staffFreeOrder->setAttrib("disabled", "disabled")
						   ->setValue(1);
		}
		$this->addElement($staffFreeOrder);
		
		$orderId = new Zend_Form_Element_Hidden("orderId");
		$orderId->setDecorators(array('ViewHelper'));
		$this->addElement($orderId);
		
		$orderButton = new Fisdap_Form_Element_SaveButton("orderButton");
		$orderButton->setLabel("Place Order");
		$this->addElement($orderButton);

        $this->setElementDecorators(self::$elementDecorators);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('orderId', 'orderButton'), true);
        $this->setElementDecorators(self::$floatingElementDecorators, array('city', 'billingState', 'zip'), true);
        $this->setElementDecorators(self::$checkboxDecorators, array('saveBillingInfo', 'staffFreeOrder'), true);
		
        if ($this->user->isInstructor()) {
            $program = $this->user->getCurrentProgram();
            $this->setDefaults(array(
                'name' => $program->billing_contact,
                'phone' => $program->billing_phone,
                'email' => $program->billing_email,
                'addressLineOne' => $program->billing_address,
                'addressLineTwo' => $program->billing_address2,
                'addressLineThree' => $program->billing_address3,
                'city' => $program->billing_city,
                'billingState' => $program->billing_state,
                'billingCountry' => $program->billing_country,
                'zip' => $program->billing_zip,
				'programName' => substr($program->name, 0, 41),
            ));
        }
        
		if ($this->order->id) {
			$this->setDefaults(array(
				'orderId' => $this->order->id,
			));
		}
	}


    /**
     * Process the submitted POST values and do whatever you need to do
     *
     * @param array $post the POSTed values from the user
     * @param Dispatcher $dispatcher
     *
     * @return mixed either the values or the form w/errors
     *
     * @throws Exception
     * @throws Zend_Form_Exception
     */
	public function process(array $post, Dispatcher $dispatcher)
	{
		if ( ! $this->isValid($post)) {
			return false;
		}

		$values = $this->getValues();

		/** @var Order $order */
		$order = $this->orderRepository->getOneById($values['orderId']);
		$order->name = $values['name'];
		$order->phone = $values['phone'];
		$order->email = $values['email'];
		$order->address1 = $values['addressLineOne'];
		$order->address2 = $values['addressLineTwo'];
		$order->program_name = $values['programName'];
		$order->city = $values['city'];
		$order->state = $values['billingState'];
		$order->country = $values['billingCountry'];
		$order->zip = $values['zip'];
		$order->invoice_delivery_method = 1;
		$order->po_number = $values['po'];

		// Staff only option to create a free order
		if ($this->user->isStaff()) {
			// Automatically set the order to be free if it has any pilot testing accounts
			if ($order->hasPilotTestingAccounts()) {
				$order->staff_free_order = true;
			} else {
				$order->staff_free_order = $values['staffFreeOrder'];
			}
		}

		$program = $this->user->getCurrentProgram();

		// Save billing info for program
		if ($values['saveBillingInfo'] || $this->firstOrder) {
			$program->billing_contact = $values['name'];
			$program->billing_phone = $values['phone'];
			$program->billing_email = $values['email'];
			$program->billing_address = $values['addressLineOne'];
			$program->billing_address2 = $values['addressLineTwo'];
			$program->billing_city = $values['city'];
			$program->billing_state = $values['billingState'];
			$program->billing_country = $values['billingCountry'];
			$program->billing_zip = $values['zip'];

            if ($program->customer_name) {
                $dispatcher->fire(new CustomerWasUpdated($program->id, $program->customer_name, $program->customer_id));
            }
		}

		// Process completed order
		$order->process();
		$order->save();

		return true;
	}
}
