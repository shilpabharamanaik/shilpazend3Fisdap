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
 * Class creating goal set element
 *
 * @package Reports
 */
class Reports_Form_Element_GoalSet extends Zend_Form_Element_Xhtml
{

	/**
	 * @var string the name of the iv site
	 */
	protected $_goalName;

	/**
	 * @var string the view helper that will render this composite element
	 */
	public $helper = "goalSetElement";

	public function init()
	{
		//jquery setup
		if (!$this->_view) {
			$this->_view = $this->getView();
		}
	}

	public function getValue()
	{
		$value = parent::getValue();
		if (empty($value)) {
			$value = array(1 => 'National Standard Curriculum');
		}
		return $value;
	}
	
	//public function setValue($value)
	//{
	//	if (empty($value)) {
	//		$value = array(1 => 'National Standard Curriculum');
	//	}
	//	return parent::setValue($value);
	//}
}
