<?php
use Fisdap\Data\Order\OrderRepository;
use Fisdap\Data\Product\ProductRepository;
use Fisdap\Entity\CertificationLevel;
use Fisdap\Entity\Order;
use Fisdap\Entity\OrderConfiguration;
use Fisdap\Entity\OrderType;
use Fisdap\Entity\User;

/**
 * @package Account
 */
class Account_Form_AccountHolders extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    private static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/accountHoldersForm.phtml")),
        array('Form'),
    );

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var integer
     */
    public $professionId;


    /**
     * Account_Form_AccountHolders constructor.
     *
     * @param OrderRepository   $orderRepository
     * @param ProductRepository $productRepository
     * @param null              $orderId
     */
    public function __construct(
            OrderRepository $orderRepository,
            ProductRepository $productRepository,
            $orderId = null
    ) {
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;

        $this->order = is_null($orderId) ? new Order() : $orderRepository->getOneById($orderId);
        $this->professionId = User::getLoggedInUser()->getCurrentProgram()->profession->id;

        parent::__construct();
    }


    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setAttrib("id", "accountHoldersForm");

        $this->setDecorators(self::$_formDecorators);
        $this->addCssFile("/css/library/Account/Form/account-holders.css");
        $this->addJsFile("/js/library/Account/Form/account-holders.js");

        $payment = new Zend_Form_Element_Radio('payment');
        $payment->setMultiOptions(OrderType::getFormOptions(false, false, "description"))
                ->setValue(1)
                ->setDecorators(self::$strippedDecorators);
        if (!$this->order->id) {
            $this->addElement($payment);
        }

        //BE AWARE: I switched the name and id attribute of the Students and Preceptors
        //form inputs so that they pretend to be part of the same radio group
        $students = new Zend_Form_Element_Radio('students');
        $students->setDecorators(self::$strippedDecorators)
                 ->setAttrib('name', 'account_type')
                 ->setAttrib('id', 'account_type')
                 ->addMultiOption(1, 'Students')
                 ->setValue(1);
        $this->addElement($students);

        //See above
        $preceptors = new Zend_Form_Element_Radio('preceptors');
        $preceptors->setDecorators(self::$strippedDecorators)
                    ->setAttrib('name', 'account_type')
                    ->setAttrib('id', 'account_type')
                    ->addMultiOption(2, 'Preceptor Training');
        $this->addElement($preceptors);

        //See above
        $providers = new Zend_Form_Element_Radio('providers');
        $providers->setDecorators(self::$strippedDecorators)
                    ->setAttrib('name', 'account_type')
                    ->setAttrib('id', 'account_type')
                    ->addMultiOption(3, 'Transition Course');
        $this->addElement($providers);

        $quantity = new Zend_Form_Element_Text('quantity_students');
        $quantity->setLabel("# of students")
                 ->setOptions(array('size' => 3))
                 ->setDecorators(self::$elementDecorators)
                 ->addValidator("Int", true)
                 ->addValidator("Between", true, array("min" => 1, "max" => 500))
                 ->addErrorMessage("This field is required. Please enter a number between 1 and 500.");
        $this->addElement($quantity);

        $quantityPreceptors = new Zend_Form_Element_Text('quantity_preceptors');
        $quantityPreceptors->setLabel("# of preceptors")
                 ->setOptions(array('size' => 3))
                 ->setDecorators(self::$elementDecorators)
                 ->addValidator("Int", true)
                 ->addValidator("Between", true, array("min" => 1, "max" => 500))
                 ->addErrorMessage("This field is required. Please enter a number between 1 and 500.");
        $this->addElement($quantityPreceptors);

        $quantityProviders = new Zend_Form_Element_Text('quantity_providers');
        $quantityProviders->setLabel("# of providers")
                 ->setOptions(array('size' => 3))
                 ->setDecorators(self::$elementDecorators)
                 ->addValidator("Int", true)
                 ->addValidator("Between", true, array("min" => 1, "max" => 500))
                 ->addErrorMessage("This field is required. Please enter a number between 1 and 500.");
        $this->addElement($quantityProviders);

        // Get the correct certifications for this type of program
        $certOptions = CertificationLevel::getFormOptions(false, false, "description", $this->professionId);

        $certification = new Zend_Form_Element_Radio('certification');
        $certification->setLabel('Student type')
                      ->setMultiOptions($certOptions)
                      ->setDecorators(self::$elementDecorators)
                      ->setValue(reset(array_keys($certOptions)));
        $this->addElement($certification);

        $certificationProvider = new Zend_Form_Element_Radio('provider_certification');
        $certificationProvider->setDecorators(self::$elementDecorators);

        $courses = $this->productRepository->findByCategory(4);

        foreach ($courses as $course) {
            $certificationProvider->addMultiOption($course->configuration, $course->name);
        }

        $certificationProvider->setValue(32768);
        $this->addElement($certificationProvider);

        $gradDate = new Fisdap_Form_Element_GraduationDate('gradDate');
        $gradDate->setDescription('(optional)')
                 ->setDecorators(self::$elementDecorators)
                 ->setYearRange(date("Y"), date("Y") + 5);
        $this->addElement($gradDate);

        $groups = new Fisdap_Form_Element_Groups('group');
        $groups->setLabel('In student group')
               ->setDescription('(optional)')
               ->setDecorators(self::$elementDecorators);
        $this->addElement($groups);

        //$this->setElementDecorators(array('ViewHelper'));
    }


    /**
     * Overwriting the isValid method to add some dependency validation
     * JANK ALERT: Because Zend does not allow me to make the students and preceptors
     * part of the same radio set, we have some hacking to fake it by inserting the value
     * that zend expects in the POST array based on the name of the radio (account_type)
     *
     * @param array $values
     * @return boolean
     */
    public function isValid($values)
    {
        //var_dump($values);

        if ($values['account_type'] == 1) {
            $values['students'] = 1;
            $this->students->setValue(1);
            $this->preceptors->setValue(null);
            $this->providers->setValue(null);
            $this->quantity_students->setRequired();
        } elseif ($values['account_type'] == 2) {
            $values['preceptors'] = 2;
            $this->students->setValue(null);
            $this->preceptors->setValue(2);
            $this->providers->setValue(null);
            $this->quantity_preceptors->setRequired();
        } else {
            $values['providers'] = 3;
            $this->students->setValue(null);
            $this->preceptors->setValue(null);
            $this->providers->setValue(3);
            $this->quantity_providers->setRequired();
        }

        if ($values['payment'] == '2' || $this->order->order_type->id == 2) {
            $this->quantity_students->setRequired(false);
            $this->quantity_preceptors->setRequired(false);
            $this->quantity_providers->setRequired(false);
            $this->quantity_students->clearValidators();
            $this->quantity_preceptors->clearValidators();
            $this->quantity_providers->clearValidators();
        }

        return parent::isValid($values);
    }


    /**
     * Process the submitted POST values and do whatever you need to do
     *
     * @param array $post the POSTed values from the user
     * @return mixed either the values or the form w/errors
     */
    public function process($post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();

            // Save the form values to the session
            $session = new \Zend_Session_Namespace("OrdersController");
            $session->accountHoldersFormValues = $values;

            // If we're buying preceptor accounts or transition courses, create a new order or add to the current order
            if ($values['preceptors'] == 2 || $values['providers'] == 3) {

                // Create new order if one does not already exist
                if (! $this->order->id) {
                    $this->order = new Order();
                    $this->order->user = User::getLoggedInUser();
                    $this->order->order_type = $values['payment'];
                }

                // Create new order configuration
                $orderConfig = new OrderConfiguration();

                // Set the product configuration depending on preceptor training or transition courses
                if ($values['preceptors'] == 2) {
                    $orderConfig->configuration = 64;
                    $quantity = $values['quantity_preceptors'];
                } elseif ($values['providers'] == 3) {
                    $orderConfig->configuration = $values['provider_certification'];
                    $quantity = $values['quantity_providers'];
                }

                // Deal with quantity
                if ($values['payment'] == 2 || $this->order->order_type->id == 2) {
                    $orderConfig->quantity = 1;
                } else {
                    $orderConfig->quantity = $quantity;
                }

                // Attach new order configuration to this order and calculate final price
                $this->order->addOrderConfiguration($orderConfig);
                $orderConfig->calculateFinalPrice();

                // If instructors are paying for these accounts, create a product code for the order configuration
                if ($this->order->order_type->id == 2) {
                    $orderConfig->generateProductCode();
                }

                $this->order->save();
                $session->orderId = $this->order->id;
            }
            
            return true;
        }

        return $this;
    }


    /**
     * Determine if there are steps left in the account ordering wizard.
     * This will determine if the user is redirected immediately to the
     * shopping cart.
     */
    public function isWizardDone()
    {
        $values = $this->getValues();
        return ($values['preceptors'] == 2 || $values['providers'] == 3);
    }
}
