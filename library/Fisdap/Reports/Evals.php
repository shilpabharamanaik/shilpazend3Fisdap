<?php
/**
 * Class Fisdap_Reports_Evals
 */
class Fisdap_Reports_Evals extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'dateRangeComponent' => array(
            'title' => 'Date range'
        ),
        'checkmarkTableGeneric' => array(
            'title' => 'Select Evaluations',
            'options' => array(
                'fieldName' => 'selected_evals',
                'summaryLabel' => 'Evaluations',
                'rows' => array(),
                'multiSelect' => true,
                'searchable' => true
            ),
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' =>  array(
                'loadJSCSS' => true,
                'loadStudents' => true,
                'showTotal' => true,
                'studentVersion' => true,
                'useSessionFilters' => true,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        ),
    );
    
    // Constructor
    public function __construct($report, $config = array())
    {
        /* Initialize action controller here */
        parent::__construct($report, $config);
        
        // default to the same site types as indicated in skillstracker settings
        $programId = \Fisdap\Entity\ProgramLegacy::getCurrentProgram()->id;
        $evals = \Fisdap\EntityUtils::getRepository('EvalDefLegacy')->getEvalsByProgram($programId);
        $eval_rows = array();
        foreach ($evals as $eval_id => $eval_name) {
            $eval_rows[] = array('value' => $eval_id,
                                 'content' => array($eval_name));
        }
        $this->formComponents['checkmarkTableGeneric']['options']['rows'] = $eval_rows;
    }
    
    protected function dateRangeComponent($config = null)
    {
        $selectedStart = $config['dateRange']['startDate'];
        $selectedEnd = $config['dateRange']['endDate'];
        
        // A custom form component for this report
        $dateRange = new Fisdap_Form_Element_DateRange('dateRange');
        $dateRange->setDefaultStart($selectedStart)
                  ->setDefaultEnd($selectedEnd)
                  ->removeDecorator("Label");
        return $dateRange;
    }
    
    protected function dateRangeComponentSummary($options = array(), $config = array())
    {
        $viewHelperObj = $this->view->getHelper('dateRangeElement');

        return $viewHelperObj->dateRangeElementSummary('dateRange', $options, $config);
    }
    
    protected function dateRangeComponentValidate($info)
    {
        $dateRange = new Fisdap_Form_Element_DateRange('dateRange');
        $selectedDateRange = $this->config["dateRange"];
        $this->valid = $dateRange->isValid($selectedDateRange);

        //Only add the error messages if there are any
        $errorMessages = $dateRange->getMessages();
        if (!empty($errorMessages)) {
            $this->errors["dateRange"][] = $errorMessages;
        }
    }
    
    public function checkmarkTableGenericValidate($info)
    {
        // make sure we have one or more evals
        $selected = $this->config["selected_evals"];
        if ($selected == "") {
            $this->valid = false;
            $this->errors["selected_evals"][] = "Please select one or more evals.";
        }
    }
    
    // overrides the one set by the view helper
    public function checkmarkTableGenericSummary($options = array(), $config = array())
    {
        // get label for the selected row (assuming first content cell is label)
        $selectedEvals = explode(",", $config["selected_evals"]);
        if (count($selectedEvals) > 1) {
            return array("Evaluation" => count($selectedEvals) . " evals");
        }
        
        foreach ($options['rows'] as $row) {
            if ($row['value'] == $selectedEvals[0]) {
                $label = $row['content'][0];
            }
        }
        
        return array("Evaluations" => $label);
    }
    
    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport()
    {
        // get the form info
        $students = $this->getMultiStudentData(true);
        $start_date = $this->config['dateRange']['startDate'];
        $end_date = $this->config['dateRange']['endDate'];
        $evals = explode(",", $this->config['selected_evals']);
        

        // If nothing is done below, we parse a blank array
        $this->data = array();
        
        // Reference the reports repo, where we lift heavy things!
        $repo = \Fisdap\EntityUtils::getRepository('Report');
        
        // loop through the selected evals and make a table for each
        foreach ($evals as $eval_def_id) {
            $eval = \Fisdap\EntityUtils::getEntity('EvalDefLegacy', $eval_def_id);
            $title = $eval->eval_title;

            // Actually do the heavy things.
            $data = $repo->getStudentEvalData($eval->id, array_keys($students), $start_date, $end_date);
            $table_data = array(
                'title' => $title,
                'nullMsg' => 'None of the selected students filled out this eval.',
                'head' => array(
                    '000' => array(
                        array(
                            'data' => 'Student',
                            'rowspan' => 2,
                            'class' => 'superheader',
                        ),
                        array(
                            'data' => 'Peer/Self evaluated',
                            'colspan' => 4,
                            'class' => 'superheader',
                        ),
                        array(
                            'data' => 'Preceptor evaluated',
                            'colspan' => 4,
                            'class' => 'superheader',
                        ),
                        array(
                            'data' => 'Instructor evaluated',
                            'colspan' => 4,
                            'class' => 'superheader',
                        ),
                        array(
                            'data' => 'Total',
                            'rowspan' => 2,
                            'class' => 'superheader',
                        ),
                    ),
                    '001' => array( // second row headers
                        array(
                            'data' => 'Passed',
                            'class' => 'column-title',
                        ),
                        array(
                            'data' => 'Confirmed',
                            'class' => 'column-title',
                        ),
                        array(
                            'data' => 'Passed & Confirmed',
                            'class' => 'column-title',
                        ),
                        array(
                            'data' => 'Total',
                            'class' => 'column-title',
                        ),
                        array(
                            'data' => 'Passed',
                            'class' => 'column-title',
                        ),
                        array(
                            'data' => 'Confirmed',
                            'class' => 'column-title',
                        ),
                        array(
                            'data' => 'Passed & Confirmed',
                            'class' => 'column-title',
                        ),
                        array(
                            'data' => 'Total',
                            'class' => 'column-title',
                        ),
                        array(
                            'data' => 'Passed',
                            'class' => 'column-title',
                        ),
                        array(
                            'data' => 'Confirmed',
                            'class' => 'column-title',
                        ),
                        array(
                            'data' => 'Passed & Confirmed',
                            'class' => 'column-title',
                        ),
                        array(
                            'data' => 'Total',
                            'class' => 'column-title',
                        )
                    ),
                ),
                'body'	=> array(),
            );
    
            // Loop through each of the sekected students
            foreach ($students as $student_id => $nameOptions) {
                $table_data['body'][$student_id] = array(
                    array("data" => $nameOptions['first_last_combined'], "class"=>"left noSum noAverage noMin noMax"),
                    array("data" => $this->getNumericValue($data[$student_id]['student']['passed']), "class"=>"center evenCol"),
                    array("data" => $this->getNumericValue($data[$student_id]['student']['confirmed']), "class"=>"center evenCol"),
                    array("data" => $this->getNumericValue($data[$student_id]['student']['both']), "class"=>"center evenCol"),
                    array("data" => $this->getNumericValue($data[$student_id]['student']['total']), "class"=>"center evenCol"),
                    array("data" => $this->getNumericValue($data[$student_id]['preceptor']['passed']), "class"=>"center"),
                    array("data" => $this->getNumericValue($data[$student_id]['preceptor']['confirmed']), "class"=>"center"),
                    array("data" => $this->getNumericValue($data[$student_id]['preceptor']['both']), "class"=>"center"),
                    array("data" => $this->getNumericValue($data[$student_id]['preceptor']['total']), "class"=>"center"),
                    array("data" => $this->getNumericValue($data[$student_id]['instructor']['passed']), "class"=>"center evenCol"),
                    array("data" => $this->getNumericValue($data[$student_id]['instructor']['confirmed']), "class"=>"center evenCol"),
                    array("data" => $this->getNumericValue($data[$student_id]['instructor']['both']), "class"=>"center evenCol"),
                    array("data" => $this->getNumericValue($data[$student_id]['instructor']['total']), "class"=>"center evenCol"),
                    array("data" => $this->getNumericValue($data[$student_id]['total']), "class"=>"center"),
                );
            }
    
            // add the footer to calculate total, average, max and min, but only if there's more than one row
            if (count($table_data['body']) > 1) {
                $average_footer = array(array("data" => "Average:", "class" => "right"));
                $total_footer = array(array("data" => "Total:", "class" => "right"));
                $min_footer = array(array("data" => "Minimum:", "class" => "right"));
                $max_footer = array(array("data" => "Maximum:", "class" => "right"));
    
                for ($i=0; $i<=12; $i++) {
                    $average_footer[] = array("data" => "-", "class" => "center");
                    $total_footer[] = array("data" => "-", "class" => "center");
                    $min_footer[] = array("data" => "-", "class" => "center");
                    $max_footer[] = array("data" => "-", "class" => "center");
                }
    
                $table_data['foot']["average"] = $average_footer;
                $table_data['foot']["sum"] = $total_footer;
                $table_data['foot']["min"] = $min_footer;
                $table_data['foot']["max"] = $max_footer;
            }
    
            // Add all of our data to the report
            $this->data[] = array(
                "type" => "table",
                "content" => $table_data,
            );
        }
    }
}
