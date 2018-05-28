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
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * Custom Zend_Form_Element_Select for displaying bases
 */

/**
 * @package Fisdap
 */
class Fisdap_Form_Element_Bases extends Zend_Form_Element_Select
{
	public function init()
	{
		$options = array(-1 => "All Bases");
		$this->setMultiOptions($options);
		$this->setAttrib('class', 'bases');
		$this->setRegisterInArrayValidator(false);
	}
}