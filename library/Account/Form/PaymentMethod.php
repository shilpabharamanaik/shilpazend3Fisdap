<?php

use Fisdap\Entity\Order;
use Fisdap\Entity\PaymentMethod;

/**
 * @package Account
 */
class Account_Form_PaymentMethod extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    private static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        'FormElements',
        array('Form'),
    );
    
    /**
     * @var Order
     */
    private $order;


    /**
     * Account_Form_PaymentMethod constructor.
     *
     * @param Order $order
     * @param mixed $options
     */
    public function __construct(Order $order, $options = null)
    {
        $this->order = $order;
        
        parent::__construct($options);
    }
    
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();
        
        $this->addJsFile("/js/library/Account/Form/payment-method.js");
        
        $paymentMethod = new Zend_Form_Element_Radio('paymentMethod');
        $paymentMethod->setLabel("How do you want to pay?")
                      ->setMultiOptions(PaymentMethod::getFormOptions(false, false, "description"))
                      ->setDecorators(self::$elementDecorators);
        $this->addElement($paymentMethod);
        
        $orderId = new Zend_Form_Element_Hidden('orderId');
        $orderId->setValue($this->order->id)
                ->setDecorators(self::$hiddenElementDecorators);
        $this->addElement($orderId);
    }
}
