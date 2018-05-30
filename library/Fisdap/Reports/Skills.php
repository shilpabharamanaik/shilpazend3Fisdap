<?php
/**
 * Class Fisdap_Reports_Skills
 * This is the Skills Report class
 * It is very much a work in progress meant to demonstrate the new reports system
 * Refer to Fisdap_Reports_Report for more documentation
 */
class Fisdap_Reports_Skills extends Fisdap_Reports_Report
{
    public $header = '<h2 class="section-header no-border">Key</h2>
                      <div class="header-section">
						<span class="section-label">Comp. Assess.</span> - Comprehensive Assessment: the student performed both the patient exam and the patient interview.
					  </div>
					  <div class="header-section">
						<span class="section-label">Team Lead +</span> - Team Lead plus Comprehensive Assessment: the student was the team lead and performed a comprehensive assessment.
					  </div>';

    public $footer = '';

    public $formComponents = array(
        'shiftInformationForm' => array(
            'title' => 'Select shift information',
        ),
        'ageSelector' => array(
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
        )
    );
    
    private $patientData = array();
    
    private $siteTypes = array();

    private $goalset = null;

    /**
     * Run a query and any processing logic that produces the data contained in the report
     * Return a multidimensional array and it will be rendered as tables
     * OR return a string and it will be rendered as HTML
     * @return array
     */
    public function runReport()
    {
        // get the form values situated
        $shiftSites = $this->getSiteIds(true);
        ksort($shiftSites);
        $this->siteTypes = array_keys($shiftSites);
        if (count($this->siteTypes) > 1) {
            $this->siteTypes[] = 'total';
        }
        
        $start_date = $this->config['startDate'];
        $end_date = $this->config['endDate'];
        $subject_ids = $this->getTypeIds();
        $site_ids = $this->getSiteIds();
        $student_ids = array($this->config['student']);

        $goalSetId = $this->config['ageGoalset'];
        $goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $goalSetId);

        $this->goalset = $goalSet;

        /**
         * FIRST DO ALL THE PATIENT DATA/TABLES
         */
        // get all the data
        $repo = \Fisdap\EntityUtils::getRepository('Report');
        $patientData = $this->formatPatientData($repo->getPatientData($student_ids, $site_ids, $subject_ids, $start_date, $end_date));
        $patientData['Complaint'] = $this->formatComplaintData($repo->getComplaintData($student_ids, $site_ids, $subject_ids, $start_date, $end_date));
        $this->patientData = $patientData;
        
        // if there's more than one type of site, we need tabbed tables
        if (count($this->siteTypes) > 1) {
            $tabbed = true;
            $contentType = 'tabbed-tables';
            $patientTableMethod = "getTabbedPatientsTables";
            $type = "tabbed";
        } else {
            // if there's only one kind of site, we just need single tables
            $tabbed = false;
            $contentType = 'table';
            $patientTableMethod = "getPatientTable";
            $type = lcfirst(implode("", $this->siteTypes));
        }
        
        // add the impressions table(s)
        $impressionsTable = $this->{$patientTableMethod}('Impression', $type);
        // if this is a single table, we want to show the title
        if (!$tabbed) {
            $impressionsTable['title'] = 'Impressions';
        }
        $this->data['Impressions'] = array("type" => $contentType,
                                           "content" => $impressionsTable);
        $this->addPageBreak();
    
        // add the cause table(s)
        $causeTable = $this->{$patientTableMethod}('Cause', $type);
        // if this is a single table, we want to show the title
        if (!$tabbed) {
            $causeTable['title'] = 'Cause of injury';
        }
        $this->data['Cause of injury'] = array("type" => $contentType,
                                               "content" => $causeTable);
        $this->addPageBreak();
        
        // add the complaints table(s)
        $complaintTable = $this->{$patientTableMethod}('Complaint', $type);
        // if this is a single table, we want to show the title
        if (!$tabbed) {
            $complaintTable['title'] = 'Complaints';
        }
        $this->data['Complaints'] = array("type" => $contentType,
                                          "content" => $complaintTable);
        $this->addPageBreak();
        
        // add the ages table(s)
        $agesTable = $this->{$patientTableMethod}('Age', $type);
        // if this is a single table, we want to show the title
        if (!$tabbed) {
            $agesTable['title'] = 'Ages';
        }
        $this->data['Ages'] = array("type" => $contentType,
                                    "content" => $agesTable);
        $this->addPageBreak();
        
