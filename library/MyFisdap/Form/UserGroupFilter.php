<?php
/*	*	*	*	*	*	*	*	*
 *
 *	Copyright (C) 1996-2011.  This is an unpublished work of
 *			Headwaters Software, Inc.
 *				ALL RIGHTS RESERVED++++
 *	This program is a trade secret of Headwaters Software, Inc.
 *	and it is not to be copied, distributed, reproduced, published,
 *	or adapted without prior authorization
 *	of Headwaters Software, Inc.
 *
 *	*	*	*	*	*	*	*	*/

/**
 * Flexible filter for selecting groups of users, based on Reports_Form_ReportFilter 
 *
 * @author jmortenson
 */

class MyFisdap_Form_UserGroupFilter extends Fisdap_Form_Base
{

	protected $programId;
	
	protected $filters;
	
	protected $defaultFilters;
	
	/**
	 * Constructs the Message Creation form
	 * @param array $filtersEnabled An array of filter names that should be enabled
	 * @param integer $programId An optoinal program ID to limit the users available
	 */
	public function __construct($filtersEnabled = array(), $programId = null)
	{
	
		// Which filter/setting options shoudl be displayed?
		$this->defaultFilters = array(
		    'StudentFilter' => TRUE,
		    'RoleFilter' => FALSE,
		    'Everyone' => FALSE,
		    'ProgramFilter' => FALSE
		);

		// Set enabled filters
		$this->setFilters($filtersEnabled);
		
		// set programId
		if (is_numeric($programId)) {
			$this->programId = $programId;
			
			// if a Program ID is supplied, disallow use of the ProgramFilter
			$loggedInUser =  \Fisdap\Entity\User::getLoggedInUser();
			if ($loggedInUser->staff != NULL && $loggedInUser->staff->isStaff()) {
				$this->filters['ProgramFilter'] = TRUE;
			}else{
				$this->filters['ProgramFilter'] = FALSE;
			}
		} else {
			$this->programId = null;
		}
		
		parent::__construct();
	}
    
	public function setFilters($filtersEnabled)
	{
		if (empty($filtersEnabled)) {
		    $this->filters = $this->defaultFilters;
		} else {
		    foreach($filtersEnabled as $option) {
			if (array_key_exists($option, $this->defaultFilters)) {
				$this->filters[$option] = TRUE;
			}
		    }
		}
	}

	public function setProgramId($value)
	{
		$this->programId = $value;
	}
	
	public function init()
	{
		$this->addCssFile("/css/library/Reports/Form/report-filter.css");
		
		// collect elements for inclusion and decoration
		$elements = array();
		$toBeDecorated = array();
		
		// Add a hidden field to store any programId value, used by student picker
		$programField = new Zend_Form_Element_Hidden('picker_program_id');
		if ($this->programId) {
			$programField->setValue($this->programId);
		} else {
			$programField->setValue(0); // essentially no value, student picker defaults to cur user's program
		}
		$elements[] = $programField;
		$toBeDecorated[] = 'picker_program_id';
						
		// Everyone
		if ($this->filters['Everyone']) {
			// Extra permissions check here, because we really really only want staff to use this
			$loggedInUser =  \Fisdap\Entity\User::getLoggedInUser();
			if ($loggedInUser->isStaff()) {
				$everyone = new Zend_Form_Element_Checkbox('everyone');
				$everyone->setLabel("Err'body in Fisdap! Careful...")
					->setDecorators(array(
						'ViewHelper',
						array('Label', array('placement' => 'APPEND'))
					));
				$elements[] = $everyone;
			}
		}
		// ProgramFilter
		if ($this->filters['ProgramFilter']) {
			// Extra permissions check here, because we really really only want staff to use this
			if (!isset($loggedInUser)) {
				$loggedInUser =  \Fisdap\Entity\User::getLoggedInUser();
			}
			if ($loggedInUser->isStaff()) {
				$programFilter = new Zend_Form_Element_Select('program');
				
				// Get the list of programs
				$programRepo = \Fisdap\EntityUtils::getRepository('ProgramLegacy');
				$allPrograms = $programRepo->getAllPrograms();
				$programFilterOptions = array(0 => '-- All Programs --');
				foreach($allPrograms as $id => $program) {
					$programFilterOptions[$id] = $program['name'];
				}
				
				$programFilter->setLabel("Just people in this program")
					->addMultiOptions($programFilterOptions)
					->setDecorators(array(
						'ViewHelper',
						array('Label', array('placement' => 'PREPEND')),
						array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),            
					));
				$elements[] = $programFilter;
			}
		}
		
		// RoleFilter
		if ($this->filters['RoleFilter']) {
			$roleFilter = new Zend_Form_Element_MultiCheckbox('user_context');
			// Idea: staff => Staff could be added to the options below...
			$roleFilter->addMultiOptions(array('instructor' => 'Instructors', 'student' => 'Students'))
				->setLabel("Send announcement to:")
				->setSeparator('&nbsp;&nbsp;&nbsp;')
				->setDecorators(array(
					'ViewHelper',
					array(array('optionsWrapper' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-multi-options', 'id' => 'filter-role-options')),
					array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
					array('LabelDescription', array('escape' => false)),
					array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt', 'id' => 'filter-role')),
				    ));
			$elements[] = $roleFilter;
			
		}
		
		// StudentFilter
		if ($this->filters['StudentFilter']) {
			$studentFilter = new Reports_Form_Element_StudentFilter('student', NULL, 'checkboxes');
			$studentFilter->setDecorators(array(
                            'ViewHelper',
                            array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt', 'id' => 'filter-student')),            
                        ));
			$elements[] = $studentFilter;
		}

		

		$this->addElements($elements);
		
		
		$this->setElementDecorators(array('ViewHelper'), $toBeDecorated, true);
		
		// add our JS at the end, because we want to interact with subform elements
		$this->getView()->headScript()->appendFile("/js/library/MyFisdap/Form/user-group-filter.js");
	}
	
	public function process($post)
	{
		if ($this->isValid($post)) {
			$values = $this->getValues();
			// set data options
			
			// start date defaults to beginning of 'millenium';)
			if (empty($values['advanced_settings']['startdate'])) {
				$values['advanced_settings']['startdate'] = '01/01/2000';
			}
			$values['advanced_settings']['startdate'] = new \DateTime($values['advanced_settings']['startdate']);
			
			// end date defaults to today
			$values['advanced_settings']['enddate'] = new \DateTime($values['advanced_settings']['enddate']);
			
			// no patient types selected means ALL
			if (empty($values['advanced_settings']['patient-types'])) {
				$values['advanced_settings']['patient-types'] = array(1, 2, 3, 4, 5, 6);
			}
			
			// no shift types selected means ALL
			if (empty($values['educational_setting']['shifttype'])) {
				$values['educational_setting']['shifttype'] = array('field', 'clinical', 'lab');
			}
			
			return $values;
		}
		
	}
}