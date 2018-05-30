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
 * SubForm for entering email addresses to send activation codes
 */

/**
 * @package    Account
 */
class Account_Form_ActivationCodesSubForm extends Zend_Form_SubForm
{
    /**
     * @var array
     */
    protected static $subformDecorators = array(
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/activationCodesSubForm.phtml")),
        array('HtmlTag', array('tag' => 'div', 'class' => 'activation-codes')),
    );
    
    /**
     * @var \Fisdap\Entity\OrderConfiguration
     */
    public $order_configuration;
    
    /**
     * @var array containing available serial numbers to email
     */
    public $serials;
    
    public function __construct($orderConfigurationId = null, $options = null)
    {
        $this->order_configuration = \Fisdap\EntityUtils::getEntity('OrderConfiguration', $orderConfigurationId);
        $this->serials = $this->order_configuration->serial_numbers;
        
        parent::__construct($options);
    }
    
    public function init()
    {
        $message = new Zend_Form_Element_Textarea("message");
        $message->setLabel("Message for students: (optional)")
                ->setAttrib("class", "message");
        $this->addElement($message);
        
        foreach ($this->serials as $sn) {
            $serialElement = new Fisdap_Form_Element_Email("serial_" . $sn->id);
            $serialElement->setDecorators(array("ViewHelper"))
                          ->setAttrib("class", "serialNumber")
                          ->addErrorMessage("Please choose a valid email address");
            
            if ($sn->isActive()) {
                $serialElement->setValue("Activated by " . $sn->user->getName() . " on " . $sn->activation_date->format("m-d-Y"))
                              ->setAttrib("disabled", "disabled");
            } elseif ($sn->distribution_email) {
                $serialElement->setValue("Sent to " . $sn->distribution_email . " on " . $sn->distribution_date->format("m-d-Y"))
                              ->setAttrib("disabled", "disabled");
            }
            
            $this->addElement($serialElement);
        }
        
        $orderConfigurationId = new Zend_Form_Element_Hidden("orderConfigurationId");
        $orderConfigurationId->setValue($this->order_configuration->id)
                             ->setAttrib("class", "orderConfigurationId")
                             ->setDecorators(array('ViewHelper'));
        $this->addElement($orderConfigurationId);
        
        $this->setDecorators(self::$subformDecorators);
    }
}
