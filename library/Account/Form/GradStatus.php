<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2012.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Form for editing a bunch of students' graduation settings
 */

/**
 * @package Account
 */
class Account_Form_GradStatus extends Fisdap_Form_Base
{
    /**
     * @var
     * Post-submission, the students in the picker who were checked off
     */
    public $checkedStudents;
    
    /**
     * @var boolean
     */
    public $staffView = false;
    
    /**
     * @var array decorators for checkbox elements
     */
    public static $checkboxDecorators = array(
        'ViewHelper',
        array('Label', array('placement' => 'APPEND', 'escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
    );
    
    /**
     * @var array decorators for radio objects
     */
    public static $radioDecorators = array(
        'ViewHelper',
        array(array('break' => 'HtmlTag'), array('tag' => 'br', 'openOnly' => true, 'placement' => 'PREPEND')),
        array('Label', array('escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt select-prompt')),
    );
    
    /**
     * @var array specialized decorator for class year setting
     */
    public static $classYearDecorators = array(
        'ViewHelper',
        array(array('break' => 'HtmlTag'), array('tag' => 'div', 'openOnly' => true, 'placement' => 'PREPEND')),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt select-prompt')),
    );
    
    /**
     * @var array decorators for select objects
     */
    public static $selectDecorators = array(
        'ViewHelper',
        array('Label', array('escape' => false)),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt select-prompt')),
    );
    
    /**
     * @param $checkedStudents the students in the picker who were checked off
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($checkedStudents = null, $options = null)
    {
        // set checkedStudents to null typically, as the variable is only used in post-submission (validation failed) situations
        $this->checkedStudents = $checkedStudents;
        
        return parent::__construct($options);
    }
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();
        
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $this->staffView = $user->isStaff();
        
        $this->setAttrib("id", "grad-status-form");
        $this->addJsFile("/js/library/Account/Form/grad-status.js");
        $this->addCssFile("/css/library/Account/Form/grad-status.css");
        
        // which grad stuff to edit
        $editDateFlag = new Zend_Form_Element_Checkbox('editDateFlag');
        $editDateFlag->setLabel("Update graduation date");
        
        $editStatusFlag = new Zend_Form_Element_Checkbox('editStatusFlag');
        $editStatusFlag->setLabel("Update graduation status");

        $gradDate = new Fisdap_Form_Element_GraduationDate('gradDate');
        $gradDate->setYearRange(date("Y") - 5, date("Y") + 5);
        
        $gradStatus = new Zend_Form_Element_Radio('gradStatus');
        $gradStatus->setLabel('Status:')
                ->setMultiOptions(\Fisdap\Entity\GraduationStatus::getFormOptions(false, false));
        
        $goodData = new Zend_Form_Element_Radio('goodData');
        $goodData->setLabel('Good Data:')
                 ->setMultiOptions(array(1 => "These students have good data for research",
                             0 => "These students do NOT have good data for research",
                             -1 => "Good data flag has not been set"));
                 
        $removeShiftsFlag = new Zend_Form_Element_Checkbox('removeShiftsFlag');
        $removeShiftsFlag->setLabel("Remove all future shifts from Scheduler");
        
        // staff only
        $editCertFlag = new Zend_Form_Element_Checkbox('editCertFlag');
        $editCertFlag->setLabel("Update certification level (staff only)");

        $editShiftFlag = new Zend_Form_Element_Checkbox('editShiftFlag');
        $editShiftFlag->setLabel("Update shift limit (staff only)");

        $shiftLimitField = new Zend_Form_Element_Select('shiftLimitField');
        $shiftLimitField->setLabel("Field")->setMultiOptions(array("10"=>"10", "20"=>"20", "30"=>"30", "40"=>"40", "-1"=>"Unlimited"));

        $shiftLimitClinical = new Zend_Form_Element_Select('shiftLimitClinical');
        $shiftLimitClinical->setLabel("Clinical")->setMultiOptions(array("10"=>"10", "20"=>"20", "30"=>"30", "40"=>"40", "-1"=>"Unlimited"));

        //Get correct cert options for this program
        $professionId = \Fisdap\Entity\ProgramLegacy::getCurrentProgram()->profession->id;
        $certOptions = \Fisdap\Entity\CertificationLevel::getFormOptions(false, false, "description", $professionId);
        
        $certLevel = new Zend_Form_Element_Select('certLevel');
        $certLevel->setLabel('Certification:')
            ->setMultiOptions($certOptions);

        $submitButton = new Fisdap_Form_Element_SaveButton('save');
        $submitButton->setLabel("Save");
        
        //Add elements that aren't in a display group
        $this->addElements(array(
            $editDateFlag,
            $editStatusFlag,
            $gradDate,
            $gradStatus,
            $goodData,
            $removeShiftsFlag,
            $submitButton
        ));
        
        //Add staff-only elements
        if ($this->staffView) {
            $this->addElements(array(
                $editCertFlag,
                $certLevel,
                $editShiftFlag,
                $shiftLimitField,
                $shiftLimitClinical,
            ));
            $checkboxes = array('editDateFlag', 'editStatusFlag', 'editCertFlag', 'removeShiftsFlag', 'editShiftFlag');
            $selects = array('gradDate', 'certLevel');
        } else {
            $checkboxes = array('editDateFlag', 'editStatusFlag', 'removeShiftsFlag');
            $selects = array('gradDate');
        }
        
        $this->setElementDecorators(self::$checkboxDecorators, $checkboxes, true);
        $this->setElementDecorators(self::$selectDecorators, $selects, true);
        $this->setElementDecorators(self::$radioDecorators, array('gradStatus', 'goodData'), true);
        
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/gradStatusForm.phtml")),
            'Form'
        ));
        
        $this->setDefaults(array(
            'gradDate' => new \DateTime(),
            'gradStatus' => 1,
            'goodData' => -1
        ));
    }
    
    /**
     * Overwriting the isValid method to add some dependency validation
     *
     * @param array $values
     * @return boolean
     */
    public function isValid($post)
    {
        // make sure they chose some students
        if (!$post['studentIDs']) {
            $this->addError("Choose at least one student.");
        }
        
        // make sure they chose at least one thing to edit
        if (!$post['editDateFlag'] && !$post['editStatusFlag'] && !$post['editCertFlag'] && !$post['editShiftFlag']) {
            $this->addError("Choose at least one graduation setting to update.");
        }
        
        //add grad date validator if we've chosen to edit the grad date
        if ($post["editDateFlag"]) {
            $this->getElement("gradDate")->addValidator(new \Fisdap_Validate_GraduationDate(true, false));
        }
        
        return parent::isValid($post);
    }
    
    /**
     * Process the submitted POST values and do whatever you need to do
     *
     * @param array $post the POSTed values from the user
     * @return mixed either the values or the form w/errors
     */
    public function process($post)
    {
        // set the session variables to remember the multistudent picker settings
        $session = new \Zend_Session_Namespace("MultiStudentPicker");
        $session->selectedCertifications = $post["certificationLevels"];
        $session->selectedStatus = $post["graduationStatus"];
        $session->selectedGradMonth = $post['graduationMonth'];
        $session->selectedGradYear = $post['graduationYear'];
        $session->selectedSectionYear = $post["sectionYear"];
        $session->selectedSection = $post["section"];
        
        if ($this->isValid($post)) {
            $students = $post['studentIDs'];
            
            // go through each of the selected students and do the appropriate updates
            foreach ($students as $student_id) {
                $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $student_id);

                // set graduation date, if necessary
                if ($post['editDateFlag']) {
                    $student->setGraduationDate(new \DateTime($post['gradDate']['year'] . "-" . $post['gradDate']['month'] . "-01"));
                }
                
                // set graduation status, if necessary
                if ($post['editStatusFlag']) {
                    $student->graduation_status = $post['gradStatus'];
                
                    switch ($post['goodData']) {
                        case 1:
                            $student->good_data = true;
                            break;
                        case 0:
                            $student->good_data = false;
                            break;
                        case -1:
                            $student->good_data = null;
                            break;
                    }
                }

                // set shift limit, if necessary
                if ($post['editShiftFlag'] && $this->staffView) {
                    $student->field_shift_limit = ($post['shiftLimitField']);
                    $student->clinical_shift_limit = ($post['shiftLimitClinical']);
                }

                // set certification level, if necessary
                if ($post['editCertFlag'] && $this->staffView) {
                    $student->setCertification($post['certLevel']);
                }
                
                $student->save();
                                
                // delete future shifts, if necessary
                if ($post['removeShiftsFlag'] && $post['editStatusFlag'] && $post['gradStatus'] == 4) {
                    $shiftRepos = \Fisdap\EntityUtils::getRepository('ShiftLegacy');
                    $shifts = $shiftRepos->getFutureShiftsByStudent($student->id);
                    foreach ($shifts as $shift) {
                        $shift->delete();
                    }
                }
            }
            
            return true;
        }
        
        return false;
    }
}
