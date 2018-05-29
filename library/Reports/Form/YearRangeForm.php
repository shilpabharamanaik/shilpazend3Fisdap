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

class Reports_Form_YearRangeForm extends Fisdap_Form_Base
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
        $year_options = array();
        for ($y = 2012; $y <= date("Y"); $y++) {
            $year_options[$y] = $y;
        }

        // start year
        $start_year = new Zend_Form_Element_Select("start_year");
        $start_year->setMultiOptions($year_options)
            ->setAttribs(array("class" => "chzn-select",
                "data-placeholder" => "Choose a start year...",
                "style" => "width:90px",
                "multiple" => false));
        $start_year->setRegisterInArrayValidator(false);
        $start_year->setRequired(true);
        $start_year->addErrorMessage("Please select a start year.");
        $start_year->setValue(date("Y")-5);

        // end year
        $end_year = new Zend_Form_Element_Select("end_year");
        $end_year->setMultiOptions($year_options)
            ->setAttribs(array("class" => "chzn-select",
                "data-placeholder" => "Choose an end year...",
                "style" => "width:90px",
                "multiple" => false));
        $end_year->setRegisterInArrayValidator(false);
        $end_year->setRequired(true);
        $end_year->addErrorMessage("Please select an end year.");
        $end_year->setValue(date("Y"));

        // Add elements
        $this->addElements(array(
            $start_year,
            $end_year,
        ));

        //Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'forms/year-range-form.phtml')),
        ));

        // set element decorators
        $this->setElementDecorators(self::$basicElementDecorators, array('start_year', 'end_year'));

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
        $summary["Years"] = $config['start_year'] . " through " . $config['end_year'];

        return $summary;
    }
}
