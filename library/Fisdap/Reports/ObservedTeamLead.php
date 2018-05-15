<?php
/**
 * Class Fisdap_Reports_ObservedTeamLead
 * This is the Observed Team Leads Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_ObservedTeamLead extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickPatientType' => false,
                'selected' => array('sites' => array(), // none == all
                ),
            ),
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one student',
            'options' =>  array(
                'mode' => 'single',
                'loadJSCSS' => true,
                'loadStudents' => true,
                'showTotal' => true,
                'studentVersion' => true,
                'useSessionFilters' => true,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        ),
    );

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport()
    {
        $sorted_sites = $this->getSiteIds(true);
        
        $filters = array();
        $filters['startDate'] = $this->config['startDate'];
        $filters['endDate'] = $this->config['endDate'];
        
        $student_id = $this->config['student'];
        $repo = \Fisdap\EntityUtils::getRepository("Run");
        $shift_summary_display_helper = new Fisdap_View_Helper_ShiftSummaryDisplayHelper();
        
        // Run a query to get data.
        $data = array();
        $types = array("field" => "Field Runs", "clinical" => "Clinical Assessments", "lab" => "Lab Scenarios");
        
        foreach ($types as $type => $title) {
            // see if we selected any sites of this type
            $site_ids = $sorted_sites[$type];
            
            if (count($site_ids) > 0) {
                $filters['sites'] = $site_ids;
                
                // make the table
                $table = array(
                    'title' => $title,
                    'nullMsg' => "No ".strtolower($title)." found.",
                    'head' => array(
                        '001' => array( // second row headers
                            array(
                                'data' => 'Shift Info',
                                'class' => 'column-title',
                            ),
                            array(
                                'data' => 'Patient',
                                'class' => 'column-title',
                            ),
                            array(
                                'data' => 'Team Lead',
                                'class' => 'column-title',
                            ),
                            array(
                                'data' => 'Exam',
                                'class' => 'column-title',
                            ),
                            array(
                                'data' => 'Interview',
                                'class' => 'column-title',
                            ),
                        ),
                    ),
                    'body' => array(),
                );
            
                //Take out the column heading for Team Lead for clinical patients
                if ($type == "clinical") {
                    unset($table["head"]["001"][2]);
                }
                
                $data = $repo->getPatientsNotTeamLead($student_id, $type, $filters);
                foreach ($data as $i => $patient) {
                    // get the shift data necessary to use the view helper
                    $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $patient['shift_id']);
                    $summary_options = array('display_size' => 'large', 'sortable' => true);
                    $shift_info = $shift_summary_display_helper->shiftSummaryDisplayHelper(null, null, $shift, $summary_options);
                    
                    $patient_info = "<a href='/skills-tracker/patients/index/runId/" . $patient['run_id'] . "' title='Go to this patient'>";
                    if ($patient['age']) {
                        $patient_info .= $patient['age'] . " yo, ";
                    }
                    if ($patient['ethnicity'] != "Unspecified") {
                        $patient_info .= $patient['ethnicity'] . " ";
                    }
                    $patient_info .= $patient['gender'];
                    $patient_info .= "</a>";
                    
                    // add this row to the table
                    $table['body'][$i] = array(
                        array(
                            'data' => $shift_info,
                            'class' => 'shift_info',
                        ),
                        array(
                            'data' => $patient_info,
                            'class' => 'center',
                        ),
                        array(
                            'data' => $patient['team_lead'] ? "performed" : "observed",
                            'class' => 'center',
                        ),
                        array(
                            'data' => $patient['exam'] ? "performed" : "observed",
                            'class' => 'center',
                        ),
                        array(
                            'data' => $patient['interview'] ? "performed" : "observed",
                            'class' => 'center',
                        ),
                    );
                    
                    //Take out the column data for Team Lead for clinical patients
                    if ($type == "clinical") {
                        unset($table["body"][$i][2]);
                    }
                }
                
                $this->data[$type] = array("type" => "table",
                                           "content" => $table);
            }
        }
    }
}
