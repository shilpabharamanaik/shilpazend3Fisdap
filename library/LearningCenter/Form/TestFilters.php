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
 * Form containing filters for selecting tests
 */

/**
 * @package    LearningCenter
 */
class LearningCenter_Form_TestFilters extends SkillsTracker_Form_Modal
{
    /**
     * @var array of default filters
     */
    public $filters;

    public function __construct($filters = array(), $options = null)
    {
        $this->filters = $filters;
        return parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        $this->setAttrib("class", "dark-accordion");
        $this->addJsFile("/js/library/LearningCenter/Form/test-filters.js");
        $this->addJsFile("/js/jquery.fancyFilters.js");
        $this->addCssFile("/css/library/LearningCenter/Form/test-filters.css");
        $this->addCssFile("/css/jquery.fancyFilters.css");
                
        $startDate = new Zend_Form_Element_Text("start_date");
        $startDate->setLabel("Start Date:")
                  ->setDecorators(self::$elementDecorators)
                  ->setAttrib("tabindex", -1)
                  ->setAttrib("class", "selectDate");
        
        if ($this->filters['start_date']) {
            $startDate->setValue($this->filters['start_date']);
        }
        
        $this->addElement($startDate);

        $endDate = new Zend_Form_Element_Text("end_date");
        $endDate->setLabel("End Date:")
                  ->setDecorators(self::$elementDecorators)
                  ->setAttrib("tabindex", -1)
                  ->setAttrib("class", "selectDate");

        if ($this->filters['end_date']) {
            $endDate->setValue($this->filters['end_date']);
        }
        
        $this->addElement($endDate);
    
        $stRepos = \Fisdap\EntityUtils::getRepository('ScheduledTestsLegacy');
    
        $contact = new Zend_Form_Element_Select("contact_name");
        $contact->setLabel("Contact:")
                ->setDecorators(self::$elementDecorators)
                ->setMultiOptions($stRepos->getUniqueContactNames());
        $this->addElement($contact);
        
        $moodleTestRepos = \Fisdap\EntityUtils::getRepository('MoodleTestDataLegacy');
        $allUniqueTests = $stRepos->getUniqueTests('entities');
        $sortedList = $moodleTestRepos->sortTestsByProduct($allUniqueTests, array('extraGroups' => array('retired', 'pilot_tests')));
        
        if ($sortedList) {
            $options = array('' => '') + $sortedList;
        }
        
        if ($options) {
            $test = new Zend_Form_Element_Select("test_id");
            $test->setLabel("Test:")
                 ->setDecorators(self::$elementDecorators)
                 ->setMultiOptions($options);
            $this->addElement($test);
        }
        
        $filterOptions = new Zend_Form_Element_Radio("filterOptions");
        $filterOptions->setMultiOptions(array(
                    "date-ranges-container" => "Date range",
                    "test-container" => "Test type",
                    "contact-container" => "Contact"
                ))
                ->setAttribs(array('class' => 'do-not-filter'));
        $this->addElement($filterOptions);
        
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/testFiltersForm.phtml")),
            'Form',
        ));
    }
}
