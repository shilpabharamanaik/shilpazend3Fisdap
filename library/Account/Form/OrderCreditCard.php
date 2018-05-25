<?php

use Fisdap\Data\Order\OrderRepository;
use Fisdap\Entity\Order;
use Fisdap\Entity\OrderTransaction;
use Fisdap\Entity\User;


/**
 * Form for ordering accounts via Credit Card
 *
 * @package Account
 */
class Account_Form_OrderCreditCard extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    private static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/orderCreditCardForm.phtml")),
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
    private $user;

    /**
     * @var boolean true if this is a individual purchase
     */
    private $individualPurchase;

    /**
     * @var boolean
     * Is this the first order for this program?
     * This information is used to determine if we ask them to save their billing info
     */
    private $firstOrder = true;

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger;


    /**
     * @param OrderRepository $orderRepository
     * @param Order $order
     * @param mixed $options additional Zend_Form options
     */
    public function __construct(OrderRepository $orderRepository, Order $order, $options = null)
    {
        $this->orderRepository = $orderRepository;
        $this->order = $order;
        $this->individualPurchase = $this->order->individual_purchase;

        if ($user = User::getLoggedInUser()) {
            $this->user = $user;
            $this->firstOrder = $orderRepository->getProgramOrderCount($this->user->getCurrentProgram()) == 0;
        }

        $this->logger = Zend_Registry::get('logger');

        parent::__construct($options);
    }


    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(self::$_formDecorators);

        // add js file to do cool input masking
        $this->addJsFile("/js/jquery.maskedinput-1.3.js");
        $this->addJsFile("/js/library/Account/Form/order-credit-card.js");
        $this->addCssFile("/css/library/Account/Form/order-credit-card.css");

        if ($this->individualPurchase) {
            $this->addCssFile("/css/account/orders/billing.css");
        }

        $this->addJsOnLoad('
			 $("#phone").mask("999-999-9999");
			');

        $this->setOptions(array('id' => 'order-credit-card'));

        $first = new Zend_Form_Element_Text("first");
        $first->setLabel("First Name:")
            ->setRequired(true)
            ->addErrorMessage("Please enter a first name.");
        $this->addElement($first);

        $middle = new Zend_Form_Element_Text("middle");
        $middle->setLabel("Middle Name/Initial:")
            ->setDescription("(optional)");
        $this->addElement($middle);

        $last = new Zend_Form_Element_Text("last");
        $last->setLabel("Last Name:")
            ->setRequired(true)
            ->addErrorMessage("Please enter a last name.");
        $this->addElement($last);

        $email = new Fisdap_Form_Element_Email("email");
        $email->setLabel("Email:")
            ->setRequired(true)
            ->addErrorMessage("Please enter a valid email address.")
            ->addValidator(new \Fisdap_Validate_MultipleEmails())
            ->removeValidator("EmailAddress");
        $this->addElement($email);

        $phone = new Zend_Form_Element_Text("phone");
        $phone->setLabel("Phone:")
            ->setRequired(true)
            ->addErrorMessage("Please enter a phone number.");
        $this->addElement($phone);

        $addressLineOne = new Zend_Form_Element_Text("ADDRESS");
        $addressLineOne->setLabel("Street Address, PO Box:")
            ->setRequired(true)
            ->addValidator("StringLength", false, array("max" => 41))
            ->addErrorMessage("Please enter a street address less than 42 characters.");
        $this->addElement($addressLineOne);

        $addressLineTwo = new Zend_Form_Element_Text("addressLineTwo");
        $addressLineTwo->setLabel("Apt, Building, Etc:")
            ->setDescription("(optional)")
            ->addValidator("StringLength", false, array("max" => 41))
            ->addErrorMessage("Please choose a street address less than 42 characters.");
        $this->addElement($addressLineTwo);

        $city = new Zend_Form_Element_Text("CITY");
        $city->setLabel("City:")
            ->setRequired(true)
            ->addValidator("StringLength", false, array("max" => 31))
            ->addErrorMessage("Please enter a city less than 32 characters.");
        $this->addElement($city);

        $billingCountry = new Fisdap_Form_Element_Countries("COUNTRY");
        $billingCountry->setLabel("Country:")
            ->setRequired(true)
            ->setStateElementName("STATE")
            ->addErrorMessage("Please choose a billing country.");
        $this->addElement($billingCountry);

        $billingState = new Fisdap_Form_Element_States("STATE");
        $billingState->setLabel("State/Provence:")
            ->addValidator(new \Fisdap_Validate_States("COUNTRY"));
        $this->addElement($billingState);

        $zip = new Zend_Form_Element_Text("ZIP");
        $zip->setLabel("Zip:")
            ->setRequired(true)
            ->addValidator('Digits')
            ->addValidator('LessThan', false, array('max' => '99999'))
            ->addErrorMessage("Please enter a valid zip code.");
        $this->addElement($zip);

        if (!$this->individualPurchase) {
            if ($this->user->isInstructor() && $this->user->hasPermission("Edit Program Settings") && !$this->firstOrder) {
                $saveBillingInfo = new Zend_Form_Element_Checkbox("saveBillingInfo");
                $saveBillingInfo->setLabel("Save billing information for next time (Note: We will NOT save your payment information).");
                $this->addElement($saveBillingInfo);
            }
        }

        $orderId = new Zend_Form_Element_Hidden("orderId");
        $orderId->setDecorators(array('ViewHelper'));
        $this->addElement($orderId);

        $orderButton = new Fisdap_Form_Element_SaveButton("orderButton");
        $orderButton->setLabel("Place Order");
        if ($this->individualPurchase && $this->user) {
            $orderButton->setLabel("Upgrade account");
        }
        $this->addElement($orderButton);

        // Set Element Decorators for these form elements
        $this->setElementDecorators(self::$elementDecorators);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('orderId', 'orderButton'), true);
        $this->setElementDecorators(self::$checkboxDecorators, array('saveBillingInfo'), true);

        // PayPal hidden fields
        $this->addElement('hidden', 'NAME', array('decorators' => array('ViewHelper')));

        if ($this->user) {
            $program = $this->user->getCurrentProgram();
        }

        // If we have a user (this isn't an individual purchase)
        if (!$this->individualPurchase) {
            // If this is an instructor, pre-populate fields from program billing information
            if ($this->user->isInstructor()) {
                $defaults = array(
                    'phone' => $program->billing_phone,
                    'email' => $program->billing_email,
                    'ADDRESS' => $program->billing_address,
                    'addressLineTwo' => $program->billing_address2,
                    'CITY' => $program->billing_city,
                    'STATE' => $program->billing_state,
                    'ZIP' => $program->billing_zip,
                    'COUNTRY' => $program->billing_country,
                );

                // Parse the name and set it
                $namePieces = preg_split('/\s/', $program->billing_contact);
                if (count($namePieces) == 2) {
                    $defaults['first'] = $namePieces[0];
                    $defaults['last'] = $namePieces[1];
                } else if (count($namePieces) == 3) {
                    $defaults['first'] = $namePieces[0];
                    $defaults['middle'] = $namePieces[1];
                    $defaults['last'] = $namePieces[2];
                }
                $this->setDefaults($defaults);
            } else {
                $defaults = array(
                    'COUNTRY' => 'USA'
                );
                $this->setDefaults($defaults);
            }
        } else {
            // If this is an individual purchase and the user is logged in, pre-populate
            if ($this->user) {
                $phone = $this->user->cell_phone ? $this->user->cell_phone : $this->user->home_phone;
                $defaults = array(
                    "first" => $this->user->first_name,
                    "last" => $this->user->last_name,
                    "email" => $this->user->email,
                    "phone" => $phone,
                    "ADDRESS" => $this->user->address,
                    "CITY" => $this->user->city,
                    "STATE" => $this->user->state,
                    "ZIP" => $this->user->zip,
                    'COUNTRY' => $program->billing_country,
                );
                $this->setDefaults($defaults);
            } else {
                $defaults = array(
                    'COUNTRY' => 'USA'
                );
                $this->setDefaults($defaults);
            }
        }

        // Populate form values from this order if it exists
        if ($this->order->id) {
            $defaults = array("orderId" => $this->order->id);

            // Check if these order values exist and set them
            if ($this->order->email) {
                $defaults['email'] = $this->order->email;
            }
            if ($this->order->phone) {
                $defaults['phone'] = $this->order->phone;
            }
            if ($this->order->address1) {
                $defaults['ADDRESS'] = $this->order->address1;
            }
            if ($this->order->address2) {
                $defaults['addressLineTwo'] = $this->order->address2;
            }
            if ($this->order->city) {
                $defaults['CITY'] = $this->order->city;
            }
            if ($this->order->state) {
                $defaults['STATE'] = $this->order->state;
            }
            if ($this->order->zip) {
                $defaults['ZIP'] = $this->order->zip;
            }
            if ($this->order->country) {
                $defaults['COUNTRY'] = $this->order->country;
            }

            //Parse the name and set it
            $namePieces = preg_split('/\s/', $this->order->name);
            if (count($namePieces) == 2) {
                $defaults['first'] = $namePieces[0];
                $defaults['last'] = $namePieces[1];
            } else if (count($namePieces) == 3) {
                $defaults['first'] = $namePieces[0];
                $defaults['middle'] = $namePieces[1];
                $defaults['last'] = $namePieces[2];
            }

            $this->setDefaults($defaults);
        }
    }


    /**
     * Process the submitted POST values and do whatever you need to do
     *
     * @param array $post the POSTed values from the user
     *
     * @return mixed either the values or the form w/errors
     *
     * @throws Exception
     * @throws Zend_Form_Exception
     */
    public function process(array $post)
    {
        if (!$this->isValid($post)) {
            return $this->getMessages();
        }

        $values = $this->getValues();

        if (!$values['orderId']) {
            throw new \Exception('An order ID must be present to process this order.');
        }

        $this->logger->debug("Processing credit card order...", $values);

        $this->order->name = $values['NAME'];
        $this->order->phone = $values['phone'];
        $this->order->email = $values['email'];
        $this->order->address1 = $values['ADDRESS'];
        $this->order->address2 = $values['addressLineTwo'];
        $this->order->city = $values['CITY'];
        $this->order->state = $values['STATE'];
        $this->order->zip = $values['ZIP'];
        $this->order->country = $values['COUNTRY'];

        // Save billing info for program
        if (!$this->individualPurchase) {
            if ($this->user->isInstructor() && ($values['saveBillingInfo'] || $this->firstOrder)) {
                $this->order->program->billing_contact = $values['NAME'];
                $this->order->program->billing_phone = $values['phone'];
                $this->order->program->billing_email = $values['email'];
                $this->order->program->billing_address = $values['ADDRESS'];
                $this->order->program->billing_address2 = $values['addressLineTwo'];
                $this->order->program->billing_city = $values['CITY'];
                $this->order->program->billing_state = $values['STATE'];
                $this->order->program->billing_zip = $values['ZIP'];
                $this->order->program->billing_country = $values['COUNTRY'];
            }
        }

        $this->order->save();

        $result = Braintree_Transaction::sale([
            'amount' => $this->order->total_cost,
            'orderId' => $this->order->id,
            'customer' => [
                'id' => $this->order->program->customer_id > 0 ? $this->order->program->customer_id : "",
                'firstName' => $values['first'],
                'lastName' => $values['last'],
                'phone' => $values['phone'],
                'email' => $values['email']
            ],
            'billing' => [
                'firstName' => $values['first'],
                'lastName' => $values['last'],
                'streetAddress' => $values['ADDRESS'],
                'extendedAddress' => $values['addressLineTwo'],
                'locality' => $values['CITY'],
                'region' => $values['STATE'],
                'postalCode' => $values['ZIP'],
                'countryCodeAlpha3' => $values['COUNTRY']
            ],
            'customFields' => [
                'username' => ($this->user) ? $this->user->getUsername() : "new user",
            ],
            'paymentMethodNonce' => $post['payment_method_nonce'],
            'options' => [
                'submitForSettlement' => true
            ]
        ]);

        $this->logger->debug("Braintree Sale Transaction Result", (array)$result);

        $this->saveBraintreeTransaction($result);

        if ($result->success === true) {
            $this->order->paypal_transaction_id = $result->transaction->id;

            //todo - add these to the order (and/or OrderWasProcessed event TBC)
            $this->logger->debug('processorAuthorizationCode: ' . $result->transaction->processorAuthorizationCode);
            $this->logger->debug('cardType: ' . $result->transaction->creditCardDetails->cardType);
            $this->logger->debug('last4: ' . $result->transaction->creditCardDetails->last4);
            $this->logger->debug('expirationDate: ' . $result->transaction->creditCardDetails->expirationDate);

            $this->order->process();
        } else {

            if ($result->errors->deepSize() > 0) {
                foreach ($result->errors->deepAll() as $error) {
                    $this->logger->error($error->message, ['code' => $error->code]);
                }
            } else {
                $this->logger->error($result->message);
            }
        }

        $this->order->save();

        return true;
    }


    /**
     * @param mixed $result
     */
    private function saveBraintreeTransaction($result)
    {
        $this->logger->debug('Braintree result is a ' . get_class($result));

        // Create a new transaction entity and set the relevant data
        $transaction = new OrderTransaction();
        $transaction->success = $result->success;

        if ($result instanceof Braintree_Result_Error) {
            $transaction->timestamp = new DateTime();
        } else {
            $transaction->timestamp = $result->transaction->createdAt;
        }

        $transaction->response = serialize($result);

        // Add the transaction to this order
        $transaction->order = $this->order;
        $this->order->order_transactions->add($transaction);

        $transaction->save(false);
    }
}
