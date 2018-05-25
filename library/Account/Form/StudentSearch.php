<?php

/**
 * Form for searching for a Fisdap student user
 */

class Account_Form_StudentSearch extends Fisdap_Form_Base
{

	/**
	 * Create and add all the elements to the form
	 */
	public function init()
	{
		$session = new Zend_Session_Namespace("accountEditController");
		
		parent::init();

		$this->addCssFile("/js/tableSorter/themes/blue/style.css");
		$this->addCssFile("/css/library/Account/Form/student-search-filters.css");
		
		//create certification form element
		$cert = new Fisdap_Form_Element_CertificationLevel("certificationLevels");
		$cert->setLabel('Certification Level:');
		
		//Check for a previous search and set defaults
		if(isset($session->prevSearch['certificationLevels']))
		{
			$cert->setValue($session->prevSearch['certificationLevels']);
		}
		
		$this->addElement($cert);
		
		//create graduation date form element
		$grad = new Fisdap_Form_Element_GraduationDate("grad");
		$grad->useExistingGraduationYears();
		
		$cachedDate = array();
		
		
		if(isset($session->prevSearch['graduationMonth']))
		{
			$cachedDate['month'] = $session->prevSearch['graduationMonth'];

		}
		
		if(isset($session->prevSearch['graduationYear']))
		{
			$cachedDate['year'] = $session->prevSearch['graduationYear'];
		}
		$grad->setValue($cachedDate);
		
		$this->addElement($grad);
		
		//create graduation status form element
		$status = new Zend_Form_Element_MultiCheckbox("status");
		
		$statusOptions = array(
			1 => "Active",
			4 => "Left Program",
			2 => "Graduated",
		);
		
		$status->setMultiOptions($statusOptions);
		
		//Check for previous search
		if(isset($session->prevSearch['gradStatus']))
		{
			$status->setValue($session->prevSearch['gradStatus']);
		}
		
		$status->setLabel("Graduation Status:");
		$this->addElement($status);
		
		//create groups form element
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$classSectionRepository = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
		$groups = $classSectionRepository->getFormOptions($user->getProgramId());
		$groups['Any group'] = "Any group";
		krsort($groups);
		
		$groupSelect = new Zend_Form_Element_Select('groups');
		$groupSelect->setMultiOptions($groups);
		
		//Check for previous search
		if(isset($session->prevSearch['section']))
		{
			$groupSelect->setValue($session->prevSearch['section']);
		}
		
		$groupSelect->setAttribs(array("style"=>"width:250px"))
					->setLabel('Groups:');
		$this->addElement($groupSelect);	

		$this->setAttrib("id", "filter-form");
		
		//Set the decorators for the form
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => "student-filters.phtml")),
		));
	}
	
	
}