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
 * This produces a form for adding/editing Cardiac Interventions in mobile
 */

/**
 * @package    Mobile
 * @subpackage Forms
 */
class Mobile_Form_CardiacInterventions extends Fisdap_Form_Base
{

	/**
	 * @var \Fisdap\Entity\CardiacIntervention
	 */
	protected $cardiac;
    
    /**
     * @var \Fisdap\Entity\Patient
     */
    protected $patient;
    
    /**
     * @var \Fisdap\Entity\ShiftLegacy
     */
    protected $shift;

	/**
	 * @param int $cardiacId the id of the cardiac intervention to edit
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($cardiacId = null, $patientId = null, $shiftId = null, $options = null)
	{
		$this->cardiac = \Fisdap\EntityUtils::getEntity('CardiacIntervention', $cardiacId);
        $this->patient = \Fisdap\EntityUtils::getEntity('Patient', $patientId);
		$this->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
        
		parent::__construct($options);
	}
	
	public function init()
	{
        parent::init();
		$this->addJsFile("/js/library/SkillsTracker/Form/cardiac-modal.js");
		$this->addJsOnLoad("initCardiacModal();");
        
		$rhythmPerformed = new Zend_Form_Element_Checkbox('rhythmPerformed');
        $rhythmPerformed->setLabel("I interpreted this rhythm");
		
		$rhythm = new Zend_Form_Element_Select('rhythm');
		$rhythm->setLabel('Rhythm:')
			   ->setMultiOptions(\Fisdap\Entity\RhythmType::getFormOptions());
		
		$twelveLead = new Zend_Form_Element_Checkbox('twelveLead');
		$twelveLead->setLabel('12 lead');
		
        $performed = new Zend_Form_Element_Checkbox('cardiacPerformed');
        $performed->setLabel("I performed this treatment");
        
        $procedure = new Zend_Form_Element_Select('cardiacProcedure');
        $procedure->setLabel('Procedure:')
                  ->setMultiOptions(\Fisdap\Entity\CardiacProcedure::getFormOptions(true))
				  ->addValidator(new Zend_Validate_Callback(array($this, 'checkPerformedProcedure')))
				  ->addErrorMessage("Please choose a procedure.");
        
		$ectopies = new Zend_Form_Element_MultiCheckbox('ectopies');
		$ectopies->setMultiOptions(\Fisdap\Entity\CardiacEctopy::getFormOptions());
		
		$procedureMethod = new Zend_Form_Element_Radio('procedureMethod');
		$procedureMethod->setMultiOptions(\Fisdap\Entity\CardiacProcedureMethod::getFormOptions())
						->setRequired(true)
						->addErrorMessage("Please choose a procedure method.");
		
		$pacingMethod = new Zend_Form_Element_Radio('pacingMethod');
		$pacingMethod->setMultiOptions(\Fisdap\Entity\CardiacPacingMethod::getFormOptions())
					 ->setRequired(true)
					 ->addErrorMessage("Please choose a pacing method.");

		$cardiacId = new Zend_Form_Element_Hidden('cardiacId');
		$patientId = new Zend_Form_Element_Hidden('patientId');
		$shiftId = new Zend_Form_Element_Hidden('shiftId');
		
        $save = new Fisdap_Form_Element_SaveButton('save');        
        
		$this->addElements(array($rhythmPerformed, $rhythm, $twelveLead, $performed, $procedure, $procedureMethod, $pacingMethod, $cardiacId, $patientId, $shiftId, $ectopies, $save));
		
		$this->setElementDecorators(self::$elementDecorators, array('cardiacPerformed', 'rhythmPerformed', 'twelveLead', 'cardiacId', 'patientId', 'shiftId'), false);
		$this->setElementDecorators(self::$checkboxDecorators, array('cardiacPerformed', 'rhythmPerformed', 'twelveLead'), true);
		$this->setElementDecorators(self::$hiddenElementDecorators, array('cardiacId', 'patientId', 'shiftId', 'save'), true);
		
		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "mobileCardiac.phtml")),
			'Form',
		));
		
		if ($this->cardiac->id) {
			$this->setDefaults(array(
				'rhythmPerformed' => $this->cardiac->rhythm_performed_by,
				'rhythm' => $this->cardiac->rhythm_type->id,
				'twelveLead' => $this->cardiac->twelve_lead,
				'cardiacPerformed' => $this->cardiac->performed_by,
				'cardiacProcedure' => $this->cardiac->procedure->id,
				'ectopies' => $this->cardiac->ectopies,
				'pacingMethod' => $this->cardiac->pacing_method->id,
				'procedureMethod' => $this->cardiac->procedure_method->id,
				'patientId' => $this->cardiac->patient->id,
				'shiftId' => $this->cardiac->shift->id,
				'cardiacId' => $this->cardiac->id,
			));
		} else {
			$this->setDefaults(array(
				'cardiacProcedure' => 0,
				'pacingMethod' => 1,
				'procedureMethod' => 1,
				'rhythm' => 25,
			));
            
            if ($this->shift->id) {
                $this->setDefault('shiftId', $this->shift->id);
            } else if ($this->patient->id) {
                $this->setDefault('patientId', $this->patient->id);
            }
		}
	}
	
	/**
	 * Validate the form, if valid, save the cardiac intervention, if not, return the error msgs
	 *
	 * @param array $data the POSTed data
	 * @return mixed either boolean true, or an array of error messages
	 */
	public function process($data)
	{
		//Remove validators if certain procedures were chosen
		$procId = $data['cardiacProcedure'];
		$procedure = \Fisdap\EntityUtils::getEntity('CardiacProcedure', $procId);
		
		if (!$procedure->require_procedure_method) {
			$this->getElement('procedureMethod')->clearValidators()->setRequired(false);
		}
		
		if (!$procedure->require_pacing_method) {
			$this->getElement('pacingMethod')->clearValidators()->setRequired(false);
		}
		
		if ($this->isValid($data)) {
			$values = $this->getValues($data);
			
			if ($values['cardiacId']) {
				$cardiac = \Fisdap\EntityUtils::getEntity('CardiacIntervention', $values['cardiacId']);				
			} else {
				$cardiac = \Fisdap\EntityUtils::getEntity('CardiacIntervention');
			}
			
			$cardiac->rhythm_performed_by = $values['rhythmPerformed'];
			$cardiac->rhythm_type = $values['rhythm'];
			$cardiac->twelve_lead = $values['twelveLead'];
			$cardiac->performed_by = $values['cardiacPerformed'];
			$cardiac->procedure = $values['cardiacProcedure'];
			$cardiac->ectopies = $values['ectopies'];
			
			if ($cardiac->procedure->require_procedure_method) {
				$cardiac->procedure_method = $values['procedureMethod'];
			} else {
				$cardiac->procedure_method = null;
			}
			
			if ($cardiac->procedure->require_pacing_method) {
				$cardiac->pacing_method = $values['pacingMethod'];
			} else {
				$cardiac->pacing_method = null;
			}
			
			if ($values['patientId']) {
				$patient = \Fisdap\EntityUtils::getEntity('Patient', $values['patientId']);
				$patient->addCardiacIntervention($cardiac);
				$patient->save();				
			} else if ($values['shiftId']) {
				$shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
				$shift->addCardiacIntervention($cardiac);
				$shift->save();
			}

			return $cardiac->id;
		}
		
		return $this;
	}
	
	public function checkPerformedProcedure($value)
    {
		//Get performed by value
		$checked = $this->getElement('cardiacPerformed')->getValue();
        if ($checked && !$value) {
			return false;
		}
		
		return true;
    }
}