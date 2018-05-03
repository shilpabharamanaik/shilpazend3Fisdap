<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Test ZendForm
 * @package Reports
 * @author jmortenson
 */
class Reports_Form_TestForm extends Fisdap_Form_Base
{


	/**
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($filters = null, $options = null)
	{
		parent::__construct($options);
	}

	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();

		// Now create the individual elements

		// slider/cert level/groups for second col
		$available = new Zend_Form_Element_Checkbox('available_filters');
		$available->setValue(1);

		$signUpNow = new Zend_Form_Element_Checkbox('available_open_window_filters');
		$signUpNow->setLabel("Hide shifts that are invisible to students.")
				  ->setValue(0);

		// slider/cert level/groups/grad date/students for third col
		$chosen = new Zend_Form_Element_Text('chosen_filters');
		$chosen->addValidator('alnum');

		// Add elements
		$this->addElements(array(

			$available,
			$signUpNow,
			$chosen,
		));
		$available->removeDecorator('Label');
		$chosen->removeDecorator('Label');


		//Set the decorators for the form
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => 'forms/test-form.phtml')),
		));
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
