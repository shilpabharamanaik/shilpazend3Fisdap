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
 * Description of LabPracticeReportFilter
 *
 * @author jmortenson
 */

class Reports_Form_LabPracticeReportFilter extends Fisdap_Form_Base
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
		$this->addJsFile("/js/library/Reports/Form/lab-practice-report-filter.js");
		$this->addCssFile("/css/library/Reports/Form/report-filter.css");
		$this->setAttrib("id", "lab-practice-report-form");
		
		// certification level
		$certLevel = new Fisdap_Form_Element_jQueryUIButtonset('certLevel');
		$certLevel->setRequired(($this->isInstructor));
		$certLevel->setLabel('Certification Level');
		$certOptions = \Fisdap\EntityUtils::getRepository('CertificationLevel')->getSortedFormOptions(\Fisdap\Entity\ProgramLegacy::getCurrentProgram()->profession->id);		$certLevel->setOptions($certOptions);
		$certLevel->setDecorators(array(
				'ViewHelper',
				array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_9')),
				array('Label', array('tag' => 'div', 'class' => 'grid_3', 'escape' => false)),
				array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
			));
		$this->addElement($certLevel); 

		// Report type/mode
		$reportType = new Fisdap_Form_Element_jQueryUIButtonset('reportType');
		$reportType->setRequired(true);
		$reportType->setLabel('View Options');
		$reportType->setOptions(array('summary' => 'Summary', 'detailed' => 'Detailed'));
		$reportType->setDecorators(array(
				'ViewHelper',
				array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_9')),
				array('Label', array('tag' => 'div', 'class' => 'grid_3', 'escape' => false)),
				array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
			));
		$this->addElement($reportType); 
		
				
		if ($this->isInstructor && $this->user->hasPermission('View Reports')) {
			$studentIds = new Zend_Form_Element_Hidden('studentIDs[]'); // an empty hidden field so that the form validator will recognize fields from the multi-student picker as legitimate
			$this->addElement($studentIds);
		}
		

		$includeClassmates = new Zend_Form_Element_Checkbox('includeClassmates');
		$includeClassmates->setLabel("Include your graduation class in the report (anonymized)")
						  ->setAttrib("checked", "checked");
		$this->addElement($includeClassmates);
		

		$saveButton = new Fisdap_Form_Element_SaveButton('Submit');		//Zend_Form_Element_Submit
		$saveButton->setOptions(array(
			'decorators' => array(
				'ViewHelper',
				array('HtmlTag', array('tag'=>'div', 'class'=>'floating-button-container')),
			),
			'label' => 'Go',
		));
		
		$startDateValue = new DateTime("-1 year");
		$endDateValue = new DateTime();
		$startDate = new Zend_Form_Element_Text("start_date");
		$startDate->setValue($startDateValue->format("m/d/Y"))
				  ->setAttrib("tabindex", -1)
				  ->setAttrib("class", "selectDate");
		
		$this->addElement($startDate);

		$endDate = new Zend_Form_Element_Text("end_date");
		$endDate->setValue($endDateValue->format("m/d/Y"))
				  ->setAttrib("tabindex", -1)
				  ->setAttrib("class", "selectDate");
				  
		$this->addElement($endDate);

		
		$this->addElement($saveButton);
		
		$this->setElementDecorators(array('ViewHelper'), array('advanced_settings'), true);
		
		$this->setDecorators(array(
            'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => "forms/labPracticeReportFilter.phtml")),
            array('Description', array('placement' => 'prepend')),
			'Form',
		));
		
		// check a user session for saved filters, set some defaults
		$sess = new Zend_Session_Namespace('labPracticeReport');
		$this->setDefaults(array(
			'certLevel' => ($sess->certLevel) ? $sess->certLevel : reset(array_keys($certOptions)),
			'reportType' => ($sess->reportType) ? $sess->reportType : 'summary',
			));
	}
	
	public function process($post)
	{
		$values = $this->getValues();
		return $values;
		
	}
	

}

?>