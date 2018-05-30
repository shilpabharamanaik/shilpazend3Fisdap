<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Form for choosing individual products to buy for rogue accounts
 */

/**
 * @package    Account
 */
class Account_Form_OrderIndividualProducts extends Fisdap_Form_Base
{
    /**
     * @var array contain the availble products
     */
    public $products;

    /**
     * @var interger the number of available products
     */
    public $productCount;

    /**
     * @var string the userId
     */
    public $user;

    /**
     * @var string the order id
     */
    public $orderId;

    /**
     * @param int $studentId the id of the student to edit
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($products = null, $user = null, $orderId = null, $options = null)
    {
        if ($products) {
            $this->products = $products;
        }

        $this->user = \Fisdap\EntityUtils::getEntity('User', $user);
        $this->orderId = $orderId;
        parent::__construct($options);
    }

    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/orderIndividualProductsForm.phtml")),
        array('Form', array('class' => 'study-tools-order-form')),
    );

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->addJsFile("/js/library/Account/Form/order-individual-products.js");
        $this->setDecorators(self::$_formDecorators);

        // if we have an order, set it up so we can handle default values
        if ($this->orderId) {
            $order = \Fisdap\EntityUtils::getEntity('Order', $this->orderId);
            foreach ($order->order_configurations as $config) {
                $orderConfigObject = $config;
            }
        }

        $products = new Zend_Form_Element_Checkbox("products");
        $examPractice = 3;
        if (!$this->products) {
            $this->products = \Fisdap\EntityUtils::getEntityManager()->getRepository("\Fisdap\Entity\Product")->getProductsByCategory($examPractice);
        }
        $this->productCount = count($this->products);
        $count = 0;

        //This variable is used to determine if we need to ask the user for which state they want the transition course
        $includeStatePrompt = false;

        foreach ($this->products as $option) {
            //If any of the products being chosen from are transition course, add a prompt for the user to select the state
            if ($option->category->id == 4) {
                $includeStatePrompt = true;
            }

            // create and add the checkbox
            $product[$count] = new Zend_Form_Element_Checkbox("product" . $count);
            $product[$count]->setLabel($option->name);

            // if we have an order, set the value of selected products to 1
            if ($this->orderId) {
                foreach ($orderConfigObject->getProductArray() as $orderProduct) {
                    // if the config of this checkbox is equal to this products config, check it off
                    if ($option->configuration == $orderProduct['configuration']) {
                        $product[$count]->setValue("1");
                    }
                    // otherwise leave it unchecked
                }
            }

            $this->addElement($product[$count]);

            // create and add a hidden element for the price
            $productPrice[$count] = new Zend_Form_Element_Hidden("price" . $count);
            $productPrice[$count]->setValue($option->price);
            $this->addElement($productPrice[$count]);

            // create and a hidden element for the product configuration
            $productConfig[$count] = new Zend_Form_Element_Hidden("config" . $count);
            $productConfig[$count]->setValue($option->configuration);
            $this->addElement($productConfig[$count]);

            $count++;
        }

        if ($includeStatePrompt) {
            $state = new Zend_Form_Element_Select("state");
            $state->setLabel("State:")
                ->setAttribs(array("class" => "chzn-select", "style" => "width:300px"));
            $states = \Fisdap\EntityUtils::getRepository("ProgramStateAssociation")->findAll();
            foreach ($states as $assoc) {
                $state->addMultiOption($assoc->program->id . "_" . $assoc->state, $assoc->state);
            }
            $this->addElement($state);
        }

        $orderConfig = new Zend_Form_Element_Hidden("orderConfig");
        $orderConfig->setValue("0")
            ->setRequired(true)
            ->addValidator('Digits', true)
            ->addValidator('GreaterThan', true, array('min' => '1'))
            ->addErrorMessage('Please choose at least one product.');
        $this->addElement($orderConfig);

        $orderCost = new Zend_Form_Element_Hidden("orderCost");
        $orderCost->setValue("0");
        $this->addElement($orderCost);

        $userId = new Zend_Form_Element_Hidden("userId");
        $userId->setValue($this->user->id);
        $this->addElement($userId);

        $continue = new \Fisdap_Form_Element_SaveButton("save");
        $continue->setLabel("Check out");
        $this->addElement($continue);

        $this->setElementDecorators(self::$checkboxHTMLDecorators);
        $this->setElementDecorators(self::$elementDecorators, array('state'), true);

        //Populate form values from this order if it exists
        if ($this->orderId) {
            $defaults = array("orderConfig" => $orderConfigObject->configuration,
                "orderCost" => $orderConfigObject->subtotal_cost);

            $this->setDefaults($defaults);
        }
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

            if ($this->orderId) {
                $addConfig = false;
                $order = \Fisdap\EntityUtils::getEntity('Order', $this->orderId);
                $orderConfig = $order->order_configurations->first();
            } else {
                $addConfig = true;
                //Create new order configuration
                $orderConfig = \Fisdap\EntityUtils::getEntity("OrderConfiguration");
                // Create new order
                $order = \Fisdap\EntityUtils::getEntity('Order');
                // set some default billing info
                // got this info from the email submitted, only choose a few options since they haven't logged in yet
                $user = \Fisdap\EntityUtils::getEntity('User', $values['userId']);
                $order->email = $user->email;
                $order->name = $user->first_name . " " . $user->last_name;
            }

            // Update/Setup our configuration
            $orderConfig->configuration = $values['orderConfig'];
            $orderConfig->quantity = 1;
            $orderConfig->individual_cost = $values['orderCost'];
            $order->individual_purchase = 1;
            if ($values['state']) {
                $matches = preg_split("/_/", $values['state']);
                $program_id = $matches[0];

                $order->program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $program_id);
            } else {
                $order->program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', 688);
            }

            if ($addConfig) {
                $order->addOrderConfiguration($orderConfig);
            }

            $order->save();
            $orderConfig->save();

            // also reapply the coupon, if we have an existing order
            if ($order->coupon->id > 0) {
                $order->applyCoupon($order->coupon->id);
            }

            $this->orderId = $order->id;

            return true;
        } else {
            return $this->getMessages();
        }
        return $this;
    }
}
