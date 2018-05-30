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
 * This produces a modal form for adding/editing Airways
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_AirwayModal extends SkillsTracker_Form_Modal
{
    /**
     * @var \Fisdap\Entity\Airway
     */
    protected $airway;
    
    public $clinical_quick_add;

    /**
     * @param int $airwayId the id of the airway to edit
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($airwayId = null, $clinical_quick_add = false, $options = null)
    {
        $this->airway = \Fisdap\EntityUtils::getEntity('Airway', $airwayId);
        $this->clinical_quick_add = $clinical_quick_add;
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        $this->setAttrib('id', 'airwayDialog');
        
        $this->addJsFile("/js/library/SkillsTracker/Form/airway-modal.js");
        $this->addCssFile("/css/library/SkillsTracker/Form/airway-modal.css");

        $performed = new Zend_Form_Element_Checkbox('airwayPerformed');
        $performed->setLabel("I performed this treatment");
        
        $airway_management_credit = new Zend_Form_Element_Checkbox('airway_management_credit');
        $airway_management_credit->setLabel("The patient required airway management.");
        
        $clincal_quick_add_page = new Zend_Form_Element_Hidden('clincal_quick_add_page');
        $clincal_quick_add_page->setValue($this->clinical_quick_add);
        
        $procedure = new Zend_Form_Element_Select('airwayProcedure');
        $procedure->setLabel('Procedure:')
                  ->setDescription('(required)')
                  ->addValidator('NotEmpty', true, array('type' => 'zero'))
                  ->setMultiOptions(\Fisdap\Entity\AirwayProcedure::getFormOptions(true))
                  ->addErrorMessage("Please choose a procedure.");
        
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
        
        $this->addElements(array($performed,$airway_management_credit, $procedure, $size, $attempts, $success, $airwayId, $patientId, $shiftId, $clincal_quick_add_page));
        
        $this->setElementDecorators(self::$elementDecorators, array('airwayPerformed','airway_management_credit', 'airwayId', 'patientId', 'shiftId'), false);
        $this->setElementDecorators(self::$checkboxDecorators, array('airwayPerformed','airway_management_credit'), true);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('airwayId', 'patientId', 'shiftId', 'clincal_quick_add_page'), true);
        
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "airwayModal.phtml", 'viewModule' => 'skills-tracker')),
            'Form'
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
                'airway_management_credit' => ($this->airway->airway_management) ? 1 : 0,
            ));
        } else {
            $this->setDefaults(array(
                'airwaySuccess' => 0,
                'airwayProcedure' => 0,
            ));
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
            
            if ($airway->procedure->require_success || ($values['clincal_quick_add_page'] && $values['airway_management_credit'])) {
                $airway->success = $values['airwaySuccess'];
            } else {
                $airway->success = null;
            }
            
            if ($values['clincal_quick_add_page']) {
                if ($values['airway_management_credit']) {
                    // did they have one that we need to update?
                    $airway->save();
                    
                    if ($values['airwayId']) {
                        $airway_management = ($airway->airway_management) ? $airway->airway_management : \Fisdap\EntityUtils::getEntity('AirwayManagement');
                    } else {
                        // create a new one
                        $airway_management = \Fisdap\EntityUtils::getEntity('AirwayManagement');
                    }
                    
                    $airway_management->airway_management_source = \Fisdap\EntityUtils::getEntity('AirwayManagementSource', 3);
                    $airway_management->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
                    $airway_management->subject = \Fisdap\EntityUtils::getEntity('Subject', 1);
                    $airway_management->performed_by = $values['airwayPerformed'];
                    $airway_management->success = $values['airwaySuccess'];
                    $airway_management->airway = $airway;
                    $airway_management->save();
                } else {
                    // did they have one that we need to delete?
                    if ($values['airwayId']) {
                        if ($airway->airway_management) {
                            $airway->airway_management->delete();
                            $airway->airway_management = null;
                            $airway->save();
                        }
                    }
                }
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
            
            return "Airway_" . $airway->id;
        }
        
        return $this->getMessages();
    }
}
