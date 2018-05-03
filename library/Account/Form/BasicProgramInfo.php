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
 * Form for collecting basic program information
 */

/**
 * @package    Account
 */
class Account_Form_BasicProgramInfo extends Fisdap_Form_Base
{
	/**
	 * @var array the decorators for the form
	 */
	protected static $_formDecorators = array(
        'FormErrors',
		'PrepareElements',
		//array('ViewScript', array('viewScript' => "forms/accountHoldersForm.phtml")),
		"FormElements",
		array('Form'),
	);

	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();

		$this->setDecorators(self::$_formDecorators);
		//$this->addCssFile("/css/library/Account/Form/account-holders.css");
		$this->addJsFile("/js/library/Account/Form/basic-program-info.js");

		$orgType = new Zend_Form_Element_Select("orgType");
		$orgType->setLabel("What type of organization do you represent?")
				->setMultiOptions(array(0 => "Select one", "School", "EMS Provider", "Hospital"))
				->addValidator("NotEmpty", true, array("type" => 'zero'))
				->addErrorMessage("Please choose an organization type.");
		$this->addElement($orgType);

		$profession = new Zend_Form_Element_Select("profession");
		$profession->setLabel("Which profession?")
				   ->addMultiOption(0, "Select one")
				   ->addMultiOptions(\Fisdap\Entity\Profession::getFormOptions())
				   ->addValidator("NotEmpty", true, array("type" => 'zero'))
				   ->addErrorMessage("Please choose a profession.");
		$this->addElement($profession);

		$emsProviderTraining = new Fisdap_Form_Element_jQueryUIButtonset("emsProviderTraining");
		$emsProviderTraining->setLabel("Does your ambulance or fire service offer initial education training?")
				   ->setOptions(array(1 => "Yes", 0 => "No"))
				   ->setValue(1);
		$this->addElement($emsProviderTraining);

		$this->setElementDecorators(self::$elementDecorators);
	}

	public function isValid($post)
	{
		if (in_array($post['orgType'], array(0, 2, 3))) {
			$this->getElement("profession")->clearValidators();
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
		}

		return false;
	}
}