        // add the gender table(s)
        $genderTable = $this->{$patientTableMethod}('Gender', $type);
        // if this is a single table, we want to show the title
        if (!$tabbed) {
            $genderTable['title'] = 'Genders';
        }
        $this->data['Genders'] = array("type" => $contentType,
                                       "content" => $genderTable);
        $this->addPageBreak();
        
        // add the cardiac arrest table(s)
        $witnessTable = $this->{$patientTableMethod}('Witness', $type);
        // if this is a single table, we want to show the title
        if (!$tabbed) {
            $witnessTable['title'] = 'Cardiac arrest: witnessed by';
        }
        $this->data['Cardiac arrest: witnessed by'] = array("type" => $contentType,
                                                            "content" => $witnessTable);
        $this->addPageBreak();
        
        $returnTable = $this->{$patientTableMethod}('Return', $type);
        // if this is a single table, we want to show the title
        if (!$tabbed) {
            $returnTable['title'] = 'Cardiac arrest: pulse return';
        }
        $this->data['Cardiac arrest: pulse return'] = array("type" => $contentType,
                                                            "content" => $returnTable);
        $this->addPageBreak();
        
        /**
         * THEN DO ALL THE SKILLS DATA/TABLES
         */
        $student_id_string = implode(", ", $student_ids);
        $site_id_string = implode(", ", $site_ids);
        $subject_id_string = implode(", ", $subject_ids);
        if ($start_date) {
            $start_datetime = strtotime($start_date);
        }
        if ($end_date) {
            $end_datetime = strtotime($end_date);
        }
        
        // start with the ALS and BLS airway tables
        $airwayData = $this->formatSkillsData($repo->getAirwayData($student_id_string, $site_id_string, $start_datetime, $end_datetime, $subject_id_string), "Airway");
        $alsTable = $this->getSkillsTable('ALS airway procedures', $airwayData['als'], true);
        $this->data['ALS_airway'] = array("type" => 'table',
                                          "content" => $alsTable);
        $this->addPageBreak();
        $blsTable = $this->getSkillsTable('BLS airway procedures', $airwayData['bls'], true);
        $this->data['BLS_airway'] = array("type" => 'table',
                                          "content" => $blsTable);
        $this->addPageBreak();
        
        // next do cardiac skills
        $cardiacData = $this->formatSkillsData($repo->getCardiacInterventionData($student_id_string, $site_id_string, $start_datetime, $end_datetime, $subject_id_string), "Cardiac");
        $rhythmTable = $this->getSkillsTable('Cardiac rhythms', $cardiacData['rhythm'], false, 'Rhythm');
        $this->data['Cardiac_rhythms'] = array("type" => 'table',
                                               "content" => $rhythmTable);
        $this->addPageBreak();
        $procedureTable = $this->getSkillsTable('Cardiac procedures', $cardiacData['procedure'], false);
        $this->data['Cardiac_procedures'] = array("type" => 'table',
                                                  "content" => $procedureTable);
        $this->addPageBreak();
        
        // venous procedures
        $venousData = $this->formatSkillsData($repo->getIvData($student_id_string, $site_id_string, $start_datetime, $end_datetime, $subject_id_string), "Venous");
        $sitesTable = $this->getSkillsTable('IV and blood draw sites', $venousData['sites'], true, 'Site');
        $this->data['IV_sites'] = array("type" => 'table',
                                        "content" => $sitesTable);
        $this->addPageBreak();
        $gaugesTable = $this->getSkillsTable('IV and blood draw gauges', $venousData['gauges'], true, 'Gauge');
        $this->data['IV_gauges'] = array("type" => 'table',
                                         "content" => $gaugesTable);
        $this->addPageBreak();
        $fluidsTable = $this->getSkillsTable('IV fluids', $venousData['fluids'], false, 'Fluid');
        $this->data['IV_fluids'] = array("type" => 'table',
                                         "content" => $fluidsTable);
        $this->addPageBreak();
        $accessTable = $this->getSkillsTable('Venous access', $venousData['access'], true);
        $this->data['Venous_access'] = array("type" => 'table',
                                             "content" => $accessTable);
        $this->addPageBreak();
        
