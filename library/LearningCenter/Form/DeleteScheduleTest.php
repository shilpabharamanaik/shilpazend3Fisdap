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
 * Form to schedule a test or edit a scheduled test
 */

/**
 * @package    LearningCenter
 */
class LearningCenter_Form_DeleteScheduleTest extends SkillsTracker_Form_Modal
{
	/**
	 * @var \Fisdap\Entity\ScheduledTestsLegacy
	 */
	public $scheduledTest;
	
	public function __construct($scheduledTestId = null, $options = null)
	{
		$this->scheduledTest = \Fisdap\EntityUtils::getEntity("ScheduledTestsLegacy", $scheduledTestId);
				
		return parent::__construct($options);
	}

	public function init()
	{
		parent::init();

		$delete = new Fisdap_Form_Element_Submit("Delete");
		$delete->setDecorators(array("ViewHelper"));
		$this->addElement($delete);
		
		$this->setDecorators(array(
            'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => "forms/deleteScheduleTestForm.phtml")),
            array('Description', array('placement' => 'prepend')),
			'Form',
		));
	}
	
	public function process($post) {
		// delete the scheduled exam
		$em = \Fisdap\EntityUtils::getEntityManager();
		$em->remove($this->scheduledTest);
		$em->flush();
	}
}
