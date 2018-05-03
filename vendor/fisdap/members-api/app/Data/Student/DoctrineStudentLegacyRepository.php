<?php namespace Fisdap\Data\Student;

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineStudentLegacyRepository
 *
 * @package Fisdap\Data\Student
 */
class DoctrineStudentLegacyRepository extends DoctrineRepository implements StudentLegacyRepository
{
    public function getStudentsWithShiftData($studentIds)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('st', 'shifts', 'shiftSignoff', 'shiftVerification')
            ->from('\Fisdap\Entity\StudentLegacy', 'st')
            ->leftJoin('st.shifts', 'shifts')
            ->leftJoin('shifts.signoff', 'shiftSignoff')
            ->leftJoin('shifts.verification', 'shiftVerification');
        //->leftJoin('st.runs', 'runs')
        //->leftJoin('runs.patients', 'patients')
        //->leftJoin('runs.signoff', 'runSignoff')
        //->leftJoin('runs.verification', 'runVerification');

        if (is_array($studentIds)) {
            //$qb->where('st.id IN (?1)');
            $qb->add('where', $qb->expr()->in('st.id', $studentIds));
        } else if (is_numeric($studentIds)) {
            $qb->where('st.id = ?1');
            $qb->setParameter(1, $studentIds);
        }

        //echo $qb->getQuery()->getSQL();
        //exit;

