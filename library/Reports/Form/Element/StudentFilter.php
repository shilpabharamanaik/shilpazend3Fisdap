<?php

/****************************************************************************
*
*         Copyright (C) 1996-2011.  This is an unpublished work of
*                          Headwaters Software, Inc.
*                             ALL RIGHTS RESERVED
*         This program is a trade secret of Headwaters Software, Inc.
*         and it is not to be copied, distributed, reproduced, published,
*         or adapted without prior authorization
*         of Headwaters Software, Inc.
*
****************************************************************************/

/**
 * Description of StudentFilter
 *
 * @author astevenson
 */
class Reports_Form_Element_StudentFilter extends Zend_Form_Element_Xhtml
{
	/**
	 * @var string the view helper that will render this composite element
	 */
	public $helper = "studentFilterElement";
	
	public function init(){
		$this->getView()->headScript()->appendFile("/js/library/Reports/Form/Element/student-filter.js");
	}
	
	public function __construct($name, $options = null, $studentListType = 'select')
	{
		if ($studentListType == 'select') {
			$this->helper = 'studentFilterElement';
		} else if ($studentListType == 'checkboxes') {
			$this->helper = 'studentGroupFilterElement';
		}
		
		parent::__construct($name, $options);
	}
}
