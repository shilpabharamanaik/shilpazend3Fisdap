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
class Account_Form_Activate extends Fisdap_Form_Base
{
	/**
	 * @var array the decorators for the form
	 */
	protected static $_formDecorators = array(
        'FormErrors',
		'PrepareElements',
        'FormElements',
		//array('ViewScript', array('viewScript' => "forms/activateForm.phtml")),
		array('Form'),
	);
	
	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();
		
		$this->setDecorators(self::$_formDecorators);

        $code = new Zend_Form_Element_Text("code");
        $code->setRequired(true)
             ->setOptions(array('size' => 45));
			 
		$notEmpty = new Zend_Validate_NotEmpty();
		$notEmpty->setMessage('Please enter your activation code. Click the question mark above for help.');
		$code->addValidator($notEmpty, true)
			 ->addValidator(new Account_Validate_ActivationCode());

        $this->addElement($code);
        
        $save = new \Fisdap_Form_Element_SaveButton("save");
        $save->setLabel("Continue");
        $this->addElement($save);
		
        $this->setElementDecorators(array('ViewHelper'));
	}
}
