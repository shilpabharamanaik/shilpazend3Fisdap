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
 * Form for editing a Fisdap Student account
 */

/**
 * @package    Account
 */
class Account_Form_Products extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/productsForm.phtml")),
        array('Form', array('class' => 'products-form')),
    );
    
    /**
     * @var array decorators for products
     */
    public static $productDecorators = array(
        'ViewHelper',
        array('Label', array('placement' => 'APPEND')),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'product-prompt')),
    );
    
    /**
     * @var integer the configuration code for
     * products that the user has already added
     * via a package.
     */
    public $packageConfiguration;
    
    /**
     * @var \Fisdap\Entity\Order
     */
    public $order;
    
    /**
     * @var array contain the products that are displayed
     */
    public $products;
    
    /**
     * @var \Fisdap\Entity\CertificationLevel
     */
    public $certification;
    
    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($certificationId, $packageConfiguration = 0, $orderId = null, $options = null)
    {
        $this->order = \Fisdap\EntityUtils::getEntity('Order', $orderId);
        $this->certification = \Fisdap\EntityUtils::getEntity('CertificationLevel', $certificationId);
        $this->packageConfiguration = $packageConfiguration;
        
        if (!($packageConfiguration & 64)) {
            $packageConfiguration += 64;
        }
        
        $packageConfiguration = $packageConfiguration | $this->certification->configuration_blacklist;
        
        $this->products = \Fisdap\EntityUtils::getRepository("Product")->getProducts($packageConfiguration, false, false, \Fisdap\Entity\User::getLoggedInUser()->isStaff(), false);
        
        parent::__construct($options);
    }
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setAttrib("id", "productsForm");

        $this->addCssFile("/css/library/Account/Form/products.css");
        $this->addJsFile("/js/library/Account/Form/products.js");
        
        $this->setDecorators(self::$_formDecorators);
        
        foreach ($this->products as $product) {
            $productElement = new Zend_Form_Element_Checkbox("products_" . $product->id);
            $productElement->setLabel($product->staff_only ? $product->name . "*" : $product->name)
                           ->setCheckedValue($product->configuration)
                           ->setDecorators(self::$productDecorators);
            //->setAttribs(array('name' => 'products[]', 'id' => 'products' . $product->id));
            $this->addElement($productElement);
        }
        
        
        if ($this->orderConfiguration->id) {
            $this->setDefaults(array(

            ));
        }
    }
    
    public function isValid($post)
    {
        $configuration = 0;
        foreach ($this->products as $product) {
            $configuration += $post['products_' . $product->id];
        }
        
        if (($configuration + $this->packageConfiguration) == 0) {
            $this->addError("Please choose a product.");
        }
        
        return parent::isValid($post);
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
            
            //Calculate the configuration of products from this form
            $configuration = 0;
            foreach ($this->products as $product) {
                if ($values['products_' . $product->id] > 0) {
                    $configuration += $values['products_' . $product->id];
                }
            }
            
            //Grab session data for the previous 2 forms to put it all together
            $session = new \Zend_Session_Namespace("OrdersController");
            $accountInfo = $session->accountHoldersFormValues;
            $packageInfo = $session->packagesFormValues;
            
            //Create new order if one does not already exist
            if (!$this->order->id) {
                $this->order = \Fisdap\EntityUtils::getEntity('Order');
                $this->order->user = \Fisdap\Entity\User::getLoggedInUser();
                $this->order->order_type = $accountInfo['payment'];
            }
            
            //Create the current order configuration
            $orderConfig = \Fisdap\EntityUtils::getEntity("OrderConfiguration");
            $orderConfig->configuration = $configuration + $this->packageConfiguration;
            $orderConfig->certification_level = $accountInfo['certification'];
            $orderConfig->group = $accountInfo['group']['id'];
            if ($accountInfo['gradDate']['year'] && $accountInfo['gradDate']['month']) {
                $orderConfig->graduation_date = new \DateTime($accountInfo['gradDate']['year'] . "-" . $accountInfo['gradDate']['month'] . "-01");
            }
            
            if ($accountInfo['payment'] == 2 || $this->order->order_type->id == 2) {
                $orderConfig->quantity = 1;
            } else {
                $orderConfig->quantity = $accountInfo['quantity_students'];
            }
            
            //Attach new order config to the order and calculate price
            $this->order->addOrderConfiguration($orderConfig);
            $orderConfig->calculateFinalPrice();
            
            //If students are paying for these accounts, create a product code for the order configuration
            if ($this->order->order_type->id == 2) {
                $orderConfig->generateProductCode();
            }
            
            //Flush Doctrine to save our changes
            $this->order->save();

            //Clear session variables and then set the order ID
            $session->unsetAll();
            $session->orderId = $this->order->id;
            
            return true;
        }
    }
    
    /**
     * Validate the form to make sure at least one product has been selected
     * Then add up the coniguration and return it.
     *
     * This function is different from the regular process() method because it's being used for upgrading accounts
     *
     * @param array $post the POSTed values from the user
     * @return integer the configuration for the selected products
     */
    public function processUpgrade($post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();
            
            //Calculate the configuration of products from this form
            $configuration = 0;
            foreach ($this->products as $product) {
                if ($values['products_' . $product->id] > 0) {
                    $configuration += $values['products_' . $product->id];
                }
            }
            
            return $configuration;
        }
        
        return false;
    }
}
