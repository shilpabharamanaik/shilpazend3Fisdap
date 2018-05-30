<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2014.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Custom ZendForm
 * @package Reports
 * @author khanson
 */
class Reports_Form_PasswordScheduleForm extends Fisdap_Form_Base
{

    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($config = null, $options = null)
    {
        parent::__construct($options);
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        // Now create the individual elements
        // start date selector
        $dateRange = new Fisdap_Form_Element_DateRange('dateRange');
        $dateRange->setDefaultEnd("+6 days")
                  ->addValidator(new \Fisdap_Validate_DateRangeLimit(0, 10))
                  ->setLabel("Date range")
                  ->setDescription('(no more than 10 days):');
    
                  
        // exams
        $exams = new Zend_Form_Element_MultiCheckbox('exams');
        $examOptions = \Fisdap\EntityUtils::getRepository("MoodleTestDataLegacy")->getMoodleTestList();
        $exams->setMultiOptions($examOptions)
              ->setLabel("Exams:")
              ->setRequired(true)
              ->addErrorMessage("Please select one or more exams.");

        // Add elements
        $this->addElements(array(
            $dateRange,
            $exams,
        ));

        //Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'forms/password-schedule-form.phtml')),
        ));
        
        // set element decorators
        $this->setElementDecorators(self::$elementDecorators, array('dateRange'));
        $this->setElementDecorators(self::$multiCheckboxDecorators, array('exams'));
        
        // set defaults
        $defaultChecked = array_keys($examOptions);
        $this->setDefaults(array(
                "exams" => $defaultChecked,
        ));
    }

    /**
     * Process the submitted POST values and do whatever you need to do
     *
     * @param array $post the POSTed values from the user
     * @return mixed either the values or the form w/errors
     */
    public function process($post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();
        }
        return false;
    }
    
    /**
     * Return an array containing the summary of what's on this report
     *
     */
    public function getReportSummary($config)
    {
        $summary = array();
        
        // get date range info
        $summary["Date range"] = $config['dateRange']['startDate'] . " through " . $config['dateRange']['endDate'];
        
        // get exam info
        $exam_ids = $config['exams'] ? $config['exams'] : array();
        $exams = array();
        foreach ($exam_ids as $exam_id) {
            $exam = \Fisdap\EntityUtils::getEntity("MoodleTestDataLegacy", $exam_id);
            $exams[] = $exam->test_name;
        }
        $summary["Exam(s)"] = implode(", ", $exams);
        
        return $summary;
    }
}
