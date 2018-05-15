<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Form to schedule a test or edit a scheduled test
 */

/**
 * @package    LearningCenter
 */
class LearningCenter_Form_ScheduleTest extends SkillsTracker_Form_Modal
{
    /**
     * @var \Fisdap\Entity\ScheduledTestsLegacy
     */
    public $scheduledTest;
    
    /**
     * @var \Fisdap\Entity\UserLegacy
     */
    public $user;
    
    /**
     * @var string
     */
    public $error;
    
    /**
     * @var
     * Post-submission, the students in the picker who were checked off
     */
    public $checkedStudents;
    
    public function __construct($scheduledTestId = null, $checkedStudents = null, $options = null)
    {
        $this->scheduledTest = \Fisdap\EntityUtils::getEntity("ScheduledTestsLegacy", $scheduledTestId);
        
        if ($user = \Fisdap\Entity\User::getLoggedInUser()) {
            $this->user = $user;
        }
        
        // set checkedStudents to null typically, as the variable is only used in post-submission (validation failed) situations
        $this->checkedStudents = $checkedStudents;
        
        return parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        $this->setAttrib("id", "schedule-test-form");
        $this->addJsFile("/js/library/LearningCenter/Form/schedule-test.js");
        $this->addCssFile("/css/library/LearningCenter/Form/schedule-test.css");
                
        $startDate = new ZendX_JQuery_Form_Element_DatePicker("start_date");
        $startDate->setLabel("Start Date")
                  ->setJQueryParam('onSelect', new Zend_Json_Expr('
						function(selectedDate) {
								// set the min End date to this day
								$("#end_date").datepicker("option", "minDate", selectedDate);
								
								// Set the max End date to 7 days from today
								var selectedDateArray = selectedDate.split("/");
								// javascript Date month value is on base-0 (0 - 11) so we have to subtract one from the text field
								var selectedDateObj = new Date(selectedDateArray[2], (parseInt(selectedDateArray[0], 10) - 1), selectedDateArray[1]);
								selectedDateObj.setDate(selectedDateObj.getDate() + 6);
								$("#end_date").datepicker("option", "maxDate", selectedDateObj);
						}
					'))
                  ->setRequired(true)
                  ->setAttrib("class", "selectDate")
                  ->setDecorators(self::$strippedFormJQueryElements)
                  ->setAttrib("tabindex", -1);
        $this->addElement($startDate);
                
        $endDate = new ZendX_JQuery_Form_Element_DatePicker("end_date");
        $endDate->setLabel("End Date")
                  ->setRequired(true)
                  ->setAttrib("class", "selectDate")
                  ->setDecorators(self::$strippedFormJQueryElements)
                  ->setAttrib("tabindex", -1);
        // set min if value already present
        if ($this->scheduledTest->id) {
            $endDate->setJQueryParam('minDate', $this->scheduledTest->start_date->format("m/d/Y"));
            
            // also set maximum end date to 6 days after the current start date
            $maxEnd = clone $this->scheduledTest->start_date;
            $plusSix = new \DateInterval("P6D");
            $maxEnd->add($plusSix);
            $endDate->setJQueryParam('maxDate', $maxEnd->format("m/d/Y"));
        }
        $this->addElement($endDate);
        
        $contact = new Zend_Form_Element_Text("contact_name");
        $contact->setLabel("Name:")
                ->setRequired(true)
                ->setDecorators(self::$gridElementDecorators);
        $this->addElement($contact);
        
        $contactPhone = new Zend_Form_Element_Text("contact_phone");
        $contactPhone->setLabel("Phone:")
                    ->setRequired(true)
                    ->setDecorators(self::$gridElementDecorators);
        $this->addElement($contactPhone);
        
        $contactEmail = new Zend_Form_Element_Text("contact_email");
        $contactEmail->setLabel("Email:")
                    ->setRequired(true)
                    ->setDecorators(self::$gridElementDecorators);
        $this->addElement($contactEmail);
        
        $moodleRepos = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy');
        $testInfo = $moodleRepos->getMoodleTestList(array('active' => 1, 'extraGroups' => array('pilot_tests')), 'productArrayWithInfo');
        $options = $testInfo['product'];
        if (is_array($options)) {
            $options = array('' => '') + $options;
        } else {
            $options = array();
        }

        //This is only temporary, I swear. Please don't hate me.
        if ($this->user->getCurrentProgram()->id != 2077) {
            if ($options['Australian Comprehensive Exams']) {
                unset($options['Australian Comprehensive Exams']);
            }
        }

        $test = new Zend_Form_Element_Select("test_id");
        $test->setLabel("Test")
             ->setRequired(true)
             ->addValidator(new Zend_Validate_NotEmpty())
             ->setDecorators(array("ViewHelper"))
             ->setMultiOptions($options);
        $this->addElement($test);
        
        // add a hidden element describing which tests are set to ShowTotals = 0
        // JS will use this info to disable the "Students can/cannot view scores" form field
        $noShowTotalsTests = array();
        foreach ($testInfo['info'] as $moodle_quiz_id => $test) {
            if ($test->show_totals == false) {
                $noShowTotalsTests[] = $moodle_quiz_id;
            }
        }
        $noShowTotals = new Zend_Form_Element_Hidden("noShowTotals");
        $noShowTotals->setDecorators(array("ViewHelper"));
        $noShowTotals->setValue(implode(',', $noShowTotalsTests));
        $this->addElement($noShowTotals);
        
        $testNotes = new Zend_Form_Element_Textarea("test_notes");
        $testNotes->setDecorators(array('ViewHelper'))
                  ->setAttribs(array("rows" => 6, "cols" => 40));
        $this->addElement($testNotes);
        
        $published = new Zend_Form_Element_Select('is_published');
        $published->setMultiOptions(array(0 => 'Can Not', 1 => 'Can'))
                  ->setDecorators(array("ViewHelper"));
        $this->addElement($published);
        
        $scheduledTestId = new Zend_Form_Element_Hidden("scheduledTestId");
        $scheduledTestId->setDecorators(array("ViewHelper"));
        $this->addElement($scheduledTestId);
        
        $save = new Fisdap_Form_Element_SaveButton("save");
        $save->setDecorators(array("ViewHelper"));
        $this->addElement($save);
        
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/scheduleTestForm.phtml")),
            array('Description', array('placement' => 'prepend')),
            'Form',
        ));

        if ($this->scheduledTest->id) {
            $this->setDefaults(array(
                "scheduledTestId" => $this->scheduledTest->id,
                "contact_name" => $this->scheduledTest->contact_name,
                "contact_phone" => $this->scheduledTest->contact_phone,
                "contact_email" => $this->scheduledTest->contact_email,
                "start_date" => $this->scheduledTest->start_date->format("m/d/Y"),
                "end_date" => ($this->scheduledTest->end_date->format("Y") < 1990) ? $this->scheduledTest->start_date->format("m/d/Y") : $this->scheduledTest->end_date->format("m/d/Y"), // some DB values of 0000-00-00 exist, and these get translated by Doctrine into weird, negative-year'd DateTime objects. In which case we default to the start date.
                "is_published" => $this->scheduledTest->publish_scores,
                "test_id" => $this->scheduledTest->test->moodle_quiz_id,
                "test_notes" => $this->scheduledTest->test_notes,
            ));
        } else {
            $today = new \DateTime();
            $this->setDefaults(array(
                "contact_name" => $this->user->getName(),
                "contact_phone" => $this->user->getCurrentRoleData()->office_phone,
                "contact_email" => $this->user->email,
                "is_published" => 1,
                "start_date" => $today->format("m/d/Y"),
                "end_date" => $today->format("m/d/Y"),
            ));
        }
    }
    