        return $qb->getQuery()->getResult();
    }

    public function getStudentWithRunData($studentId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('st', 'runs', 'runSignoff', 'runVerification')
            ->from('\Fisdap\Entity\StudentLegacy', 'st')
            ->leftJoin('st.runs', 'runs')
            ->leftJoin('runs.signoff', 'runSignoff')
            ->leftJoin('runs.verification', 'runVerification')
            ->where($qb->expr()->in('st.id', $studentId));
        //->setParameter(1, $studentId);

        return $qb->getQuery()->getResult();
    }

    public function getStudentWithPatientData($studentId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('st', 'patients', 'patientSignoff', 'patientVerification', 'narrative')
            ->from('\Fisdap\Entity\StudentLegacy', 'st')
            ->leftJoin('st.patients', 'patients')
            ->leftJoin('patients.signoff', 'patientSignoff')
            ->leftJoin('patients.verification', 'patientVerification')
            ->leftJoin('patients.narrative', 'narrative')
            ->where($qb->expr()->in('st.id', $studentId));
        //->setParameter(1, $studentId);

        return $qb->getQuery()->getResult();
    }

    public function getStudentsWithAllData($studentIds)
    {
        $result = $this->getStudentsWithShiftData($studentIds);
        $result = $this->getStudentWithRunData($studentIds);
        $result = $this->getStudentWithPatientData($studentIds);

        return $result;
    }

    /**
     * MySQL to get combined skill info across our many skill tables
     * Gets similar data as $patientRepo->getSkillsByPatient() but by student IDs
     * Used in Skills report
     */
    public function getStudentSkillData($studentIds) {
        // sanity check IDs
        foreach($studentIds as $id){
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $inCondition = implode(",", $studentIds);

        // the first table in the big UNION section needs to define the columns/column-labels
        // to which the rest conform. so it needs to include a bunch of columns not actually
        // relevant to that particular table
        $sql = "
	    SELECT
		SQL_NO_CACHE 
		s.shift_id AS shift_id, s.Audited AS shift_audited, s.Type AS shift_type,
		run.id AS run_id, 
		patient.id AS patient_id, patient.age AS patient_age, patient.team_lead AS patient_team_lead,
		    patient.interview AS patient_interview, patient.exam AS patient_exam, patient.cause_id AS patient_cause_id,
		cause.name AS patient_cause_name,
		skills.*,
		p_impression.id AS p_impression_id, p_impression.name AS p_impression_name,
		s_impression.id AS s_impression_id, s_impression.name AS s_impression_name
	    FROM ShiftData s
	    INNER JOIN
	    (
		(SELECT
		    s.shift_id AS shift_id, s.id AS skill_id, null AS procedure_id, null AS size,
		    null AS attempts, s.run_id AS run_id, null as success,
		    s.performed_by as performed_by, 'vital' as skill_type
		    FROM StudentData sd
		    LEFT JOIN fisdap2_vitals s ON s.student_id = sd.Student_id
		    WHERE sd.Student_id IN ({$inCondition})
		    )
		UNION ALL
		(SELECT
		    s.shift_id, s.id, s.procedure_id, s.size, s.attempts, s.run_id, s.success,
		    s.performed_by, 'airway'
		    FROM StudentData sd
		    LEFT JOIN fisdap2_airways s ON s.student_id = sd.Student_id
		    WHERE sd.Student_id IN ({$inCondition})
		    )
		UNION ALL
		(SELECT
		    s.shift_id, s.id, null, null, null, s.run_id, null, s.performed_by, 'other'
		    FROM StudentData sd
		    LEFT JOIN fisdap2_other_interventions s ON s.student_id = sd.Student_id
		    WHERE sd.Student_id IN ({$inCondition})
		    )
		UNION ALL
		(SELECT
		    s.shift_id, s.id, null, null, null, s.run_id, null, s.performed_by, 'iv'
		    FROM StudentData sd
		    LEFT JOIN fisdap2_ivs s ON s.student_id = sd.Student_id
		    WHERE sd.Student_id IN ({$inCondition})
		    )
		UNION ALL
		(SELECT
		    s.shift_id, s.id, null, null, null, s.run_id, null, s.performed_by, 'med'
		    FROM StudentData sd
		    LEFT JOIN fisdap2_meds s ON s.student_id = sd.Student_id
		    WHERE sd.Student_id IN ({$inCondition})
		    )
	    ) AS skills ON skills.shift_id = s.Shift_id
	    LEFT JOIN fisdap2_runs AS run ON s.Shift_id = run.shift_id
	    LEFT JOIN fisdap2_patients AS patient ON run.id = patient.run_id
	    INNER JOIN fisdap2_cause AS cause ON patient.cause_id = cause.id
	    LEFT JOIN fisdap2_impression AS p_impression ON patient.primary_impression_id = p_impression.id
	    LEFT JOIN fisdap2_impression AS s_impression ON patient.secondary_impression_id = s_impression.id
	    WHERE
	    s.Student_id IN ({$inCondition})
	    ";

        // run MySQL query with Zend DB driver
        $db = \Zend_Registry::get('db');
        $statement = $db->query($sql);
        $skillData = array();
        while($row = $statement->fetch()) {
            $skillData[$row['skill_id']] = $row;
        }

        return $skillData;
    }

    /**
     * MySQL to get narrative info
     * Used in Narrative report
     */
    public function getStudentNarrativeData($students, $sites = NULL, $start_date = null, $end_date = null) {

        // sanity check IDs
        foreach($students as $id){
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $studentIds = implode(",", $students);

        // figure out search criteria
        if ($sites) {
            // sanity check IDs
            foreach($sites as $id){
                if (!is_numeric($id)) {
                    return FALSE; // @todo probably should do some real error handling
                }
            }
            $siteIds = implode(",", $sites);
            $site_clause = "AND s.AmbServ_id IN (".$siteIds.") ";
        }

        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
            $start_date_clause = "AND s.StartDate >= '".date('Y-m-d', $start_date)."' ";
        }

        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
            $end_date_clause = "AND s.StartDate <= '".date('Y-m-d', $end_date)."' ";
        }

        $sql = 	"SELECT s.Student_id as student_id, s.Shift_id as shift_id, ".
            "n.id as narrative_id, n.run_id as run_id, ".
            "p.preceptor_id as preceptor_id ".
            "FROM ShiftData s, fisdap2_narratives n ".
            "LEFT JOIN fisdap2_runs r ".
            "ON (n.run_id = r.id) ".
            "LEFT JOIN fisdap2_patients p ".
            "ON (n.patient_id = p.id) ".
            "WHERE s.Student_id IN ({$studentIds}) ".
            $start_date_clause.
            $end_date_clause.
            $site_clause.
            "AND s.Shift_id = n.shift_id ".
            "AND s.soft_deleted = 0 ".
            "AND ((n.run_id IS NULL) OR (r.soft_deleted = 0)) ".
            "ORDER BY s.start_datetime";

        // run MySQL query with Zend DB driver
        $db = \Zend_Registry::get('db');
        $statement = $db->query($sql);
        $narrativeData = array();
        while($row = $statement->fetch()) {
            $narrativeData[$row['student_id']][$row['narrative_id']] = $row;
        }

        return $narrativeData;
    }

    /**
     * MySQL to get comment info
     * Used in Comments report
     */
    public function getStudentCommentData($students, $sites = NULL, $start_date = null, $end_date = null) {

        // sanity check IDs
        foreach($students as $id){
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $studentIds = implode(",", $students);

        // figure out search criteria
        if ($sites) {
            // sanity check IDs
            foreach($sites as $id){
                if (!is_numeric($id)) {
                    return FALSE; // @todo probably should do some real error handling
                }
            }
            $siteIds = implode(",", $sites);
            $site_clause = "AND s.AmbServ_id IN (".$siteIds.") ";
        }

        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
            $start_date_clause = "AND s.StartDate >= '".date('Y-m-d', $start_date)."' ";
        }

        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
            $end_date_clause = "AND s.StartDate <= '".date('Y-m-d', $end_date)."' ";
        }

        $sql = 	"SELECT s.Student_id as student_id, s.Shift_id as shift_id, ".
            "c.id as comment_id ".
            "FROM ShiftData s, fisdap2_comments c ".
            "WHERE s.Student_id IN ({$studentIds}) ".
            $start_date_clause.
            $end_date_clause.
            $site_clause.
            "AND s.Shift_id = c.table_data_id ".
            "AND s.soft_deleted = 0 ".
            "ORDER BY s.start_datetime";

        // run MySQL query with Zend DB driver
        $db = \Zend_Registry::get('db');
        $statement = $db->query($sql);
        $commentData = array();
        while($row = $statement->fetch()) {
            $commentData[$row['student_id']][$row['comment_id']] = $row;
        }

        return $commentData;
    }

    /**
     * This function takes a bitmask and returns any student who has a product configuration which
     * matches that mask.
     * @param integer $config
     */
    public function getStudentIdsByProductCodeConfig($config){
        if(!is_numeric($config)){
            $config = 0;
        }

        $programId = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();

        $sql = "
			SELECT
				sd.Student_id
			FROM
				fisdap2_users u
				INNER JOIN StudentData sd ON sd.user_id = u.id
				INNER JOIN SerialNumbers sn ON sn.User_id = u.id
			WHERE
				sn.Configuration & $config > 0
				AND sd.Program_id = $programId
		";

        // run MySQL query with Zend DB driver
        $db = \Zend_Registry::get('db');
        $res = $db->query($sql);

        $studentIds = array();
        while($row = $res->fetch()) {
            $studentIds[] = $row['Student_id'];
        }

        $uniqueIds = array_unique($studentIds);

        return $uniqueIds;
    }

    /**
     * MySQL to get attendance info
     * Used in Attendance report
     */
    public function getStudentAttendanceData($students, $sites = NULL, $start_date = null, $end_date = null) {

        // sanity check IDs
        foreach($students as $id){
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $studentIds = implode(",", $students);

        // figure out search criteria
        if ($sites) {
            // sanity check IDs
            foreach($sites as $id){
                if (!is_numeric($id)) {
                    return FALSE; // @todo probably should do some real error handling
                }
            }
            $siteIds = implode(",", $sites);
            $site_clause = "AND s.AmbServ_id IN (".$siteIds.") ";
        }

        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
            $start_date_clause = "AND s.StartDate >= '".date('Y-m-d', $start_date)."' ";
        }

        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
            $end_date_clause = "AND s.StartDate <= '".date('Y-m-d', $end_date)."' ";
        }

        $sql = 	"SELECT s.Student_id as student_id, s.Shift_id as shift_id, s.attendence_comments as comments, ".
            "sa.name as attendance, s.start_datetime as date ".
            "FROM ShiftData s, fisdap2_shift_attendence sa ".
            "WHERE s.Student_id IN ({$studentIds}) ".
            $start_date_clause.
            $end_date_clause.
            $site_clause.
            "AND s.attendence_id = sa.id ".
            "AND s.soft_deleted = 0 ".
            "ORDER BY s.start_datetime";

        // run MySQL query with Zend DB driver
        $db = \Zend_Registry::get('db');
        $statement = $db->query($sql);
        $attendanceData = array();
        while($row = $statement->fetch()) {
            $attendanceData[$row['student_id']][$row['shift_id']] = $row;
        }

        return $attendanceData;
    }

    /**
     * MySQL to get Late data info
     * Used in LateData report
     */
    public function getStudentLateData($students, $sites = NULL, $start_date = null, $end_date = null) {

        // sanity check IDs
        foreach($students as $id){
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $studentIds = implode(",", $students);

        // figure out search criteria
        if ($sites) {
            // sanity check IDs
            foreach($sites as $id){
                if (!is_numeric($id)) {
                    return FALSE; // @todo probably should do some real error handling
                }
            }
            $siteIds = implode(",", $sites);
            $site_clause = "AND s.AmbServ_id IN (".$siteIds.") ";
        }

        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
            $start_date_clause = "AND s.StartDate >= '".date('Y-m-d', $start_date)."' ";
        }

        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
            $end_date_clause = "AND s.StartDate <= '".date('Y-m-d', $end_date)."' ";
        }else{
            $end_date_clause = "AND s.StartDate <= '".date('Y-m-d')."' ";
        }

        $sql = 	"SELECT s.Student_id as student_id, s.Shift_id as shift_id, s.late as late ".
            "FROM ShiftData s ".
            "WHERE s.Student_id IN ({$studentIds}) ".
            $start_date_clause.
            $end_date_clause.
            $site_clause.
            "AND s.soft_deleted = 0 ".
            "ORDER BY s.start_datetime";

        // run MySQL query with Zend DB driver
        $db = \Zend_Registry::get('db');
        $statement = $db->query($sql);
        $attendanceData = array();
        while($row = $statement->fetch()) {
            $attendanceData[$row['student_id']][$row['shift_id']] = $row;
        }

        return $attendanceData;
    }


    /**
     * Used to retrieve patient acuity data for Patient Acuity report
     *
     * @param $students
     * @param null $sites
     * @param null $start_date
     * @param null $end_date
     * @return array $acuityData - Multidimensional array of student's patient acuity data
     */
    public function getStudentPatientAcuityData($students, $sites = NULL, $start_date = null, $end_date = null) {

        // sanity check IDs
        foreach($students as $id){
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $studentIds = implode(",", $students);

        // figure out search criteria
        if ($sites) {
            // sanity check IDs
            foreach($sites as $id){
                if (!is_numeric($id)) {
                    return FALSE; // @todo probably should do some real error handling
                }
            }
            $siteIds = implode(",", $sites);
            $site_clause = "AND s.AmbServ_id IN (".$siteIds.") ";
        }

        if ($start_date) {
            // sanity check date
            $start_date = strtotime($start_date);
            if (!$start_date) {
                return FALSE; // @todo probably should do some real error handling
            }
            $start_date_clause = "AND s.start_datetime >= '".date('Y-m-d', $start_date)."' ";
        }

        if ($end_date) {
            // sanity check date
            $end_date = strtotime($end_date);
            if (!$end_date) {
                return FALSE; // @todo probably should do some real error handling
            }
            $end_date_clause = "AND s.start_datetime <= '".date('Y-m-d', $end_date)."' ";
        }else{
            $end_date_clause = "AND s.start_datetime <= '".date('Y-m-d')."' ";
        }

        $sql = 	"SELECT p.Student_id as student_id, p.Shift_id as shift_id, ".
            "(select count(*) from fisdap2_patients where shift_id = p.Shift_id and patient_criticality_id = 1) as green, ".
            "(select count(*) from fisdap2_patients where shift_id = p.Shift_id and patient_criticality_id = 2) as yellow, ".
            "(select count(*) from fisdap2_patients where shift_id = p.Shift_id and patient_criticality_id = 3) as red, ".
            "(select count(*) from fisdap2_patients where shift_id = p.Shift_id and patient_criticality_id = 4) as black, ".
            "(select count(*) from fisdap2_patients where shift_id = p.Shift_id and patient_criticality_id is null) as none ".
            "FROM fisdap2_patients p, ShiftData s ".
            "WHERE p.shift_id = s.Shift_id ".
            "AND p.Student_id IN ({$studentIds}) ".
            $start_date_clause.
            $end_date_clause.
            $site_clause.
            "AND s.soft_deleted = 0 ".
            "ORDER BY s.start_datetime";


        // run MySQL query with Zend DB driver
        $db = \Zend_Registry::get('db');
        $statement = $db->query($sql);
        $acuityData = array();
        while($row = $statement->fetch()) {
            $acuityData[$row['student_id']][$row['shift_id']] = $row;
        }

        return $acuityData;
    }

    /**
     * MySQL to get narrative info
     * Used in Narrative report
     */
    public function getStudentCertLevels($students) {

        // sanity check IDs
        foreach($students as $id){
            if (!is_numeric($id)) {
                return FALSE; // @todo probably should do some real error handling
            }
        }
        $studentIds = implode(",", $students);

        $sql = 	"SELECT Student_id as student_id, ".
            "c.id as level_id, c.description as level_name ".
            "FROM StudentData st ".
            "LEFT JOIN fisdap2_user_roles ur ".
            "ON (st.user_role_id = ur.id) ".
            "LEFT JOIN fisdap2_certification_levels c ".
            "ON (ur.certification_level_id = c.id) ".
            "WHERE Student_id IN ({$studentIds})";

        // run MySQL query with Zend DB driver
        $db = \Zend_Registry::get('db');
        $statement = $db->query($sql);
        $certLevels = array();
        while($row = $statement->fetch()) {
            $certLevels[$row['student_id']] = $row;
        }

        return $certLevels;
    }

    /**
     * Get user account data for 1 or more students
     * Hydrated by array for speed
     * @param array $studentIds array of Student ID values
     * @param array $fields array of entity => field names to return. Only certain tables supported (student, )
     *
     * @return array Array keyed by student ID of arrays of user data
     */
    public function getStudentUserData(array $studentIds, array $fields)
    {
        if (empty($studentIds) || empty($fields)) {
            // return an empty array because no students or fields were specified
            return array();
        }

        $qb = $this->_em->createQueryBuilder();
        $qb->from('\Fisdap\Entity\StudentLegacy', 'st')
            ->innerJoin('st.user_context', 'ur')
            ->innerJoin('ur.user', 'u');

        // add fields
        $joins = array();
        foreach ($fields as $table => $names) {
            // No additional joins supported at this time! So the next five lines are kinda useless
            // but wanted to leave the structure here for later implementation
            switch ($table) {
                case 'st':
                case 'u':
                default:
                    break;
            }
            foreach ($names as $field => $name) {
                if (is_string($field)) {
                    $qb->addSelect($table . '.' . $field . ' AS ' . $name);
                } else {
                    $qb->addSelect($table . '.' . $name);
                }
            }
        }

        // add filter for particular students
        if (count($studentIds) > 1) {
            $qb->andWhere($qb->expr()->in('st.id', ':student_ids'))
                ->setParameter('student_ids', $studentIds);
        } else {
            $qb->andWhere('st.id = :student_ids')
                ->setParameter('student_ids', array_shift($studentIds));
        }

        $dql = $qb->getQuery();
        $result = $dql->getResult();

        return $result;
    }

    /**
     * MySQL to get a student's patient gender info
     * Used in CoAEMSP 3c2 report
     *
     * @param $studentId
     * @param $shiftOptions
     * @param bool $male
     *
     * @return int
     */
    public function getStudentPatientGenderData($studentId, $shiftOptions, $male = true)
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

        $subject_clause = $subjectIds ? "AND P.subject_id IN (" . $subjectIds . ") " : "";
        $sql = "SELECT count(*) as count " .
            "FROM ShiftData S, fisdap2_runs R, fisdap2_patients P " .
            "WHERE P.shift_id = S.Shift_id " .
            "AND P.run_id = R.id " .
            "AND P.student_id IN ({$studentIds}) " .
            $start_date_clause .
            $end_date_clause .
            $site_clause .
            $audited_clause .
            $subject_clause .
            ($male ? "AND P.gender_id = 2 " : "AND P.gender_id = 1 ").
            "AND R.soft_deleted = 0 " .
            "AND S.soft_deleted = 0 " .
            "GROUP BY S.Student_id";

        $stmt = $this->_em->getConnection()->prepare($sql);
        $stmt->execute();

        $result = 0;

        while ($row = $stmt->fetch()) {
            $result = $row['count'];
        }

        return $result;
    }
}
