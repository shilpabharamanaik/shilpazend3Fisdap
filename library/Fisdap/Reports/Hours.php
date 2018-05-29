<?php

/**
 * Class Fisdap_Reports_Hours
 * The Hours report!
 * @author jmortenson
 */
class Fisdap_Reports_Hours extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickPatientType' => FALSE,
                'selected' => array('sites' => array('0-Clinical', '0-Field'))
            ),
        ),
        'checkmarkTableGeneric' => array(
            'title' => 'Select a Display Format',
            'options' => array(
                'fieldName' => 'display_format',
                'summaryLabel' => 'Display options',
                'rows' => array(
                    array(
                        'value' => 'site-type',
                        'content' => array('By Site Type <span class="checkmark-table-description">(combined hours for each type: Field/Clinical/Lab)</span>'),
                    ),
                    array(
                        'value' => 'site',
                        'content' => array('By Site <span class="checkmark-table-description">(hours for each site)</span>'),
                    ),
                    array(
                        'value' => 'site-and-department',
                        'content' => array('By Site and Department/Base <span class="checkmark-table-description">(hours for each base at each site)</span>'),
                    ),
                    array(
                        'value' => 'standard-departments',
                        'content' => array('By Standard Departments <span class="checkmark-table-description">(combined hours for each standard clinical department: Anesthesia, ER, etc.)</span>'),
                    ),
                ),
                'selected' => 'site-and-department'
            ),
        ),
        'Fisdap_Reports_Form_Hours' => array(
            'title' => 'Select Hours Type(s)',
            'options' => array(),
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' => array(
                'loadJSCSS' => TRUE,
                'loadStudents' => TRUE,
                'showTotal' => TRUE,
                'studentVersion' => TRUE,
                'includeAnon' => TRUE,
                'useSessionFilters' => TRUE,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        ),
    );

    /**
     * Master columns used to format table output, in the preferred order
     */
    private $masterColumns = array(
        'scheduled' => 'Scheduled',
        'locked' => 'Locked',
        'audited' => 'Audited',
    );

    /**
     * Metadata about students, bases and sites that we collect out of the query that was run
     * Here so we can reference things like student names, base names, etc. without extra DB trips
     */
    private $meta = array();

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport()
    {
        // alter the masterColumns if one or more should be hidden
        foreach ($this->masterColumns as $key => $label) {
            // if the config doesn't have hours_$key set (ie hours_audited), then the checkbox has been unchecked
            // and we should not display that type
            if (!$this->config['hours_' . $key]) {
                unset($this->masterColumns[$key]);
            }
        }

        // perform query to get shift data
        // get the repo
        $repo = \Fisdap\EntityUtils::getRepository('ShiftLegacy');

        // get the filters based on the report config
        $filter = $this->getShiftFilter();

        // Select fields that should be returned
        $fields = array(
            'sh' => array('hours', 'audited', 'type', 'locked'),
            'attend' => array('id' => 'attendence_id'),
            'base' => array('id' => 'base_id', 'name' => 'base_name'),
            'site' => array('id' => 'site_id', 'name' => 'site_name'),
            'st' => array('id' => 'student_id', 'first_name', 'last_name'), // @todo this needs to be different
        );

        // execute query
        $shifts = $repo->getShiftsFields($filter, $fields);

        // get default bases for Standard Departments report type
        if ($this->config['display_format'] == 'standard-departments') {
            $baseRepo = \Fisdap\EntityUtils::getRepository('BaseLegacy');
        }

        // collect shift info into properly-structured $data array
        $data = array();
        $this->meta['students'] = $this->getMultiStudentData();
        foreach ($shifts as $shift) {
            // collect metadata
            $this->meta['bases'][$shift['base_id']] = $shift['base_name'];
            $this->meta['sites'][$shift['site_id']] = $shift['site_name'];

            /*
             * Tables for report as are follows:
             *  - Type: by student (student_id) by shift type (type)
             *  - Site: by student (student_id) by site (site_name)
             *  - Site & Dept: by student (student_id) by site (site_name) by base (base_name)
             *  - Std Depts: by student (student_id) by certain bases (base_name)
             */
            // add totals
            $baseKey = $shift['student_id'];
            switch ($this->config['display_format']) {
                case 'site-type':
                    $this->addShiftHoursToTotals($shift, $data[$baseKey][$shift['type']]); // add to student shift type totals
                    $this->addShiftHoursToTotals($shift, $data['total'][$shift['type']]); // add to global shift type totals
                    break;
                case 'site':
                    $this->addShiftHoursToTotals($shift, $data[$baseKey][$shift['site_id']]); // add to student site totals
                    $this->addShiftHoursToTotals($shift, $data['total'][$shift['site_id']]); // add to global site totals
                    break;
                case 'site-and-department':
                    $this->addShiftHoursToTotals($shift, $data[$baseKey][$shift['site_id']][$shift['base_id']]); // add to student site/base totals
                    $this->addShiftHoursToTotals($shift, $data['total'][$shift['site_id']][$shift['base_id']]); // add to global site/base totals
                    break;
                case 'standard-departments':
                    // only add to totals if this base is in the standard bases/departments list
                    if ($baseRepo->isDefault($shift['base_name'])) {
                        $this->addShiftHoursToTotals($shift, $data[$baseKey][$shift['base_name']]); // add to student base totals
                        $this->addShiftHoursToTotals($shift, $data['total'][$shift['base_name']]); // add to global base totals
                    }
                    break;
                default:
                    throw new Exception('An invalid (or missing) display format was submitted to the Hours Report.');

                    break;
            }
        }


        // setup proper columns/callbacks based on the display_format chosen
        switch ($this->config['display_format']) {
            case 'site-type':
                $bodyFormatCallback = 'formatBodySiteType';
                $headerCols = array('Type');
                break;
            case 'site':
                $bodyFormatCallback = 'formatBodySite';
                $headerCols = array('Site');
                break;
            case 'site-and-department':
                $bodyFormatCallback = 'formatBodySiteAndDepartment';
                $headerCols = array('Site', 'Base');
                break;
            case 'standard-departments':
                $bodyFormatCallback = 'formatBodyStandardDepartments';
                $headerCols = array('Standard Departments');
                break;
            default:
                throw new Exception('An invalid (or missing) display format was submitted to the Hours Report.');
                break;
        }

        // add master columns to the header columns
        $head = array($headerCols + $this->masterColumns); // single header row

        // set up data table structure
        // output global totals first
        $globalTotalsTable = array(
            "type" => "table",
            "content" => array(
                'title' => "All Student Hours",
                'head' => $head,
                'nullMsg' => "No hours found for these students.",
                'body' => (is_array($data['total'])) ? $this->{$bodyFormatCallback}($data['total']) : array(),
            ),
        );
        // if there is more than one row in the totals body, add a "totals" dynamic footer
        // uses sumFootRow() in display.js to calculate
        if (count($globalTotalsTable['content']['body']) > 1) {

            $globalTotalsTable['content']['foot']["sum"] = $this->addDynamicTotalsFooter((count($headerCols) - 1), count($this->masterColumns));
        }
        $this->data = array('totals' => $globalTotalsTable);


        // now output tables for each student
        // check for any students that are not represented in the results array (they have no data, but we still want to display them)
        foreach ($filter['studentIds'] as $id) {
            if (!isset($data[$id])) {
                // insert the student into the array, so they can be sorted and displayed with the rest
                $data[$id] = NULL;
            }
        }

        // Add a table for each student
        foreach ($this->meta['students'] as $studentId => $nameOptions) {
            // omit the global totals from this foreach (that special case is handled above)
            // process the student if there is data for him/her in $data, otherwise just return an empty table
            if ($studentId != 'total' && isset($data[$studentId]) && is_array($data[$studentId])) {
                $studentTable = array(
                    'type' => 'table',
                    'content' => array(
                        'title' => $nameOptions['first_last_combined'],
                        'nullMsg' => "No hours found for this student.",
                        'head' => $head,
                        'body' => $this->{$bodyFormatCallback}($data[$studentId]),
                    ),
                );

                // if there is more than one student, add a "totals" dynamic footer
                // uses sumFootRow() in display.js to calculate
                if (count($studentTable['content']['body']) > 1) {
                    $studentTable['content']['foot']["sum"] = $this->addDynamicTotalsFooter((count($headerCols) - 1), count($this->masterColumns));
                }

                $this->data[$studentId] = $studentTable;
            } else if (!isset($data[$studentId]) || !is_array($data[$studentId])) {
                // no data for this student, so just returning an empty table
                $studentTable = array(
                    'type' => 'table',
                    'content' => array(
                        'title' => $nameOptions['first_last_combined'],
                        'nullMsg' => "No hours found for this student.",
                        'head' => $head,
                        'body' => array(),
                    )
                );

                $this->data[$studentId] = $studentTable;
            }
        }
    }

    /**
     * Return a custom short label/description of the Hours report
     * Overrides parent method
     */
    public function getShortConfigLabel()
    {
        // generate the form summary
        $this->getSummary('div');

        // return the label
        return $this->summaryParts['Student(s)'] . ": " . $this->summaryParts['Showing hour type(s)'] . "; " . $this->summaryParts['Date range'];
    }


    /**
     * Utility method used when iterating over shift data to tally total hours
     * using the standard categories (scheduled, locked, audited)
     */
    private function addShiftHoursToTotals(&$shift, &$totals)
    {
        $totals['scheduled'] += $shift['hours'];

        // add to "locked" / "completed" hours
        if ($shift['locked'] && ($shift['attendence_id'] != 3 && $shift['attendence_id'] != 4)) {
            $totals['locked'] += $shift['hours'];
        }

        // add to "audited" hours
        if ($shift['audited']) {
            $totals['audited'] += $shift['hours'];
        }
    }

    /**
     * Utility method to add a dynamic Totals row to the footer of a table
     */
    private function addDynamicTotalsFooter($numPrefxCols, $numSumCols)
    {
        $footer = array();
        // add empty padding cells if there are more than one prefix columns
        for ($i = 0; $i < $numPrefxCols; $i++) {
            $footer[] = array("data" => "");
        }
        // add the "Totals" label
        $footer[] = array("data" => "Totals:", "class" => "right");
        // add placeholders for each of the numeric sum columns
        // to be dynamically filled in later
        for ($i = 0; $i < $numSumCols; $i++) {
            $footer[] = array("data" => "");
        }
        return $footer;
    }

    /**
     * Utility method to format table using display_type = site-type (Type)
     */
    private function formatBodySiteType($data)
    {
        $output = array();
        $rowsInOrder = array(
            'lab' => 'Lab',
            'clinical' => 'Clinical',
            'field' => 'Field',
        );

        foreach ($rowsInOrder as $rowKey => $rowLabel) {
            $row = array();
            $row[] = array("data" => $rowLabel, "class" => "noSum");
            foreach ($this->masterColumns as $columnKey => $columnLabel) {
                if (isset($data[$rowKey]) && isset($data[$rowKey][$columnKey])) {
                    $row[] = $this->roundHours($data[$rowKey][$columnKey]);
                } else {
                    $row[] = 0; // default to outputting zero
                }
            }

            $output[] = $row;
        }

        return $output;
    }


    /**
     * Utility method to format table using display_type = site (Site)
     */
    private function formatBodySite($data)
    {
        $output = array();

        // Sort the rows by site name
        uksort($data, array($this, 'sortSiteIds'));

        // Create output rows
        foreach ($data as $siteId => $siteData) {
            $row = array();
            if ($siteId != 'total') { // special case for the totals
                $row[] = array("data" => $this->meta['sites'][$siteId], "class" => "noSum");
                foreach ($this->masterColumns as $key => $label) {
                    if (isset($siteData[$key])) {
                        $row[] = $this->roundHours($siteData[$key]);
                    } else {
                        $row[] = 0; // default ot outputting zero
                    }
                }

                $output[] = $row;
            }
        }

        return $output;
    }


    /**
     * Utility method to format table using display_type = site-and-department (Site and Base)
     */
    private function formatBodySiteAndDepartment($data)
    {
        $output = array();

        // Sort the rows by site name
        uksort($data, array($this, 'sortSiteIds'));

        // Create output rows
        foreach ($data as $siteId => $siteData) {
            if ($siteId != 'total') { // special case for the site totals
                // each site has a set of bases, iterate through bases
                foreach ($siteData as $baseId => $baseData) {
                    // sort the bases by base name
                    uksort($baseData, array($this, 'sortBaseIds'));

                    // row output starts with both site and base names
                    $row = array();
                    $row[] = array("data" => $this->meta['sites'][$siteId], "class" => "noSum");
                    $row[] = array("data" => $this->meta['bases'][$baseId], "class" => "noSum");
                    foreach ($this->masterColumns as $key => $label) {
                        if (isset($baseData[$key])) {
                            $row[] = $this->roundHours($baseData[$key]);
                        } else {
                            $row[] = 0; // default ot outputting zero
                        }
                    }

                    $output[] = $row;
                }
            }
        }

        return $output;
    }


    /**
     * Utility method to format table using display_type = standard-departments (Standard Departments/Bases)
     */
    private function formatBodyStandardDepartments($data)
    {
        $output = array();

        // Sort the rows by base name
        uksort($data, array($this, 'sortBaseIds'));

        // Create output rows
        foreach ($data as $baseName => $baseData) {
            $row = array(array("data" => $baseName, "class" => "noSum"));
            foreach ($this->masterColumns as $key => $label) {
                if (isset($baseData[$key])) {
                    $row[] = $this->roundHours($baseData[$key]);
                } else {
                    $row[] = 0; // default ot outputting zero
                }
            }
                $output[] = $row;
        }

        return $output;
    }


    /**
     * Sort array of Site IDs by site name
     */
    private function sortSiteIds($a, $b)
    {
        return strnatcmp($this->meta['sites'][$a], $this->meta['sites'][$b]);
    }

    /**
     * Sort array of Base IDs by base name
     */
    private function sortBaseIds($a, $b)
    {
        return strnatcmp($this->meta['bases'][$a], $this->meta['bases'][$b]);
    }

    /**
     * Utility method to round an hours number in the desired format
     */
    private function roundHours($hours)
    {
        return round($hours, 2);
    }
}