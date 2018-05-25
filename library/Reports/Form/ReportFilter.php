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
 * Description of ReportFilter
 *
 * @author astevenson
 */

class Reports_Form_ReportFilter extends Fisdap_Form_Base
{
	public $roleName;
	public $programId;
	public $user;
	
	public $isInstructor;
	
	public function setRoleName($value)
	{
		$this->roleName = $value;
		$this->isInstructor = ($value == 'instructor');
	}

	public function setProgramId($value)
	{
		$this->programId = $value;
	}
	
	public function setUser($value) {
		$this->user = $value;
	}
	
	public function init()
	{
		$this->addCssFile("/css/library/Reports/Form/report-filter.css");
		$this->setAttrib("id", "goal-report-form");
		
		$goal = new Reports_Form_Element_GoalSet('goal');
		$goalSets = \Fisdap\EntityUtils::getRepository('Goal')->getProgramGoalSets($this->programId, true);
		if (!empty($goalSets)) {
			foreach ($goalSets as $goalSet) {
				$goalSetOptions[$goalSet->id] = $goalSet->getSummary();
			}
		}
		
		$goal->setOptions(array(
			'value' => $goalSetOptions,	//array(1 => "National Standard Curriculum from ReportFilter"),
		));
		
		$advancedSettings = new Reports_Form_Element_AdvancedSettings('advanced_settings');
				
		if ($this->isInstructor && $this->user->hasPermission('View Reports')) {
			$studentIds = new Zend_Form_Element_Hidden('studentIDs[]'); // an empty hidden field so that the form validator will recognize fields from the multi-student picker as legitimate
			$this->addElement($studentIds);
		}
		
		$educationalSetting = new Reports_Form_Element_EducationalSetting('educational_setting');

		if (!$this->isInstructor) {
			$classmatesDecorators =  array(
				'ViewHelper',
				array('Label', array('placement' => 'APPEND')),
				array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'report-block'))
			);
			$classmatesFilter = new Zend_Form_Element_Checkbox('classmatesFilter');
			$classmatesFilter->setLabel("Include your graduation class in the report (anonymized)")
			->setDecorators($classmatesDecorators);
		}

		$saveButton = new Fisdap_Form_Element_SaveButton('Submit');		//Zend_Form_Element_Submit
		$saveButton->setOptions(array(
			'decorators' => array(
				'ViewHelper',
				array('HtmlTag', array('tag'=>'div', 'class'=>'floating-button-container')),
			),
			'label' => 'Go',
		));
		
		//Removing the cancel button at louise's request
		// activate cancel button if there is 'back link' present
		//$pageTitleLinkURL = $this->getView()->pageTitleLinkURL;
		//if ($pageTitleLinkURL) {
		//	$cancelButton = new Fisdap_Form_Element_GrayButton('Cancel');		//Zend_Form_Element_Submit
		//	$cancelButton->setAttrib('id', 'cancel-button')
		//	 ->setAttrib('onclick','window.location =\''. $pageTitleLinkURL .'\' ')
		//	 ->setOptions(array(
		//		'decorators' => array(
		//			'ViewHelper',
		//			array('HtmlTag', array('tag'=>'div', 'class'=>'floating-button-container')),
		//		),
		//		//'label' => 'Cancel',
		//	));
		//	 
		//	$redirectTo = new Zend_Form_Element_Hidden('cancel_redirect_to');
		//	$redirectTo->setValue($pageTitleLinkURL);
		//}
		
		
		$this->addElement($goal);
		if ($this->isInstructor) {
			// NO LONGER USING OLD STUDENT FILTER: REMOVE ME: $this->addElement($studentFilter);
			// $this->setElementDecorators(array('ViewHelper'), array('student'));
		}
		
		// output setting disabled until we have these features
		//$this->addElements(array($educationalSetting, $advancedSettings, $outputSettings, $saveButton));
		$this->addElements(array($educationalSetting, $advancedSettings));
		//if ($pageTitleLinkURL) {
		//	$this->addElements(array($cancelButton, $redirectTo));
		//}
		
		if (!$this->isInstructor) {
			$this->addElement($classmatesFilter);
		}
		
		$this->addElement($saveButton);
		
		$this->setElementDecorators(array('ViewHelper'), array('goal', 'educational_setting', 'advanced_settings', 'output_settings'), true);
		
		$this->setDecorators(array(
            'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => "forms/reportFilter.phtml")),
            array('Description', array('placement' => 'prepend')),
			'Form',
		));
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

?>