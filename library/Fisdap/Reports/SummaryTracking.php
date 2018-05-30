<?php
/**
 * Class Fisdap_Reports_SummaryTracking
 * This is the Summary Tracking Report class
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_SummaryTracking extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'goalSetTable' => array(
            'title' => 'Select a goal set',
        ),
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickAuditStatus' => true
            )
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' =>  array(
                'mode' => 'multiple',
                'loadJSCSS' => true,
                'loadStudents' => true,
                'showTotal' => true,
                'studentVersion' => true,
                'includeAnon' => true,
                'useSessionFilters' => true,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        )
    );

    private $siteTypes = array();

    public function runReport()
    {
        // Set the mysql timeout higher
        \Fisdap\EntityUtils::getEntityManager()->getConnection()->exec("SET SESSION wait_timeout = 100000");
        \Zend_Registry::get('db')->query("SET SESSION wait_timeout = 100000");
        $logger = \Zend_Registry::get('logger');


        // get the form values situated
        $shiftSites = $this->getSiteIds(true);
        ksort($shiftSites);
        $this->siteTypes = array_keys($shiftSites);
        if (count($this->siteTypes) > 1) {
            $this->siteTypes[] = 'total';
        }

        $requestedSites = $this->getSiteIds(true);

        $dataOptions['startDate'] = ($this->config['startDate']) ? new \DateTime($this->config['startDate']) : "";
        $dataOptions['endDate'] =  ($this->config['endDate']) ? new \DateTime($this->config['endDate']) : "";
        $dataOptions['subjectTypes'] = $this->getTypeIds();
        $dataOptions['shiftSites'] = $this->getSiteIds(true);
        $dataOptions['auditedOrAll'] = ($this->config['auditStatus'] == 'audited') ? 1 : 0;

        // get the goalset
        $goalSetId = $this->config['selected-goalset'];
        $goalSet = \Fisdap\EntityUtils::getRepository('GoalSet')->getGoalsForGoalSet($goalSetId);

        // loop through the students getting the data, separate out the data by type

        $sortableByLast = true;
        $students = $this->getMultiStudentData($sortableByLast);
        $goalsResults = array();
        $goalCategories = array();

        foreach ($this->siteTypes as $siteType) {
            if ($siteType == 'total') {
                $dataOptions['shiftSites'] = $this->getSiteIds();
            } else {
                $dataOptions['shiftSites'] = $requestedSites[$siteType];
            }
            foreach ($students as $student_id => $nameOptions) {
                if ($student_id > 0) { // add student only if student_id is valid
                    $goals = new \Fisdap\Goals($student_id, $goalSet, $dataOptions, $nameOptions['first_last_combined']);
                    $goalsResults[$student_id][$siteType] = $goals->getGoalsResults(null, false);
                    if (empty($goalCategories)) {
                        $anyStudentsResults = $goalsResults[$student_id][$siteType];
                        $goalCategories = array_unique(array_keys($goalsResults[$student_id][$siteType]));
                    }
                    unset($goals);
                }
            }
        }


        $contentType = 'tabbed-tables';

        // Sort the categories so they appear in the correct order (alphabetically)...
        sort($goalCategories);

        // add data tables
        foreach ($goalCategories as $goalCategory) {
            $logger->debug($goalCategory);
            if ($goalCategory == "Team Lead") {
                continue;
            }
            foreach ($this->siteTypes as $siteType) {
                $dataTables[$goalCategory . '_' . $siteType] = $this->getDataTable($goalCategory, $siteType, $goalsResults, $anyStudentsResults, $students);
            }
            $this->data[$goalCategory] = array("type" => $contentType, "content" => $dataTables);
            $this->addPageBreak();
            unset($dataTables);
        }
    }

    /**
     * processes the chosen results from the sites filter to return a clean array of site ids
     */
    public function getSiteIds($sortedByType = false)
    {
        $chosen = $this->config['sites_filters'];

        // get the data from the repo
        $site_ids = \Fisdap\EntityUtils::getRepository('SiteLegacy')->parseSelectedSites($chosen, $sortedByType);
        return $site_ids;
    }


    private function getDataTable($goalCategory, $type, $goalsResults, $anyStudentsResults, $students)
    {
        $title = $goalCategory;

        // set up the table structure
        $superHeaderRow = array(array("data" => "Student",
            "class" => "superheader",
            "rowspan" => 2));
        $subHeaderRow = array();
        foreach ($anyStudentsResults[$goalCategory] as $i => $goalResult) {
            $catName = $goalResult->goal->def->name;

            $superHeader = "$catName <br/>goal: " . $goalResult->requirementDesc;
            $subHeader = "<span class='subheader'>goal: " . $goalResult->requirementDesc . "</span>";


            $superHeaderRow[] = array("data" => $superHeader, "rowspan" => 2);
            //$subHeaderRow[] = array("data" => $subHeader, "rowspan" => 2);
        }

        $superHeaderRow[] = array("data" => "Overall %",
            "class" => "superheader",
            "rowspan" => 2);

        // set up the table
        $table_data = array('title' => $title,
            'tab' => ucfirst($type),
            'nullMsg' => "No skills found.",
            'head' => array(),
            'body' => array(),
        );

        if (!empty($superHeaderRow)) {
            $table_data['head'][] = $superHeaderRow;
        }

        if (!empty($subHeaderRow)) {
            $table_data['head'][] = $subHeaderRow;
        } else {
            foreach ($table_data['head'][0] as $i => $headerRow) {
                $table_data['head'][0][$i]["rowspan"] = 1;
                //$headerRow['rowspan'] = 1;
            }
        }

        //fill the table with data
        foreach ($goalsResults as $student_id => $goalData) {

            //we only want data for the 'type' we specified
            //$student_id = key($goalResults);
            $studentGoalResult = $goalData[$type];

            $logger = \Zend_Registry::get('logger');
            $logger->debug($student_id);


            // add the student's name
            $dataRow = array(array("data" => $students[$student_id]['first_last_combined'],
                "class" => "noAverage"));

            // start a running total for this goal category
            $totalRequired = 0;
            $totalEarned = 0;

            // add each section of this category
            $sectionNum = 1;
            foreach ($studentGoalResult[$goalCategory] as $goalResult) {
                // add these skills to the running total
                $totalRequired += $goalResult->goal->number_required;
                $totalEarned += $goalResult->pointsTowardGoal;
                $goalMet = $goalResult->met(true) ? "completed" : "";
                $colClass = $sectionNum % 2 ? "evenCol" : "";

                $observed = $goalResult->observedCountDesc;
                $performed = $goalResult->performedCountDesc;

                $count = $observed + $performed;

                $dataRow[] = array("data" => $count, "class" => "center $goalMet $colClass");

                $sectionNum++;
            }

            // add the overall percentage
            $colClass = $sectionNum % 2 ? "evenCol" : "";
            $overall = ($totalRequired == 0) ? 'n/a' : number_format($totalEarned / $totalRequired * 100, 1) . '%';
            $dataRow[] = array("data" => $overall, "class" => "center $colClass");

            // add the row
            $table_data['body'][$goalResult->student_id] = $dataRow;
        }

        // add the footer to calculate averages, but only if there's more than one student
        if (count($students) > 1) {
            $footer = array(array("data" => "Averages:", "class" => "right"));
            foreach ($anyStudentsResults[$goalCategory] as $i => $goalResult) {
                $footer[] = array("data" => "P", "class" => "center");
            }
            $footer[] = array("data" => "%", "class" => "center percent");
            $table_data['foot']["average"] = $footer;
        }
        $logger->debug($table_data);
        return $table_data;
    }
}
