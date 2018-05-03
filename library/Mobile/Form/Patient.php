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
 * Patient Care Form for mobile devices
 */

/**
 * @package    Mobile
 * @subpackage Forms
 */
class Mobile_Form_Patient extends SkillsTracker_Form_PatientCare
{
    /**
	 * @var array decorators for individual elements
	 */
    //public static $elementDecorators = array(
    //    'ViewHelper',
    //    'Label',
    //    array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
    //);

	
	/**
	 * @var array decorators for checkbox elements
	 */
	//public static $checkboxDecorators = array(
	//	'ViewHelper',
	//	array('Label', array('placement' => 'APPEND')),
	//	array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
	//);
	
	/**
	 * @var array decorators for buttons
	 */
	//public static $buttonDecorators = array(
	//	'ViewHelper',
	//);
	
	/**
	 * @var array decorators for hidden elements
	 */
	//public static $hiddenElementDecorators = array(
	//	'ViewHelper',
	//);
    
    public function init()
    {
        parent::init();
        
        $this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "mobilePatientCareForm.phtml")),
			'Form',
		));
		
		$this->setElementDecorators(self::$elementDecorators, array('hiddenPatientId', 'runId', 'interview', 'exam', 'airway_success', 'teamLead', 'save', 'cancel', 'formName'), false);
		$this->setElementDecorators(self::$checkboxDecorators, array('teamLead', 'interview', 'exam', 'airway_success'), true);
		$this->setElementDecorators(self::$hiddenElementDecorators, array('hiddenPatientId', 'save', 'cancel', 'formName', 'runId'), true);
	}
	
	/**
	 * Validate the form, if valid, save something, if not, return the error msgs
	 *
	 * @param array $data the POSTed data
	 * @return mixed either boolean true, or an array of error messages
	 */
	public function process($data)
	{
		
		
		if ($this->isValid($data)) {
			$values = $this->getValues($data);
			
			if ($values['hiddenPatientId']) {
				$patient = \Fisdap\EntityUtils::getEntity('Patient', $values['hiddenPatientId']);				
			} else {
				$patient = \Fisdap\EntityUtils::getEntity('Patient');
			}
			$patient->team_lead = $values['teamLead'];
			$patient->team_size = $values['teamSize'];
			$patient->preceptor = $values['preceptor'];
			$patient->interview = $values['interview'];
			$patient->exam = $values['exam'];
                        $patient->airway_success = $values['airway_success'];
			$patient->age = $values['age']['years'];
			$patient->months = $values['age']['months'];
			$patient->gender = $values['gender'];
			$patient->ethnicity = $values['ethnicity'];
			$patient->primary_impression = $values['primary'];
			$patient->secondary_impression = $values['secondary'];
			$patient->setComplaintIds($values['complaints']);
			$patient->response_mode = $values['responseMode'];
			$patient->patient_criticality = $values['criticality'];
			$patient->patient_disposition = $values['disposition'];
			$patient->transport_mode = $values['transport'];
			
			$patient->msa_alertness = $values['alert'];
			if ($patient->msa_alertness->name == "No") {
				$patient->msa_orientations = $values['orientation'];
				$patient->msa_responses = $values['response'];	
			} else {
				$patient->msa_orientations = null;
				$patient->msa_responses = null;
			}
			
			if (($patient->primary_impression && $patient->primary_impression->isTrauma()) || ($patient->secondary_impression && $patient->secondary_impression->isTrauma())) {
				$patient->mechanisms = $values['mechanism'];
				$patient->cause = $values['cause'];
				$patient->intent = $values['intent'];
			} else {
				$patient->mechanisms = null;
				$patient->cause = null;
				$patient->intent = null;
			}
			
			if (($patient->primary_impression && $patient->primary_impression->isArrest()) || ($patient->secondary_impression && $patient->secondary_impression->isArrest())) {
				$patient->witness = $values['witness'];
				$patient->pulse_return = $values['pulseReturn'];
			} else {
				$patient->witness = null;
				$patient->pulse_return = null;
			}
			
			$run = \Fisdap\EntityUtils::getEntity('Run', $values['runId']);
			$run->addPatient($patient);
			$run->save();

			return true;
		}
		
		return $this;
	}
}
