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
 * This is the form that is used for gathering Preceptor sign off report options
 *
 * @package Reports
 * @author hammer :)
 */
class Reports_Form_PreceptorSignoffReportOptions extends Fisdap_Form_Base
{
    public $program_id;
    public $config;
    public $eval_types;

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
        
        // get our evaulation type options
        $evaluation_type_options = \Fisdap\Entity\PreceptorRatingType::getFormOptions(false, false);
        $this->eval_types = $evaluation_type_options;
        
        // create the evaluation types chosen
        $evaluation_types = new Zend_Form_Element_Select("preceptor_signoff_evaluation_types");
        $evaluation_types->setMultiOptions($evaluation_type_options)
             ->setAttribs(array("class" => "chzn-select",
                                "data-placeholder" => "Choose evaluation types...",
                                "style" => "width:390px",
                                "multiple" => true,
                                "tabindex" => count($evaluation_type_options)));
        $evaluation_types->setRegisterInArrayValidator(false);
        $evaluation_types->setRequired(true);
        $evaluation_types->addErrorMessage("Please select one or more evaluation types.");
        
        // create the eureka goal/window inputs
        $eureka_goal = new Zend_Form_Element_Text('eureka_goal');
        $eureka_goal->setAttribs(array("class" => "fancy-input"));
        $eureka_window = new Zend_Form_Element_Text('eureka_window');
        $eureka_window->setAttribs(array("class" => "fancy-input"));
        
        // --------------- get our elements for the form ---------------
        $this->addElements(array($eureka_goal, $eureka_window, $evaluation_types));
        
        // --------------- deal with defaults ---------------
        $default_eureka_goal = ($this->config) ? $this->config['eureka_goal'] : "16";
        $default_eureka_window = ($this->config) ? $this->config['eureka_window'] : "20";
        
        
        $this->setDefaults(array(
                "eureka_goal" => $default_eureka_goal,
                "eureka_window" => $default_eureka_window,
                "preceptor_signoff_evaluation_types" => $this->config['preceptor_signoff_evaluation_types'],
        ));
        
        
        // --------------- set form decorators ---------------
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/preceptor-signoff-report-options.phtml")),
        ));
    } // end init()

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
        
        if ($config['eureka_goal']) {
            $success_rate_precetage = round((intval($config['eureka_goal'])/intval($config['eureka_window']))*100, 1);
        }
        
        $types = array();

        if ($config['preceptor_signoff_evaluation_types']) {
            foreach ($config['preceptor_signoff_evaluation_types'] as $type_id) {
                $types[] = $this->eval_types[$type_id];
            }
        }
        
        $summary['Evaluation type(s)']  = ($types) ? implode(", ", $types) : "";
        $summary['Eureka point'] = $config['eureka_goal'] . " / " . $config['eureka_window'] . " <span class='subtle_eureka_summary'>(" . $success_rate_precetage . "% success rate)</span>";
        
        return $summary;
    }
    
    public function addFilesForEurekaGraphs()
    {
        // let's borrow some js/stying from the eureka report
        $this->addJsFile("/js/library/Reports/Form/eureka-report.js");
        $this->addCssFile("/css/library/Reports/Form/eureka-report.css");
        
        $this->addJsFile("/js/library/Reports/Form/preceptor-signoff-report.js");
        $this->addCssFile("/css/library/Reports/Form/preceptor-signoff-report.css");
        
        $this->addJsFile("/js/jquery-migrate-1.2.1.js");
            
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
