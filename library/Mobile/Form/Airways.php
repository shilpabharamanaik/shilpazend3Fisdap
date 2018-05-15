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
 * This produces a modal form for adding/editing Airways in Mobile
 */

/**
 * @package    Mobile
 * @subpackage Forms
 */
class Mobile_Form_Airways extends Fisdap_Form_Base
{
    /**
     * @var \Fisdap\Entity\Airway
     */
    protected $airway;
    
    /**
     * @var \Fisdap\Entity\Patient
     */
    protected $patient;
    
    /**
     * @var \Fisdap\Entity\ShiftLegacy
     */
    protected $shift;

    /**
     * @param int $airwayId the id of the airway to edit
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($airwayId = null, $patientId = null, $shiftId = null, $options = null)
    {
        $this->airway = \Fisdap\EntityUtils::getEntity('Airway', $airwayId);
        $this->patient = \Fisdap\EntityUtils::getEntity('Patient', $patientId);
        $this->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        $this->addJsFile("/js/library/SkillsTracker/Form/airway-modal.js");
        $this->addJsOnLoad("initAirwayModal();");

        $performed = new Zend_Form_Element_Checkbox('airwayPerformed');
        $performed->setLabel("I performed this treatment");
        
        $procedure = new Zend_Form_Element_Select('airwayProcedure');
        $procedure->setLabel('Procedure:')
                  ->setDescription('(required)')
                  ->addValidator('NotEmpty', true, array('type' => 'zero'))
                  ->addErrorMessage("Please choose a procedure.")
                  ->setMultiOptions(\Fisdap\Entity\AirwayProcedure::getFormOptions(true));
        
        $size = new Zend_Form_Element_Text('airwaySize');
        $size->setLabel('Size:')
             ->setRequired(true)
             ->setDescription('(required)')
             ->addErrorMessage('Please tell us the size of the airway.');
        
        $attempts = new Zend_Form_Element_Text('airwayAttempts');
        $attempts->setLabel('Number of attempts:')
                 ->setRequired(true)
                 ->setDescription('(numbers only, required)')
                 ->addValidator('Int')
                 ->addErrorMessage('Tell us how many times you attempted the procedure (using only numbers).');
        
        $success = new Zend_Form_Element_Radio('airwaySuccess');
        $success->setLabel('Successful')
                ->setMultiOptions(array(0 => "No", 1 => "Yes"))
                ->setRequired(true)
                ->setDescription('(required)')
                ->addErrorMessage('Please tell us whether the procedure was performed successfully.');

        $airwayId = new Zend_Form_Element_Hidden('airwayId');
        $patientId = new Zend_Form_Element_Hidden('patientId');
        $shiftId = new Zend_Form_Element_Hidden('shiftId');
        
        $save = new Fisdap_Form_Element_SaveButton('save');
        
        $this->addElements(array($performed, $procedure, $size, $attempts, $success, $airwayId, $patientId, $shiftId, $save));
        
        $this->setElementDecorators(self::$elementDecorators, array('airwayPerformed', 'airwayId', 'patientId', 'shiftId'), false);
        $this->setElementDecorators(self::$checkboxDecorators, array('airwayPerformed'), true);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('airwayId', 'patientId', 'shiftId', 'save'), true);
        
        $this->setDecorators(array(
            //'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "mobileAirway.phtml")),
            'Form',
        ));
        
        if ($this->airway->id) {
            $this->setDefaults(array(
                'airwayPerformed' => $this->airway->performed_by,
                'airwayProcedure' => $this->airway->procedure->id,
                'airwaySize' => $this->airway->size,
                'airwayAttempts' => $this->airway->attempts,
                'airwaySuccess' => $this->airway->success,
                'patientId' => $this->airway->patient->id,
                'shiftId' => $this->airway->shift->id,
                'airwayId' => $this->airway->id,
            ));
        } else {
            $this->setDefaults(array(
                'airwaySuccess' => 0,
                'airwayProcedure' => 0,
            ));
            
            if ($this->shift->id) {
                $this->setDefault('shiftId', $this->shift->id);
            } elseif ($this->patient->id) {
                $this->setDefault('patientId', $this->patient->id);
            }
        }
    }
    
    /**
     * Validate the form, if valid, save the Airway, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($data)
    {
        //Remove validators if certain procedures were chosen
        $procId = $data['airwayProcedure'];
        $procedure = \Fisdap\EntityUtils::getEntity('AirwayProcedure', $procId);
        
        if (!$procedure->require_attempts) {
            $this->getElement('airwayAttempts')->clearValidators()->setRequired(false);
        }
        
        if (!$procedure->require_size) {
            $this->getElement('airwaySize')->clearValidators()->setRequired(false);
        }
        
        if (!$procedure->require_success) {
            $this->getElement('airwaySuccess')->clearValidators()->setRequired(false);
        }
        
        if ($this->isValid($data)) {
            $values = $this->getValues($data);
            
            if ($values['airwayId']) {
                $airway = \Fisdap\EntityUtils::getEntity('Airway', $values['airwayId']);
            } else {
                $airway = \Fisdap\EntityUtils::getEntity('Airway');
            }
            
            $airway->performed_by = $values['airwayPerformed'];
            $airway->procedure = $values['airwayProcedure'];
            
            if ($airway->procedure->require_size) {
                $airway->size = $values['airwaySize'];
            } else {
                $airway->size = null;
            }
            
            if ($airway->procedure->require_attempts) {
                $airway->attempts = $values['airwayAttempts'];
            } else {
                $airway->attempts = null;
            }
            
            if ($airway->procedure->require_success) {
                $airway->success = $values['airwaySuccess'];
            } else {
                $airway->success = null;
            }
            
            if ($values['patientId']) {
                $patient = \Fisdap\EntityUtils::getEntity('Patient', $values['patientId']);
                $patient->addAirway($airway);
                $patient->save();
            } elseif ($values['shiftId']) {
                $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
                $shift->addAirway($airway);
                $shift->save();
            }


            return $airway->id;
        }
        
        return $this;
    }
}
