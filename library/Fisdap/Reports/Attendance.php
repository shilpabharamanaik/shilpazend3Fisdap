<?php
/**
 * Class Fisdap_Reports_Attendance
 * This is the Attendance Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_Attendance extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickPatientType' => false,
                                'selected' => array('sites' => array(), 'endDate' => 'today',)
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

    public $scripts = array("/js/library/Fisdap/Reports/attendance.js");
    
    public $styles = array("/css/library/Fisdap/Reports/attendance.css");

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
            $data = $repo->getStudentAttendanceData(array_keys($students), $site_ids, $start_date, $end_date);
                        
            $this->addDetailedTables($students, $data);
        } else {
            $students = $this->getMultiStudentData($sortableByLast);
                        
            // Run a query to get data.
            $repo = \Fisdap\EntityUtils::getRepository('StudentLegacy');
            $data = $repo->getStudentAttendanceData(array_keys($students), $site_ids, $start_date, $end_date);
                        
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
            $attendanceTable = array(
                'title' => $name . "'s Attendance Report",
                'nullMsg' => "No shifts found.",
                'head' => array(
                    '0' => array( // there's only 1 row header for this report
                        'Shift Info',
                        'Attendance',
                        'Comments',
                    ),
                ),
                'body' => array(),
            );
            
            $shift_summary_display_helper = new Fisdap_View_Helper_ShiftSummaryDisplayHelper();

            // get the data for the chosen student
            if ($data[$student_id]) {
                foreach ($data[$student_id] as $id => $attendance_info) {
                    $summary_options = array('display_size' => 'large', 'sortable' => true);
                    $shift_info = $shift_summary_display_helper->shiftSummaryDisplayHelper(null, $attendance_info['shift_id'], null, $summary_options);
                    // add the row
                    $attendanceTable['body'][$id] = array(
                        array(
                            'data' => $shift_info,
                            'class' => 'shift_info',
                        ),
                                    array(
                                        'data' => $attendance_info['attendance'],
                            'class' => 'attendance',
                    ),
                                    array(
                                        'data' => $attendance_info['comments'],
                            'class' => 'comments',
                    )
                    );
                }
            }
            
            // add the table to this report
            $this->data[$student_id] = array("type" => "table",
                                             "content" => $attendanceTable);
        }
    }
    
    protected function addSummaryTable($students, $data)
    {
        // make the table
        $attendanceTable = array(
            'title' => "Attendance Summary",
            'nullMsg' => "No shifts found.",
            'head' => array(
                '0' => array( // there's only 1 row header for this report
                    'Student',
                    'Tardy',
                    'Absent',
                    'Absent w/ permission',
                    'On time',
                    'Total',
                ),
            ),
            'body' => array(),
        );
        
        foreach ($students as $student_id => $nameOptions) {
            // get the data for the chosen student
            if ($data[$student_id]) {
                $tallies = array();
                foreach ($data[$student_id] as $id => $attendance_info) {
                    $tallies[$attendance_info['attendance']]++;
                    $tallies['total']++;
                }
                
                // add the row
                $attendanceTable['body'][$student_id] = array(
                    array(
                        'data' => $nameOptions['first_last_combined'],
                        'class' => 'noSum noAverage',
                    ),
                    array(
                        'data' => $tallies['Tardy'] ? $tallies['Tardy'] : 0,
                        'class' => 'center',
                    ),
                    array(
                        'data' => $tallies['Absent'] ? $tallies['Absent'] : 0,
                        'class' => 'center',
                    ),
                    array(
                        'data' => $tallies['Absent with Permission'] ? $tallies['Absent with Permission'] : 0,
                        'class' => 'center',
                    ),
                    array(
                        'data' => $tallies['On Time'] ? $tallies['On Time'] : 0,
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
        if (count($attendanceTable['body']) > 1) {
            $average = array(array("data" => "Average:", "class" => "right"));
            $sum = array(array("data" => "Total:", "class" => "right"));
            
            for ($i = 1; $i <= 5; $i++) {
                $average[] = array("data" => "-", "class" => "center");
                $sum[] = array("data" => "-", "class" => "center");
            }

            $attendanceTable['foot']["average"] = $average;
            $attendanceTable['foot']["sum"] = $sum;
        }
            
        // add the table to this report
        $this->data[] = array("type" => "table",
                              "content" => $attendanceTable);
    }
}
