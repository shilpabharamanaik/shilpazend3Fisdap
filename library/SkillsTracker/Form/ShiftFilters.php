<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
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
 * This produces a form for shift filters
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_ShiftFilters extends Zend_Form
{
	/**
	 * @var array form decorators
	 */
	public static $formDecorators = array(
        'FormElements',
        'Form',
    );
	
	/**
	 * @var array decorators for individual elements
	 */
    public static $elementDecorators = array(
        'ViewHelper',
		array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
        'Label',
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'shift-list-filter')),
    );
	
	public static $checkboxDecorators = array(
        'ViewHelper',
        array('Label', array('placement' => 'append', 'class' => 'pending-label')),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'pending-checkbox')),
    );
	
    public static $grayButtonDecorators = array(
        'ViewHelper',
		array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'shift-list-button gray-button extra-small')),
    );
	
	public static $greenButtonDecorators = array(
        'ViewHelper',
		array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'shift-list-button green-buttons extra-small')),
    );
	
	/**
	 * @var array the list of shift filters
	 */
	protected static $_shiftsFilterOptions = array(
		'field' => 'Field',
		'clinical' => 'Clinical',
		'lab' => 'Lab',
	);
	
	/**
	 * @var array the list of attendance filters
	 */
	protected static $_attendanceFilterOptions = array(
		1 => 'On time',
		2 => 'Tardy',
		3 => 'Absent',
		4 => 'Absent w/ permission',
	);
	
	/**
	 * @var array the list of shift filters
	 */
	protected static $_datesFilterOptions = array(
		'all' => 'All dates',
		'past' => 'Past',
		'future' => 'Future',
		'peri' => '6 weeks before and after today',
	);
	
	
	public function init()
	{
		// DATE RANGE
		$datefilters = new Zend_Form_Element_Select('dateFilters');
		$datefilters->setLabel('Shift dates')
				->setMultiOptions(self::$_datesFilterOptions)
				->setAttribs(array("class" => "chzn-select do-not-filter",
								"style" => "width: 420px",
								"multiple" => false));
		$this->addElement($datefilters);
		
		// ATTENDANCE TYPE
		$atfilters = new Zend_Form_Element_Select('attendanceFilters');
		$atfilters->setLabel('Attendance')
				->setMultiOptions(self::$_attendanceFilterOptions)
				->setAttribs(array("class" => "chzn-select do-not-filter",
								"style" => "width: 420px",
								"data-placeholder" => 'All attendance types...',
								"multiple" => true));
		$this->addElement($atfilters);
		
		// SHIFT TYPE
		$filters = new Zend_Form_Element_Select('shiftsFilters');
		$filters->setMultiOptions(self::$_shiftsFilterOptions)
			 ->setLabel('Shift type')
			 ->setAttribs(array("class" => "chzn-select do-not-filter",
								"style" => "width: 420px",
								"data-placeholder" => 'All shift types...',
								"multiple" => true));
		$this->addElement($filters);
		
		// pending
		$pending = new Zend_Form_Element_Checkbox('pending');
		$pending->setLabel('Show only pending shifts')
			 ->setAttribs(array("class" => "do-not-filter"));
		$this->addElement($pending);
		
		// buttons
		$filterButton = new Zend_Form_Element_Button('filter');
		$filterButton->setOptions(array(
			'label' => 'Go',
			'id' => 'filter-shifts-button',
			'class' => 'jq-button',
			'decorators' => array(
				'Viewhelper'
			)
		));
		$filterButton->removeDecorator('Label');
		$this->addElement($filterButton);
		
		$resetButton = new Zend_Form_Element_Button('reset');
		$resetButton->setOptions(array(
			'label' => 'Reset filters',
			'id' => 'reset-filter-button',
			'class' => 'jq-button',
			'decorators' => array(
				'Viewhelper'
			)
		));
		$resetButton->removeDecorator('Label');
		$this->addElement($resetButton);
		
		
		$this->setDecorators(self::$formDecorators);
		$this->setElementDecorators(self::$elementDecorators, array('shiftsFilters',
																	'attendanceFilters',
																	'dateFilters'));
		$this->setElementDecorators(self::$checkboxDecorators, array('pending'));
		$this->setElementDecorators(self::$grayButtonDecorators, array('reset'));
		$this->setElementDecorators(self::$greenButtonDecorators, array('filter'));
	}
}