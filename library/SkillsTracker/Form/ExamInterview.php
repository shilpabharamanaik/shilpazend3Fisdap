<?php

class SkillsTracker_Form_ExamInterview extends Fisdap_Form_Base
{
    public $patients;
    public $student_id;

    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/examInterviewForm.phtml")),
        array('Form', array('class' => 'exam-interview-form')),
        );


    public function __construct($student_id = null)
    {
        $this->student_id = $student_id;

        parent::__construct();
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(self::$_formDecorators);

        $this->addCssFile("/css/skills-tracker/shifts/exam-interview.css");
        $this->addJsFile("/js/library/SkillsTracker/Form/exam-interview.js");

        //get affected patients from MRAPI

        $student_id = $this->student_id;


        $patients = \Fisdap\EntityUtils::getRepository("Patient")->getPatientsForExamInterviewTool($student_id);

        $this->patients = $patients;

        foreach ($patients as $patient) {
            $exam = new Zend_Form_Element_Checkbox("exam_" . $patient->id);
            $exam->setLabel("Exam Performed?:");

            $this->addElement($exam);

            $interview = new Zend_Form_Element_Checkbox("interview_" . $patient->id);
            $interview->setLabel("Interview Performed?:");

            $this->addElement($interview);
        }

        $saveButton = new \Fisdap_Form_Element_SaveButton("save");
        $saveButton->setLabel("Save");

        $this->addElement($saveButton);

        $this->setElementDecorators(self::$elementDecorators);
    }

    public function process($data)
    {
        $retVal = array(
            'code' => 200
        );

        if ($this->isValid($data)) {
            foreach ($this->patients as $patient) {
                if ($data['exam_' . $patient->id] == 1) {
                    //set exam to performed/1
                    $patient->exam = 1;
                } else {
                    //set exam to observed/0
                    $patient->exam = 0;
                }

                if ($data['interview_' . $patient->id] == 1) {
                    //set interview to performed/1
                    $patient->interview = 1;
                } else {
                    //set interview to observed/0
                    $patient->interview = 0;
                }

                $patient->save();
            }
        } else {
            $retVal['code'] = 500;
        }

        return $retVal;
    }
}
