<?php
/**
 * Class Fisdap_Reports_LateData
 * This is the LateData Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_LateData extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickPatientType' => false,
                                'selected' => array('sites' => array())
            ),
        ),
        'displayTypePickerComponent' => array(
            'title' => 'Select a display format',
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'name' => 'student-picklist',
            'options' =>  array(
                'mode' => 'multiple',
                'loadJSCSS' => true,
                'loadStudents' => true,
                'showTotal' => true,
                'studentVersion' => true,
                'useSessionFilters' => true,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        ),
    );

    public $scripts = array("/js/library/Fisdap/Reports/late-data.js");
    
    public $styles = array("/css/library/Fisdap/Reports/late-data.css");

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport()
    {
        $sortableByLast = true;
        
        
        // clean up the site info
        $site_ids = $this->getSiteIds();
        
        $start_date = $this->config['startDate'];
        $end_date = $this->config['endDate'];
        
        if ($this->config['reportType'] == 'detailed') {
            $student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $this->config['student']);
            $students = array($student->id => $student->user->getName());
                        
            // Run a query to get data.
            $repo = \Fisdap\EntityUtils::getRepository('StudentLegacy');
            $data = $repo->getStudentLateData(array_keys($students), $site_ids, $start_date, $end_date);
            
            $this->addDetailedTables($students, $data);
        } else {
            $students = $this->getMultiStudentData($sortableByLast);
                        
            // Run a query to get data.
            $repo = \Fisdap\EntityUtils::getRepository('StudentLegacy');
            $data = $repo->getStudentLateData(array_keys($students), $site_ids, $start_date, $end_date);
                        
            $this->addSummaryTable($students, $data);
        }
    }
    
    protected function displayTypePickerComponent($config = null)
    {
        $selected = $config['reportType'];
        
        // A custom form component for this report
        $reportType = new Fisdap_Form_Element_jQueryUIButtonset('reportType');
        $reportType->setRequired(true);
        $reportType->setOptions(array('summary' => 'Summary', 'detailed' => 'Detailed'));
        $reportType->setUiTheme("");
        $reportType->setUiSize("extra-small");
        $reportType->setDecorators(array(
                'ViewHelper',
                array(array('element' => 'HtmlTag'), array('tag' => 'div', 'class' => '')),
                array('Label', array('tag' => 'div', 'class' => 'hidden', 'escape' => false)),
                array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt')),
            ));
        
        if ($selected) {
            $reportType->setValue($selected);
        } else {
            $reportType->setValue("summary");
        }
        
        $reportType->removeDecorator('Label');
        
        return $reportType;
    }
    
    protected function displayTypePickerComponentSummary($config = null)
    {
        return array();
    }
    
    protected function addDetailedTables($students, $data)
    {
        foreach ($students as $student_id => $name) {
            // make a table
            $lateDataTable = array(
                'title' => $name . "'s Late Data Report",
                'nullMsg' => "No shifts found.",
                'head' => array(
                    '0' => array( // there's only 1 row header for this report
                        'Shift Info',
                        'Shift data entered',
                    ),
                ),
                'body' => array(),
            );
            
            $shift_summary_display_helper = new Fisdap_View_Helper_ShiftSummaryDisplayHelper();

            // get the data for the chosen student
            if ($data[$student_id]) {
                foreach ($data[$student_id] as $id => $lateData_info) {
                    if ($lateData_info['late'] == 1) {
                        $lateData_info['late'] = 'Late';
                    } elseif ($lateData_info['late'] == 0) {
                        $lateData_info['late'] = 'On time';
                    }

                    $summary_options = array('display_size' => 'large', 'sortable' => true);
                    $shift_info = $shift_summary_display_helper->shiftSummaryDisplayHelper(null, $lateData_info['shift_id'], null, $summary_options);

                    // add the row
                    $lateDataTable['body'][$id] = array(
                        array(
                            'data' => $shift_info,
                            'class' => 'shift_info',
                        ),
                                    array(
                                        'data' => $lateData_info['late'],
                            'class' => 'data_entered',
                    )
                    );
                }
            }
            
            // add the table to this report
            $this->data[$student_id] = array("type" => "table",
                                             "content" => $lateDataTable);
        }
    }
    
    protected function addSummaryTable($students, $data)
    {
        // make the table
        $lateDataTable = array(
            'title' => "Late Data Summary",
            'nullMsg' => "No shifts found.",
            'head' => array(
                '0' => array( // there's only 1 row header for this report
                    'Student',
                    'Late data',
                    'On time data',
                            'Total',
                ),
            ),
            'body' => array(),
        );
        
        foreach ($students as $student_id => $nameOptions) {
            // get the data for the chosen student
            if ($data[$student_id]) {
                $tallies = array();
                foreach ($data[$student_id] as $id => $lateData_info) {
                    if ($lateData_info['late']==1) {
                        $lateData_info['late'] = "Late";
                    } elseif ($lateData_info['late']==0) {
                        $lateData_info['late'] = "On time";
                    }
                    $tallies[$lateData_info['late']]++;
                    $tallies['total']++;
                }
                
                // add the row
                $lateDataTable['body'][$student_id] = array(
                    array(
                        'data' => $nameOptions['first_last_combined'],
                        'class' => 'noSum noAverage',
                    ),
                    array(
                        'data' => $tallies['Late'] ? $tallies['Late'] : 0,
                        'class' => 'center',
                    ),
                    array(
                        'data' => $tallies['On time'] ? $tallies['On time'] : 0,
                        'class' => 'center',
                    ),
                    array(
                        'data' => $tallies['total'] ? $tallies['total'] : 0,
                        'class' => 'center',
                    ),
                );
            }
        }
        
        // add the footer to calculate totals, but only if there's more than one row
        if (count($lateDataTable['body']) > 1) {
            $average = array(array("data" => "Average:", "class" => "right"));
            $sum = array(array("data" => "Total:", "class" => "right"));
            
            for ($i = 1; $i <= 3; $i++) {
                $average[] = array("data" => "-", "class" => "center");
                $sum[] = array("data" => "-", "class" => "center");
            }
                        
                        
            $lateDataTable['foot']["average"] = $average;
            $lateDataTable['foot']["sum"] = $sum;
        }
            
        // add the table to this report
        $this->data[] = array("type" => "table",
                              "content" => $lateDataTable);
    }
}
