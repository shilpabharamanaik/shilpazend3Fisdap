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
class Account_Form_EditCertificationModal extends Fisdap_Form_Base
{
	
	/**
	 * @var array the decorators for the form
	 */
	protected static $_formDecorators = array(
        'FormErrors',
		'PrepareElements',
		array('ViewScript', array('viewScript' => "forms/editCertificationModal.phtml")),
		array('Form'),
	);
	
	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();
		
		$this->addCssFile("/css/library/Account/Form/update-cert-level.css");
		$this->addJsFile("/js/library/Account/Form/update-cert-level.js");
		$this->setDecorators(self::$_formDecorators);
		
		$levels = new Zend_Form_Element_Select("certLevels");
		$levels->addMultiOptions(\Fisdap\Entity\CertificationLevel::getFormOptions(false, true, "description", 1, false));
		$levels->setLabel("Set the certification level to:");
		$this->addElement($levels);
		
	}

	public function process()
	{
		
	}
}