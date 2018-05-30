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
 * This produces a modal form for adding/editing Ivs
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_IvModal extends SkillsTracker_Form_Modal
{

    /**
     * @var \Fisdap\Entity\Iv
     */
    protected $iv;

    /**
     * @param int $shiftId the id of the shift to edit
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($ivId = null, $options = null)
    {
        $this->iv = \Fisdap\EntityUtils::getEntity('Iv', $ivId);
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        $this->addJsFile("/js/library/SkillsTracker/Form/iv-modal.js");
        $this->addCssFile("/css/library/SkillsTracker/Form/iv-modal.css");
        
        $performed = new Zend_Form_Element_Checkbox('ivPerformed');
        $performed->setLabel("I performed this treatment");
        
        $procedure = new Zend_Form_Element_Select('ivProcedure');
        $procedure->setLabel('Procedure:' . self::NSC_DIAMOND)
                  ->setMultiOptions(\Fisdap\Entity\IvProcedure::getFormOptions());
        
        $size = new Zend_Form_Element_Text('ivSize');
        $size->setLabel('Size:')
             ->setDescription("(14-24)")
             ->addErrorMessage('For "size," you may only use even numbers between 14 and 24.');
        
        $sizeio = new Zend_Form_Element_Select('ioSize');
        $sizeio->setLabel('Size:')
            ->setMultiOptions(array(""=> "Select", "15" => "15mm", "25" => "25mm", "45" => "45mm"))
            ->addErrorMessage('For "size," You can select an value from the dropdown');
        
        $site = new SkillsTracker_Form_Element_IvSite('site');
        $site->setLabel('Site:');
        
        $fluid = new Zend_Form_Element_Select('fluid');
        $fluid->setLabel('Fluid Type:')
              ->setMultiOptions(\Fisdap\Entity\IvFluid::getFormOptions());
        
        $attempts = new Zend_Form_Element_Text('ivAttempts');
        $attempts->setLabel('Number of attempts:')
                 ->setDescription('(numbers only, required)')
                 ->setRequired(true)
                 ->addValidator("Digits")
                 ->addErrorMessage("Tell us how many times you attempted the procedure (using only numbers).");
        
        $success = new Zend_Form_Element_Radio('ivSuccess');
        $success->setLabel('Successful')
                ->setMultiOptions(array(0 => "No", 1 => "Yes"))
                ->setDescription('(required)')
                ->setRequired(true)
                ->addErrorMessage("Please tell us whether the procedure was performed successfully.");

        $ivId = new Zend_Form_Element_Hidden('ivId');
        $patientId = new Zend_Form_Element_Hidden('patientId');
        $shiftId = new Zend_Form_Element_Hidden('shiftId');
        
        $this->addElements(array($performed, $procedure, $size, $sizeio, $site, $fluid, $attempts, $success, $ivId, $patientId, $shiftId));
        
        $this->setElementDecorators(self::$elementDecorators, array('ivPerformed', 'ivId', 'patientId', 'shiftId'), false);
        $this->setElementDecorators(self::$checkboxDecorators, array('ivPerformed'), true);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('ivId', 'patientId', 'shiftId'), true);
        
        $this->setDecorators(array(
                'PrepareElements',
                array('ViewScript', array('viewScript' => "ivModal.phtml", 'viewModule' => 'skills-tracker')),
                'Form',
                array('DialogContainer', array(
                        'id'          => 'ivDialog',
                        'class'          => 'ivDialog',
                        'jQueryParams' => array(
                                'tabPosition' => 'top',
                                'modal' => true,
                                'autoOpen' => false,
                                'resizable' => false,
                                'width' => 800,
                                'title' => 'Venous Access'
                        )
                ))));
        
        if ($this->iv->id) {
            $this->setDefaults(array(
                'ivPerformed' => $this->iv->performed_by,
                'ivProcedure' => $this->iv->procedure->id,
                'ivSize' => $this->iv->gauge,
                'ioSize' => $this->iv->gauge,
                'site' => $this->iv->site->id,
                'fluid' => $this->iv->fluid->id,
                'ivAttempts' => $this->iv->attempts,
                'ivSuccess' => $this->iv->success,
                'patientId' => $this->iv->patient->id,
                'shiftId' => $this->iv->shift->id,
                'ivId' => $this->iv->id,
            ));
        } else {
            $this->setDefaults(array(
                'site' => 2,
                'fluid' => 5,
                'ivProcedure' => 1,
            ));
        }
    }
    
    /**
     * Validate the form, if valid, save the IV, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($data)
    {
        //Remove validators if certain procedures were chosen
        $procId = $data['ivProcedure'];
        $procedure = \Fisdap\EntityUtils::getEntity('IvProcedure', $procId);
        
        if (!$procedure->require_attempts) {
            $this->getElement('ivAttempts')->clearValidators()->setRequired(false);
        }
        
        if (!$procedure->require_size) {
            $this->getElement('ivSize')->clearValidators()->setRequired(false);
        }
        
        if (!$procedure->require_success) {
            $this->getElement('ivSuccess')->clearValidators()->setRequired(false);
        }
        
        if ($this->isValid($data)) {
            $values = $this->getValues($data);
            
            if ($values['ivId']) {
                $iv = \Fisdap\EntityUtils::getEntity('Iv', $values['ivId']);
            } else {
                $iv = \Fisdap\EntityUtils::getEntity('Iv');
            }
            
            $iv->performed_by = $values['ivPerformed'];
            $iv->procedure = $values['ivProcedure'];
            $iv->site = $values['site'];
            $iv->fluid = $values['fluid'];
            
            if ($iv->procedure->require_size) {
                if ($iv->procedure->id == 2) {
                    $iv->gauge = $values['ioSize'];
                } else {
                    $iv->gauge = $values['ivSize'];
                }
            } else {
                $iv->gauge = null;
            }
            
            if ($iv->procedure->require_attempts) {
                $iv->attempts = $values['ivAttempts'];
            } else {
                $iv->attempts = null;
            }
            
            if ($iv->procedure->require_success) {
                $iv->success = $values['ivSuccess'];
            } else {
                $iv->success = null;
            }

            if ($values['patientId']) {
                $patient = \Fisdap\EntityUtils::getEntity('Patient', $values['patientId']);
                $patient->addIv($iv);
                $patient->save();
            } elseif ($values['shiftId']) {
                $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['shiftId']);
                $shift->addIv($iv);
                $shift->save();
            }

            return "Iv_" . $iv->id;
        }
        
        return $this->getMessages();
    }
}
