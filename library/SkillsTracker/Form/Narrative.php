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
 * Narrative Form
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_Narrative extends Fisdap_Form_Base
{
    /**
     * @var array decorators for individual elements
     */
    public static $elementDecorators = array(
        'ViewHelper',
    
    array(array('labelH3Close' => 'HtmlTag'),
          array('tag' => 'h3', 'closeOnly' => true, 'placement' => 'prepend')),
    'Label',
    array(array('labelH3Open' => 'HtmlTag'),
          array('tag' => 'h3', 'openOnly' => true, 'placement' => 'prepend', 'class' => 'section-header no-border')),
    
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
    );
    
    /**
     * @var array decorators for buttons
     */
    public static $buttonDecorators = array(
        'ViewHelper',
    );
    
    /**
     * @var array decorators for hidden elements
     */
    public static $hiddenElementDecorators = array(
        'ViewHelper',
    );
    
    /**
     * @var \Fisdap\Entity\Narrative
     */
    public $narrative;
    
    /**
     * @var \Fisdap\Entity\Patient
     */
    public $patient;
    
    /**
     * @var mixed
     */
    public $sections;
    
    /**
     * @var Boolean
     */
    public $is_preview;
    
    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($narrativeId = null, $patientId = null, $preview_sections = null, $is_preview = null, $options = null)
    {
        $this->narrative = \Fisdap\EntityUtils::getEntity('Narrative', $narrativeId);
        if ($narrativeId) {
            $this->patient = $this->narrative->patient;
        } elseif ($patientId) {
            $this->patient = \Fisdap\EntityUtils::getEntity('Patient', $patientId);
        }
        $this->sections = $preview_sections;
        $this->is_preview = $is_preview;
        
        parent::__construct($options);
    }
    
    public function init()
    {
        $this->setAttrib('id', 'narrativeForm');
        
        $this->addJsFile("/js/library/SkillsTracker/Form/narrative.js");
        
        $this->addElementPrefixPath('Fisdap_Form_Decorator', 'Fisdap/Form/Decorator/', 'decorator');
        
        // capture the section definition ids to use in the view
        $ids = array();
        
        // if this is an existing narrative, get the sections from the narrative
        if ($this->narrative->id) {
            $sections = \Fisdap\Entity\Narrative::orderSections($this->narrative->sections);
        } else {
            // if this is a new, real narrative, get the sections from the program settings
            if (!$this->sections) {
                $program_id = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
                $sections = \Fisdap\EntityUtils::getRepository('NarrativeSectionDefinition')->getNarrativeSectionsByProgram($program_id, true);
                
                // also, while we're here, instantiate the new narrative
                $this->narrative = new \Fisdap\Entity\Narrative();
                $this->narrative->patient = $this->patient;
            } else {
                // if this is a preview narrative, get the sections from the constructor
                $sections = $this->sections;
            }
        }
        
        // loop through the sections and add the appropriate form element for each
        foreach ($sections as $section) {
            if ($this->narrative->id) {
                $id = $section->definition->id;
                $title = $section->definition->name;
                $size = $section->definition->size;
                $seed = $section->definition->seeded;
                $section_text = $section->section_text;
                // if the section is seeded, but has no text, re-seed it
                if ($seed && $section_text == "") {
                    $section_text = $this->narrative->getNarrativeSeed();
                }
            } else {
                // if this section has been deleted, skip it
                if (!$section->active) {
                    continue;
                }
                $id = $section->id;
                $title = $section->name;
                $size = $section->size;
                $seed = $section->seeded;
                if ($seed) {
                    if ($this->is_preview) {
                        $section_text = "Team Info: 3 members, including John Smith, lead by the student.\n\nPatient Info:\n85 y 0 mo Caucasian Male \nPrimary Impression: Abdominal pain/problems\nSecondary Impression: Sepsis/Infection\nBP: 100/60\n\nMed\nOxygen (Performed)\n3lpm; Nasal Cannula\n\nCardiac \nNormal Sinus Rhythm (Interpreted); \n12 Lead\nOther\nPulse Oximetry (Performed)\n\nIv\nSuccessful IV (Performed)\n18 gauge; Fore Arm left; 1 attempts";
                    } else {
                        $section_text = "";
                    }
                } else {
                    $section_text = "";
                }
            }
            $ids[] = $id;
            $text = new Fisdap_Form_Element_TextareaHipaa($id."_text");
            $text->setAttrib("style", "width: 100%;")
                 ->setAttrib("rows", $size)
                 ->setValue($section_text);
            $text->setLabel($title);
            if ($this->is_preview) {
                $text->setAttrib('disabled', 'disabled');
            }
            $this->addElements(array($text));
        }
        
        $this->sections = $ids;
        
        $narrativeId = new Zend_Form_Element_Hidden("narrativeId");
        $patientId = new Zend_Form_Element_Hidden("patientId");
        $formName = new Zend_Form_Element_Hidden('formName');
        $formName->setValue('Narrative');
        
        $save = new Fisdap_Form_Element_SaveButton("save");
        
        $cancel = new Fisdap_Form_Element_CancelButton("cancel");
        
        $this->addElements(array(
            $narrativeId,
            $patientId,
            $save,
            $cancel,
            $formName
        ));
        
        $this->setElementDecorators(self::$elementDecorators, array('narrativeId', 'save', 'cancel'), false);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('narrativeId', 'patientId', 'formName'), true);
        $this->setElementDecorators(self::$buttonDecorators, array('save', 'cancel'), true);
        
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "narrativeForm.phtml")),
            'Form',
        ));
        
        if ($this->narrative->id) {
            $this->setDefaults(array(
                'narrativeId' => $this->narrative->id,
                'patientId' => $this->narrative->patient->id
                ));
        } elseif ($this->patient->id) {
            $this->setDefaults(array(
                                'patientId' => $this->patient->id,
                        ));
        }
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
            
            if ($values['narrativeId']) {
                $narrative = \Fisdap\EntityUtils::getEntity('Narrative', $values['narrativeId']);
                $new = false;
                $sections = $narrative->sections;
            } else {
                $narrative = \Fisdap\EntityUtils::getEntity('Narrative');
                $new = true;
                $program_id = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
                $sections = \Fisdap\EntityUtils::getRepository('NarrativeSectionDefinition')->getNarrativeSectionsByProgram($program_id, true);
            }
            
            // loop through the narrative sections and update each
            foreach ($sections as $section) {
                if (!$new) {
                    $id = $section->definition->id;
                    $section->set_section_text(trim($values[$id.'_text']));
                    $narrative->addSection($section);
                } else {
                    $id = $section->id;
                    $section_instance = new \Fisdap\Entity\NarrativeSection;
                    $section_instance->narrative = $narrative;
                    $section_instance->definition = $section;
                    $section_instance->set_section_text(trim($values[$id.'_text']));
                    $narrative->addSection($section_instance);
                }
            }
            
            $patient = \Fisdap\EntityUtils::getEntity('Patient', $values['patientId']);
            $patient->set_narrative($narrative);
            $patient->save();

            return $narrative->id;
        }
        
        return $this;
    }
}
