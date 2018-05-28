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
 * @package    Mobile
 * @subpackage Forms
 */
class Mobile_Form_Vitals extends Fisdap_Form_Base
{
	/**
	 * @var \Fisdap\Entity\Vital
	 */
	protected $vital;
    
    /**
     * @var \Fisdap\Entity\Patient
     */
    protected $patient;
    
    /**
     * @var \Fisdap\Entity\ShiftLegacy
     */
    protected $shift;

	/**
	 * @param int $vitalId the id of the Vital to edit
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($vitalId = null, $patientId = null, $shiftId = null, $options = null)
	{
		$this->vital = \Fisdap\EntityUtils::getEntity('Vital', $vitalId);
		$this->patient = \Fisdap\EntityUtils::getEntity('Patient', $patientId);
		$this->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
		parent::__construct($options);
	}
	
	public function init()
	{
        parent::init();
		
		$this->addJsFile("/js/library/SkillsTracker/Form/vital-modal.js");
        
        $performed = new Zend_Form_Element_Checkbox('performed');
        $performed->setLabel("I obtained vital signs.");
		
		$bp = new SkillsTracker_Form_Element_BloodPressure('bp');
		$bp->setLabel('Blood Pressure:')
		   ->setDescription('(numbers or "p" only)')
		   ->addValidator(new \SkillsTracker_Validate_BloodPressure())
		   ->setAttrib('size', 4);;
		
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
            
        $save = new Fisdap_Form_Element_SaveButton('save');

		$hiddenId = new Zend_Form_Element_Hidden('hiddenId');
		$patientId = new Zend_Form_Element_Hidden('patientId');
		$shiftId = new Zend_Form_Element_Hidden('shiftId');
		
		$this->addElements(array($performed, $bp, $pulse, $respirations, $spo2, $skin, $pupils, $lungSounds, $bloodGlucose, $apgar, $gcs, $hiddenId, $patientId, $shiftId, $save));
		
		$this->setElementDecorators(self::$elementDecorators, array('performed', 'hiddenId', 'patientId', 'shiftId'), false);
		$this->setElementDecorators(self::$checkboxDecorators, array('performed'), true);
		$this->setElementDecorators(self::$hiddenElementDecorators, array('hiddenId', 'patientId', 'shiftId', 'save'), true);
		
		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "mobileVital.phtml")),
			'Form',
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
			));
		} else {
			$this->setDefaults(array(
				'pulse' => array('rate' => "", 'quality' => 1),
				'respirations' => array('rate' => "", 'quality' => 1),
			));
            
            if ($this->shift->id) {
                $this->setDefault('shiftId', $this->shift->id);
            } else if ($this->patient->id) {
                $this->setDefault('patientId', $this->patient->id);
            }
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
        //xdebug_break();
        
		if ($this->isValid($data)) {
			$values = $this->getValues($data);
			
			if ($values['hiddenId']) {
				$vital = \Fisdap\EntityUtils::getEntity('Vital', $values['hiddenId']);				
			} else {
				$vital = \Fisdap\EntityUtils::getEntity('Vital');
			}
			
			$vital->performed_by = ($values['performed'] == "") ? null : $values['performed'];
			$vital->systolic_bp = ($values['bp']['systolic'] == "") ? null : $values['bp']['systolic'] ;
			$vital->diastolic_bp = ($values['bp']['diastolic'] == "") ? null : $values['bp']['diastolic'];
			$vital->pulse_rate = ($values['pulse']['rate'] == "") ? null : $values['pulse']['rate'];
			$vital->pulse_quality = ($values['pulse']['quality'] == "") ? null : $values['pulse']['quality'];
			$vital->resp_rate = ($values['respirations']['rate'] == "") ? null : $values['respirations']['rate'];
			$vital->resp_quality = ($values['respirations']['quality'] == "") ? null : $values['respirations']['quality'];
			$vital->spo2 = ($values['spo2'] == "") ? null : $values['spo2'];
			$vital->skins = ($values['skin'] == "") ? null : $values['skin'];
			$vital->lung_sounds = ($values['lungSounds'] == "") ? null : $values['lungSounds'];
			$vital->pupils_equal = ($values['pupils']['equal'] == "") ? null : $values['pupils']['equal'];
			$vital->pupils_round = ($values['pupils']['round'] == "") ? null : $values['pupils']['round'];
			$vital->pupils_reactive = ($values['pupils']['reactive'] == "") ? null : $values['pupils']['reactive'];
			$vital->blood_glucose = ($values['bloodGlucose'] == "") ? null : $values['bloodGlucose'];
			$vital->apgar = ($values['apgar'] == "") ? null : $values['apgar'];
			$vital->gcs = ($values['gcs'] == "") ? null : $values['gcs'];
			
			if ($values['patientId']) {
				$patient = \Fisdap\EntityUtils::getEntity('Patient', $values['patientId']);
				$patient->addVital($vital);
				$patient->save();				
			} else if ($values['shiftId']) {
				$shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
				$shift->addVital($vital);
				$shift->save();
			}

			return $vital->id;
		}
		
		return $this;
	}
}