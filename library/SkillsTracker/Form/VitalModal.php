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
 * This produces a modal form for adding/editing Vitals
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_VitalModal extends SkillsTracker_Form_Modal
{
	/**
	 * @var \Fisdap\Entity\Vital
	 */
	protected $vital;

	/**
	 * @param int $vitalId the id of the Vital to edit
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($vitalId = null, $options = null)
	{
		$this->vital = \Fisdap\EntityUtils::getEntity('Vital', $vitalId);
		parent::__construct($options);
	}
	
	public function init()
	{
        parent::init();
		
		$this->setAttrib('id', 'vitalDialog');
		
		$this->addJsFile("/js/library/SkillsTracker/Form/vital-modal.js");
        
        $performed = new Zend_Form_Element_Checkbox('performed');
        $performed->setLabel("I obtained vital signs.");
		
		$bp = new SkillsTracker_Form_Element_BloodPressure('bp');
		$bp->setLabel('Blood Pressure:')
		   ->setDescription('(numbers or "p" only)')
		   ->addValidator(new \SkillsTracker_Validate_BloodPressure())
		   ->setAttrib('size', 4);
		
		$pulse = new SkillsTracker_Form_Element_Pulse('pulse');
		$pulse->setLabel('Pulse:')
			  ->setDescription("(numbers only)")
			  ->addValidator(new \SkillsTracker_Validate_Pulse())
			  ->setAttrib('size', 4);
		
		$respirations = new SkillsTracker_Form_Element_Respirations('respirations');
		$respirations->setLabel('Respirations:')
					 ->setDescription("(numbers only)")
					 ->addValidator(new \SkillsTracker_Validate_Respirations())
					 ->setAttrib('size', 4);
		
		$spo2 = new Zend_Form_Element_Text('spo2');
		$spo2->setLabel('SpO2:')
			 ->setDescription("(0-100)")
			 ->addValidator('Digits', true)
			 ->addValidator('Between', true, array('min' => 0, 'max' => 100))
			 ->addErrorMessage('Please only use a number from 0 to 100 to record the SpO2.')
			 ->setAttrib('size', 4);
		
		$skin = new Zend_Form_Element_MultiCheckbox('skin');
		$skin->setLabel('Skin:')
			 ->setAttrib('class', 'skins')
			 ->setMultiOptions(\Fisdap\Entity\VitalSkin::getFormOptions());
		
		$pupils = new SkillsTracker_Form_Element_Pupils('pupils');
		$pupils->setLabel('Pupils:');
		
		$lungSounds = new Zend_Form_Element_MultiCheckbox('lungSounds');
		$lungSounds->setLabel('Lung sounds:')
				   ->setMultiOptions(\Fisdap\Entity\VitalLungSound::getFormOptions());
		
		$bloodGlucose = new Zend_Form_Element_Text('bloodGlucose');
		$bloodGlucose->setLabel('Blood Glucose:')
					 ->setAttrib('size', 4);
		
		$apgar = new Zend_Form_Element_Text('apgar');
		$apgar->setLabel('APGAR')
			  ->setDescription("(0-10)")
			  ->addValidator('Digits', true)
			  ->addValidator('Between', true, array('min' => 0, 'max' => 10))
			  ->addErrorMessage('Please only use a number from 0 to 10 to record APGAR.')
			  ->setAttrib('size', 4);
		
		$gcs = new Zend_Form_Element_Text('gcs');
		$gcs->setLabel('GCS:')
			->setDescription("(3-15)")
			->addValidator('Digits', true)
			->addValidator('Between', true, array('min' => 3, 'max' => 15))
			->addErrorMessage('Please only use a number from 3 to 15 to record the GCS.')
			->setAttrib('size', 4);

		$hiddenId = new Zend_Form_Element_Hidden('hiddenId');
		$patientId = new Zend_Form_Element_Hidden('patientId');
		$shiftId = new Zend_Form_Element_Hidden('shiftId');
		
		// New elements added for m364
		$painscale = new Zend_Form_Element_Text('painscale');
		$painscale->setLabel('Pain Scale')
		->setDescription("(0-10)")
		->addValidator('Digits', true)
		->addValidator('Between', true, array('min' => 0, 'max' => 10))
		->addErrorMessage('Please only use a number from 0 to 10.')
		->setAttrib('size', 4);
		
		$endTidal = new Zend_Form_Element_Text('end_tidal_co2');
		$endTidal->setLabel('End-Tidal CO2')
		->setDescription("(3 characters)")
		->addValidator('StringLength', true, array('max' => 3))
		->addErrorMessage('Please enter a valid end-tidal CO2 value.')
		->setAttrib('size', 3);
		
		$temperature = new SkillsTracker_Form_Element_Temperature('temperature');
		$temperature->setLabel('Temperature')
		->setDescription("(use format _ _ _._, e.g. 98.4 or 101.2)")
		->addValidator(new \SkillsTracker_Validate_Temperature())
		->setAttrib('size', 5);
		
		$this->addElements(array($performed, $bp, $pulse, $respirations, $spo2, $skin, $pupils, $lungSounds, $bloodGlucose, $apgar, $gcs, $hiddenId, $patientId, $shiftId, $painscale, $endTidal, $temperature, $temperatureUnits));
		
		$this->setElementDecorators(self::$elementDecorators, array('performed', 'hiddenId', 'patientId', 'shiftId'), false);
		$this->setElementDecorators(self::$checkboxDecorators, array('performed'), true);
		$this->setElementDecorators(self::$hiddenElementDecorators, array('hiddenId', 'patientId', 'shiftId'), true);
		
		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "vitalModal.phtml", 'viewModule' => 'skills-tracker')),
			'Form'
		));
		
		if ($this->vital->id) {
			$this->setDefaults(array(
				'performed' => $this->vital->performed_by,
				'bp' => array('systolic' => $this->vital->systolic_bp, 'diastolic' => $this->vital->diastolic_bp),
				'pulse' => array('rate' => $this->vital->pulse_rate, 'quality' => $this->vital->pulse_quality),
				'respirations' => array('rate' => $this->vital->resp_rate, 'quality' => $this->vital->resp_quality),
				'spo2' => $this->vital->spo2,
				'skin' => $this->vital->skins,
				'pupils' => array('equal' => $this->vital->pupils_equal, 'round' => $this->vital->pupils_round, 'reactive' => $this->vital->pupils_reactive),
				'lungSounds' => $this->vital->lung_sounds,
				'bloodGlucose' => $this->vital->blood_glucose,
				'apgar' => $this->vital->apgar,
				'gcs' => $this->vital->gcs,
				'patientId' => $this->vital->patient->id,
				'shiftId' => $this->vital->shift->id,
				'hiddenId' => $this->vital->id,
				'painscale' => $this->vital->pain_scale,
				'end_tidal_co2' => $this->vital->end_tidal_co2,
				'temperature' => array('temperature' => $this->vital->temperature, 'units' => $this->vital->temperature_units),
			));
		} else {
			$this->setDefaults(array(
				'pulse' => array('rate' => "", 'quality' => 1),
				'respirations' => array('rate' => "", 'quality' => 1),
			));
		}
	}
	
	/**
	 * Validate the form, if valid, save the Vital, if not, return the error msgs
	 *
	 * @param array $data the POSTed data
	 * @return mixed either boolean true, or an array of error messages
	 */
	public function process($data)
	{
		if ($this->isValid($data)) {
			$values = $this->getValues($data);
			
			if ($values['hiddenId']) {
				$vital = \Fisdap\EntityUtils::getEntity('Vital', $values['hiddenId']);				
			} else {
				$vital = \Fisdap\EntityUtils::getEntity('Vital');
			}
			
			// assume that none of the inputs have been filled out
			$vital_is_empty = TRUE;
			
			$vital->performed_by = ($values['performed'] == "") ? null : $values['performed'];
			$vital->systolic_bp = ($values['bp']['systolic'] == "") ? null : $values['bp']['systolic'];
			$vital->diastolic_bp = ($values['bp']['diastolic'] == "") ? null : $values['bp']['diastolic'];
			if ($values['bp']['systolic'] != "") {
				$vital_is_empty = FALSE;
			}
			
			$vital->pulse_rate = ($values['pulse']['rate'] == "") ? null : $values['pulse']['rate'];
			$vital->pulse_quality = ($values['pulse']['quality'] == "") ? null : $values['pulse']['quality'];
			if ($values['pulse']['rate'] != "") {
				$vital_is_empty = FALSE;
			}
			
			$vital->resp_rate = ($values['respirations']['rate'] == "") ? null : $values['respirations']['rate'];
			$vital->resp_quality = ($values['respirations']['quality'] == "") ? null : $values['respirations']['quality'];
			if ($values['respirations']['rate'] != "") {
				$vital_is_empty = FALSE;
			}
			
			$vital->spo2 = ($values['spo2'] == "") ? null : $values['spo2'];
			if ($values['spo2'] != "") {
				$vital_is_empty = FALSE;
			}
			
			$vital->skins = ($values['skin'] == "") ? null : $values['skin'];
			if ($values['skin'] != "") {
				$vital_is_empty = FALSE;
			}
			
			$vital->lung_sounds = ($values['lungSounds'] == "") ? null : $values['lungSounds'];
			if ($values['lungSounds'] != "") {
				$vital_is_empty = FALSE;
			}
			
			$vital->pupils_equal = ($values['pupils']['equal']>=0)?$values['pupils']['equal']:null;
			$vital->pupils_round = ($values['pupils']['round']>=0)?$values['pupils']['round']:null;
			$vital->pupils_reactive = ($values['pupils']['reactive']>=0)?$values['pupils']['reactive']:null;
			if ($values['pupils']['equal'] >= 0 ||
			    $values['pupils']['round'] >= 0 ||
			    $values['pupils']['reactive'] >= 0) {
				$vital_is_empty = FALSE;
			}
			
			$vital->blood_glucose = ($values['bloodGlucose'] == "") ? null : $values['bloodGlucose'];
			if ($values['bloodGlucose'] != "") {
				$vital_is_empty = FALSE;
			}
			
			$vital->apgar = ($values['apgar'] == "") ? null : $values['apgar'];
			if ($values['apgar'] != "") {
				$vital_is_empty = FALSE;
			}
			
			$vital->gcs = ($values['gcs'] == "") ? null : $values['gcs'];
			if ($values['gcs'] != "") {
				$vital_is_empty = FALSE;
			}
			
			$vital->pain_scale = ($values['painscale'] == "") ? null : $values['painscale'];
			if ($values['painscale'] != "") {
				$vital_is_empty = FALSE;
			}
			
			$vital->end_tidal_co2 = ($values['end_tidal_co2'] == "") ? null : $values['end_tidal_co2'];
			if ($values['end_tidal_co2'] != "") {
				$vital_is_empty = FALSE;
			}
			
			$vital->temperature = ($values['temperature']['temperature'] == "") ? null : $values['temperature']['temperature'];
			if ($values['temperature']['temperature'] != "") {
				// Only set this if temperature is included...
				$vital->temperature_units = $values['temperature']['units'];
				$vital_is_empty = FALSE;
			}
			
			// if nothing has been entered, throw a validation error
			if ($vital_is_empty) {
				return array(array('Please enter at least one vital sign'));
			}
			
			if ($values['patientId']) {
				$patient = \Fisdap\EntityUtils::getEntity('Patient', $values['patientId']);
				$patient->addVital($vital);
				$patient->save();				
			} else if ($values['shiftId']) {
				$shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
				$shift->addVital($vital);
				$shift->save();
			}

			return "Vital_" . $vital->id;
		}
		
		return $this->getMessages();
	}
}
