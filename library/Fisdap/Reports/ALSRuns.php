<?php

/**
 * Class Fisdap_Reports_Skills
 * This is the Skills Report class
 * It is very much a work in progress meant to demonstrate the new reports system
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_ALSRuns extends Fisdap_Reports_Report
{
    public $header = '';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
            'options' => array(
                'pickSiteType' => true,
                'pickPatientType' => false,
                'siteTypes' => array("Field"),
            ),
        ),
        'definitionsSelector' => array(
            'title' => 'Select definitions',
            'options' => array(
                'selected' => array('als-type', 'fisdap')
            )
        ),
        'multistudentPicklist' => array(
            'title' => 'Select one or more student(s)',
            'options' => array(
                'mode' => 'multiple',
                'loadJSCSS' => TRUE,
                'loadStudents' => TRUE,
                'showTotal' => TRUE,
                'studentVersion' => TRUE,
                'useSessionFilters' => TRUE,
                'sessionNamespace' => "ReportStudentFilter",
            ),
        )
    );

    private $reportData = array();

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

        $studentIds = array_keys($students);
        $studentIdString = implode(', ', $studentIds);

        if ($this->config['startDate'] != '') {
            $startDateObj = new \DateTime($this->config['startDate']);
        } else {
            // Make up an old date here if one wasn't supplied (should cover all of our data).
            $startDateObj = new \DateTime('01/01/1990');
        }
        $startDate = $startDateObj->format('Y-m-d');

        if ($this->config['endDate'] != '') {
            $endDateObj = new \DateTime($this->config['endDate']);
        } else {
            // Use now if no end date supplied.
            $endDateObj = new \DateTime();
        }
        $endDate = $endDateObj->format('Y-m-d');

        // get the goalset so we know how to define team lead and ages
        $goalSetId = $this->config['goalset'];
        if ($goalSetId > 0) {
            $goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $goalSetId);

            // team lead definitions
            $leadGoal = $goalSet->getGoalByName("Team Lead Total");
            $teamLeadClause = "AND p.team_lead = 1 ";
            $ALSLeadClause = "SUM(p.team_lead";
            if ($leadGoal->exam) {
                $teamLeadClause .= "AND p.exam = 1 ";
                $ALSLeadClause .= " && p.exam";
            }
            if ($leadGoal->interview) {
                $teamLeadClause .= "AND p.interview = 1 ";
                $ALSLeadClause .= " && p.interview";
            }
            $ALSLeadClause .= ") as als_lead, ";

            // ages
            $adultAge = $goalSet->adult_start_age;
            $geriatricAge = $goalSet->geriatric_start_age;
        } else {
            // if this is an old config and there's no goal set, use the original defaults
            $teamLeadClause = "AND p.team_lead = 1 ";
            $ALSLeadClause = "SUM(p.team_lead) as als_lead, ";
            $adultAge = 18;
            $geriatricAge = 70;
        }

        // Grab the selected field site IDs here...
        $siteIdClause = "";
        if (is_array($this->config['sites_filters']) && count($this->config['sites_filters']) > 0) {
            // If 0-field is in the array, don't do any special filters (all field shifts will be included)
            if (!in_array('0-Field', $this->config['sites_filters'])) {
                $siteIds = implode(',', $this->config['sites_filters']);
                $siteIdClause = "s.AmbServ_id IN ($siteIds) AND ";
            } else {
                $siteIdClause = "s.Type = 'field' AND ";
            }
        }

        //Pulling these queries almost verbatim from the old ones- only updating them to run on a block of student IDs
        // instead of one query per cell per student.
        $query = "SELECT SUM(Hours) as Hours, Student_id ".
            "FROM ShiftData s ".
            "WHERE $siteIdClause ".
            "s.Student_id IN ($studentIdString) ".
            "AND s.StartDate >= '$startDate' ".
            "AND s.StartDate <= '$endDate' ".
            "AND s.Type='field' ".
            "AND Completed = 1 ".
            "GROUP BY Student_id";
        $this->executeAndCacheQuery($query, array('Hours' => 'Hours'));

        $query = "SELECT COUNT(s.Shift_id) AS Runs, r.Student_id ".
            "FROM fisdap2_runs r ".
            "INNER JOIN ShiftData s ".
            "ON s.Shift_id = r.shift_id ".
            "WHERE $siteIdClause ".
            "s.StartDate>='$startDate' ".
            "AND s.StartDate<='$endDate' ".
            "AND r.Student_id IN ($studentIdString) ".
            "AND s.Type='field' ".
            "GROUP BY r.Student_id";
        $this->executeAndCacheQuery($query, array('Runs' => 'Runs'));

        $query = "SELECT COUNT(DISTINCT p.id) AS NumLeader, p.Student_id ".
            "FROM ShiftData s ".
            "INNER JOIN fisdap2_patients p ".
            "ON p.shift_id = s.Shift_id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate>='$startDate' ".
            "AND s.StartDate<='$endDate' ".
            "AND p.student_id IN ($studentIdString) ".
            $teamLeadClause.
            "GROUP BY p.student_id";
        $this->executeAndCacheQuery($query, array('NumLeader' => 'NumLeader'));

        $query = "SELECT COUNT(DISTINCT p.id) AS NumInterview, p.Student_id ".
            "FROM ShiftData s ".
            "INNER JOIN fisdap2_patients p ".
            "ON p.shift_id = s.Shift_id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate>='$startDate' ".
            "AND s.StartDate<='$endDate' ".
            "AND p.student_id IN ($studentIdString) ".
            "AND p.interview = 1 ".
            "GROUP BY p.student_id";
        $this->executeAndCacheQuery($query, array('NumInterview' => 'NumInterview'));

        $query = "SELECT COUNT(DISTINCT p.id) AS NumExam, p.Student_id ".
            "FROM ShiftData s ".
            "INNER JOIN fisdap2_patients p ".
            "ON p.shift_id = s.Shift_id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate>='$startDate' ".
            "AND s.StartDate<='$endDate' ".
            "AND p.student_id IN ($studentIdString) ".
            "AND p.exam = 1 ".
            "GROUP BY p.student_id";
        $this->executeAndCacheQuery($query, array('NumExam' => 'NumExam'));

        $query = "SELECT COUNT(DISTINCT p.id) AS NumPediatrics, p.Student_id ".
            "FROM ShiftData s ".
            "INNER JOIN fisdap2_patients p ".
            "ON p.shift_id = s.Shift_id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate>='$startDate' ".
            "AND s.StartDate<='$endDate' ".
            "AND p.student_id IN ($studentIdString) ".
            "AND p.age < $adultAge ".
            "GROUP BY p.student_id";
        $this->executeAndCacheQuery($query, array('NumPediatrics' => 'NumPediatrics'));

        $query = "SELECT COUNT(DISTINCT p.id) AS NumAdults, p.Student_id ".
            "FROM ShiftData s ".
            "INNER JOIN fisdap2_patients p ".
            "ON p.shift_id = s.Shift_id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate>='$startDate' ".
            "AND s.StartDate<='$endDate' ".
            "AND p.student_id IN ($studentIdString) ".
            "AND p.age >= $adultAge  ".
            "AND p.age < $geriatricAge ".
            "GROUP BY p.student_id";
        $this->executeAndCacheQuery($query, array('NumAdults' => 'NumAdults'));

        $query = "SELECT COUNT(DISTINCT p.id) AS NumGeriatrics, p.Student_id ".
            "FROM ShiftData s ".
            "INNER JOIN fisdap2_patients p ".
            "ON p.shift_id = s.Shift_id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate>='$startDate' ".
            "AND s.StartDate<='$endDate' ".
            "AND p.student_id IN ($studentIdString) ".
            "AND p.age >= $geriatricAge ".
            "GROUP BY p.student_id";
        $this->executeAndCacheQuery($query, array('NumGeriatrics' => 'NumGeriatrics'));

        $query = "SELECT SUM(i.attempts) AS NumAttempts, ".
            "SUM(i.success) AS Success, ".
            "i.student_id AS Student_id ".
            "FROM fisdap2_ivs i ".
            "INNER JOIN ShiftData s ".
            "ON s.Shift_id = i.shift_id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate >= '$startDate' ".
            "AND s.StartDate <= '$endDate' ".
            "AND i.student_id IN ($studentIdString) ".
            "AND i.performed_by = 1 ".
            "GROUP BY i.student_id";
        $this->executeAndCacheQuery($query, array('NumAttempts' => 'IVNumAttempts', 'Success' => 'IVSuccess'));

        $query = "SELECT COUNT(p.id) AS NumEKGs, ".
            "p.student_id AS Student_id ".
            "FROM fisdap2_patients p ".
            "INNER JOIN ShiftData s ".
            "ON s.Shift_id = p.shift_id ".
            "INNER JOIN fisdap2_cardiac_interventions ci ".
            "ON ci.patient_id = p.id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate >= '$startDate' ".
            "AND s.StartDate <= '$endDate' ".
            "AND p.student_id IN ($studentIdString) ".
            "AND ci.rhythm_performed_by = 1 ".
            "GROUP BY p.student_id";
        $this->executeAndCacheQuery($query, array('NumEKGs' => 'NumEKGs'));

        $query = "SELECT COUNT(DISTINCT m.id) AS NumMeds, ".
            "m.student_id AS Student_id ".
            "FROM fisdap2_meds m ".
            "INNER JOIN ShiftData s ".
            "ON s.Shift_id = m.shift_id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate>='$startDate' ".
            "AND s.StartDate<='$endDate' ".
            "AND m.student_id IN ($studentIdString) ".
            "AND m.medication_id != 25 ".
            "GROUP BY m.student_id";
        $this->executeAndCacheQuery($query, array('NumMeds' => 'NumMeds'));

        $query = "SELECT COUNT(DISTINCT a.id) AS NumVent, ".
            "a.student_id AS Student_id ".
            "FROM fisdap2_airways a ".
            "INNER JOIN ShiftData s ".
            "ON s.Shift_id = a.shift_id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate>='$startDate' ".
            "AND s.StartDate<='$endDate' ".
            "AND a.student_id IN ($studentIdString) ".
            "AND a.procedure_id IN (12, 19, 28) ".
            "GROUP BY a.student_id";
        $this->executeAndCacheQuery($query, array('NumVent' => 'NumVent'));

        $query = "SELECT COUNT(DISTINCT p.id) AS NumCArest, ".
            "p.student_id AS Student_id ".
            "FROM fisdap2_patients p ".
            "INNER JOIN ShiftData s ".
            "ON s.Shift_id = p.shift_id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate>='$startDate' ".
            "AND s.StartDate<='$endDate' ".
            "AND p.student_id IN ($studentIdString) ".
            "AND (p.primary_impression_id = 4 OR p.secondary_impression_id = 4) ".
            "GROUP BY p.student_id";
        $this->executeAndCacheQuery($query, array('NumCArest' => 'NumCArest'));

        $query = "SELECT COUNT(DISTINCT o.id) AS NumMDConsult, ".
            "o.student_id AS Student_id ".
            "FROM fisdap2_other_interventions o ".
            "INNER JOIN ShiftData s ".
            "ON s.Shift_id = o.shift_id ".
            "WHERE $siteIdClause ".
            "s.Type='field' ".
            "AND s.StartDate>='$startDate' ".
            "AND s.StartDate<='$endDate' ".
            "AND o.student_id IN ($studentIdString) ".
            "AND o.procedure_id IN (42) ".
            "AND o.performed_by = 1 ".
            "GROUP BY o.student_id";
        $this->executeAndCacheQuery($query, array('NumMDConsult' => 'NumMDConsult'));

        // These are tucked away since there are 2 ways to calculate these fields
        // and I don't want to clutter up the main block too much.
        $this->cacheALSCalls($studentIdString, $siteIdClause, $ALSLeadClause);
		$title1 = "Runs/Patients";
		$title2 = "ALS Skills";
        $data = $this->getStudentData($students);      
		$reportTable1 = array(
			'title' => $title1,
            'head' => array(
                '000' => array( // First row headers...
                    'Name',
                    'Hrs',
                    'Runs',
                    'Hrs/ Run',
                    'Team Lead Total',
                    'ALS Calls',
                    'ALS Team Leads',
                    'ALS Transported',
                    'Interv.',
                    'Phys. Exam',
                    'Ped. Pts.',
                    'Adult Pts.',
                    'Ger. Pts.'                    
                )
            ),
            'body' => array()
        );

        foreach ($data as $dataRow1) {
            $tableRow1 = array();
            $tableRow1['Name'] = array("data" => $dataRow1['Name'], "class"=>"left noSum noAverage noMin noMax");
            $tableRow1['Hrs'] = $dataRow1['Hours'];
            $tableRow1['Runs'] = $dataRow1['Runs'];
            $tableRow1['Hrs/ Run'] = ($dataRow1['Runs'] > 0) ? round($dataRow1['Hours'] / $dataRow1['Runs'], 2) : 'N/A';
            $tableRow1['Team Lead Total'] = $dataRow1['NumLeader'];
            $tableRow1['ALS Calls'] = (($dataRow1['ALSCount'] == '') ? 0 : $dataRow1['ALSCount']);
            $tableRow1['ALS Team Leads'] = (($dataRow1['ALSLead'] == '') ? 0 : $dataRow1['ALSLead']);
            $tableRow1['ALS Transported'] = (($dataRow1['ALSTransport'] == '') ? 0 : $dataRow1['ALSTransport']);
            $tableRow1['Interv.'] = $dataRow1['NumInterview'];
            $tableRow1['Phys. Exam'] = $dataRow1['NumExam'];
            $tableRow1['Ped. Pts.'] = $dataRow1['NumPediatrics'];
            $tableRow1['Adult Pts.'] = $dataRow1['NumAdults'];
            $tableRow1['Ger. Pts.'] = $dataRow1['NumGeriatrics'];
			$reportTable1['body'][] = $tableRow1;
        }

        // add the footer to calculate total, average, and max, but only if there's more than one row
        if (count($reportTable1['body']) > 1) {
            $total_footer1 = array(array("data" => "Total:", "class" => "right"));
            $max_footer1 = array(array("data" => "Maximum:", "class" => "right"));
            $average_footer1 = array(array("data" => "Average:", "class" => "right"));

            // add a cell for each of the remaining columns
            for ($i=1; $i<=12; $i++) {
                $total_footer1[] = array("data" => "-", "class" => "");
                $max_footer1[] = array("data" => "-", "class" => "");
                $average_footer1[] = array("data" => "-", "class" => "");
            }

            $reportTable1['foot']["sum"] = $total_footer1;
            $reportTable1['foot']["max"] = $max_footer1;
            $reportTable1['foot']["average"] = $average_footer1;
        }
		
		$reportTable2 = array(
		'title' => $title2,
            'head' => array(
                '000' => array( // First row headers...
                    'Name',                    
                    'IV Suc.',
                    'IV Attempts',
                    'IV Suc. Rate',
                    'EKG',
                    'Meds',
                    'Vent.',
                    'Cardiac Arrest',
                    'MD Cnslt'
                )
            ),
            'body' => array()
        );

        foreach ($data as $dataRow2) {
            $tableRow2 = array();
            $tableRow2['Name'] = array("data" => $dataRow2['Name'], "class"=>"left noSum noAverage noMin noMax");           
            $tableRow2['IV Suc.'] = $dataRow2['IVSuccess'];
            $tableRow2['IV Atmts'] = $dataRow2['IVNumAttempts'];
            $tableRow2['IV Suc. Rate'] = ($dataRow2['IVNumAttempts'] > 0) ? floor(($dataRow2['IVSuccess'] / $dataRow2['IVNumAttempts']) * 100) . '%' : 'N/A';
            $tableRow2['EKG'] = $dataRow2['NumEKGs'];
            $tableRow2['Meds'] = $dataRow2['NumMeds'];
            $tableRow2['Vent.'] = $dataRow2['NumVent'];
            $tableRow2['Cardiac Arrest'] = $dataRow2['NumCArest'];
            $tableRow2['MD Cnslt'] = $dataRow2['NumMDConsult'];
            $reportTable2['body'][] = $tableRow2;
        }
		if (count($reportTable2['body']) > 1) {
            $total_footer2 = array(array("data" => "Total:", "class" => "right"));
            $max_footer2 = array(array("data" => "Maximum:", "class" => "right"));
            $average_footer2 = array(array("data" => "Average:", "class" => "right"));

            // add a cell for each of the remaining columns
            for ($i=1; $i<=8; $i++) {
                $total_footer2[] = array("data" => "-", "class" => "");
                $max_footer2[] = array("data" => "-", "class" => "");
                $average_footer2[] = array("data" => "-", "class" => "");
            }

            $reportTable2['foot']["sum"] = $total_footer2;
            $reportTable2['foot']["max"] = $max_footer2;
            $reportTable2['foot']["average"] = $average_footer2;
        }
        // return all tables for this report
        $this->data['als_report1'] = array("type" => "table", "content" => $reportTable1);
        $this->data['als_report2'] = array("type" => "table", "content" => $reportTable2);
    }

    /**
     * This executes a query for a list of students, there should be about one query run per column in the end table.
     *
     * The $targets array is used to map resulting columns from the query with the cached storage array.
     *
     * @param string $query Query to execute.  This query should return at LEAST 1 column, Student_id.  Other columns
     * should have names (using the AS clause).
     * @param array $targets Associative array.  Indices are the column names, the value at that index should be the name
     * of the index to store the results in (in $this->reportData).
     */
    private function executeAndCacheQuery($query, $targets)
    {
        // Execute the query here, use the $targets array to map the results to the cached results
        $conn = \Fisdap\EntityUtils::getEntityManager()->getConnection();
        $res = $conn->query($query);

        // Init the target arrays if they don't exist (which they really shouldn't)
        foreach ($targets as $field) {
            if (!array_key_exists($field, $this->reportData)) {
                $this->reportData[$field] = array();
            }
        }

        while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
            $studentId = $row['Student_id'];

            foreach ($targets as $column => $field) {
                $this->reportData[$field][$studentId] = $row[$column];
            }
        }
    }

    /**
     * This restructures the reportData into something a little more directly readable by the report
     * output section.
     *
     * @param array $students Array containing the IDs of all of the students we want to report on.
     *
     * @return array
     */
    private function getStudentData($students)
    {
        $returnData = array();

        foreach ($students as $studentId => $nameOptions) {
            $row = array();
            $row['Name'] = $nameOptions['first_last_combined'];

            foreach ($this->reportData as $field => $studentsData) {
                if (array_key_exists($studentId, $studentsData)) {
                    $row[$field] = $studentsData[$studentId];
                } else {
                    // Default any empty field to 0
                    $row[$field] = 0;
                }
            }

            $returnData[] = $row;
        }

        return $returnData;
    }

    /**
     * This function figures out the number of ALS calls and ALS leads.  Since this can be
     * calculated in 2 ways, moving it here to make it a bit less messy up above.
     *
     * @param string $studentIds comma separated list of student IDs to find runs for.
     */
    private function cacheALSCalls($studentIds, $siteIdClause, $ALSLeadClause)
    {
        $conn = \Fisdap\EntityUtils::getEntityManager()->getConnection();

        if ($this->config['startDate'] != '') {
            $startDateObj = new \DateTime($this->config['startDate']);
        } else {
            // Make up an old date here if one wasn't supplied (should cover all of our data).
            $startDateObj = new \DateTime('01/01/1990');
        }
        $startDate = $startDateObj->format('Y-m-d');

        if ($this->config['endDate'] != '') {
            $endDateObj = new \DateTime($this->config['endDate']);
        } else {
            // Use now if no end date supplied.
            $endDateObj = new \DateTime();
        }
        $endDate = $endDateObj->format('Y-m-d');

        $runIds = array();

        $query = false;

        // If we're calculating things the fisdap way...
        if ($this->config['als-type'] == 'fisdap') {
            $query = "
	    		SELECT
				    DISTINCT r.id as run_id
				FROM
				    fisdap2_runs r
				    INNER JOIN fisdap2_patients p ON p.run_id = r.id
				    INNER JOIN ShiftData s ON r.shift_id = s.Shift_id
				    LEFT JOIN fisdap2_meds m ON m.patient_id = p.id
				    LEFT JOIN fisdap2_cardiac_interventions ci ON ci.patient_id = p.id
				    LEFT JOIN fisdap2_ivs i ON i.patient_id = p.id
				WHERE
					$siteIdClause
				    (
				        (m.medication_id IS NOT NULL AND m.medication_id != 25)
				        OR (ci.id IS NOT NULL AND i.id IS NOT NULL)
				    )
				    AND s.StartDate >= '$startDate'
   					AND s.StartDate <= '$endDate'
				    AND r.student_id IN ($studentIds)
				;
	    	";
        } elseif ($this->config['als-type'] == 'als_skill') {
            $query = "
    			SELECT
    				DISTINCT r.id as run_id
    			FROM
    				ShiftData s
    				INNER JOIN fisdap2_runs r ON r.shift_id = s.Shift_id
    				INNER JOIN fisdap2_patients p ON p.run_id = r.id
    				LEFT JOIN fisdap2_ivs i ON i.patient_id = p.id
    				LEFT JOIN fisdap2_meds m ON m.patient_id = p.id
    				LEFT JOIN fisdap2_cardiac_interventions c ON c.patient_id = p.id
    				LEFT JOIN fisdap2_airways a ON a.patient_id = p.id
    				LEFT JOIN fisdap2_other_interventions o ON o.patient_id = p.id
    			WHERE
    				$siteIdClause
    				s.StartDate >= '$startDate'
   					AND s.StartDate <= '$endDate'
   					AND r.student_id IN ($studentIds)
   					AND (
   						(i.id IS NOT NULL AND i.id > 0 AND i.soft_deleted = 0)
   						OR (p.primary_impression_id = 4 OR p.secondary_impression_id = 4)
   						OR (m.medication_id != 25 AND m.medication_id IS NOT NULL AND m.medication_id > 0 AND m.soft_deleted = 0)
   						OR (c.id IS NOT NULL AND c.id > 0 AND c.soft_deleted = 0)
   						OR (a.id IS NOT NULL AND a.procedure_id IN (1,3,5,6,9,10,11,14,15,17,18,19,20,21,22,23,25) AND a.id > 0 AND a.soft_deleted = 0)
   						OR (o.procedure_id NOT IN (27,30,31,32,33,35,36,37,38,39,40,41,42,43,44,45,46) AND o.id IS NOT NULL AND o.id > 0 AND o.soft_deleted = 0)
   					)
   				;
    		";
        } elseif ($this->config['als-type'] == "california") {
            $query = "
    			SELECT
    				DISTINCT r.id as run_id
    			FROM
    				ShiftData s
    				INNER JOIN fisdap2_runs r ON r.shift_id = s.Shift_id
    				INNER JOIN fisdap2_patients p ON p.run_id = r.id
    				LEFT JOIN fisdap2_ivs i ON i.patient_id = p.id
    				LEFT JOIN fisdap2_meds m ON m.patient_id = p.id
    				LEFT JOIN fisdap2_cardiac_interventions c ON c.patient_id = p.id
    				LEFT JOIN fisdap2_airways a ON a.patient_id = p.id
    				LEFT JOIN fisdap2_other_interventions o ON o.patient_id = p.id
    			WHERE
    				$siteIdClause
    				s.StartDate >= '$startDate'
   					AND s.StartDate <= '$endDate'
   					AND r.student_id IN ($studentIds)
   					AND (
   						(i.id IS NOT NULL AND i.id > 0 AND i.performed_by = 1 AND i.soft_deleted = 0)
   						OR (m.medication_id != 25 AND m.medication_id IS NOT NULL AND m.medication_id > 0 AND m.soft_deleted = 0 AND m.performed_by = 1)
   						OR (c.id IS NOT NULL AND c.id > 0 AND c.soft_deleted = 0 AND c.performed_by = 1 AND c.procedure_id != 1)
   						OR (a.id IS NOT NULL AND a.procedure_id IN (1,3,5,6,9,10,11,14,15,17,18,19,20,23,25) AND a.id > 0 AND a.soft_deleted = 0 AND a.performed_by = 1)
   						OR (o.procedure_id IN (3,25,47) AND o.id IS NOT NULL AND o.id > 0 AND o.soft_deleted = 0 AND o.performed_by = 1)
   					)
   				;
            ";

        }

        // Just in case something weird happens and we don't get an explicit skill set to run
        if ($query) {
            $res = $conn->query($query);

            while ($row = $res->fetch(\PDO::FETCH_ASSOC)) {
                $runIds[] = $row['run_id'];
            }

            // We should have a set of runs that are considered ALS - find out how many there are, and how
            // many were marked as team_lead.
            if (count($runIds) > 0) {
                $runIdStr = implode(',', $runIds);

                // use the team lead definition set in the goal set

                $query = "SELECT ".
                    "COUNT(DISTINCT p.id) as als_count, ".
                    $ALSLeadClause.
                    "r.student_id AS Student_id ".
                    "FROM fisdap2_runs r ".
                    "INNER JOIN fisdap2_patients p ".
                    "ON p.run_id = r.id ".
                    "WHERE r.id IN ($runIdStr) ".
                    "GROUP BY r.student_id";
                $this->executeAndCacheQuery($query, array('als_count' => 'ALSCount', 'als_lead' => 'ALSLead'));

                //get new ALS Transported number

                $query = "SELECT ".
                    "COUNT(DISTINCT p.id) as als_transported, ".
                    "r.student_id AS Student_id ".
                    "FROM fisdap2_runs r ".
                    "INNER JOIN fisdap2_patients p ".
                    "ON p.run_id = r.id ".
                    "WHERE r.id IN ($runIdStr) ".
                    "AND p.patient_disposition_id = 1 ".
                    "GROUP BY r.student_id";
                $this->executeAndCacheQuery($query, array('als_transported' => 'ALSTransport'));

            }
        }
    }
}