        // medications
        $medData = $this->formatSkillsData($repo->getMedData($student_id_string, $site_id_string, $start_datetime, $end_datetime, $subject_id_string, true), "Med");
        $routeTable = $this->getSkillsTable('Medication routes', $medData['routes'], false, 'Route');
        $this->data['Med_routes'] = array("type" => 'table',
                                          "content" => $routeTable);
        $this->addPageBreak();
        $medTable = $this->getSkillsTable('Medications', $medData['meds'], false, 'Medication');
        $this->data['Meds'] = array("type" => 'table',
                                    "content" => $medTable);
        $this->addPageBreak();
        
        // other procedures
        $otherData = $this->formatSkillsData($repo->getOtherInterventionData($student_id_string, $site_id_string, $start_datetime, $end_datetime, $subject_id_string), "Other");
        $otherTable = $this->getSkillsTable('Other interventions', $otherData, true);
        $this->data['Other'] = array("type" => 'table',
                                     "content" => $otherTable);
        $this->addPageBreak();
        
        // vitals
        $vitalsData = $this->formatSkillsData($repo->getVitalData($student_id_string, $site_id_string, $start_datetime, $end_datetime, $subject_id_string), "Vitals");
        $vitalsTable = $this->getSkillsTable('Vitals', $vitalsData, false);
        $this->data['Vitals'] = array("type" => 'table',
                                      "content" => $vitalsTable);
    }
    
    private function getTabbedPatientsTables($skillType, $type)
    {
        // set-up the patients tabbed tables
        foreach ($this->siteTypes as $siteType) {
            $patientsTables[$skillType.'_'.$siteType] = $this->getPatientTable($skillType, $siteType);
        }
        
        return $patientsTables;
    }
    
    private function getPatientTable($skillType, $type)
    {
        if ($type == 'clinical') {
            $performed_cols = 3;
            $sum_cols = 6;
        } else {
            $performed_cols = 5;
            $sum_cols = 8;
        }
        
        $table = array(
            'tab' => ucfirst($type),
            'nullMsg' => "No patients found.",
            'head' => array(
                '001' => array( // first row headers
                    array(
                        'data' => $skillType,
                        'rowspan' => 2,
                        'class' => 'superheader',
                    ),
                    array(
                        'data' => 'Observed',
                        'colspan' => 2,
                        'class' => 'superheader',
                    ),
                    array(
                        'data' => 'Performed',
                        'colspan' => $performed_cols,
                        'class' => 'superheader',
                    ),
                    array(
                        'data' => 'Total',
                        'rowspan' => 2,
                        'class' => 'superheader',
                    ),
                ),
                '002' => array( // second row headers
                    'Exam',
                    'Interview',
                    'Exam',
                    'Interview',
                    'Comp. Assess.'
                ),
            ),
            'body' => array(),
        );
        
        // add additional columns for non-clinical tables
        if ($type != 'clinical') {
            $table['head']['002'][] = 'Team Lead';
            $table['head']['002'][] = 'Team Lead +';
        }
        
        // ok now add the data
        $tableData = $this->patientData[$skillType][$type];
        if (count($tableData) > 0) {
            foreach ($tableData as $skill => $data) {
                $rowData = array(
                                 array(
                                    'data' => $skill,
                                    'class' => 'noSum'
                                 ),
                                 array(
                                    'data' => $data['exam']['O'] ? $data['exam']['O'] : 0,
                                    'class' => 'center evenCol'
                                 ),
                                 array(
                                    'data' => $data['interview']['O'] ? $data['interview']['O'] : 0,
                                    'class' => 'center evenCol'
                                 ),
                                 array(
                                    'data' => $data['exam']['P'] ? $data['exam']['P'] : 0,
                                    'class' => 'center'
                                 ),
                                 array(
                                    'data' => $data['interview']['P'] ? $data['interview']['P'] : 0,
                                    'class' => 'center'
                                 ),
                                 array(
                                    'data' => $data['comp_assess'] ? $data['comp_assess'] : 0,
                                    'class' => 'center'
                                 ),
                           );
                
                // add additional data for non-clincal tables
                if ($type != 'clinical') {
                    $rowData[] = array(
                                       'data' => $data['team_lead'] ? $data['team_lead'] : 0,
                                       'class' => 'center'
                                       );
                    $rowData[] = array(
                                       'data' => $data['team_lead+'] ? $data['team_lead+'] : 0,
                                       'class' => 'center');
                }
                
                // add total for all table types
                $rowData[] = array('data' => $data['count'] ? $data['count'] : 0,
                                   'class' => 'center evenCol');
                
                $table['body'][] = $rowData;
            }
        }
        
        // add the footer to calculate totals, but only if there's more than one row
        if (count($table['body']) > 1) {
            $footer = array(array("data" => "Total:", "class" => "right"));

            for ($i = 1; $i <= $sum_cols; $i++) {
                $footer[] = array("data" => "-", "class" => "center");
            }
            
            $table['foot']["sum"] = $footer;
        }
        
        return $table;
    }
    
    // this function plows through the raw patient data and formats it especially for this report
    private function formatPatientData($patients)
    {
        $data = array();

        //loop through all the patients and parse out the data for each table
        foreach ($patients as $patient) {
            $shiftType = $patient['type'];
            $exam = $patient['exam'] ? "P" : "O";
            $interview = $patient['interview'] ? "P" : "O";
            $comp_assess = $patient['exam'] && $patient['interview'];
            $team_lead = $patient['team_lead'];
            $team_lead_plus = $patient['team_lead'] && $patient['exam'] && $patient['interview'];
            
            // count the impressions
            if ($patient['primary_impression']) {
                $data = $this->addPatientCounts($data, 'Impression', $shiftType, $patient['primary_impression'], $exam, $interview, $comp_assess, $team_lead, $team_lead_plus);
            }
            if ($patient['secondary_impression'] && $patient['primary_impression'] != $patient['secondary_impression']) {
                $data = $this->addPatientCounts($data, 'Impression', $shiftType, $patient['secondary_impression'], $exam, $interview, $comp_assess, $team_lead, $team_lead_plus);
            }
            
            // count the causes
            if ($patient['cause']) {
                $data = $this->addPatientCounts($data, 'Cause', $shiftType, $patient['cause'], $exam, $interview, $comp_assess, $team_lead, $team_lead_plus);
            }

            // count the ages
            if (isset($patient['age']) || isset($patient['months'])) {
                if ($patient['age'] == 0 && $patient['months'] < $this->goalset->infant_start_age) {
                    $age = "<span class='hidden'>00</span>NewbornTest:";
                } elseif ($patient['age'] == 0 && $patient['months'] >= $this->goalset->infant_start_age) {
                    $age = "<span class='hidden'>01</span>Infant:";
                } elseif ($patient['age'] < $this->goalset->preschooler_start_age) {
                    $age = "<span class='hidden'>02</span>Toddler:";
                } elseif ($patient['age'] < $this->goalset->school_age_start_age) {
                    $age = "<span class='hidden'>03</span>Preschooler:";
                } elseif ($patient['age'] < $this->goalset->adolescent_start_age) {
                    $age = "<span class='hidden'>04</span>School age:";
                } elseif ($patient['age'] < $this->goalset->adult_start_age) {
                    $age = "<span class='hidden'>05</span>Adolescent:";
                } elseif ($patient['age'] < $this->goalset->geriatric_start_age) {
                    $age = "<span class='hidden'>06</span>Adult:";
                } else {
                    $age = "<span class='hidden'>07</span>Geriatric:";
                }

                $data = $this->addPatientCounts($data, 'Age', $shiftType, $age, $exam, $interview, $comp_assess, $team_lead, $team_lead_plus);
            }

            // count the genders
            if ($patient['gender']) {
                $data = $this->addPatientCounts($data, 'Gender', $shiftType, $patient['gender'], $exam, $interview, $comp_assess, $team_lead, $team_lead_plus);
            }
            
            // count the witnesses
            if ($patient['witness']) {
                $data = $this->addPatientCounts($data, 'Witness', $shiftType, $patient['witness'], $exam, $interview, $comp_assess, $team_lead, $team_lead_plus);
            }
            
            // count the pulse returns
            if ($patient['pulse_return']) {
                $data = $this->addPatientCounts($data, 'Return', $shiftType, $patient['pulse_return'], $exam, $interview, $comp_assess, $team_lead, $team_lead_plus);
            }
        }
        
        return $data;
    }
    
    // this function plows through the raw complaint data and formats it especially for this report
    private function formatComplaintData($patients)
    {
        $data = array();

        //loop through all the patients and parse out the complaint data
        foreach ($patients as $patient) {
            $shiftType = $patient['type'];
            $exam = $patient['exam'] ? "P" : "O";
            $interview = $patient['interview'] ? "P" : "O";
            $comp_assess = $patient['exam'] && $patient['interview'];
            $team_lead = $patient['team_lead'];
            $team_lead_plus = $patient['team_lead'] && $patient['exam'] && $patient['interview'];
            
            // count the complaints
            if ($patient['complaint']) {
                $data = $this->addPatientCounts($data, 'Complaint', $shiftType, $patient['complaint'], $exam, $interview, $comp_assess, $team_lead, $team_lead_plus);
            }
        }
        
        return $data['Complaint'];
    }
    
    private function addPatientCounts($data, $skillType, $shiftType, $skill, $exam, $interview, $comp_assess, $team_lead, $team_lead_plus)
    {
        $data[$skillType][$shiftType][$skill]['count']++;
        $data[$skillType]['total'][$skill]['count']++;
        
        $data[$skillType][$shiftType][$skill]['exam'][$exam]++;
        $data[$skillType]['total'][$skill]['exam'][$exam]++;
        $data[$skillType][$shiftType][$skill]['interview'][$interview]++;
        $data[$skillType]['total'][$skill]['interview'][$interview]++;
        
        if ($comp_assess) {
            $data[$skillType][$shiftType][$skill]['comp_assess']++;
            $data[$skillType]['total'][$skill]['comp_assess']++;
        }
        
        if ($team_lead) {
            $data[$skillType][$shiftType][$skill]['team_lead']++;
            $data[$skillType]['total'][$skill]['team_lead']++;
        }
        
        if ($team_lead_plus) {
            $data[$skillType][$shiftType][$skill]['team_lead+']++;
            $data[$skillType]['total'][$skill]['team_lead+']++;
        }
        
        return $data;
    }
    
    private function formatSkillsData($result, $skillType)
    {
        $data = array();
        
        // VITALS TABLE
        $vital_types = array(array('field' => array('systolic_bp'), 'name' => 'Blood pressure'),
                             array('field' => array('pulse_rate'), 'name' => 'Pulse'),
                             array('field' => array('resp_rate'), 'name' => 'Respirations'),
                             array('field' => array('spo2'), 'name' => 'SpO2'),
                             array('field' => array('skin_id'), 'name' => 'Skin'),
                             array('field' => array('lung_sound_id'), 'name' => 'Lung sounds'),
                             array('field' => array('pupils_equal', 'pupils_round', 'pupils_reactive'), 'name' => 'Pupils'),
                             array('field' => array('blood_glucose'), 'name' => 'Blood glucose'),
                             array('field' => array('apgar'), 'name' => 'APGAR'),
                             array('field' => array('gcs'), 'name' => 'GCS'),
                            );
        $count = 0;
        while ($row = $result->fetch()) {
            $count++;
            $shiftType = $row['Type'];
            $skill = $row['name'];
            $op = ($row['performed_by'] == 1) ? 'P' : 'O';
            $attempts = $row['attempts'];
            $requiresSuccess = $row['require_success'];
            $success = $row['success'];
            
            switch ($skillType) {
                case 'Airway':
                    if ($row['is_als']) {
                        $als = $this->addSkillCounts($als, $shiftType, $skill, $op, $attempts, $requiresSuccess, $success);
                    } else {
                        $bls = $this->addSkillCounts($bls, $shiftType, $skill, $op, $attempts, $requiresSuccess, $success);
                    }
                    break;
                case 'Cardiac':
                    // add the rhythms
                    $rhythm_name = $row['rhythm_name'];
                    $rhythm_op = ($row['rhythm_performed_by'] == 1) ? 'P' : 'O';
                    if ($rhythm_name) {
                        $rhythm = $this->addSkillCounts($rhythm, $shiftType, $rhythm_name, $rhythm_op, $attempts, $requiresSuccess, $success);
                        if ($row['twelve_lead'] == 1) {
                            $procedure = $this->addSkillCounts($procedure, $shiftType, '12 leads', $rhythm_op, $attempts, $requiresSuccess, $success);
                        }
                    }
                    // add the procedure
                    $procedure_name = $row['treatment_name'];
                    $procedure_op = ($row['treatment_performed_by'] == 1) ? 'P' : 'O';
                    if ($procedure_name) {
                        if ($row['method']) {
                            $procedure_name .= " - ".lcfirst($row['method']);
                        }
                        if ($row['pacing_method']) {
                            $procedure_name .= " - ".lcfirst($row['pacing_method']);
                        }
                        $procedure = $this->addSkillCounts($procedure, $shiftType, $procedure_name, $procedure_op, $attempts, $requiresSuccess, $success);
                    }
                    break;
                case 'Venous':
                    // sites
                    $site_name = $row['site_name'];
                    if ($row['side']) {
                        $site_name .= " - ".$row['side'];
                    }
                    if ($site_name && in_array($row['procedure_id'], array(1, 3, 8))) { // only include IVs, blood draws, and IVs w/ blood draws
                        $sites = $this->addSkillCounts($sites, $shiftType, $site_name, $op, $attempts, $requiresSuccess, $success);
                    }
                    // gauges
                    if ($row['gauge'] && in_array($row['procedure_id'], array(1, 3, 8))) { // only include IVs, blood draws, and IVs w/ blood draws
                        $gauge_name = $row['gauge']." gauge";
                        $gauges = $this->addSkillCounts($gauges, $shiftType, $gauge_name, $op, $attempts, $requiresSuccess, $success);
                    }
                    // fluids
                    $fluid_name = $row['fluid_name'];
                    $fluid_attempts = 1; // with fluids we only care about each instance, not each individual attempt
                    if ($fluid_name && in_array($row['procedure_id'], array(1, 8))) { // only include IVs and IVs w/ blood draws
                        $fluids = $this->addSkillCounts($fluids, $shiftType, $fluid_name, $op, $fluid_attempts, $requiresSuccess, $success);
                    }
                    // access
                    $procedure_name = $row['procedure_name'];
                    if ($procedure_name) {
                        $access = $this->addSkillCounts($access, $shiftType, $procedure_name, $op, $attempts, $requiresSuccess, $success);
                    }
                    break;
                case "Med":
                    // route
                    $route_name = $row['route_name'];
                    if ($route_name) {
                        $routes = $this->addSkillCounts($routes, $shiftType, $route_name, $op, $attempts, $requiresSuccess, $success);
                    }
                    // med
                    $med_name = $row['type_name'];
                    if ($med_name) {
                        $meds = $this->addSkillCounts($meds, $shiftType, $med_name, $op, $attempts, $requiresSuccess, $success);
                    }
                    break;
                case "Vitals":
                    // loop through all the vitals types and add to the count if there's data for that type
                    foreach ($vital_types as $type_info) {
                        $fields = $type_info['field'];
                        $vital_name = $type_info['name'];
                        
                        // see if there's data in any of the relevant fields
                        $count_instance = false;
                        foreach ($fields as $field) {
                            if ($row[$field] >= 0 && is_numeric($row[$field])) {
                                // 0 is not a valid input for blood glucose
                                if ($row[$field] == 0 && $vital_name == 'Blood glucose') {
                                    $count_instance = false;
                                } else {
                                    $count_instance = true;
                                }
                            }
                        }
                        
                        // go ahead and increment this type
                        if ($count_instance) {
                            $data = $this->addSkillCounts($data, $shiftType, $vital_name, $op, $attempts, $requiresSuccess, $success);
                        }
                    }
                    break;
                default:
                    $data = $this->addSkillCounts($data, $shiftType, $skill, $op, $attempts, $requiresSuccess, $success);
                    break;
            }
        }

        switch ($skillType) {
            case 'Airway':
                $data['als'] = $als;
                $data['bls'] = $bls;
                break;
            case 'Cardiac':
                $data['rhythm'] = $rhythm;
                $data['procedure'] = $procedure;
                break;
            case 'Venous':
                $data['sites'] = $sites;
                $data['gauges'] = $gauges;
                $data['fluids'] = $fluids;
                $data['access'] = $access;
                break;
            case 'Med':
                $data['routes'] = $routes;
                $data['meds'] = $meds;
                break;
        }

        return $data;
    }
    
    private function addSkillCounts($data, $shiftType, $skill, $op, $attempts, $requiresSuccess, $success = null)
    {
        if ($attempts > 1) {
            $data[$skill][$shiftType][$op] += $attempts;
            $data[$skill]['total'][$op] += $attempts;
        } else {
            $data[$skill][$shiftType][$op]++;
            $data[$skill]['total'][$op]++;
        }
        
        if ($requiresSuccess) {
            $data[$skill]['requiresSuccess'] = true;
        } else {
            $data[$skill]['requiresSuccess'] = false;
        }
        
        if ($requiresSuccess && $success) {
            $data[$skill][$shiftType]['success'][$op]++;
            $data[$skill]['total']['success'][$op]++;
        }
        return $data;
    }
    
    private function getSkillsTable($title, $tableData, $requiresSuccess, $procedureLabel = 'Procedure')
    {
        // figure out what the header is going to look like
        // if there's more than one site type
        $colCount = 0;
        if (count($this->siteTypes) > 1) {
            $topspan = ($requiresSuccess) ? 4 : 2;
            $rowspan = ($requiresSuccess) ? 3 : 2;
            
            $topheader[] = array('data' => $procedureLabel,
                                 'rowspan' => $rowspan,
                                 'class' => 'superheader');
            $colCount++;
            
            foreach ($this->siteTypes as $type) {
                $topheader[] = array('data' => ucfirst($type),
                                     'colspan' => $topspan,
                                     'class' => 'superheader');
                $bottomheader[] = 'O';
                $bottomheader[] = 'P';
                $colCount++;
                $colCount++;
                
                if ($requiresSuccess) {
                    $successheader[] = array('data' => 'total',
                                             'colspan' => 2);
                    $successheader[] = array('data' => 'success',
                                             'colspan' => 2);
                    $bottomheader[] = 'O';
                    $bottomheader[] = 'P';
                    $colCount++;
                    $colCount++;
                }
            }
            
            $head[1] = $topheader;
            if ($requiresSuccess) {
                $head[2] = $successheader;
            }
            $head[3] = $bottomheader;
        } else {
            // if there's only one site type, the header is simple
            $head = array(
                          1 => array(
                                     $title,
                                     'Observed',
                                     'Performed'));
            $colCount++;
            $colCount++;
            $colCount++;
            
            if ($requiresSuccess) {
                $head[1][] = 'Success Observed';
                $head[1][] = 'Success Performed';
                $colCount++;
                $colCount++;
            }
        }
        
        // make the table
        $table['title'] = $title;
        $table['nullMsg'] = "No skills found.";
        $table['head'] = $head;
        $table['body'] = array();
        
        // ok now add the data
        if (count($tableData) > 0) {
            foreach ($tableData as $skill => $data) {
                $rowData = array();
                $rowData[] = array('data' => $skill, 'class' => 'noSum');
                
                if (count($this->siteTypes) > 1) {
                    $sectionNum = 1;
                    $observedClass = "";
                } else {
                    $sectionNum = 0;
                    $observedClass = "evenCol";
                }
                
                foreach ($this->siteTypes as $type) {
                    $colClass = $sectionNum % 2 ? "evenCol" : "";
                    $rowData[] = array('data' => $data[$type]['O'] ? $data[$type]['O'] : 0,
                                       'class' => "center $colClass $observedClass");
                    $rowData[] = array('data' => $data[$type]['P'] ? $data[$type]['P'] : 0,
                                       'class' => "center $colClass");
                    
                    if ($requiresSuccess) {
                        // if this particular skill requires success
                        if ($data['requiresSuccess']) {
                            $rowData[] = array('data' => $data[$type]['success']['O'] ? $data[$type]['success']['O'] : 0,
                                               'class' => "center $colClass $observedClass");
                            $rowData[] = array('data' => $data[$type]['success']['P'] ? $data[$type]['success']['P'] : 0,
                                               'class' => "center $colClass");
                        } else {
                            $rowData[] = array('data' => 'n/a',
                                               'class' => "center $colClass $observedClass");
                            $rowData[] = array('data' => 'n/a',
                                               'class' => "center $colClass");
                        }
                    }
                    $sectionNum++;
                }
                $table['body'][] = $rowData;
            }
        }
        
        // add the footer to calculate totals, but only if there's more than one row
        if (count($table['body']) > 1) {
            $footer = array(array("data" => "Total:", "class" => "right"));
            
            for ($i = 1; $i <= $colCount-1; $i++) {
                $footer[] = array("data" => "-", "class" => "center");
            }
        
            $table['foot']["sum"] = $footer;
        }
        
        return $table;
    }
}
