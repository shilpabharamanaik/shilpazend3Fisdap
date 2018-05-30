<?php
/*	*	*	*	*	*	*	*	*
 *
 *	Copyright (C) 1996-2011.  This is an unpublished work of
 *			Headwaters Software, Inc.
 *				ALL RIGHTS RESERVED++++
 *	This program is a trade secret of Headwaters Software, Inc.
 *	and it is not to be copied, distributed, reproduced, published,
 *	or adapted without prior authorization
 *	of Headwaters Software, Inc.
 *
 *	*	*	*	*	*	*	*	*/

/**
 * Description of LabPracticeGoalsForm
 *
 * @author stape
 */

class Reports_Form_LabPracticeGoalsForm extends Fisdap_Form_Base
{
    /**
     * @var array default values for the report form
     */
    public $config;
    
    public function __construct($config = null, $options = null)
    {
        $this->config = $config;
        
        parent::__construct($options);
    }
    
    public function init()
    {
        //$this->addJsFile("/js/library/Reports/Form/lab-practice-report-filter.js");
        //$this->addCssFile("/css/library/Reports/Form/report-filter.css");
        //$this->setAttrib("id", "lab-practice-report-form");
        
        // certification level
        $certLevel = new Fisdap_Form_Element_jQueryUIButtonset('certLevel');
        $certLevel->setRequired(($this->isInstructor));
        $certLevel->setLabel('Practice goal set');
        $certLevel->setUiTheme("");
        $certLevel->setButtonWidth("90px");
        $certLevel->setUiSize("extra-small");
        $certOptions = \Fisdap\EntityUtils::getRepository('CertificationLevel')->getSortedFormOptions(\Fisdap\Entity\ProgramLegacy::getCurrentProgram()->profession->id);
        $certLevel->setOptions($certOptions);
        $certLevel->setDecorators(array(
                'ViewHelper',
                array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_8')),
                array('Label', array('tag' => 'div', 'class' => 'grid_4 inline-label', 'escape' => false)),
                array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
            ));
        
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $certLevelHidden = new Zend_Form_Element_Hidden("certLevel");
        $certLevelHidden->setValue($user->getCurrentUserContext()->certification_level->id)
                        ->setDecorators(array("ViewHelper"));
        
        if ($user->isInstructor()) {
            $this->addElement($certLevel);
        } else {
            $this->addElement($certLevelHidden);
        }

        // Report type/mode
        $reportType = new Fisdap_Form_Element_jQueryUIButtonset('reportType');
        $reportType->setRequired(true);
        $reportType->setLabel('View options');
        $reportType->setOptions(array('summary' => 'Summary', 'detailed' => 'Detailed'));
        $reportType->setUiTheme("");
        $reportType->setUiSize("extra-small");
        $reportType->setButtonWidth("90px");
        $reportType->setDecorators(array(
                'ViewHelper',
                array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_8')),
                array('Label', array('tag' => 'div', 'class' => 'grid_4 inline-label', 'escape' => false)),
                array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
            ));
        $this->addElement($reportType);
        
        $dateRange = new Fisdap_Form_Element_DateRange('dateRange');
        $dateRange->setDefaultStart("-1 year")
                  ->setLabel("Date range")
                  ->setDefaultStart("")
                  ->setDefaultEnd("")
                  ->setDecorators(array(
                        'ViewHelper',
                        array('Label'),
                        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
                    ));
        $this->addElement($dateRange);
        
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            //'FormElements',
            array('ViewScript', array('viewScript' => "forms/lab-practice-goals-form.phtml")),
        ));
        
        //Do we have existing form values to populate
        if ($this->config) {
            $this->setDefaults($this->config);
        } else {
            $this->setDefaults(array(
                "reportType" => "summary",
            ));
        }
        
        $dateRange->removeDecorator('Label');
    }
    
    public function process($post)
    {
        $values = $this->getValues();
        return $values;
    }
    
    /**
     * Return an array containing the summary of what's on this report
     *
     */
    public function getReportSummary($config)
    {
        $summary = array();
        
        // get date range info
        if (empty($config['dateRange']['startDate']) && empty($config['dateRange']['endDate'])) {
            // no dates selected means give them all dates
            $summary["Date range"] = "All dates";
        } elseif (empty($config['dateRange']['endDate'])) {
            // no end date selected means give them all dates from start date
            $summary["Date range"] = "From " . date_create($config['dateRange']['startDate'])->format('F j, Y');
        } elseif (empty($config['dateRange']['startDate'])) {
            // no start date selected means give them all dates through end date
            $summary["Date range"] = "Through " . date_create($config['dateRange']['endDate'])->format('F j, Y');
        } else {
            // both dates selected means give them the date range
            $summary["Date range"] = date_create($config['dateRange']['startDate'])->format('F j, Y') . " through " . date_create($config['dateRange']['endDate'])->format('F j, Y');
        }
        
        // get certification information
        $summary["Certification"] = \Fisdap\EntityUtils::getEntity("CertificationLevel", $config['certLevel'])->description;
        
        return $summary;
    }

    /**
     * Override isValid to do custom validation that checks cert level
     */
    public function isValid($post)
    {
        // make sure a cert level is selected
        if (!$post['certLevel']) {
            $this->addError("Please select a goal set.");

            return false;
        } else {
            // we're good, check other validation stuff
            return parent::isValid($post);
        }
    }
}
