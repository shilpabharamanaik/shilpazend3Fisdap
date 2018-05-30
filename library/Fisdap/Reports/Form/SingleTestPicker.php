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
 * Single Test Picker
 * Choose a Fisdap Test (exam) and optionally a date range
 *
 * This extends SkillsTracker_Form_Modal because we need self::$strippedFormJQueryElements
 * but maybe that's crazy...
 *
 * @author jmortenson
 * // SkillsTracker_Form_Modal
 */
class Fisdap_Reports_Form_SingleTestPicker extends Fisdap_Form_Base
{
    /**
     * @var array Form options
     */
    public $formOptions = array();

    /**
    * @param $options mixed additional Zend_Form options
    */
    public function __construct($filters = null, $options = null, $formOptions = array())
    {
        $this->formOptions = $formOptions;

        parent::__construct($options);
    }
        
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $formElements = array();

        $moodleRepos = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy');
        $testInfo = $moodleRepos->getMoodleTestList(array('active' => array(1,3), 'extraGroups' => array('pilot_tests, retired')), 'productArrayWithInfo');

        $options = $testInfo['product'];
        if (is_array($options)) {
            $options = array('' => '') + $options;
        } else {
            $options = array();
        }

        // do we need to exclude certain quizzes?

        if (isset($this->formOptions['excludeTests']) && is_array($this->formOptions['excludeTests'])) {
            foreach ($options as $groupKey => $groupValue) {
                if (is_array($groupValue)) {
                    foreach ($groupValue as $key => $value) {
                        if (in_array($key, $this->formOptions['excludeTests'])) {
                            // remove from the array of options
                            unset($options[$groupKey][$key]);
                        }
                    }

                    // we might have unset all of the group items
                    if (count($options[$groupKey]) == 0) {
                        unset($options[$groupKey]);
                    }
                }
            }
        }


        $test = new Zend_Form_Element_Select("test_id");
        $test->setLabel("Test")
            ->setRequired(true)
            ->addValidator(new Zend_Validate_NotEmpty())
            ->setDecorators(array("ViewHelper"))
            ->setMultiOptions($options)
            ->setAttribs(array("class" => "chzn-select",
                "data-placeholder" => "",
                "style" => "width:260px",
                ));
        $formElements[] = $test;

        $attempt = new Zend_Form_Element_Select("attempt");
        $attempt->setLabel("Attempt:")
            ->setMultiOptions(array(1 => 1,2 => 2,3 => 3,4 => 4,5 => 5,6 => 6,7 => 7,8 => 8))
            ->setAttribs(array("class" => "chzn-select",
                "data-placeholder" => "",
                "style" => "width:50px",
            ));

        if (!isset($this->formOptions['dateRange']) || $this->formOptions['dateRange']) {
            $dateRange = new Fisdap_Form_Element_DateRange('dateRange');
            $dateRange->setDefaultStart("-1 year")
                ->setLabel('')
                ->setDefaultStart("")
                ->setDefaultEnd("")
                ->setDecorators(array(
                    'ViewHelper',
                    //array('Label'),
                    array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'form-prompt')),
                ));
            $formElements[] = $dateRange;
        }

        // add elements to the form
        $this->addElements(array($test, $dateRange, $attempt)); //$this->addElements($formElements);
        
        //Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'forms/single-test-picker.phtml')),
        ));
    }

    
    /**
     * Return user-legible set of fields/values for display in a Fisdap Report Summary
     */
    public function getReportSummary($config = array())
    {
        // get test names so we can display the happy legible test name
        $moodleRepos = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy');
        $testInfo = $moodleRepos->getMoodleTestList(array('active' => array(1,3), 'extraGroups' => array('pilot_tests')), 'productArrayWithInfo');
        $testLabel = $testInfo['info'][$config['test_id']]->test_name;

        $summary = array(
            'Exam' => $testLabel,
        );
        if ($config['dateRange']['startDate']) {
            $summary['Start Date'] = $config['dateRange']['startDate'];
        }
        if ($config['dateRange']['endDate']) {
            $summary['End Date'] = $config['dateRange']['endDate'];
        }

        return $summary;
    }
}
