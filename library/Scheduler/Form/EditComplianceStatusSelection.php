<?php

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_EditComplianceStatusSelection extends Fisdap_Form_Base
{
    public $userContextIds;
    public $requirement_ids;
    public $people_sub_filters;
    public $selection_by;
    public $all_people;
    
    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($select_form_values = null, $options = null)
    {
        $this->userContextIds = $select_form_values['userContextIds'];
        $this->requirement_ids = $select_form_values['requirementIds'];
        $this->people_sub_filters = $select_form_values['people_sub_filters'];
        $this->all_people = $select_form_values['all_students'];
        $this->selection_by = $select_form_values['selection-by'];
        
        if (is_null($this->all_people)) {
            $this->all_people = true;
        }
        
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        $this->addJsFile("/js/jquery.chosen.relative.js");
        $this->addJsFile("/js/library/Scheduler/Form/edit-compliance-status-selection.js");
        $this->addCssFile("/css/library/Scheduler/Form/edit-compliance-status-selection.css");
        
        $requirement_search = new Zend_Form_Element_Text("requirement_search");
        $requirement_search->setAttribs(array("class" => "search-box", "autocomplete" => "off"));
        
        $requirement_options = \Fisdap\EntityUtils::getRepository('Requirement')->getFormOptions($user->getProgramId());
        $requirements = new Zend_Form_Element_Select("requirements");
        $requirements->setAttribs(array("multiple" => "multiple"));
        $requirements->setRegisterInArrayValidator(false);
        $requirements->setMultiOptions($requirement_options);
        $requirements->setValue($this->requirement_ids);
        $requirements->removeDecorator('Label');
        
        $people_search = new Zend_Form_Element_Text("people_search");
        $people_search->setAttribs(array("class" => "search-box", "autocomplete" => "off"));
        
        $people_options = \Fisdap\EntityUtils::getRepository('User')->getAllPeopleFormOptions($user->getProgramId());
        $people = new Zend_Form_Element_Select("people");
        $people->setAttribs(array("multiple" => "multiple"));
        $people->setRegisterInArrayValidator(false);
        $people->setMultiOptions($people_options);
        $people->setValue($this->userContextIds);
        $people->removeDecorator('Label');
        
        $this->addElements(array($requirement_search, $requirements, $people_search, $people));
        
        $chosen_width = "278px";
        
        $all_people = new Zend_Form_Element_Hidden("all");
        $all_people->setValue($this->all_people);
        
        // create certification form element
        $program_profession = $user->getCurrentProgram()->profession->id;
        $cert_options = \Fisdap\EntityUtils::getRepository('CertificationLevel')->getFormOptions($program_profession);
        $cert = $this->createChosen('cert_levels', "Certification levels", $chosen_width, "All certification levels...", $cert_options);
        
        // create graduation date form element
        $grad = new Fisdap_Form_Element_GraduationDate("grad");
        $grad->useExistingGraduationYears();
        
        $status_options = array(1 => "Active",4 => "Left Program",2 => "Graduated");
        $status = $this->createChosen('status', "Graduation status", $chosen_width, "All graduation statuses...", $status_options);
        $status->setValue(1);
        
        $classSectionRepository = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
        $groups_options = $classSectionRepository->getFormOptions($user->getProgramId());
        $groups_options['Any group'] = "Any group";
        
        $groups = $this->createChosen('groups', "Student groups", $chosen_width, "All student groups...", $groups_options);
        
        $show_instructors = new Zend_Form_Element_Checkbox("show_instructors");
        $show_instructors->setValue(1);

        $this->addElements(array($cert, $grad, $status, $groups, $show_instructors, $all_people));
        
        if ($this->people_sub_filters) {
            $grad->setValue(array("month" => $this->people_sub_filters['graduationMonth'], "year" => $this->people_sub_filters['graduationYear']));
            
            // if the number of selected options for multi-select chosen is equal to the number of options, don't populate them
            if (count($this->people_sub_filters['certificationLevels']) != count($cert_options)) {
                $this->setDefaults(array('cert_levels' => $this->people_sub_filters['certificationLevels']));
            }
            
            if (count($this->people_sub_filters['graduationStatus']) != count($status_options)) {
                $this->setDefaults(array('status' => $this->people_sub_filters['graduationStatus']));
            }
            
            if (count($this->people_sub_filters['section']) != count($groups_options)) {
                $this->setDefaults(array('groups' => $this->people_sub_filters['section']));
            }
            
            $this->setDefaults(array('show_instructors' => $this->people_sub_filters['show_instructors']));
        }
        
        // Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'forms/edit-compliance-status-selection.phtml')),
            'Form'
        ));
    }
    
    private function createChosen($element_name, $label, $width, $placeholder_text, $options, $multi = "multiple")
    {
        $chosen = new Zend_Form_Element_Select($element_name);
        $chosen->setMultiOptions($options)
             ->setLabel($label)
             ->setAttribs(array("class" => "chzn-select update-people-description-on-change",
                                           "data-placeholder" => $placeholder_text,
                                           "style" => "width:" . $width,
                                           "multiple" => $multi,
                                           "tabindex" => count($options)));
        return $chosen;
    }
    
    public function process()
    {
        return true;
    }
}
