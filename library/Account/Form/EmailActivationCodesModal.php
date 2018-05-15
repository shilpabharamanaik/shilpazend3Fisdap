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
 * Form for processing an activation code
 */

/**
 * @package    Account
 */
class Account_Form_EmailActivationCodesModal extends Fisdap_Form_Base
{
    
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/emailActivationCodesModal.phtml")),
        array('Form'),
    );
    
    /**
     * @var array of activation codes
     */
    public $activationCodes;
    
    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($options = null)
    {
        //$this->activationCodes = $activationCodes;
        parent::__construct($options);
    }
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();
        
        $this->addCssFile("/css/library/Account/Form/email-codes.css");
        $this->addJsFile("/js/library/Account/Form/email-codes.js");
        $this->setDecorators(self::$_formDecorators);
        
        $message = new Zend_Form_Element_Textarea("message");
        $message->setLabel("Message:");
        $message->getDecorator('Label')->setOptions(array('tag' => 'h3', 'class' => 'section-header no-border'));
        $this->addElement($message);
    }
    
    public function createInputs($activationCodes = null)
    {
        foreach ($activationCodes as $code) {
            $codeElement = new Zend_Form_Element_Checkbox("codes_" . $code->id);
            $codeElement->setLabel($code->number)
                           ->setValue(1);
            $this->addElement($codeElement);
        }
    }
    
    public function process()
    {
    }
}
