<?php

/**
 * Class Fisdap_Reports_RadHours
 * This is the RADT Accreditation Hours Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_RadHours extends Fisdap_Reports_Report
{
    const ABSENT = 3;
    const ABSENT_WITH_PERMISSION = 4;

    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickPatientType' => false,
                'selected' => array('sites' => array('0-Clinical'))
            ),
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' => array(
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

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport()
    {
        // Get the students back for this report...
        $sortableByLast = true;
        $students = $this->getMultiStudentData($sortableByLast);

        // perform query to get shift data
        // get the repo
        $repo = \Fisdap\EntityUtils::getRepository('ShiftLegacy');

        // get the filters based on the report config
        $filter = $this->getShiftFilter();

        // Select fields that should be returned
        $fields = array(
            'sh' => array('hours', 'start_datetime', 'end_datetime'),
            'attend' => array('id' => 'attendence_id'),
            'st' => array('id' => 'student_id')
        );

        // execute query
        $shifts = $repo->getShiftsFields($filter, $fields);

        // format the data
        // we'll need the business hours calculator to help us calculate off hours
        $startOfBusiness = array("hour" => 5); // 0500
        $endOfBusiness = array("hour" => 19); // 1900
        $businessHoursCalculator = new Util_BusinessHoursCalculator($startOfBusiness, $endOfBusiness);

        foreach ($shifts as $shift) {
            // if the student wasn't absent for this shift, add the hours to the correct student
            if ($shift['attendence_id'] != self::ABSENT && $shift['attendence_id'] != self::ABSENT_WITH_PERMISSION) {
                $students[$shift['student_id']]['Hours'] += $shift['hours'];
                $students[$shift['student_id']]['OffHours'] += $businessHoursCalculator->calculateOffHours($shift['start_datetime'], $shift['end_datetime']);
            }
        }

        // make a table
        $reportTable = array(
            'head' => array(
                '0' => array( // there's only 1 row header for this report
                    'Student',
                    'Clinical Hours',
                    'Off-Hours',
                    'Off-Hours %'
                ),
            ),
            'body' => array(),
        );

        // create a row for each student
        foreach ($students as $studentId => $student) {
            $tableRow = array();

            $percentOff = ($student['Hours'] > 0) ? round($student['OffHours'] / $student['Hours'], 3) * 100 : 0;

            // highlight students who have met their hours goals with the acceptable percentage of off hours
            $hoursGoal = 1320;
            $maxPercentOff = 25;
            if ($student['Hours'] >= $hoursGoal && $percentOff < $maxPercentOff) {
                $completed = "completed";
            } else {
                $completed = "";
            }

            $tableRow[] = array("data" => $student['first_last_combined'], "class" => "noAverage $completed");
            $tableRow[] = array("data" => ($student['Hours']) ? $student['Hours'] : 0, "class" => "center $completed");
            $tableRow[] = array("data" => ($student['OffHours']) ? $student['OffHours'] : 0, "class" => "center $completed");
            $tableRow[] = array("data" => $percentOff, "class" => "center $completed");

            $reportTable['body'][] = $tableRow;
        }

        // add the footer to calculate totals, but only if there's more than one row
        if (count($reportTable['body']) > 1) {
            $average = array();
            $average[] = array("data" => "Average:", "class" => "right");
            $average[] = array("data" => "-", "class" => "center");
            $average[] = array("data" => "-", "class" => "center");
            $average[] = array("data" => "-", "class" => "center");

            $reportTable['foot']["average"] = $average;
        }

        // add this table to this report
        $this->data[] = array("type" => "table", "content" => $reportTable);
    }
}
