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
 * This produces a form for shift filters
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class Scheduler_Form_RequirementFilters extends Fisdap_Form_Base
{
    /**
     * @var array form decorators
     */
    public static $formDecorators = array(
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/requirement-filters.phtml")),
        'Form',
    );
    
    public function init()
    {
        parent::init();
        
        $program_id = \Fisdap\Entity\User::getLoggedInUser()->getCurrentProgram()->id;
        
        // add files for fancy filters
        $this->addJsFile("/js/jquery.fancyFilters.js");
        $this->addCssFile("/css/jquery.fancyFilters.css");
        
        $this->addJsFile("/js/library/Scheduler/Form/requirement-filters.js");
        $this->setAttrib('id', 'requirement-filters');
        
        // add files for chosen
        //$this->addJsFile("/js/jquery.chosen.js");
        //$this->addCssFile("/css/jquery.chosen.css");
        
        $width = "260px";
        
        $category = new Zend_Form_Element_Select("category");
        $category->setMultiOptions(\Fisdap\Entity\RequirementCategory::getFormOptions(false, false))
               ->setLabel("Category")
               ->setAttribs(array("class" => "chzn-select",
                            "data-placeholder" => "",
                            "style" => "width:" . $width,
                            "multiple" => 1));
        $this->addElement($category);
        
        $accountType = new Zend_Form_Element_Select("accountType");
        $accountType->setLabel("Account type")
               ->setAttribs(array("class" => "chzn-select",
                            "data-placeholder" => "",
                            "style" => "width:" . $width,
                            "multiple" => 1));
        $programProfession = \Fisdap\Entity\User::getLoggedInUser()->getCurrentProgram()->profession->id;
        $certOptions = \Fisdap\EntityUtils::getRepository('CertificationLevel')->getFormOptions($programProfession);
        $certOptions[0] = "Instructor";
        $accountType->setMultiOptions($certOptions);
        $this->addElement($accountType);
        
        $students = new Zend_Form_Element_Select("students");
        $students->setMultiOptions(array())
               ->setLabel("Students")
               ->setAttribs(array("class" => "chzn-select",
                            "data-placeholder" => "",
                            "style" => "width:" . $width,
                            "multiple" => 1));
        
        //$studentOptions = \Fisdap\EntityUtils::getRepository("User")->getAllStudentsByProgram(\Fisdap\Entity\User::getLoggedInUser()->getCurrentProgram()->id);
        $students->setMultiOptions(\Fisdap\EntityUtils::getRepository('ProgramLegacy')->getCompleteStudentFormOptions($program_id, true, true, true, true));
        $this->addElement($students);
        
        $instructors = new Zend_Form_Element_Select("instructors");
        $instructors->setMultiOptions(array())
               ->setLabel("Instructors")
               ->setAttribs(array("class" => "chzn-select",
                            "data-placeholder" => "",
                            "style" => "width:" . $width,
                            "multiple" => 1));
        $instructorOptions = \Fisdap\EntityUtils::getRepository("User")->getAllInstructorsByProgram($program_id);
        foreach ($instructorOptions as $option) {
            $instructors->addMultiOption($option['userContextId'], $option['first_name'] . " " . $option['last_name']);
        }
        $this->addElement($instructors);
        
        $programRequirements = new Zend_Form_Element_Checkbox("programRequirements");
        $programRequirements->setLabel("Program requirements")
                            ->setValue(1);
        $this->addElement($programRequirements);
        
        $siteRequirements = new Zend_Form_Element_Checkbox("siteRequirements");
        $siteRequirements->setLabel("Site requirements")
                            ->setValue(1);
        $this->addElement($siteRequirements);
        
        $sites = new Zend_Form_Element_Select("sites");
        $sites->setMultiOptions(\Fisdap\EntityUtils::getRepository('SiteLegacy')->getFormOptionsByProgram($program_id, null, null, null, true))
               ->setLabel("Sites")
               ->setAttribs(array("class" => "chzn-select",
                            "data-placeholder" => "",
                            "style" => "width:" . $width,
                            "multiple" => 1));
        $this->addElement($sites);
        
        $this->setDecorators(self::$formDecorators);
        $this->setElementDecorators(self::$elementDecorators);
        $this->setElementDecorators(self::$checkboxDecorators, array("siteRequirements", "programRequirements"));
        
        //$this->setDefaults(array('shiftsFilters' => 'all'));
        //$this->setDefaults(array('attendanceFilters' => 'all'));
    }
    
    public function process($post)
    {
        $this->isValid($post);
        $values = $this->getValues();
        
        $values['students'] = $values['students'] ? $values['students'] : array();
        $values['instructors'] = $values['instructors'] ? $values['instructors'] : array();
        
        $values['userContexts'] = array_merge($values['students'], $values['instructors']);
        
        return $values;
    }
}
