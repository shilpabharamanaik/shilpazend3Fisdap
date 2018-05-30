<?php
/**
 * Class Fisdap_Reports_GraduationRequirements
 * This is the Graduation Requirements Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_PreceptorTraining extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'multiPicklist' => array(
            'title' => 'Select one or more instructor(s)',
            'options' =>  array(
                'type' => 'instructors'
            ),
        )
    );
    
    // Overriding the default constructor so we can initialize the multiPickList options dynamically before load
    public function __construct($report, $config = array())
    {
        parent::__construct($report, $config);

        // Get the list of assignable instructors here...
        $this->initPicklistOptions();
    }
    
    /**
     * This function finds all students whow have a transition course product for this program
     * and makes the multistudentPicklist show those as selectable.
     */
    private function initPicklistOptions()
    {
        // Get a list of all students with the transition course property.
        $instructorRepo = \Fisdap\EntityUtils::getRepository('InstructorLegacy');
        $productRepo = \Fisdap\EntityUtils::getRepository('Product');
    
        // Get the 3 transition course products...
        $expr = \Doctrine\Common\Collections\Criteria::expr();
        $criteria = \Doctrine\Common\Collections\Criteria::create();
        $criteria->where($expr->in('id', array(9)));
        $products = $productRepo->matching($criteria);
    
        $config = 0;
    
        foreach ($products as $p) {
            $config = $config | $p->configuration;
        }
    
        $instructors = $instructorRepo->getInstructorsByProductCodeConfig($config, $this->user->getProgramId());
    
        $this->formComponents['multiPicklist']['options']['assignableItems'] = $instructors;
    }
    
    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport()
    {
        $reportTable = array(
            'head' => array(
                '000' => array( // First row headers...
                    'Name',
                    'Completed Date'
                )
            ),
            'body' => array()
        );
        
        $bodyRows = array();
        
        $data = $this->getReportData();
        
        $reportTable['body'] = $data;
        
        $this->data['transition_report'] = array("type" => "table", "content" => $reportTable);
    }
    
    private function getReportData()
    {
        $instructorIds = explode(',', $this->config['multi_picklist_selected']);
        
        // Get the transition completions for these users
        $completions = \Fisdap\MoodleUtils::getPreceptorTrainingCompletionsById($instructorIds);
        
        $reportData = array();
        
        foreach ($completions as $c) {
            $instructor = \Fisdap\EntityUtils::getEntity('InstructorLegacy', $c['instructor_id']);
            $reportRow = array();
            $reportRow['name'] = $instructor->last_name . ", " . $instructor->first_name;
            $reportRow['completed'] = date('Y-m-d', $c['timefinish']);
            
            $reportData[] = $reportRow;
        }
        
        return $reportData;
    }
    
    // No real need for validation- the MSP handles it.
    public function preceptorTrainingValidate($info)
    {
    }
    
    // Only allow instructors to view this report.
    public static function hasPermission($userContext)
    {
        return $userContext->isInstructor();
    }
}
