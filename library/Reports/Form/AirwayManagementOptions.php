<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2014.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or      *
*        adapted without prior authorization of Headwaters Software, Inc.    *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Custom ZendForm
 * This is the form that is used for gathering Eureka report options
 * 
 * @package Reports
 * @author hammer :)
 */
class Reports_Form_AirwayManagementOptions extends Fisdap_Form_Base
{
	public $program_id;
	public $config;

	/**
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($config = null, $options = null)
	{
		$this->config = $config;
		$this->program_id = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
		parent::__construct($options);
	}

	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();
		
		// get all our necessary JS/CSS files for the eureka graphs
		$this->addFilesForEurekaGraphs();
		
		
		// --------------- get our elements for the form ---------------
		
		// Report type/mode
		$reportType = new Fisdap_Form_Element_jQueryUIButtonset('airway_management_report_type');
		$reportType->setRequired(true);
		$reportType->setLabel('View type:');
		$reportType->setOptions(array('summary' => 'Summary', 'detailed' => 'Detailed'));
		$reportType->setUiTheme("");
		$reportType->setUiSize("extra-small");
		$reportType->setButtonWidth("90px");
		$reportType->setDecorators(array(
				'ViewHelper',
				array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => 'grid_8')),
				array('Label', array('tag' => 'div', 'class' => 'grid_3 inline-label', 'escape' => false)),
				array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
			));
		$this->addElement($reportType);
		
		$includeObserved = new Zend_Form_Element_Checkbox('include_observed_airway_managements');
		$includeObserved->setAttribs(array("class" => "slider-checkbox"))
				   ->setLabel("Observed attempts");
		$this->addElement($includeObserved);

		
		// --------------- deal with defaults ---------------
		// Do we have existing form values to populate
		if ($this->config) {
			$this->setDefaults($this->config);
		} else {
			$this->setDefaults(array(
				"airway_management_report_type" => "detailed",
				"include_observed_airway_managements" => 1,
			));
		}
		
		
		// --------------- set form decorators ---------------
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => "forms/airway-management-report-options.phtml")),
		));
		
	} // end init()
	
	/**
	 * Return an array containing the summary of what's on this report
	 *
	 */
	public function getReportSummary($config)
	{
		$summary = array();
		
		$report_type = ucfirst($config['airway_management_report_type']);
		
		if($report_type == "Detailed"){
			$observed_on = ($config['include_observed_airway_managements']) ? "All attempts" : "Exclude observed";
			$summary['Attempts'] = $observed_on;
		}
		
		return $summary;
	}
	
	public function addFilesForEurekaGraphs()
	{
		$this->addJsFile("/js/jquery-migrate-1.2.1.js");
            
		$this->addCssFile("/css/jquery.sliderCheckbox.css");
		$this->addJsFile("/js/jquery.sliderCheckbox.js");
		
		$this->addCssFile("/css/library/Reports/reports.css");
		$this->addJsFile("/js/jquery.tablescroll.js");
		$this->addCssFile("/css/jquery.tablescroll.css");
		
		$this->addJsFile("/js/library/Fisdap/Utils/create-pdf.js");
		$this->addJsFile("/js/jquery.printElement.min.js");
		
		$this->addJsFile("/js/jquery.eurekaGraph.js");
		$this->addJsFile("/js/library/SkillsTracker/View/Helper/eureka-modal.js");
		$this->addJsFile("/js/jquery.jqplot.min.js");
		$this->addJsFile("/js/syntaxhighlighter/scripts/shCore.min.js");
		$this->addJsFile("/js/syntaxhighlighter/scripts/shBrushJScript.min.js");
		$this->addJsFile("/js/syntaxhighlighter/scripts/shBrushXml.min.js");
		
		// gross collection of stylesheet we need for the graphing plugin
		$this->addCssFile("/css/jquery.jqplot.min.css");
		$this->addCssFile("/css/jquery.eurekaGraph.css");
		$this->addCssFile("/js/syntaxhighlighter/styles/shCoreDefault.min.css");
		$this->addCssFile("/js/syntaxhighlighter/styles/shThemejqPlot.min.css");
		
		// add files for slider checkboxes
		$this->addJsFile("/js/jquery.sliderCheckbox.js");
		$this->addCssFile("/css/jquery.sliderCheckbox.css");
		
	} // end addFilesForEurekaGraphs()
}
