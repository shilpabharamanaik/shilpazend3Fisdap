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
 * Form for upgrading students
 */

/**
 * @package    Account
 */
class Account_Form_UpgradeInstructorAccounts extends Fisdap_Form_Base
{
	/**
	 * @var array the decorators for the form
	 */
	protected static $_formDecorators = array(
		'FormErrors',
		array('ViewScript', array('viewScript' => "forms/upgradeInstructorAccountsForm.phtml")),
		array('Form', array('class' => 'upgrade-instructor-accounts-form')),
	);

    /**
     * @var array
     */
	public $instructors;

    /**
     * @var array
     */
    public $products;

    /**
     * @param int $configuration
     * @param     $options mixed additional Zend_Form options
     */
	public function __construct($configuration = 64, $options = null)
	{
		$this->instructors = \Fisdap\EntityUtils::getRepository('ProgramLegacy')->getInstructors(\Fisdap\Entity\User::getLoggedInUser()->getProgramId());
		$this->products = \Fisdap\EntityUtils::getRepository('Product')->getProducts($configuration, true);
		parent::__construct($options);
	}
	
	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();

		$this->setAttrib("id", "upgrade-instructors-form");
		$this->addJsFile("/js/library/Account/Form/upgrade-instructor-accounts.js");
		$this->addJsFile("https://cdnjs.cloudflare.com/ajax/libs/floatthead/1.2.8/jquery.floatThead.min.js");
		$this->addCssFile("/css/library/Account/Form/upgrade-instructor-accounts.css");
		
		//Loop over each instructor to add the necessary checkboxes
		foreach($this->instructors as $instructor){
			$instructorSn = $instructor->getUserContext()->getPrimarySerialNumber();

            //Loop over the possible products and add if they don't have that product
            foreach($this->products as $product) {
                if (!$instructorSn || !$instructorSn->hasProduct($product->configuration)) {
                    $instructorElement = new Zend_Form_Element_Checkbox("instructors_" . $instructor->id . "_products_" . $product->id);
                    $instructorElement->setAttribs(["data-price" => $product->price, 'data-productid' => $product->id, "class" => "product"]);
                    $this->addElement($instructorElement);
                }
            }
		}
		
		$this->setDecorators(self::$_formDecorators);
        $this->setElementDecorators(['ViewHelper']);
	}

    /**
     * @param array $post
     *
     * @return bool
     * @throws Zend_Form_Exception
     */
	public function process(array $post)
	{
        if ($this->isValid($post)) {
            $values = $this->getValues();

            //Create new order for upgrade
            $order = \Fisdap\EntityUtils::getEntity("Order");
            $order->user = \Fisdap\Entity\User::getLoggedInUser();
            $order->upgrade_purchase = true;
            $order->order_type = 1;

            //Loop over the instructors
            foreach ($this->instructors as $instructor) {

                //Reset the configuration to 0 for each instructor
                $configuration = 0;

                //Now loop over the products and see if any checkboxes are set
                foreach ($this->products as $product) {
                    if ($values['instructors_' . $instructor->id . '_products_' . $product->id]) {
                        //Sum the configuration of an any checked products
                        $configuration += $product->configuration;
                    }
                }

                //If we have any products to upgrade for this instructor, do so now.
                if ($configuration > 0) {
                    //Create and add order config to order
                    $orderConfig = \Fisdap\EntityUtils::getEntity("OrderConfiguration");
                    $order->addOrderConfiguration($orderConfig);

                    //Save the rest of the order config details
                    $orderConfig->upgraded_user = $instructor->user;
                    $orderConfig->configuration = $configuration;
                    $orderConfig->quantity = 1;
                    $orderConfig->calculateFinalPrice();
                }
            }

            //Add order config to order and save
            $order->save();

            $session = new \Zend_Session_Namespace("OrdersController");
            $session->orderId = $order->id;

            return true;
        }

        return false;
	}
}