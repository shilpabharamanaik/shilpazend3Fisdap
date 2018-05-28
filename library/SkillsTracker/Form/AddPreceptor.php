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
 * This produces a modal form for adding a new preceptor
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_AddPreceptor extends SkillsTracker_Form_Modal
{

	/**
	 * @var \Fisdap\Entity\Site
	 */
	protected $site;
	
	/**
	 * @var \Fisdap\Entity\StudentLegacy
	 */
	protected $student;

	/**
	 * @param int $medId the id of the Med to edit
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($studentId, $siteId, $options = null)
	{
		$this->site = \Fisdap\EntityUtils::getEntity('SiteLegacy', $siteId);
		$this->student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $studentId);
		parent::__construct($options);
	}
	
	public function init()
	{
        parent::init();
	
		$first = new Zend_Form_Element_Text('first');
		$first->setLabel('First Name:')
			  ->setRequired(true)
			  ->setDescription("(required)")
			  ->addValidator('regex', false, array("/^[-_a-zA-Z\s.]+$/"))
			  ->addErrorMessage("Please provide a valid first name. Names can only contain letters and dashes.");
		
		$last = new Zend_Form_Element_Text('last');
		$last->setLabel('Last Name:')
			 ->setRequired(true)
			 ->setDescription("(required)")
			  ->addValidator('regex', false, array("/^[-_a-zA-Z\s.]+$/"))
			  ->addErrorMessage("Please provide a valid last name. Names can only contain letters and dashes.");
			 
		$homePhone = new Zend_Form_Element_Text('homePhone');
		$homePhone->setLabel('Home Phone:');
		
		$workPhone = new Zend_Form_Element_Text('workPhone');
		$workPhone->setLabel('Work Phone:');
		
		$siteId = new Zend_Form_Element_Hidden('siteId');
		$studentId = new Zend_Form_Element_Hidden('studentId');

		$this->addElements(array($first, $last, $homePhone, $workPhone, $siteId, $studentId));
		
		//Set the default decorators
		$this->setElementDecorators(self::$elementDecorators);
		
		//Overwrite the decorators for the hidden elements
		$this->setElementDecorators(self::$hiddenElementDecorators, array('siteId', 'studentId'), true);
		
		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "addPreceptorForm.phtml")),
			'Form',
		));
		

		$this->setDefaults(array(
			'studentId' => $this->student->id,
			'siteId' => $this->site->id,
		));

	}
	
	/**
	 * Validate the form, if valid, save the Preceptor, if not, return the error msgs
	 *
	 * @param array $data the POSTed data
	 * @return mixed either boolean true, or an array of error messages
	 */
	public function process($data)
	{
		//xdebug_break();
		
		if ($this->isValid($data)) {
			$values = $this->getValues($data);
			
			$preceptor = \Fisdap\EntityUtils::getEntity('PreceptorLegacy');
			$preceptor->first_name = $values['first'];
			$preceptor->last_name = $values['last'];
			$preceptor->home_phone = $values['homePhone'];
			$preceptor->work_phone = $values['workPhone'];
			$preceptor->student = $values['studentId'];
			$preceptor->site = $values['siteId'];
			$preceptor->save();
			
			$student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $values['studentId']);
			$program = $student->program;
			$program->addPreceptor($preceptor);
			
			$preceptor->save();
			$program->save();
				  
			return "<option value='" . $preceptor->id . "' SELECTED='SELECTED'>" . $preceptor->first_name . " " . $preceptor->last_name . "</option>";
		}
		
		return $this->getMessages();
	}
}