    public function process($post)
    {
        //Grab this field out here because
        $this->checkedStudents = $studentIds = $post['studentIDs'];
        
        if ($this->isValid($post)) {
            $values = $this->getValues();
            
            if ($values['scheduledTestId']) {
                $scheduledTest = \Fisdap\EntityUtils::getEntity("ScheduledTestsLegacy", $values['scheduledTestId']);
            } else {
                $scheduledTest = \Fisdap\EntityUtils::getEntity("ScheduledTestsLegacy");
            }
            
            
            
            $scheduledTest->contact_name = $values['contact_name'];
            $scheduledTest->contact_phone = $values['contact_phone'];
            $scheduledTest->contact_email = $values['contact_email'];
            $scheduledTest->test = \Fisdap\EntityUtils::getEntity('MoodleTestDataLegacy', $values['test_id']);
            
            $scheduledTest->program_id = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
            
            $scheduledTest->start_date = new DateTime($values['start_date']);
            $scheduledTest->end_date = new DateTime($values['end_date']);
            
            $scheduledTest->test_notes = $values['test_notes'];
            
            if (!isset($_POST['is_published'])) {
                $scheduledTest->publish_scores = 0;
            } else {
                $scheduledTest->publish_scores = $values['is_published'];
            }
            //record students in LEGACY, DEPCRECATED serialized field
            $scheduledTest->scheduled_students = serialize($studentIds);
            
            //also store students in new relationship
            $students = new \Doctrine\Common\Collections\ArrayCollection();
            foreach ($studentIds as $id) {
                $students->add(\Fisdap\EntityUtils::getEntity('StudentLegacy', $id));
            }
            $scheduledTest->students = $students;
            
            $scheduledTest->pilot_agreed_on = new DateTime();
            
            // If this is an existing test, we need to manually handle updating passwords.
            // I don't know why Doctrine's PreUpdate hook doesn't work the way I want it to, but it doesn't
            // so don't even try :(
            if ($scheduledTest->id) {
                $scheduledTest->checkPasswordsOnPrePersist();
            }
            
            $scheduledTest->save();
            
            // send confirmation email to contact person
            $scheduledTest->sendInstructorNotification();
            
            return $scheduledTest->id;
        }
        
        return false;
    }
}
