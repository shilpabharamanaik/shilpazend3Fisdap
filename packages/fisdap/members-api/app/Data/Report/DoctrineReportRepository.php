<?php namespace Fisdap\Data\Report;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineReportRepository
 *
 * @package Fisdap\Data\Report
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineReportRepository extends DoctrineRepository implements ReportRepository
{
    public function getAvailableReportsByProfession($profession_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("r")
            ->from("\Fisdap\Entity\Report", "r")
            ->join("r.categories", "c")
            ->join("c.profession", "p")
            ->where("p.id = ?1")
            ->andWhere("r.standalone = 0")
            ->setParameter(1, $profession_id)
            ->orderBy("r.name");

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * Query the database for data used in the Shift Request report.
     * @param  array $students Student ids
     * @param  array $sites Site ids
     * @param  string $start_date Start date
     * @param  string $end_date End date
     * @return array              Tabular data to be used in the report
     */
    public function getShiftRequestData($students, $sites = null, $start_date = null, $end_date = null)
    {

        // sanity check IDs
        foreach ($students as $id) {
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $studentIds = implode(",", $students);;
        // figure out search criteria
        if ($sites) {
            // sanity check IDs
            foreach ($sites as $id) {
                if (!is_numeric($id)) {
                    return FALSE; // @todo probably should do some real error handling
                }
            }
            $siteIds = implode(",", $sites);
        } else {
            $siteIds = null;
        }

        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }


        /* Some helper clauses to use for filters */
        $site_clause = $siteIds ? "AND e.Site_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND e.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND e.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";

        /* Grab all accepted shift requests */
        $sql = "
			SELECT
				sd.Student_id as student_id,
				sr.accepted_id,
				sr.approved_id,
				rt.name as request_type_name

			FROM
				fisdap2_user_roles ur,
				fisdap2_users u,
				StudentData sd,
				EventData e,
				fisdap2_shift_requests sr
			LEFT JOIN fisdap2_request_type rt ON rt.id = sr.request_type_id
			LEFT JOIN fisdap2_slot_assignments sa ON sa.id = sr.assignment_id
			WHERE
				sr.owner_id = ur.id AND
				ur.user_id = u.id AND
				sd.user_id = u.id AND
				e.Event_id = sr.event_id
				AND sd.Student_id IN({$studentIds})
				{$site_clause}
				{$start_date_clause}
				{$end_date_clause}

		";

        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);
        $data = array();

        /* Prepopulate the blank student array */
        foreach ($students as $student) {
            if (!array_key_exists($student, $data)) {
                $data[$student] = array(
                    'drop' => array(
                        'requested' => 0,
                        'approved' => 0
                    ),
                    'swap' => array(
                        'requested' => 0,
                        'approved' => 0
                    ),
                    'cover' => array(
                        'requested' => 0,
                        'approved' => 0
                    ),
                    'total' => array(
                        'requested' => 0,
                        'approved' => 0,
                    )
                );
            }

        }

        /* Fill the students array with data */
        while ($row = $results->fetch()) {

            $student_id = $row['student_id'];
            $accepted = $row['accepted_id'];
            $approved = $row['approved_id'];
            $request_type = $row['request_type_name'];

            /* Work magic on the row for the current student */
            /* count all requests */
            $data[$student_id][$request_type]['requested']++;
            $data[$student_id]['total']['requested']++;

            /* tally approved (ie; completed) requests*/
            if ($approved == 2) {
                $data[$student_id][$request_type]['approved']++;
                $data[$student_id]['total']['approved']++;
            }
        }
        return $data;

    }

    /**
     * MySQL to get skills info
     * Used in Skills Finder report
     */
    public function getStudentSkillsFinderData($skill_type, $students, $sites = NULL, $start_date = null, $end_date = null)
    {
        // sanity check IDs
        foreach ($students as $id) {
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $studentIds = implode(",", $students);

        // figure out search criteria
        if ($sites) {
            // sanity check IDs
            foreach ($sites as $id) {
                if (!is_numeric($id)) {
                    return FALSE; // @todo probably should do some real error handling
                }
            }
            $siteIds = implode(",", $sites);
        } else {
            $siteIds = null;
        }

        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        $method = "get" . $skill_type . "Data";

        // ALS skills returns multiple result sets, so format accordingly
        if ($skill_type == "ALSSkills") {
            $result_array = $this->{$method}($studentIds, $siteIds, $start_date, $end_date);
        } else {
            $result_array = array($this->{$method}($studentIds, $siteIds, $start_date, $end_date));
        }

        // loop through the result rows and format the data
        $skillData = array();
        $shift_summary_display_helper = new \Fisdap_View_Helper_ShiftSummaryDisplayHelper();

        foreach ($result_array as $results) {
            while ($row = $results->fetch()) {
                //var_export($row);
                // format standard stuff first
                $start_datetime = new \DateTime($row['start_datetime']);
                $shift_data = array('shift_id' => $row['Shift_id'],
                    'start_datetime' => $start_datetime,
                    'type' => $row['Type']);
                $summary_options = array('display_size' => 'small', 'sortable' => true);
                $shift_info = $shift_summary_display_helper->shiftSummaryDisplayHelper($shift_data, null, null, $summary_options);

                $subject_type = $row['SubjectType'];
                if ($subject_type == NULL) {
                    $subject_type = 'Human - live';
                }
                $patient_id = $row['patient_id'];
                if ($patient_id > 0) {
                    $patient_summary = \Fisdap\Entity\Patient::formatSummaryLine($row);
                } else {
                    $patient_summary = "N/A";
                }

                // add standard stuff
                $row_data = array(array('data' => $shift_info, 'class' => 'center'),
                    array('data' => $subject_type, 'class' => 'center'),
                    array('data' => $patient_summary, 'class' => 'center')
                );

                // add O/P column to all but the complaints report
                $performed_by = $row['performed_by'];
                if ($skill_type != "Complaints" && $skill_type != "Impressions") {
                    $row_data[] = array('data' => $this::get_printable_01($performed_by, 'O', 'P'), 'class' => 'center');
                }

                // now add custom data based on skill type
                switch ($skill_type) {
                    case "Vital":
                        $sys_bp = $row['systolic_bp'];
                        $dias_bp = $row['diastolic_bp'];
                        if ($sys_bp < 0 || $dias_bp < 0 || $sys_bp == NULL || $dias_bp == NULL) {
                            $bp = NULL;
                        } else {
                            $bp = $sys_bp . '/' . $dias_bp;
                        }

                        $row_data[] = array('data' => $this::value_or_na($bp), 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['pulse_rate']), 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['resp_rate']), 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['spo2']), 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['blood_glucose']), 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['apgar']), 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['gcs']), 'class' => 'center');
                        break;
                    case "Airway":
                        $row_data[] = array('data' => $row['name'], 'class' => 'center');
                        $row_data[] = array('data' => $this::get_printable_01($row['is_als'], 'BLS', 'ALS'), 'class' => 'center');
                        $row_data[] = array('data' => $this::get_printable_01($row['success']), 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['size']), 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['attempts']), 'class' => 'center');
                        break;
                    case "CardiacIntervention":
                        $treatmentOP = $this::value_or_na(($row['treatment_name']) ? $this::get_printable_01($row['treatment_performed_by'], 'O', 'P') : NULL);
                        $treatment = $this::value_or_na($row['treatment_name']);
                        $row_data[] = array('data' => $row['rhythm_name'], 'class' => 'center');
                        $row_data[] = array('data' => $this::get_printable_01($row['twelve_lead']), 'class' => 'center');
                        $row_data[] = array('data' => $treatmentOP, 'class' => 'center');
                        $row_data[] = array('data' => $treatment, 'class' => 'center');
                        break;
                    case "Iv":
                        $iv_site = $row['site_name'];
                        if ($iv_site !== NULL) {
                            $side = $row['side'];
                            if ($side !== NULL) {
                                $iv_site = $iv_site . " - " . $side;
                            }
                        } else {
                            $iv_site = $this::value_or_na($site_name);
                        }
                        $row_data[] = array('data' => $row['procedure_name'], 'class' => 'center');
                        $row_data[] = array('data' => $iv_site, 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['gauge']), 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['fluid_name']), 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['attempts']), 'class' => 'center');
                        $row_data[] = array('data' => $this::get_printable_01($row['success']), 'class' => 'center');
                        break;
                    case "Med":
                        $row_data[] = array('data' => $row['type_name'], 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['route_name']), 'class' => 'center');
                        $row_data[] = array('data' => $row['dose'], 'class' => 'center');
                        break;
                    case "ALSSkills":
                        $row_data[] = array('data' => $row['procedure_name'], 'class' => 'left');
                        $row_data[] = array('data' => $this::get_printable_01($row['success']), 'class' => 'center');
                        $row_data[] = array('data' => $this::value_or_na($row['attempts']), 'class' => 'center');
                        break;
                    case "OtherIntervention":
                        $row_data[] = array('data' => $row['name'], 'class' => 'center');
                        break;
                    case "Complaints":
                        $row_data[] = array('data' => $row['name'], 'class' => 'center');
                        $row_data[] = array('data' => $this::get_printable_01($row['exam'], 'O', 'P'), 'class' => 'center');
                        $row_data[] = array('data' => $this::get_printable_01($row['interview'], 'O', 'P'), 'class' => 'center');
                        $row_data[] = array('data' => $this::get_printable_01($row['team_lead'], 'O', 'P'), 'class' => 'center');
						break;
					case "Impressions":                        
                        $row_data[] = array('data' => $row['primary_impression_name'], 'class' => 'center');
                        $row_data[] = array('data' => $row['secondary_impression_name'], 'class' => 'center');
                        $row_data[] = array('data' => $this::get_printable_01($row['exam'], 'O', 'P'), 'class' => 'center');
                        $row_data[] = array('data' => $this::get_printable_01($row['interview'], 'O', 'P'), 'class' => 'center');
                        $row_data[] = array('data' => $this::get_printable_01($row['team_lead'], 'O', 'P'), 'class' => 'center');
                        break;
                }

                $skillData[$row['Student_id']][$row['skill_id']] = $row_data;
            }
        }

        return $skillData;
    }

    public function getVitalData($studentIds, $siteIds, $start_date, $end_date, $subjectIds = null)
    {
        $site_clause = $siteIds ? "AND S.AmbServ_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND S.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND S.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";
        $subject_clause = $subjectIds ? "AND V.subject_id IN (" . $subjectIds . ") " : "";

        $sql = "SELECT *, CONCAT(ST.name, ' - ', ST.type) as SubjectType, V.id as skill_id, " .
            "E.name as ethnicity, G.name as gender " .
            "FROM ShiftData S, fisdap2_vitals V " .
            "LEFT JOIN fisdap2_vitals_skins " .
            "ON V.id = fisdap2_vitals_skins.vital_id " .
            "LEFT JOIN fisdap2_vitals_lung_sounds " .
            "ON V.id = fisdap2_vitals_lung_sounds.vital_id " .
            "LEFT JOIN fisdap2_runs R " .
            "ON V.run_id = R.id " .
            "LEFT JOIN fisdap2_patients P ON R.id = P.run_id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = P.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = P.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON V.subject_id = ST.id " .
            "WHERE V.shift_id = S.Shift_id " .
            "AND V.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND V.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "AND ((V.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "GROUP BY V.id " .
            "ORDER BY S.start_datetime ASC, V.skill_order ASC";

        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);

        return $results;
    }

    public function getAirwayData($studentIds, $siteIds, $start_date, $end_date, $subjectIds = null)
    {
        $site_clause = $siteIds ? "AND S.AmbServ_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND S.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND S.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";
        $subject_clause = $subjectIds ? "AND A.subject_id IN (" . $subjectIds . ") " : "";

        $sql = "SELECT *, CONCAT(ST.name, ' - ', ST.type) as SubjectType, AT.name as name, A.id as skill_id, " .
            "E.name as ethnicity, G.name as gender " .
            "FROM ShiftData S, fisdap2_airway_procedure AT, fisdap2_airways A " .
            "LEFT JOIN fisdap2_runs R " .
            "ON A.run_id = R.id " .
            "LEFT JOIN fisdap2_patients P ON R.id = P.run_id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = P.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = P.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON A.subject_id = ST.id " .
            "WHERE A.shift_id = S.Shift_id " .
            "AND A.procedure_id = AT.id " .
            "AND A.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND A.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "AND ((A.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, A.skill_order ASC";

        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);

        return $results;
    }

    public function getCardiacInterventionData($studentIds, $siteIds, $start_date, $end_date, $subjectIds = null)
    {
        $site_clause = $siteIds ? "AND S.AmbServ_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND S.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND S.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";
        $subject_clause = $subjectIds ? "AND C.subject_id IN (" . $subjectIds . ") " : "";

        $sql = "SELECT *, RT.name as rhythm_name, P.name as treatment_name, " .
            "E.name as ethnicity, G.name as gender, " .
            "M.name as method, PM.name as pacing_method, " .
            "C.rhythm_performed_by as performed_by, C.performed_by as treatment_performed_by, " .
            "CONCAT(ST.name, ' - ', ST.type) as SubjectType, C.id as skill_id " .
            "FROM ShiftData S, fisdap2_cardiac_interventions C " .
            "LEFT JOIN fisdap2_runs R " .
            "ON C.run_id = R.id " .
            "LEFT JOIN fisdap2_patients PT ON R.id = PT.run_id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = PT.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = PT.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON C.subject_id = ST.id " .
            "LEFT JOIN fisdap2_rhythm_type RT " .
            "ON C.rhythm_type_id = RT.id " .
            "LEFT JOIN fisdap2_cardiac_procedure P " .
            "ON C.procedure_id = P.id " .
            "LEFT JOIN fisdap2_cardiac_procedure_method M " .
            "ON C.procedure_method_id = M.id " .
            "LEFT JOIN fisdap2_cardiac_pacing_method PM " .
            "ON C.pacing_method_id = PM.id " .
            "WHERE C.shift_id = S.Shift_id " .
            "AND C.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND C.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "AND ((C.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, C.skill_order ASC";

        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);

        return $results;
    }

    public function getIvData($studentIds, $siteIds, $start_date, $end_date, $subjectIds = null)
    {
        $site_clause = $siteIds ? "AND S.AmbServ_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND S.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND S.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";
        $subject_clause = $subjectIds ? "AND I.subject_id IN (" . $subjectIds . ") " : "";

        $sql = "SELECT *, CONCAT(ST.name, ' - ', ST.type) as SubjectType, I.id as skill_id, " .
            "E.name as ethnicity, G.name as gender, " .
            "P.name as procedure_name, site.name as site_name, fluid.name as fluid_name " .
            "FROM ShiftData S, fisdap2_iv_procedure P, fisdap2_ivs I " .
            "LEFT JOIN fisdap2_runs R " .
            "ON I.run_id = R.id " .
            "LEFT JOIN fisdap2_patients PT ON R.id = PT.run_id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = PT.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = PT.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON I.subject_id = ST.id " .
            "LEFT JOIN fisdap2_iv_site site " .
            "ON I.site_id = site.id " .
            "LEFT JOIN fisdap2_iv_fluid fluid " .
            "ON I.fluid_id = fluid.id " .
            "WHERE I.shift_id = S.Shift_id " .
            "AND I.procedure_id = P.id " .
            "AND I.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND I.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "AND ((I.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, I.skill_order ASC";

        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);

        return $results;
    }

    /**
     * @param $studentIds Student ids to be included in query
     * @param $siteIds Site ids to be included in query
     * @param $start_date date indicating lower bound for query data
     * @param $end_date date indicating upper bound for query data
     * @param null $subjectIds desired patient subject type ids
     * @param bool $countO2 flag determining whether to include oxygen admin as a med
     * @return mixed $results query result array
     */
    public function getMedData($studentIds, $siteIds, $start_date, $end_date, $subjectIds = null, $countO2 = true)
    {
        $site_clause = $siteIds ? "AND S.AmbServ_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND S.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND S.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";
        $subject_clause = $subjectIds ? "AND M.subject_id IN (" . $subjectIds . ") " : "";

        // should we count oxygen?
        if (!$countO2) {
            $oxygen_clause = "AND M.medication_id != 25 ";
        }

        $sql = "SELECT *, CONCAT(ST.name, ' - ', ST.type) as SubjectType, M.id as skill_id, " .
            "E.name as ethnicity, G.name as gender, " .
            "T.name as type_name, RT.name as route_name " .
            "FROM ShiftData S, fisdap2_med_type T, fisdap2_meds M " .
            "LEFT JOIN fisdap2_runs R " .
            "ON M.run_id = R.id " .
            "LEFT JOIN fisdap2_patients P ON R.id = P.run_id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = P.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = P.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON M.subject_id = ST.id " .
            "LEFT JOIN fisdap2_med_route RT " .
            "ON M.route_id = RT.id " .
            "WHERE M.shift_id = S.Shift_id " .
            "AND M.medication_id = T.id " .
            $oxygen_clause .
            "AND M.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND M.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "AND ((M.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, M.skill_order ASC";

        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);

        return $results;
    }

    public function getALSSkillsData($studentIds, $siteIds, $start_date, $end_date, $subjectIds = null)
    {
        $site_clause = $siteIds ? "AND S.AmbServ_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND S.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND S.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";
        $subject_clause = $subjectIds ? "AND Skill.subject_id IN (" . $subjectIds . ") " : "";

        $db = \Zend_Registry::get('db');
        $results = array();

        // get ALL ivs (all IVs are ALS)
        $iv_sql = "SELECT *, CONCAT(ST.name, ' - ', ST.type) as SubjectType, CONCAT('iv_', Skill.id) as skill_id, " .
            "E.name as ethnicity, G.name as gender, " .
            "CONCAT('Venous Access: ', P.name) as procedure_name " .
            "FROM ShiftData S, fisdap2_iv_procedure P, fisdap2_ivs Skill " .
            "LEFT JOIN fisdap2_runs R " .
            "ON Skill.run_id = R.id " .
            "LEFT JOIN fisdap2_patients PT ON Skill.patient_id = PT.id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = PT.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = PT.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON Skill.subject_id = ST.id " .
            "WHERE Skill.shift_id = S.Shift_id " .
            "AND Skill.procedure_id = P.id " .
            "AND Skill.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND Skill.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "AND ((Skill.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, Skill.skill_order ASC";
        $results[] = $db->query($iv_sql);

        // get cardiac arrest patients
        $arrest_sql = "SELECT *, CONCAT(ST.name, ' - ', ST.type) as SubjectType, CONCAT('patient_', Skill.id) as skill_id, " .
            "E.name as ethnicity, G.name as gender, " .
            "Skill.id as patient_id, 'Impression: Cardiac Arrest' as procedure_name " .
            "FROM ShiftData S, fisdap2_patients Skill " .
            "LEFT JOIN fisdap2_runs R " .
            "ON Skill.run_id = R.id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = Skill.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = Skill.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON Skill.subject_id = ST.id " .
            "WHERE Skill.shift_id = S.Shift_id " .
            "AND (Skill.primary_impression_id = 4 OR Skill.secondary_impression_id = 4) " .
            "AND Skill.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND S.soft_deleted = 0 " .
            "AND ((Skill.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, Skill.id ASC";
        $results[] = $db->query($arrest_sql);

        // get non-O2 meds
        $med_sql = "SELECT *, CONCAT(ST.name, ' - ', ST.type) as SubjectType, CONCAT('med_', Skill.id) as skill_id, " .
            "E.name as ethnicity, G.name as gender, " .
            "CONCAT('Meds: ', T.name) as procedure_name " .
            "FROM ShiftData S, fisdap2_med_type T, fisdap2_meds Skill " .
            "LEFT JOIN fisdap2_runs R " .
            "ON Skill.run_id = R.id " .
            "LEFT JOIN fisdap2_patients PT ON Skill.patient_id = PT.id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = PT.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = PT.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON Skill.subject_id = ST.id " .
            "WHERE Skill.shift_id = S.Shift_id " .
            "AND Skill.medication_id = T.id " .
            "AND Skill.medication_id != 25 " .
            "AND Skill.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND Skill.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "AND ((Skill.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, Skill.skill_order ASC";
        $results[] = $db->query($med_sql);

        // get ALL cardiac interventions
        $cardiac_sql = "SELECT *, CONCAT('Cardiac: ', RT.name) as procedure_name, Skill.rhythm_performed_by as performed_by, " .
            "E.name as ethnicity, G.name as gender, " .
            "CONCAT(ST.name, ' - ', ST.type) as SubjectType, CONCAT('cardiac_', Skill.id) as skill_id " .
            "FROM ShiftData S, fisdap2_cardiac_interventions Skill " .
            "LEFT JOIN fisdap2_runs R " .
            "ON Skill.run_id = R.id " .
            "LEFT JOIN fisdap2_patients PT ON Skill.patient_id = PT.id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = PT.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = PT.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON Skill.subject_id = ST.id " .
            "LEFT JOIN fisdap2_rhythm_type RT " .
            "ON Skill.rhythm_type_id = RT.id " .
            "WHERE Skill.shift_id = S.Shift_id " .
            "AND Skill.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND Skill.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "AND ((Skill.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, Skill.skill_order ASC";
        $results[] = $db->query($cardiac_sql);

        // get ALS airway data
        $airway_sql = "SELECT *, CONCAT(ST.name, ' - ', ST.type) as SubjectType, " .
            "E.name as ethnicity, G.name as gender, " .
            "CONCAT('Airway: ', AT.name) as procedure_name, CONCAT('airway_', Skill.id) as skill_id " .
            "FROM ShiftData S, fisdap2_airway_procedure AT, fisdap2_airways Skill " .
            "LEFT JOIN fisdap2_runs R " .
            "ON Skill.run_id = R.id " .
            "LEFT JOIN fisdap2_patients PT ON Skill.patient_id = PT.id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = PT.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = PT.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON Skill.subject_id = ST.id " .
            "WHERE Skill.shift_id = S.Shift_id " .
            "AND Skill.procedure_id IN (1,3,5,6,9,10,11,14,15,17,18,19,20,21,22,23,25) " .
            "AND Skill.procedure_id = AT.id " .
            "AND Skill.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND Skill.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "AND ((Skill.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, Skill.skill_order ASC";
        $results[] = $db->query($airway_sql);

        // get other ALS skills
        $other_sql = "SELECT *, CONCAT('Other: ', P.name) as procedure_name, " .
            "E.name as ethnicity, G.name as gender, " .
            "CONCAT(ST.name, ' - ', ST.type) as SubjectType, CONCAT('other_', Skill.id) as skill_id " .
            "FROM ShiftData S, fisdap2_other_procedure P, fisdap2_other_interventions Skill " .
            "LEFT JOIN fisdap2_runs R " .
            "ON Skill.run_id = R.id " .
            "LEFT JOIN fisdap2_patients PT ON Skill.patient_id = PT.id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = PT.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = PT.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON Skill.subject_id = ST.id " .
            "WHERE Skill.shift_id = S.Shift_id " .
            "AND Skill.procedure_id = P.id " .
            "AND Skill.procedure_id NOT IN (27,30,31,32,33,35,36,37,38,39,40,41,42,43,44,45,46) " .
            "AND Skill.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND Skill.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "AND ((Skill.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, Skill.skill_order ASC";
        $results[] = $db->query($other_sql);

        return $results;
    }

    public function getOtherInterventionData($studentIds, $siteIds, $start_date, $end_date, $subjectIds = null)
    {
        $site_clause = $siteIds ? "AND S.AmbServ_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND S.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND S.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";
        $subject_clause = $subjectIds ? "AND I.subject_id IN (" . $subjectIds . ") " : "";

        $sql = "SELECT *, P.name as name, CONCAT(ST.name, ' - ', ST.type) as SubjectType, I.id as skill_id, " .
            "E.name as ethnicity, G.name as gender " .
            "FROM ShiftData S, fisdap2_other_procedure P, fisdap2_other_interventions I " .
            "LEFT JOIN fisdap2_runs R " .
            "ON I.run_id = R.id " .
            "LEFT JOIN fisdap2_patients PT ON R.id = PT.run_id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = PT.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = PT.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON I.subject_id = ST.id " .
            "WHERE I.shift_id = S.Shift_id " .
            "AND I.procedure_id = P.id " .
            "AND I.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $subject_clause .
            "AND I.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "AND ((I.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, I.skill_order ASC";

        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);

        return $results;
    }

    public function getComplaintsData($studentIds, $siteIds, $start_date, $end_date)
    {
        $site_clause = $siteIds ? "AND S.AmbServ_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND S.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND S.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";

        $sql = "SELECT *, CONCAT(ST.name, ' - ', ST.type) as SubjectType, C.name as name, CONCAT(P.id, '_', C.id) as skill_id, " .
            "E.name as ethnicity, G.name as gender " .
            "FROM ShiftData S, fisdap2_complaint C, fisdap2_patients_complaints PC, fisdap2_patients P " .
            "LEFT JOIN fisdap2_runs R " .
            "ON P.run_id = R.id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = P.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = P.gender_id " .
            "LEFT JOIN fisdap2_subject ST " .
            "ON P.subject_id = ST.id " .
            "WHERE P.shift_id = S.Shift_id " .
            "AND PC.patient_id = P.id " .
            "AND PC.complaint_id = C.id " .
            "AND P.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            "AND S.soft_deleted = 0 " .
            "AND ((P.run_id IS NULL) OR (R.soft_deleted = 0)) " .
            "ORDER BY S.start_datetime ASC, P.id ASC";

        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);

        return $results;
    }
	
    public function getImpressionsData($studentIds, $siteIds, $start_date, $end_date)
    {
       $site_clause = $siteIds ? "AND S.AmbServ_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND S.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND S.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";

        $sql = "SELECT *, CONCAT(ST.name, ' - ', ST.type) as SubjectType, P.id as patient_id, CONCAT('Patient',P.id) as skill_id, " .
            "E.name as ethnicity, G.name as gender, i1.name as primary_impression_name, i2.name as secondary_impression_name " .
            "FROM ShiftData S, fisdap2_patients P " .
            "LEFT JOIN fisdap2_runs R " .
            "ON P.run_id = R.id " .
            "LEFT JOIN fisdap2_ethnicity E ON E.id = P.ethnicity_id " .
            "LEFT JOIN fisdap2_gender G ON G.id = P.gender_id " .
			"LEFT JOIN fisdap2_impression i1 ON i1.id = P.primary_impression_id " .
            "LEFT JOIN fisdap2_impression i2 ON i2.id = P.secondary_impression_id " .
            "LEFT JOIN fisdap2_subject ST " .			
            "ON P.subject_id = ST.id " .
            "WHERE P.shift_id = S.Shift_id " .
			"AND P.primary_impression_id != '' " .
			"AND P.age != '' " .
            "AND P.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            "AND S.soft_deleted = 0 " .
            "AND ((P.run_id IS NULL) OR (R.soft_deleted = 0)) " .
			"GROUP BY P.id ".
            "ORDER BY S.start_datetime ASC, P.id ASC";

        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);

        return $results;
    }

    /**
     * Convert a 0/1 value into a printable string.
     * @param value The 0/1 value to test.
     * @param value0 The value for 0, defaults to 'Y'.
     * @param value1 The value for 1, defaults to 'N'.
     * @return The printable string.
     */
    public static function get_printable_01($value, $value0 = 'N', $value1 = 'Y')
    {
        // return N/A if the value is not really there
        if ($value === NULL) {
            return 'N/A';
        }

        $s = ($value == 1) ? $value1 : $value0;
        return $s;
    }

    /**
     * Return "N/A" if the value is null, otherwise return the value
     * @param value The value.
     * @return "N/A" or the value
     */
    public static function value_or_na($value)
    {
        if ($value === NULL) {
            return "N/A";
        } else {
            return $value;
        }
    }

    public function getRecentActiveConfigs($userContextId, $limit)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("c")
            ->from('\Fisdap\Entity\ReportConfiguration', "c")
            ->join("c.report", "r")
            ->where("c.user_context = ?1")
            ->andWhere("r.standalone = 0")
            ->setParameter(1, $userContextId)
            ->orderBy("c.updated", "DESC")
            ->setMaxResults($limit);

        //$sql = $qb->getQuery()->getSQL();
        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * MySQL to get goals info
     * Used in Accreditation G/H report
     */
    public function getStudentGoalsData($goal, $students, $shiftOptions, $types = null)
    {
        // sanity check IDs
        foreach ($students as $id) {
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $studentIds = implode(",", $students);

        // figure out search criteria
        $sites = $shiftOptions['shiftSites'];
        if ($sites) {
            // sanity check IDs
            foreach ($sites as $id) {
                if (!is_numeric($id)) {
                    return FALSE; // @todo probably should do some real error handling
                }
            }
            $siteIds = implode(",", $sites);
        } else {
            $siteIds = null;
        }

        $subjects = $shiftOptions['subjectTypes'];
        if ($subjects) {
            // sanity check IDs
            foreach ($subjects as $id) {
                if (!is_numeric($id)) {
                    return FALSE; // @todo probably should do some real error handling
                }
            }
            $subjectIds = implode(",", $subjects);
        } else {
            $subjectIds = null;
        }

        $start_date = $shiftOptions['startDate'];
        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        $end_date = $shiftOptions['endDate'];
        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        $audited = $shiftOptions['audited'];

        $site_clause = $siteIds ? "AND S.AmbServ_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND S.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND S.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";
        $audited_clause = $audited ? "AND S.Audited = 1 " : "";
        $site_type_clause = $types ? "AND S.Type IN ('" . implode("', '", $types) . "') " : "";

        $goal_sql = $goal->getGoalSQL();
        $goal_tables = $goal_sql['tables'];
        $goal_clause = $goal_sql['clause'];
        // only look for teamlead if we're just looking ar field shifts
        // $goal_tl = (implode("', '", $types) == 'field') ? $goal_sql['team_lead'] : "";

        // ^ That's not correct. Both lab and field can have team lead, so we need to look at both of those.
        // Additionally, this check didn't take into consideration the "total" call, which included clinical, causing
        // this check to fail. - Nick 5/27/2017
        $goal_tl = $goal->team_lead ? "AND CASE WHEN S.Type NOT IN ('clinical') THEN (P.team_lead = 1) ELSE (1=1) END " : "";

        $category = $goal->def->category;

        if ($category == "Skills") {
            $subject_clause = $subjectIds ? "AND SK.subject_id IN (" . $subjectIds . ") " : "";
            $sql = "SELECT S.Student_id as student_id, count(*) as count " .
                "FROM ShiftData S " .
                $goal_tables .
                "WHERE SK.shift_id = S.Shift_id " .
                "AND SK.student_id IN ({$studentIds}) " .
                "AND SK.soft_deleted = 0 " .
                "AND S.soft_deleted = 0 " .
                "AND ((SK.run_id IS NULL) OR (R.soft_deleted = 0)) " .
                $start_date_clause .
                $end_date_clause .
                $site_clause .
                $audited_clause .
                $site_type_clause .
                $subject_clause .
                $goal_clause .
                "GROUP BY S.Student_id";
        } else {
            $subject_clause = $subjectIds ? "AND P.subject_id IN (" . $subjectIds . ") " : "";
            $sql = "SELECT S.Student_id as student_id, count(*) as count " .
                "FROM ShiftData S, fisdap2_runs R, fisdap2_patients P " .
                $goal_tables .
                "WHERE P.shift_id = S.Shift_id " .
                "AND P.run_id = R.id " .
                "AND P.student_id IN ({$studentIds}) " .
                $start_date_clause .
                $end_date_clause .
                $site_clause .
                $audited_clause .
                $site_type_clause .
                $subject_clause .
                $goal_clause .
                $goal_tl .
                "AND R.soft_deleted = 0 " .
                "AND S.soft_deleted = 0 " .
                "GROUP BY S.Student_id";
        }
        //echo $sql."<br><br>";
        //return;
        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);

        // ok, now make the results usable
        while ($row = $results->fetch()) {
            $resultArray[$row['student_id']] = $row['count'];
        }

        // now go through and make sure ALL the students have results
        // we do this to give the students who had no results a 0 instead of ignoring them
        foreach ($students as $student_id) {
            $result = ($resultArray[$student_id]) ? $resultArray[$student_id] : 0;
            $studentResults[$student_id] = $result;
        }
        return $studentResults;
    }

    /**
     * MySQL to get goals info
     * Used in Accreditation E report
     */
    public function getClinicalSiteData($site_id, $students, $start_date, $end_date)
    {

        // sanity check IDs while formatting form data
        if (!is_numeric($site_id)) {
            return FALSE; // @todo probably should do some real error handling
        }

        foreach ($students as $id) {
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $student_ids = implode(",", $students);

        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        $program_id = \Fisdap\Entity\User::getLoggedInUser()->getCurrentProgram()->id;

        $start_date_clause = $start_date ? "AND sh.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND sh.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";

        $sql = "SELECT ab.BaseName as Rotation, " .
            "(count(distinct sh.Shift_id) / count(distinct sh.start_datetime)) as StudentsPerShift, " .
            "(count(distinct sh.Shift_id) / count(distinct st.Student_id)) as ShiftsPerStudent, " .
            "avg(sh.Hours) as HoursPerShift, " .
            "count(distinct sh.Shift_id) as AnnualVisits " .
            "FROM AmbulanceServices a, AmbServ_Bases ab, StudentData st, ShiftData sh " .
            "WHERE a.AmbServ_id = $site_id " .
            "AND st.Program_id = $program_id " .
            $start_date_clause .
            $end_date_clause .
            "AND sh.soft_deleted = 0 " .
            "AND st.Student_id = sh.Student_id " .
            "AND st.Student_id in ($student_ids) " .
            "AND sh.AmbServ_id = a.AmbServ_id " .
            "AND a.AmbServ_id = ab.AmbServ_id " .
            "AND ab.Base_id = sh.StartBase_id " .
            "GROUP BY ab.Base_id " .
            "ORDER BY ab.BaseName";
        //echo $sql."<br><br>";
        //return;
        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);


        // ok, now make the results usable
        while ($row = $results->fetch()) {
            $resultArray[] = $row;
        }

        return $resultArray;
    }

    /**
     * MySQL to get goals info
     * Used in Accreditation F report
     */
    public function getFieldSiteData($site_id, $students, $start_date, $end_date)
    {

        // sanity check IDs while formatting form data
        if (!is_numeric($site_id)) {
            return FALSE; // @todo probably should do some real error handling
        }

        foreach ($students as $id) {
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $student_ids = implode(",", $students);

        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        $program_id = \Fisdap\Entity\User::getLoggedInUser()->getCurrentProgram()->id;

        $start_date_clause = $start_date ? "AND sh.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND sh.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";

        $sql = "SELECT " .
            "COUNT(r.id) AS num_runs, " . // number of runs
            "SUM(p.primary_impression_id = 4 OR p.secondary_impression_id = 4) AS arrests, " . // # cardiac arrests
            "SUM(i1.nsc_type = 'trauma' OR i2.nsc_type = 'trauma') AS trauma, " . // # trauma impressions
            "SUM((p.patient_criticality_id = 3 OR p.transport_mode_id IN (2, 3)) AND (i1.nsc_type = 'trauma' OR i1.nsc_type = 'trauma')) AS critical, " . // # 'crictical' trauma patients
            "SUM(p.age <= 18) AS peds, " . // # pediatrics
            "SUM((p.primary_impression_id = 3 OR p.secondary_impression_id = 3) AND (p.primary_impression_id != 4 AND p.secondary_impression_id != 4)) AS cardiac, " . // # non-arrest cardiac
            "(COUNT(DISTINCT sh.Shift_id) / COUNT(distinct st.Student_id)) AS ShiftsPerStudent, " .
            "(COUNT(r.id) / COUNT(distinct sh.Shift_id)) AS RunsPerShift " .
            "FROM AmbulanceServices a, StudentData st, ShiftData sh " .
            "LEFT JOIN fisdap2_runs r ON (r.shift_id = sh.Shift_id AND r.soft_deleted = 0) " .
            "LEFT JOIN fisdap2_patients p ON r.id = p.run_id " .
            "LEFT JOIN fisdap2_impression i1 ON i1.id = p.primary_impression_id " .
            "LEFT JOIN fisdap2_impression i2 ON i2.id = p.secondary_impression_id " .
            "WHERE a.AmbServ_id = $site_id " .
            "AND st.Program_id = $program_id " .
            $start_date_clause .
            $end_date_clause .
            "AND sh.soft_deleted = 0 " .
            "AND st.Student_id = sh.Student_id " .
            "AND st.Student_id IN ($student_ids) " .
            "AND sh.AmbServ_id = a.AmbServ_id";

        //echo $sql."<br><br>";
        //return;
        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);


        // ok, now make the results usable
        while ($row = $results->fetch()) {
            $resultArray[] = $row;
        }

        return $resultArray;
    }

    /**
     * MySQL to get hours info
     * Used in Accreditation F report
     */
    public function getFieldSiteHoursData($site_id, $students, $start_date, $end_date)
    {

        // sanity check IDs while formatting form data
        if (!is_numeric($site_id)) {
            return FALSE; // @todo probably should do some real error handling
        }

        foreach ($students as $id) {
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $student_ids = implode(",", $students);

        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        $program_id = \Fisdap\Entity\User::getLoggedInUser()->getCurrentProgram()->id;

        $start_date_clause = $start_date ? "AND sh.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND sh.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";

        $sql = "SELECT " .
            "avg(sh.Hours) as HoursPerShift " . // hours per shift
            "FROM AmbulanceServices a, StudentData st, ShiftData sh " .
            "WHERE a.AmbServ_id = $site_id " .
            "AND st.Program_id = $program_id " .
            $start_date_clause .
            $end_date_clause .
            "AND st.Student_id = sh.Student_id " .
            "AND st.Student_id IN ($student_ids) " .
            "AND sh.AmbServ_id = a.AmbServ_id";

        //echo $sql."<br><br>";
        //return;
        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);


        // ok, now make the results usable
        while ($row = $results->fetch()) {
            $resultArray[] = $row;
        }

        return $resultArray;
    }

    public function getPatientData($studentIds, $siteIds, $subjectIds, $start_date, $end_date)
    {
        $resultArray = array();
        $student_clause = $studentIds ? "AND sh.Student_id IN (" . implode(", ", $studentIds) . ") " : "";
        $site_clause = $siteIds ? "AND sh.AmbServ_id IN (" . implode(", ", $siteIds) . ") " : "";
        $subject_clause = $subjectIds ? "AND p.subject_id IN (" . implode(", ", $subjectIds) . ") " : "";
        $start_date_clause = $start_date ? "AND sh.start_datetime >= '" . date('Y-m-d', strtotime($start_date)) . "' " : "";
        $end_date_clause = $end_date ? "AND sh.start_datetime <= '" . date('Y-m-d', strtotime($end_date)) . "' " : "";

        $sql = "SELECT p.*, sh.type, " .
            "i1.name as primary_impression, i2.name as secondary_impression, " .
            "c.name as cause, g.name as gender, " .
            "w.name as witness, pr.name as pulse_return " .
            "FROM ShiftData sh, fisdap2_patients p " .
            "LEFT JOIN fisdap2_impression i1 ON i1.id = p.primary_impression_id " .
            "LEFT JOIN fisdap2_impression i2 ON i2.id = p.secondary_impression_id " .
            "LEFT JOIN fisdap2_cause c ON c.id = p.cause_id " .
            "LEFT JOIN fisdap2_gender g ON g.id = p.gender_id " .
            "LEFT JOIN fisdap2_pulse_return pr ON pr.id = p.pulse_return_id " .
            "LEFT JOIN fisdap2_witness w ON w.id = p.witness_id " .
            "WHERE sh.Shift_id = p.shift_id " .
            $student_clause .
            $site_clause .
            $subject_clause .
            $start_date_clause .
            $end_date_clause;

        //echo $sql."<br><br>";
        //return;
        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);

        // ok, now make the results usable
        while ($row = $results->fetch()) {
            $resultArray[] = $row;
        }

        return $resultArray;
    }

    public function getComplaintData($studentIds, $siteIds, $subjectIds, $start_date, $end_date)
    {
        $resultArray = array();

        $student_clause = $studentIds ? "AND sh.Student_id IN (" . implode(", ", $studentIds) . ") " : "";
        $site_clause = $siteIds ? "AND sh.AmbServ_id IN (" . implode(", ", $siteIds) . ") " : "";
        $subject_clause = $subjectIds ? "AND p.subject_id IN (" . implode(", ", $subjectIds) . ") " : "";
        $start_date_clause = $start_date ? "AND sh.start_datetime >= '" . date('Y-m-d', strtotime($start_date)) . "' " : "";
        $end_date_clause = $end_date ? "AND sh.start_datetime <= '" . date('Y-m-d', strtotime($end_date)) . "' " : "";

        $sql = "SELECT p.*, sh.type, c.name as complaint " .
            "FROM ShiftData sh, fisdap2_patients p " .
            "LEFT JOIN fisdap2_patients_complaints pc ON pc.patient_id = p.id " .
            "LEFT JOIN fisdap2_complaint c ON pc.complaint_id = c.id " .
            "WHERE sh.Shift_id = p.shift_id " .
            $student_clause .
            $site_clause .
            $subject_clause .
            $start_date_clause .
            $end_date_clause;

        //echo $sql."<br><br>";
        //return;
        // run MySQL query
        $db = \Zend_Registry::get('db');
        $results = $db->query($sql);

        // ok, now make the results usable
        while ($row = $results->fetch()) {
            $resultArray[] = $row;
        }

        return $resultArray;
    }

    public function getEurekaProcedureData($procedure_type, array $procedure_ids, array $site_ids, array $patient_type_ids, array $student_ids, $start_date = null, $end_date = null)
    {
        $qb = $this->_em->createQueryBuilder();

        if ($procedure_type == "other") {
            $procedure_entity_name = "\\Fisdap\\Entity\\OtherIntervention";
        } else if ($procedure_type == "iv") {
            $procedure_entity_name = "\\Fisdap\\Entity\\Iv";
        } else {
            $procedure_entity_name = "\\Fisdap\\Entity\\Airway";
        }

        $qb->select("partial pro_type.{id,success,skill_order,attempts}, partial procedure.{id,name}, partial subject.{id}, partial student.{id,first_name,last_name}, partial shift.{id,start_datetime,type,hours}, partial site.{id,name}, partial base.{id,name}, partial event.{id}, partial preceptor_assoc.{id}, partial preceptor.{id,first_name,last_name}, partial slot.{id}, partial assignment.{id}, partial user_context.{id}, partial user.{id,first_name,last_name}")
            ->from($procedure_entity_name, 'pro_type')
            ->leftJoin('pro_type.procedure', 'procedure')
            ->leftJoin('pro_type.subject', 'subject')
            ->leftJoin('pro_type.student', 'student')
            ->leftJoin('pro_type.shift', 'shift')
            ->leftJoin('shift.site', 'site')
            ->leftJoin('shift.base', 'base')
            ->leftJoin('shift.slot_assignment', 'assignment')
            ->leftJoin('assignment.slot', 'slot')
            ->leftJoin('slot.event', 'event')
            ->leftJoin('event.preceptor_associations', 'preceptor_assoc')
            ->leftJoin('preceptor_assoc.preceptor', 'preceptor')
            ->leftJoin('assignment.user_context', 'user_context')
            ->leftJoin('user_context.user', 'user')
            ->where($qb->expr()->in('procedure.id', $procedure_ids))
            ->andWhere($qb->expr()->in('subject.id', $patient_type_ids))
            ->andWhere($qb->expr()->in('student.id', $student_ids))
            ->andWhere($qb->expr()->in('site.id', $site_ids))
            ->andWhere("pro_type.performed_by = 1");

        $param_count = 1;

        if ($start_date) {
            $qb->andWhere("shift.start_datetime >= ?1");
            $start_datetime_object = date_create($start_date);
            $qb->setParameter(1, $start_datetime_object->format("Y-m-d"));
            $param_count++;
        }

        if ($end_date) {
            $qb->andWhere("shift.start_datetime <= ?" . $param_count);
            $end_datetime_object = date_create($end_date);
            $qb->setParameter($param_count, $end_datetime_object->format("Y-m-d"));
        }

        $qb->orderBy("shift.start_datetime, pro_type.skill_order");

        //DEBUG
        //$logger = \Zend_Registry::get('logger');
        //$logger->debug('Eureka query SQL: ' . $qb->getQuery()->getSQL());

        return $qb->getQuery()->getArrayResult();
    }

    public function getPreceptorSignOffData($eval_type_ids, $site_ids, $student_ids, $start_date, $end_date)
    {
        $qb = $this->_em->createQueryBuilder();

        $partials = "partial rating.{id,value},";
        $partials .= "partial eval_type.{id,name},";
        $partials .= "partial rater_type.{id,name},";
        $partials .= "partial signoff.{id},";
        $partials .= "partial student.{id,first_name,last_name},";
        $partials .= "partial run.{id},";
        $partials .= "partial shift.{id,start_datetime,type,hours},";
        $partials .= "partial site.{id,name},";
        $partials .= "partial base.{id,name}";

        $qb->select($partials)
            ->from("\Fisdap\Entity\PreceptorRating", 'rating')
            ->leftJoin('rating.type', 'eval_type')
            ->leftJoin('rating.rater_type', 'rater_type')
            ->leftJoin('rating.signoff', 'signoff')
            ->leftJoin('signoff.student', 'student')
            ->leftJoin('signoff.run', 'run')
            ->leftJoin('run.shift', 'shift')
            ->leftJoin('shift.site', 'site')
            ->leftJoin('shift.base', 'base')
            ->where($qb->expr()->in('eval_type.id', $eval_type_ids))
            ->andWhere($qb->expr()->in('student.id', $student_ids))
            ->andWhere($qb->expr()->in('site.id', $site_ids));

        $param_count = 1;

        if ($start_date) {
            $qb->andWhere("shift.start_datetime >= ?1");
            $start_datetime_object = date_create($start_date);
            $qb->setParameter(1, $start_datetime_object->format("Y-m-d"));
            $param_count++;
        }

        if ($end_date) {
            $qb->andWhere("shift.start_datetime <= ?" . $param_count);
            $end_datetime_object = date_create($end_date);
            $qb->setParameter($param_count, $end_datetime_object->format("Y-m-d"));
        }

        $qb->orderBy("shift.start_datetime, rating.id");

        return $qb->getQuery()->getArrayResult();
    }

    public function getStudentEvalData($eval_id, $student_ids, $start_date, $end_date)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("partial s.{id,subject_id,evaluator_type,passed,confirmed}")
            ->from("\Fisdap\Entity\EvalSessionLegacy", 's')
            ->where($qb->expr()->in('s.eval_def_id', $eval_id))
            ->andWhere($qb->expr()->in('s.subject_type', 2))
            ->andWhere($qb->expr()->in('s.subject_id', $student_ids));

        $param_count = 1;
        if ($start_date) {
            $qb->andWhere("s.date >= ?1");
            $start_datetime_object = date_create($start_date);
            $qb->setParameter(1, $start_datetime_object->format("Y-m-d"));
            $param_count++;
        }

        if ($end_date) {
            $qb->andWhere("s.date <= ?" . $param_count);
            $end_datetime_object = date_create($end_date);
            $qb->setParameter($param_count, $end_datetime_object->format("Y-m-d"));
        }

        $eval_sessions = $qb->getQuery()->getArrayResult();
        // ok now tally up all the totals
        $evaluator_types = array(1 => 'instructor', 2 => 'student', 3 => 'preceptor');
        $data = array();
        foreach ($eval_sessions as $eval_session) {
            $data[$eval_session['subject_id']][$evaluator_types[$eval_session['evaluator_type']]]['passed'] += $eval_session['passed'];
            $data[$eval_session['subject_id']][$evaluator_types[$eval_session['evaluator_type']]]['confirmed'] += $eval_session['confirmed'];
            $data[$eval_session['subject_id']][$evaluator_types[$eval_session['evaluator_type']]]['both'] += ($eval_session['confirmed'] && $eval_session['passed']) ? 1 : 0;
            $data[$eval_session['subject_id']][$evaluator_types[$eval_session['evaluator_type']]]['total']++;
            $data[$eval_session['subject_id']]['total']++;
        }
        return $data;
    }


    /**
     * MySQL to get goals info
     * Used in CoAEMSP 3c2 report
     */
    public function get3c2Data($goal, $studentId, $shiftOptions)
    {
        // sanity check IDs
        if (!is_numeric($studentId)) {
            return FALSE; // @todo probably should do some real error handling
        }
        $studentIds = $studentId;

        // figure out search criteria
        $sites = $shiftOptions['shiftSites'];
        if ($sites) {
            // sanity check IDs
            foreach ($sites as $id) {
                if (!is_numeric($id)) {
                    return FALSE; // @todo probably should do some real error handling
                }
            }
            $siteIds = implode(",", $sites);
        } else {
            $siteIds = null;
        }

        $subjects = $shiftOptions['subjectTypes'];
        if ($subjects) {
            // sanity check IDs
            foreach ($subjects as $id) {
                if (!is_numeric($id)) {
                    return FALSE; // @todo probably should do some real error handling
                }
            }
            $subjectIds = implode(",", $subjects);
        } else {
            $subjectIds = null;
        }

        $start_date = $shiftOptions['startDate'];
        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        $end_date = $shiftOptions['endDate'];
        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
        }

        $audited = $shiftOptions['audited'];

        $site_clause = $siteIds ? "AND S.AmbServ_id IN (" . $siteIds . ") " : "";
        $start_date_clause = $start_date ? "AND S.start_datetime >= '" . date('Y-m-d', $start_date) . "' " : "";
        $end_date_clause = $end_date ? "AND S.start_datetime <= '" . date('Y-m-d', $end_date) . "' " : "";
        $audited_clause = $audited ? "AND S.Audited = 1 " : "";
        //$site_type_clause = $types ? "AND S.Type IN ('" . implode("', '", $types) . "') " : "";

        $goal_sql = $goal->getGoalSQL();
        $goal_tables = $goal_sql['tables'];
        $goal_clause = $goal_sql['clause'];

        $category = $goal->def->category;

        if ($category == "Skills") {
            $subject_clause = $subjectIds ? "AND SK.subject_id IN (" . $subjectIds . ") " : "";
            $sql = "SELECT count(*) as count " .
                "FROM ShiftData S " .
                $goal_tables .
                "WHERE SK.shift_id = S.Shift_id " .
                "AND SK.student_id IN ({$studentIds}) " .
                "AND SK.soft_deleted = 0 " .
                "AND S.soft_deleted = 0 " .
                "AND ((SK.run_id IS NULL) OR (R.soft_deleted = 0)) " .
                $start_date_clause .
                $end_date_clause .
                $site_clause .
                $audited_clause .
                //$site_type_clause .
                $subject_clause .
                $goal_clause .
                "GROUP BY S.Student_id";
        } else if ($category == "Hours") {
            $sql =
                "SELECT ".
                    "SUM(q1.Hours) as count " .
                "FROM (".
                    "SELECT ".
                        "S.Hours, ".
                        "S.student_id ".
                    "FROM ".
                        "ShiftData S ".
                        $goal_tables .
                    "WHERE ".
                        "S.student_id IN ({$studentIds}) " .
                    $start_date_clause .
                    $end_date_clause .
                    $site_clause .
                    $audited_clause .
                    $goal_clause .
                    "AND ".
                        "S.soft_deleted = 0 " .
                    "GROUP BY S.Shift_id".
                ") AS q1 ".
                "GROUP BY ".
                    "q1.student_id";
        } else {
            $subject_clause = $subjectIds ? "AND P.subject_id IN (" . $subjectIds . ") " : "";
            $sql = "SELECT count(*) as count " .
                "FROM ShiftData S, fisdap2_runs R, fisdap2_patients P " .
                $goal_tables .
                "WHERE P.shift_id = S.Shift_id " .
                "AND P.run_id = R.id " .
                "AND P.student_id IN ({$studentIds}) " .
                $start_date_clause .
                $end_date_clause .
                $site_clause .
                $audited_clause .
                //$site_type_clause .
                $subject_clause .
                $goal_clause .
                //$goal_tl .
                "AND R.soft_deleted = 0 " .
                "AND S.soft_deleted = 0 " .
                "GROUP BY S.Student_id";
        }

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();

        $result = 0;
        while ($row = $stmt->fetch()) {
            $result = $row['count'];
        }

        return $result;
    }


    public function getSurgTechScrubRoleData($eval_ids, $student_id, $start_date, $end_date, $site_ids){


        $qb = $this->_em->createQueryBuilder();

        //get all eval_sessions that match our eval_ids for the student
        $qb->select("partial s.{id, eval_def_id}")
            ->from("\Fisdap\Entity\EvalSessionLegacy", 's')
            ->innerJoin("s.shift", "shift")
            ->innerJoin("shift.site", "site")
            ->where($qb->expr()->in('s.eval_def_id', $eval_ids))
            ->andWhere($qb->expr()->eq('s.subject_type', 2))
            ->andWhere($qb->expr()->in('s.subject_id', $student_id))
            ->andWhere($qb->expr()->in('site.id', $site_ids));

        $param_count = 1;
        if ($start_date) {
            $qb->andWhere("s.date >= ?1");
            $start_datetime_object = date_create($start_date);
            $qb->setParameter(1, $start_datetime_object->format("Y-m-d"));
            $param_count++;
        }

        if ($end_date) {
            $qb->andWhere("s.date <= ?" . $param_count);
            $end_datetime_object = date_create($end_date);
            $qb->setParameter($param_count, $end_datetime_object->format("Y-m-d"));
        }

        $eval_sessions = $qb->getQuery()->getArrayResult();

        //set our base values for all of the evals the user requested
        $data = array();
        if(is_array($eval_ids)) {
            foreach ($eval_ids as $eval_id) {
                $data[$eval_id]['first'] = 0;
                $data[$eval_id]['second'] = 0;
                $data[$eval_id]['obs'] = 0;
                $data[$eval_id]['total'] = 0;
                $data[$eval_id]['eval_id'] = $eval_id;
            }
        }else{
            $data[$eval_ids]['first'] = 0;
            $data[$eval_ids]['second'] = 0;
            $data[$eval_ids]['obs'] = 0;
            $data[$eval_ids]['total'] = 0;
            $data[$eval_ids]['eval_id'] = $eval_ids;
        }

        //get item sessions matching those eval sessions
        foreach($eval_sessions as $eval_session){
            $sql = "SELECT score " .
                "FROM Eval_ItemSessions " .
                "WHERE Eval_ItemSessions.EvalSession_id = " . $eval_session['id'];

            $db = \Zend_Registry::get('db');
            $results = $db->query($sql);

            // make the results easier to work with
            $resultArray = array();

            while ($row = $results->fetch()) {
                $resultArray[] = $row;
            }
            //all of these SurgTech evals have two questions. If the 'score' of the first one is 1, we want to count the value of the second question score.
            if($resultArray[0]['score'] == 1){
                switch($resultArray[1]['score']){
                    case 1:
                        $data[$eval_session['eval_def_id']]['obs']++;
                        $data[$eval_session['eval_def_id']]['total']++;
                        break;
                    case 2:
                        $data[$eval_session['eval_def_id']]['second']++;
                        $data[$eval_session['eval_def_id']]['total']++;
                        break;
                    case 3:
                        $data[$eval_session['eval_def_id']]['first']++;
                        $data[$eval_session['eval_def_id']]['total']++;
                        break;
                }
            }
        }

        return $data;
    }
}
