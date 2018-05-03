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
 * This produces a form for adding/editing Other Interventions on a mobile device
 */

/**
 * @package    Mobile
 * @subpackage Forms
 */
class Mobile_Form_OtherInterventions extends Fisdap_Form_Base
{
	/**
	 * @var \Fisdap\Entity\OtherIntervention
	 */
	protected $other;

    /**
     * @var \Fisdap\Entity\Patient
     */
    protected $patient;
    
    /**
     * @var \Fisdap\Entity\ShiftLegacy
     */
    protected $shift;
    
	/**
	 * @param int $otherId the id of the Other Intervention to edit
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($otherId = null, $patientId = null, $shiftId = null, $options = null)
	{
		$this->other = \Fisdap\EntityUtils::getEntity('OtherIntervention', $otherId);
        $this->patient = \Fisdap\EntityUtils::getEntity('Patient', $patientId);
		$this->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
		parent::__construct($options);
	}
	
	public function init()
	{
        parent::init();
		$this->addJsFile("/js/library/SkillsTracker/Form/other-modal.js");
        $this->addJsOnLoad("initOtherModal();");
		
        $performed = new Zend_Form_Element_Checkbox('otherPerformed');
        $performed->setLabel("I performed this treatment");
        
		$procedure = new Zend_Form_Element_Select('otherProcedure');
		$procedure->setLabel('Procedure:')
				  ->setMultiOptions(\Fisdap\Entity\OtherProcedure::getFormOptions(true))
				  ->setDescription('(required)')
				  ->addValidator('NotEmpty', true, array('type' => 'zero'))
				  ->addErrorMessage("Please choose a procedure.");
		
		$size = new Zend_Form_Element_Select('otherSize');
		$size->setLabel('Size:')
			 ->setMultiOptions(array(
				10 => 10,
				12 => 12,
				14 => 14,
			 ))
			 ->setRequired(true)
			 ->setDescription('(required)')
			 ->addErrorMessage('Please tell us the size of the airway.');
		
		$success = new Zend_Form_Element_Radio('otherSuccess');
        $success->setLabel('Successful')
                ->setDescription('(required)')
                ->setMultiOptions(array(0 => "No", 1 => "Yes"))
				->setRequired(true)
				->addErrorMessage("Please tell us whether the procedure was performed successfully.");
		
		$attempts = new Zend_Form_Element_Text('otherAttempts');
		$attempts->setLabel('Attempts:')
				 ->setDescription('(numbers only, required)')
				 ->setRequired(true)
				 ->addValidator("Digits")
				 ->addErrorMessage("Tell us how many times you attempted the procedure (using only numbers).");

		$otherId = new Zend_Form_Element_Hidden('otherId');
		$patientId = new Zend_Form_Element_Hidden('patientId');
		$shiftId = new Zend_Form_Element_Hidden('shiftId');
        
        $save = new Fisdap_Form_Element_SaveButton('save');
		
		$this->addElements(array($performed, $procedure, $size, $attempts, $success, $otherId, $patientId, $shiftId, $save));
		
		$this->setElementDecorators(self::$elementDecorators, array('otherPerformed', 'otherId', 'patientId', 'shiftId'), false);
		$this->setElementDecorators(self::$checkboxDecorators, array('otherPerformed'), true);
		$this->setElementDecorators(self::$hiddenElementDecorators, array('otherId', 'patientId', 'shiftId', 'save'), true);
		
		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "mobileOther.phtml")),
			'Form',
		));
		
		if ($this->other->id) {
			$this->setDefaults(array(
				'otherPerformed' => $this->other->performed_by,
				'otherProcedure' => $this->other->procedure->id,
				'otherSize' => $this->other->size,
				'otherAttempts' => $this->other->attempts,
				'otherSuccess' => $this->other->success,
				'patientId' => $this->other->patient->id,
				'shiftId' => $this->other->shift->id,
				'otherId' => $this->other->id,
			));
		} else {
			$this->setDefaults(array(
				'otherSuccess' => 0,
				'otherProcedure' => 0,
			));
            
            if ($this->shift->id) {
                $this->setDefault('shiftId', $this->shift->id);
            } else if ($this->patient->id) {
                $this->setDefault('patientId', $this->patient->id);
            }
		}
	}
	
	/**
	 * Validate the form, if valid, save the Other Intervention, if not, return the error msgs
	 *
	 * @param array $data the POSTed data
	 * @return mixed either boolean true, or an array of error messages
	 */
	public function process($data)
	{
		//Remove validators if certain procedures were chosen
		$procId = $data['otherProcedure'];
		$procedure = \Fisdap\EntityUtils::getEntity('OtherProcedure', $procId);
		
		if (!$procedure->require_attempts) {
			$this->getElement('otherAttempts')->clearValidators()->setRequired(false);
		}
		
		if (!$procedure->require_size) {
			$this->getElement('otherSize')->clearValidators()->setRequired(false);
		}
		
		if (!$procedure->require_success) {
			$this->getElement('otherSuccess')->clearValidators()->setRequired(false);
		}
		
		if ($this->isValid($data)) {
			$values = $this->getValues($data);
			
			if ($values['otherId']) {
				$other = \Fisdap\EntityUtils::getEntity('OtherIntervention', $values['otherId']);				
			} else {
				$other = \Fisdap\EntityUtils::getEntity('OtherIntervention');
			}
			
			$other->performed_by = $values['otherPerformed'];
			$other->procedure = $values['otherProcedure'];
			
			if ($other->procedure->require_size) {
				$other->size = $values['otherSize'];
			} else {
				$other->size = null;
			}
			
			if ($other->procedure->require_attempts) {
				$other->attempts = $values['otherAttempts'];
			} else {
				$other->attempts = null;
			}
			
			if ($other->procedure->require_success) {
				$other->success = $values['otherSuccess'];
			} else {
				$other->success = null;
			}
			
			if ($values['patientId']) {
				$patient = \Fisdap\EntityUtils::getEntity('Patient', $values['patientId']);
				$patient->addOtherIntervention($other);
				$patient->save();				
			} else if ($values['shiftId']) {
				$shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
				$shift->addOtherIntervention($other);
				$shift->save();
			}

			return $other->id;
		}
		
		return $this;
	}
}