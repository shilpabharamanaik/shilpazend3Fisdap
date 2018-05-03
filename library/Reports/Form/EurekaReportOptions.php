<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           							  *
*        Copyright (C) 1996-2014.  This is an unpublished work of               *
*                         Headwaters Software, Inc.                         				  *
*                            ALL RIGHTS RESERVED                            				  *
*        This program is a trade secret of Headwaters Software, Inc. and      *
*        it is not to be copied, distributed, reproduced, published, or     		  *
*        adapted without prior authorization of Headwaters Software, Inc.     *
*                                                                           							  *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Custom ZendForm
 * This is the form that is used for gathering Eureka report options
 * 
 * @package Reports
 * @author hammer :)
 */
class Reports_Form_EurekaReportOptions extends Fisdap_Form_Base
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
		
		// get our procedure options (these must have 'require_success' turned on)
		$airway = $this->createProcedureOptions('AirwayProcedure');
		$iv = $this->createProcedureOptions('IvProcedure');
		$other = $this->createProcedureOptions('OtherProcedure');
		$skill_options = array("Airway" => $airway, "IV" => $iv, "Other" => $other);
		
		// create the skills chosen
		$skills = new Zend_Form_Element_Select("eureka_skills");
		$skills->setMultiOptions($skill_options)
			 ->setAttribs(array("class" => "chzn-select",
								"data-placeholder" => "Choose skills...",
								"style" => "width:390px",
								"multiple" => true,
								"tabindex" => count($skill_options)));
		$skills->setRegisterInArrayValidator(false);
		$skills->setRequired(true);
		$skills->addErrorMessage("Please select one or more skills.");
		
		// create the slider checkbox for combining graphs
		$combine_graphs = new Zend_Form_Element_Checkbox("eureka_combine_graphs");
		
		// create the eureka goal/window inputs
		$eureka_goal = new Zend_Form_Element_Text('eureka_goal');
		$eureka_goal->setAttribs(array("class" => "fancy-input"));
		$eureka_window = new Zend_Form_Element_Text('eureka_window');
		$eureka_window->setAttribs(array("class" => "fancy-input"));
		
		// --------------- get our elements for the form ---------------
		$this->addElements(array($eureka_goal, $eureka_window, $skills, $combine_graphs));
		
		// --------------- deal with defaults ---------------
		$default_eureka_goal = ($this->config) ? $this->config['eureka_goal'] : "16";
		$default_eureka_window = ($this->config) ? $this->config['eureka_window'] : "20";
		
		
		$this->setDefaults(array(
				"eureka_goal" => $default_eureka_goal,
				"eureka_window" => $default_eureka_window,
				"eureka_skills" => $this->config['eureka_skills'],
				"eureka_combine_graphs" => $this->config['eureka_combine_graphs']
		));
		
		
		// --------------- set form decorators ---------------
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => "forms/eureka-report-options.phtml")),
		));
		
	} // end init()
	
	private function createProcedureOptions($procedure_type)
	{
		// Get a full listing of all available procedures...
		$full_proc_name = "\Fisdap\Entity\\" . $procedure_type;
		$all_procedures = $full_proc_name::getFormOptions(false, true, false);
		
		$procedure_type_entity_name = "\Fisdap\Entity\Program" . $procedure_type;
		
		$return_array = array();
		
		foreach($all_procedures as $id => $name){
			// does this program include the procedure/skill?
			$program_includes = $procedure_type_entity_name::programIncludesProcedure($this->program_id, $id);
			
			if($program_includes){
				// does this skill require success?
				$requires_success = \Fisdap\EntityUtils::getEntity($procedure_type, $id)->require_success;
				
				if($requires_success){
					$return_array[strtolower($procedure_type) . "_" . $id] = $name;
				}
			}
		}
		
		return $return_array;
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
		
		$single_graph_summary = "";
		$eureka_object = new Fisdap_Reports_Eureka(1, $config);
		
		$skills = $eureka_object->getSortedSelectedProcedureNames();
		
		if(count($skills) > 1){
			$single_graph_summary = ($config['eureka_combine_graphs']) ? "combined into single graph" : "multiple graphs";
		}
		
		if($config['eureka_goal']){
			$success_rate_precetage = round((intval($config['eureka_goal'])/intval($config['eureka_window']))*100,1);
		}
		
		$summary['Skill(s)']  = implode(", ", $skills);
		$summary['Skill(s)'] .= ($single_graph_summary) ? " <span class='subtle_eureka_summary'>(" . $single_graph_summary . ")</span>" : "";
		$summary['Eureka point'] = $config['eureka_goal'] . " / " . $config['eureka_window'] . " <span class='subtle_eureka_summary'>(" . $success_rate_precetage . "% success rate)</span>";
		
		return $summary;
	}
	
	public function addFilesForEurekaGraphs()
	{
		$this->addJsFile("/js/library/Reports/Form/eureka-report.js");
		$this->addCssFile("/css/library/Reports/Form/eureka-report.css");
		
		$this->addJsFile("/js/jquery-migrate-1.2.1.js");
            
		$this->addCssFile("/css/library/Reports/reports.css");
		$this->addJsFile("/js/jquery.tablescroll.js");
		$this->addCssFile("/css/jquery.tablescroll.css");
		
		$this->addJsFile("/js/library/Fisdap/Utils/create-pdf.js");
		$this->addJsFile("/js/library/Fisdap/Utils/jspdf.min.js");
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
