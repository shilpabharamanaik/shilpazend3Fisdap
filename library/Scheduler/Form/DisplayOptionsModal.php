<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This produces a modal form for editing a users filter display options (triggered from month details view only)
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 * @author 	   Hammer :)
 */
class Scheduler_Form_DisplayOptionsModal extends Fisdap_Form_BaseJQuery
{
	/**
	 * @var \Fisdap\Entity\User
	 */
	public $user;
	
	/**
	 * @var \Fisdap\Entity\SchedulerFilterSet
	 */
	public $filter_display_options;
	
	/**
	 *
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($filter_set = null, $options = array())
	{
		$this->user = \Fisdap\Entity\User::getLoggedInUser();
		
		if($filter_set){
			$this->filter_display_options = $filter_set->getDisplayOptionsArray();
		}
		
		parent::__construct($options);
	}
	
	public function init()
	{
		parent::init();
		
		if($this->filter_display_options){
			foreach($this->filter_display_options as $option_name => $option_data){
				
				$slider_checkbox = new Zend_Form_Element_Checkbox($option_name . "_slider_checkbox");
				$slider_checkbox->setValue($option_data['value']);
				$this->addElement($slider_checkbox);
				
			}
		}
		
		//Set the decorators for the form
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => "display-options-modal.phtml")),
		));
	}
	
}
