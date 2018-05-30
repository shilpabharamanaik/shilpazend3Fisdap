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
 * This produces a modal form for adding/editing Other Interventions
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_OtherModal extends SkillsTracker_Form_Modal
{

    /**
     * @var \Fisdap\Entity\OtherIntervention
     */
    protected $other;

    /**
     * @param int $otherId the id of the Other Intervention to edit
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($otherId = null, $options = null)
    {
        $this->other = \Fisdap\EntityUtils::getEntity('OtherIntervention', $otherId);
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        $this->setAttrib('id', 'otherDialog');
        
        $this->addJsFile("/js/library/SkillsTracker/Form/other-modal.js");
        
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
             ->setMultiOptions(array(10 => 10, 12 => 12, 14 => 14))
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
        
        $this->addElements(array($performed, $procedure, $size, $attempts, $success, $otherId, $patientId, $shiftId));
        
        $this->setElementDecorators(self::$elementDecorators, array('otherPerformed', 'otherId', 'patientId', 'shiftId'), false);
        $this->setElementDecorators(self::$checkboxDecorators, array('otherPerformed'), true);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('otherId', 'patientId', 'shiftId'), true);
        
        $save_btn_wrapper = '<span class="green-buttons"></span>';
        
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "otherModal.phtml", 'viewModule' => 'skills-tracker')),
            'Form'
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
                'otherProcedure' => 0,
            ));
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
            } elseif ($values['shiftId']) {
                $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
                $shift->addOtherIntervention($other);
                $shift->save();
            }

            return "OtherIntervention_" . $other->id;
        }
        
        return $this->getMessages();
    }
}
