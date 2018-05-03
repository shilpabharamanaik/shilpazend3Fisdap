<?php
/**
 * Class Fisdap_Reports_PatientAcuity
 * This is the PatientAcuity Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_PatientAcuity extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickPatientType' => FALSE,
                'selected' => array('sites' => array())
            ),
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'name' => 'student-picklist',
            'options' =>  array(
                'mode' => 'multiple',
                'loadJSCSS' => TRUE,
                'loadStudents' => TRUE,
                'showTotal' => TRUE,
                'studentVersion' => TRUE,
                'useSessionFilters' => TRUE,
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
    public function runReport() {
        $sortableByLast = true;


        // clean up the site info
        $site_ids = $this->getSiteIds();

        $start_date = $this->config['startDate'];
        $end_date = $this->config['endDate'];

            $students = $this->getMultiStudentData($sortableByLast);

            // Run a query to get data.
            $repo = \Fisdap\EntityUtils::getRepository('StudentLegacy');
            $data = $repo->getStudentPatientAcuityData(array_keys($students), $site_ids, $start_date, $end_date);

            $this->addSummaryTable($students, $data);
    }


    protected function addSummaryTable($students, $data) {
        // make the table
        $patientAcuityTable = array(
            'title' => "Patient Acuity Summary",
            'nullMsg' => "No shifts found.",
            'head' => array(
                '0' => array( // there's only 1 row header for this report
                    'Student',
                    'Green',
                    'Yellow',
                    'Red',
                    'Black',
                    'None',
                    'Total',
                ),
            ),
            'body' => array(),
        );

        foreach($students as $student_id => $nameOptions) {
            // get the data for the chosen student
            if ($data[$student_id]) {
                $tallies = array();
                foreach ($data[$student_id] as $id => $acuity_info) {
                    $tallies['Green'] = $tallies['Green'] + $acuity_info['green'];
                    $tallies['Yellow'] = $tallies['Yellow'] + $acuity_info['yellow'];
                    $tallies['Red'] = $tallies['Red'] + $acuity_info['red'];
                    $tallies['Black'] = $tallies['Black'] + $acuity_info['black'];
                    $tallies['none'] = $tallies['none'] + $acuity_info['none'];
                    $tallies['total'] = $tallies['total'] + $acuity_info['green'] + $acuity_info['yellow'] + $acuity_info['red'] + $acuity_info['black'] + $acuity_info['none'];
                }

                // add the row
                $patientAcuityTable['body'][$student_id] = array(
                    array(
                        'data' => $nameOptions['first_last_combined'],
                        'class' => 'noSum noAverage',
                    ),
                    array(
                        'data' => $tallies['Green'] ? $tallies['Green'] : 0,
                        'class' => 'center',
                    ),
                    array(
                        'data' => $tallies['Yellow'] ? $tallies['Yellow'] : 0,
                        'class' => 'center',
                    ),
                    array(
                        'data' => $tallies['Red'] ? $tallies['Red'] : 0,
                        'class' => 'center',
                    ),
                    array(
                        'data' => $tallies['Black'] ? $tallies['Black'] : 0,
                        'class' => 'center',
                    ),
                    array(
                        'data' => $tallies['none'] ? $tallies['none'] : 0,
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
        if (count($patientAcuityTable['body']) > 1) {
            $average = array(array("data" => "Average:", "class" => "right"));
            $sum = array(array("data" => "Total:", "class" => "right"));

            for ($i = 2; $i <= 7; $i++) {
                $average[] = array("data" => "-", "class" => "center");
                $sum[] = array("data" => "-", "class" => "center");
            }


            $patientAcuityTable['foot']["average"] = $average;
            $patientAcuityTable['foot']["sum"] = $sum;
        }

        // add the table to this report
        $this->data[] = array("type" => "table",
            "content" => $patientAcuityTable);
    }
}