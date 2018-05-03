<?php namespace Fisdap\Data\Shift;

use Fisdap\Attachments\Associations\Repositories\RepositoryAttachmentsSupport;
use Fisdap\Attachments\Associations\Repositories\StoresAttachments;
use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineShiftLegacyRepository
 *
 * @package Fisdap\Data\Shift
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineShiftLegacyRepository extends DoctrineRepository implements ShiftLegacyRepository, StoresAttachments
{
    use RepositoryAttachmentsSupport;


    /**
     *	@param mixed $student: student_id or student entity
     *	@param array params:
     *		shiftType
     */
    public function getShiftIdsByStudent($student, $params=null)
    {
        if(is_integer($student)) {
            $studentId = $student;
        } else {
            $studentId = $student->id;
        }

        $qb = $this->_em->createQueryBuilder();

        $qb ->select('sh.id')
            ->from('\Fisdap\Entity\ShiftLegacy', 'sh')
            ->where('sh.student = ?1')
            ->setParameter(1, $studentId);

        if ($params['shiftType']) {
            $qb->andWhere('sh.type = ?2')
                ->setParameter(2, $params['shiftType']);
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function getAllShiftsByStudent($studentId, $shiftType = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('sh, st')
            ->from('\Fisdap\Entity\ShiftLegacy', 'sh')
            ->leftJoin('sh.student', 'st')
            ->where('st.id = ?1')
            ->orderBy('sh.start_date', 'DESC')
            ->setParameter(1, $studentId);

        if ($shiftType) {
            $qb->andWhere('sh.type = ?2')
                ->setParameter(2, $shiftType);
        }

        return $qb->getQuery()->getResult();
    }

    public function getPendingShiftsByStudent($studentId)
    {
        $dql = 'SELECT sh, st FROM \Fisdap\Entity\ShiftLegacy sh JOIN sh.student st '
            . 'WHERE st.id = ?1 AND sh.locked = 0 AND sh.start_date '
            . '<= CURRENT_DATE() ORDER BY sh.start_date DESC';

        return $this->_em->createQuery($dql)
            ->setParameter(1, $studentId)
            ->getResult();
    }

    public function getFutureShiftsByStudent($studentId)
    {
        $dql = 'SELECT sh, st FROM \Fisdap\Entity\ShiftLegacy sh JOIN sh.student st '
            . 'WHERE st.id = ?1 AND sh.start_date > CURRENT_DATE() ORDER BY sh.start_date DESC';

        return $this->_em->createQuery($dql)
            ->setParameter(1, $studentId)
            ->getResult();
    }

    public function getUpcomingShifts($studentId, $hours=72){
        $secs = $hours * 60 * 60;

        $sql = "SELECT sh.*, CONVERT_TZ(TIMESTAMPADD(HOUR, BigBroInc, TIMESTAMPADD(SECOND, hours * 3600, TIMESTAMP(StartDate, CONCAT(StartTime, '00')))), CONCAT(standard_offset, ':00'), '-06:00') as deadline FROM ShiftData sh, StudentData st, ProgramData p, fisdap2_program_settings ps, fisdap2_timezone t WHERE st.Student_id = sh.Student_id AND st.Program_id = p.Program_id AND p.Program_id = ps.program_id AND ps.timezone_id = t.id AND Completed = 0 AND late = 0 AND BigBroStuReminders = 1 AND sh.Student_id = $studentId HAVING deadline > NOW() AND deadline < TIMESTAMP(NOW(), '$hours:00:00')";

        $conn = $this->_em->getConnection();
        return $conn->query($sql);
    }

    /**
     * Get properties of the ShiftData table using array hydration
     * Useful for getting large chunks of limited data for multiple students
     * See the Hours report /library/Fisdap/Reports/Hours.php for an example usage
     * $fields should be an array of DQL-appropriate fields:
     * 	$fields = array(
    'sh' => array(), // array of fields on the shift table
    'attend' => array(), // array of fields on teh attendance table
    )
     * Certain filters are supported by passing values in through $filter array
     * @author jmortenson
     */
    public function getShiftsFields($filter = array(), $fields = array()) {
        $qb = $this->_em->createQueryBuilder();
        $qb->from('\Fisdap\Entity\ShiftLegacy', 'sh');

        // add fields
        $joins = array();
        foreach($fields as $table => $names) {
            switch($table) {
                case 'attend':
                    $qb->innerJoin('sh.attendence', 'attend');
                    $joins['attend'] = 'attend';
                    break;
                case 'base':
                    $qb->leftJoin('sh.base', 'base');
                    $joins['base'] = 'base';
                    break;
                case 'site':
                    $qb->leftJoin('sh.site', 'site');
                    $joins['site'] = 'site';
                    break;
                case 'st':
                    $qb->leftJoin('sh.student', 'st');
                    $joins['st'] = 'st';
                case 'sh':
                default:
                    break;
            }
            foreach($names as $field => $name) {
                if (is_string($field)) {
                    $qb->addSelect($table . '.' . $field . ' AS ' . $name);
                } else {
                    $qb->addSelect($table . '.' . $name);
                }
            }
        }

        // Add filter conditions

        // filter by student ID?
        if (isset($filter['studentIds'])) {
            if (!isset($joins['st'])) {
                $qb->leftJoin('sh.student', 'st');
                $joins['st'] = 'st';
            }
            if (count($filter['studentIds']) > 1) {
                $qb->andWhere($qb->expr()->in('st.id', ':student_ids'))
                    ->setParameter('student_ids', $filter['studentIds']);
            } else {
                $qb->andWhere('st.id = :student_ids')
                    ->setParameter('student_ids', array_shift($filter['studentIds']));
            }
        }

        // filter by program
        if (isset($filter['programIds'])) {
            if (!isset($joins['st'])) {
                // make sure we join student
                $qb->leftJoin('sh.student', 'st');
                $joins['st'] = 'st';
            }
            if (!isset($joins['p'])) {
                $qb->innerJoin('st.program', 'p');
                $joins['p'] = 'p';
            }
            if (count($filter['programIds']) > 1) {
                $qb->andWhere($qb->expr()->in('p.id', ':program_ids'))
                    ->setParameter('program_ids', $filter['programIds']);
            } else {
                $qb->andWhere('p.id = :program_ids')
                    ->setParameter('program_ids',array_shift($filter['programIds']));
            }
        }

        // filter by date
        // expects a PHP DateTime object
        // this is a little funky, at least refactor naming
        if (isset($filter['start_datetime'])) {
            if ($filter['start_datetime']['min'] instanceof \DateTime) {
                //set time to 00:00:00 since we want to include any shifts began on the day
                $filter['start_datetime']['min']->setTime(0, 0, 0);
                $qb->andWhere('sh.start_datetime >= :minDate')
                    ->setParameter('minDate', $filter['start_datetime']['min']);
            }

            if ($filter['start_datetime']['max'] instanceof \DateTime) {
                //set time to 23:59:59 since we want to include any shifts beginning on the final day
                $filter['start_datetime']['max']->setTime(23, 59, 59);
                $qb->andWhere('sh.start_datetime <= :maxDate')
                    ->setParameter('maxDate', $filter['start_datetime']['max']);
            }
        }

        // filter by sites
        if (is_array($filter['siteIds'])) {
            if (!isset($joins['site'])) {
                $qb->leftJoin('sh.site', 'site');
            }
            if (count($filter['siteIds']) > 1) {
                $qb->andWhere($qb->expr()->in('site.id', ':site_ids'))
                    ->setParameter('site_ids', $filter['siteIds']);
            } else if (count($filter['siteIds']) == 1) {
                $qb->andWhere('site.id = :siteid')
                    ->setParameter('siteid', array_shift($filter['siteIds']));
            }
        }

        $dql = $qb->getQuery();
        $result = $dql->getResult();

        return $result;
    }

    public function getShiftsByStudent($studentId, $filter = null)
    {
        if(!$filter){
            $filter = array("type" => array(),
                            "attendance" => array(),
                            "date" => "all",
                            "pending" => null);
        }

        // handle $filter['type'] being passed in as a string instead of array
        if (is_array($filter['type'])) {
            $types = $filter['type'];
        } else if ($filter['type']) {
            $types = array($filter['type']);
        }
        $pending = $filter['pending'];
        $attendance = $filter['attendance'];
        $date = $filter['date'];

        //Fisdap hack: since getShiftsByStudent used to take only one type at a time, we need to adjust accordingly
        if (!is_array($types)) {
            $types = array($types);
        }

        $sql = "
			SELECT
				sh.Shift_id AS id,
				sh.soft_deleted,
				sh.Type AS type,
				sh.start_datetime,
				sh.Audited AS audited,
				sh.Completed AS locked,
				sh.Event_id AS event_id,
				sh.Hours AS hours,
				sh.end_datetime,
				sh.attendence_id,
				sh.StartDate,
				sh.StartTime,
				sh.late AS late,

				base.BaseName as base_name,
				site.AmbServName as site_name,

                CONCAT_WS(' ', site.Address, site.City, site.Region, site.PostalCode) as site_address,
                CONCAT_WS(' ', base.Address, base.City, base.State, base.PostalCode) as base_address,

				proset.allow_signoff_on_patient,
				proset.allow_educator_shift_audit,

				ps.id AS signoff_id,
				v.id AS verification_id,

				(SELECT COUNT(DISTINCT sub_p.id) FROM fisdap2_patients sub_p WHERE sub_p.shift_id = sh.Shift_id) AS patient_count,
				(SELECT COUNT(*) FROM fisdap2_comments sub_c WHERE sub_c.table = 'shifts' AND sub_c.table_data_id = sh.Shift_id AND sub_c.soft_deleted = 0) AS comment_count,
				(SELECT COUNT(*) FROM fisdap2_runs sub_r INNER JOIN fisdap2_verifications sub_v ON sub_v.id = sub_r.verification_id WHERE sub_r.shift_id = sh.Shift_id) AS verified_runs,
				(SELECT COUNT(*) FROM fisdap2_runs sub_r WHERE sub_r.shift_id = sh.Shift_id) AS total_runs,

				CASE sh.attendence_id
					WHEN '1' THEN 'On time'
					WHEN '2' THEN 'Tardy'
					WHEN '3' THEN 'Absent'
					WHEN '4' THEN 'Absent w/<br />Permission'
				END as attendance
			FROM
				ShiftData sh
				LEFT JOIN StudentData st ON sh.student_id = st.student_id
				LEFT JOIN AmbulanceServices site ON sh.ambserv_id = site.ambserv_id
				LEFT JOIN AmbServ_Bases base ON sh.startbase_id = base.base_id
				LEFT JOIN fisdap2_program_settings proset ON st.program_id = proset.program_id
				LEFT JOIN fisdap2_preceptor_signoffs ps ON ps.shift_id = sh.Shift_id
				LEFT JOIN fisdap2_verifications v ON v.shift_id = sh.Shift_id
			WHERE
				sh.student_id = {$studentId}
		";

        switch($date) {
            case "past":
                $sql .= " AND sh.start_datetime <= '".date("Y-m-d H:i:s")."'";
                break;
            case "future":
                $sql .= " AND sh.end_datetime >= '".date("Y-m-d H:i:s")."'";
                break;
            case "peri":
                // 6 weeks past
                $sql .= " AND sh.start_datetime >= '".date("Y-m-d H:i:s", strtotime("-6 weeks"))."'";
                // 6 weeks forward
                $sql .= " AND sh.start_datetime <= '".date("Y-m-d H:i:s", strtotime("+6 weeks"))."'";
                break;
        }

        if ($pending) {
            $sql .= ' AND sh.Completed = 0';
            $sql .= " AND sh.start_datetime <= '".date("Y-m-d H:i:s")."'";
        }

        if (count($types) > 0) {
            $sql .= ' AND sh.Type IN ("'.implode('", "', $types).'")';
        }

        if (count($attendance) > 0) {
            $sql .= ' AND sh.attendence_id IN ('.implode(', ', $attendance).')';
        }

        $sql .= "
			GROUP BY sh.Shift_id
			ORDER BY
				sh.start_datetime DESC
		";

        $conn = $this->_em->getConnection();
        $result = $conn->query($sql);

        return $result;


    }

    public function getShiftEntitiesByStudent($studentId, $filter = 'all')
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('sh, st, usr, site, base, pro, proset, runs')//, signoff, verification, run_signoff')//, patient, narrative, patient_signoff')
        ->from('\Fisdap\Entity\ShiftLegacy', 'sh')
            ->leftJoin('sh.student', 'st')
            ->leftJoin('st.user', 'usr')
            ->leftJoin('sh.site', 'site')
            ->leftJoin('sh.base', 'base')
            //->leftJoin('sh.signoff', 'signoff')
            //->leftJoin('sh.verification', 'verification')
            ->leftJoin('st.program', 'pro')
            ->leftJoin('pro.program_settings', 'proset')
            ->leftJoin('sh.runs', 'runs')
            //->leftJoin('runs.signoff', 'run_signoff')
            //->leftJoin('sh.patients', 'patient')
            //->leftJoin('patient.signoff', 'patient_signoff')
            //->leftJoin('patient.narrative', 'narrative')
            ->where('sh.student = ?1')
            ->orderBy('sh.start_date', 'DESC')
            ->setParameter(1, $studentId);

        switch($filter) {
            case "pending":
                $qb->andWhere('sh.locked = 0')
                    ->andWhere('sh.start_date <= CURRENT_DATE()');
                break;
            case "field":
            case "clinical":
            case "lab":
                $qb->andWhere('sh.type = ?2')
                    ->setParameter(2, $filter);
                break;
            case "locked":
                $qb->andWhere('sh.locked = 1');
                break;
        }

        $result = $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_OBJECT);

        return $result;
    }

    public function getSkillsByShift($shiftId, $filters = null)
    {
        $ivs = $this->_em->getRepository('\Fisdap\Entity\Iv')->findByShift($shiftId);
        $vitals = $this->_em->getRepository('\Fisdap\Entity\Vital')->findByShift($shiftId);
        $airways = $this->_em->getRepository('\Fisdap\Entity\Airway')->findByShift($shiftId);
        $other_interventions = $this->_em->getRepository('\Fisdap\Entity\OtherIntervention')->findByShift($shiftId);
        $cardiac_interventions = $this->_em->getRepository('\Fisdap\Entity\CardiacIntervention')->findByShift($shiftId);
        $meds = $this->_em->getRepository('\Fisdap\Entity\Med')->findByShift($shiftId);
        $labSkills = $this->_em->getRepository('\Fisdap\Entity\LabSkill')->findByShift($shiftId);

        $skills = array_merge($ivs, $vitals, $airways, $other_interventions, $cardiac_interventions, $meds);

        if ($filters['labOnly']) {
            $skills = array_merge($skills, $labSkills);
        }

        if ($filters['shiftOnly']) {
            foreach ($skills as $id => $skill) {
                //Ignore any skills attached to a patient or practice item
                if ($skill->patient->id || $skill->practice_item->id) {
                    unset($skills[$id]);
                }
            }
            $skills = array_values($skills);
        }

        usort($skills, array("self", "comparator"));


        return $skills;
    }

    private static function comparator($a, $b)
    {
        if ($a->skill_order == $b->skill_order) {
            return 0;
        } else if ($a->skill_order > $b->skill_order) {
            return 1;
        } else {
            return -1;
        }
    }

    public function getStudentLateShifts($studentId){
        $sql = "
			SELECT
				Shift_id as id
			FROM
				ShiftData sd
			WHERE
				sd.Completed = 0
				AND sd.late = 1
				AND sd.Student_id = $studentId
			ORDER BY sd.StartDate DESC, sd.StartTime DESC
		";

        $conn = $this->_em->getConnection();
        return $conn->query($sql);
    }

    public function getStudentLateShiftArray($studentId){
        $qb = $this->_em->createQueryBuilder();

        $qb->select("s.id, s.start_datetime, s.hours, s.end_datetime, si.abbreviation as site_abbreviation, ba.name as base_name")
            ->from("\Fisdap\Entity\ShiftLegacy", "s")
            ->leftJoin("s.site", "si")
            ->leftJoin("s.base", "ba")
            ->where("s.locked = 0")
            ->andWhere("s.late = 1")
            ->andWhere("s.student = ?1")
            ->orderBy("s.start_datetime", "DESC")
            ->setParameter(1, $studentId);

        $res = $qb->getQuery()->getArrayResult();

        return $res;
    }

    // get late shifts for students in a particular program whose grad status is 'In Progress'
    public function getLateShiftAllStudents($programId){
        $sql = "
			SELECT
				sd.Student_id as student_id,
				COUNT(*) as late_shift_count,
				GROUP_CONCAT(sd.Shift_id) as late_shift_ids,
				CONCAT(sad.FirstName, ' ', sad.LastName) as name
			FROM
				ShiftData sd
				INNER JOIN StudentData sad ON sad.Student_id = sd.Student_id
			WHERE
				sd.Completed = 0
				AND sd.late = 1
				AND sad.Program_id = $programId
				AND sad.graduation_status_id = 1
			GROUP BY sd.Student_id
			ORDER BY sad.LastName
		";

        $conn = $this->_em->getConnection();
        return $conn->query($sql);
    }

    public function getNewLateShifts()
    {
        //$qb = $this->_em->createQueryBuilder();
        //
        //$qb->select('sh')
        //   ->from('\Fisdap\Entity\Shift', 'sh')
        //   ->where('sh.late = false')
        //   ->andWhere('sh.locked = false')
        //   ->andWhere('sh.end_datetime > CURRENT_TIMESTAMP()');
        //
        //   return $qb->getQuery()->getResult();

        $rsm = new \Doctrine\ORM\Query\ResultSetMapping;
        $rsm->addEntityResult('\Fisdap\Entity\ShiftLegacy', 's');
        //$rsm->addFieldResult('s', 'id', 'id');

        $query = $this->_em->createNativeQuery('SELECT * FROM ShiftData WHERE completed = 0 AND late = 0 AND end_datetime > DATE_ADD(NOW(),INTERVAL 72 HOUR)', $rsm);

        return $query->getResult();
    }

    public function getVerifiedRunCount($shiftId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('count(r.id)')
            ->from('\Fisdap\Entity\Run', 'r')
            ->leftJoin('r.verification', 'v')
            ->where('r.shift = ?1')
            ->andWhere('v.verified = true')
            ->setParameter(1, $shiftId);

        $result = $qb->getQuery()->getSingleResult();
        return array_pop($result);
    }

    public function getRunCount($shiftId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('count(r.id)')
            ->from('\Fisdap\Entity\Run', 'r')
            ->where('r.shift = ?1')
            ->setParameter(1, $shiftId);

        $result = $qb->getQuery()->getSingleResult();
        return array_pop($result);
    }

    public function getPendingTradeDrops($classSectionId=null, $user=null){
        if($user == null){
            $user = \Fisdap\Entity\User::getLoggedInUser();
        }

        if($user->isInstructor()){
            $programId = $user->getCurrentProgram()->id;

            $shiftTypes = array();
            if($user->hasPermission('Edit Clinic Schedules')){
                $shiftTypes[] = 'clinical';
            }
            if($user->hasPermission('Edit Field Schedules')){
                $shiftTypes[] = 'field';
            }
            if($user->hasPermission('Edit Lab Schedules')){
                $shiftTypes[] = 'lab';
            }

            $cutoff_date = date("Y-m-d",mktime(0, 0, 0, date("m")  , date("d")-7, date("Y")));

            if ($classSectionId > 0) {
                $sectionTable = ", SectStudents ss ";
                $sectionJoin = "AND ss.Student_id = st.Student_id AND ss.Section_id = {$classSectionId} ";

                $sql = "
				SELECT
					s.Shift_id,
					st.Student_id,
					s.StartTime,
					s.StartDate,
					CONCAT(st.FirstName, ' ', st.LastName) as student_name
				FROM
					ShiftData s, StudentData st $sectionTable
				WHERE
					s.TradeStatus = 2
					AND st.Program_id=$programId
					AND s.StartDate >= '$cutoff_date'
					AND s.Type IN ('" . implode("', '", $shiftTypes) . "')
					AND s.Student_id = st.Student_id
					$sectionJoin
				ORDER BY
					st.Class_Year,
					st.ClassMonth,
					st.LastName
			";

                return $this->_em->getConnection()->query($sql);
            } else {
                return false;
            }


        }else{
            return false;
        }
    }

    /**
     *	@param shiftId the shift we're attempting to find a preceptor for
     *	@param count determines what we're looking to return (just the total number of preceptors, or an actual name)
     *	@param fromEvent determines if we're looking for runs with preceptors, or if we're looking for an event preceptor
     */
    public function getShiftEventPreceptor($shiftId){
        $sql = "
			SELECT
				pd.FirstName,
				pd.LastName
			FROM
				ShiftData sd
				JOIN EventData ed ON sd.Event_id = ed.Event_id
				JOIN EventPreceptorData epd on ed.Event_id = epd.Event_id
				JOIN PreceptorData pd on epd.Preceptor_id = pd.Preceptor_id
			WHERE
				sd.Shift_id = ($shiftId)
		";

        $conn = $this->_em->getConnection();
        $result = $conn->query($sql);

        $row = $result->fetch();

        if($row){
            return $row['FirstName'] . " " . $row['LastName'];
        }else{
            return false;
        }
    }

    /**
     * Get all shifts for a particular student that occur
     * on the given day, possibly limited by shift type.
     *
     * @param integer $studentId the ID of the given student
     * @param string $startDate the date to match against in format YYYY-MM-DD
     * @param string $shiftType the shift type to filter by
     * @return array of \Fisdap\Entity\Shift
     */
    public function getShiftsByStartDate($studentId, $startDate, $shiftType = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('shift', 'st')
            ->from('\Fisdap\Entity\ShiftLegacy', 'shift')
            ->leftJoin('shift.student', 'st')
            ->andWhere('shift.student = ?1')
            ->andWhere('shift.start_date = ?2')
            ->setParameter(1, $studentId)
            ->setParameter(2, $startDate);

        if ($shiftType) {
            $qb->andWhere('shift.type = ?3')
                ->setParameter(3, $shiftType);
        }

        $results = $qb->getQuery()->getResult();
        return $results;
    }

    /**
     * Get all shifts for a particular student that occur
     * during a date range
     *
     * @param integer $studentId the ID of the given student
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param integer $shiftId a shift to ignore when searching (i.e. a shift we're editing)
     * @return array of shift ids
     */
    public function getShiftsByDateRange($studentId, $startDate, $endDate, $shiftId = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('shift.id')
            ->from('\Fisdap\Entity\ShiftLegacy', 'shift')
            ->leftJoin('shift.student', 'st')
            ->andWhere('shift.student = ?1')
            ->andWhere('shift.start_datetime >= ?2')
            ->andWhere('shift.start_datetime <= ?3')
            ->setParameter(1, $studentId)
            ->setParameter(2, $startDate->format("Y-m-d H:i:s"))
            ->setParameter(3, $endDate->format("Y-m-d H:i:s"));

        if ($shiftId) {
            $qb->andWhere("shift.id != ?4")
                ->setParameter(4, $shiftId);
        }
        $results = $qb->getQuery()->getArrayResult();
        return $results;
    }

    public function getShiftsByEvent($event_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('sh')
            ->from('\Fisdap\Entity\ShiftLegacy', 'sh')
            ->where('sh.event_id = ?1')
            ->setParameter(1, $event_id);

        return $qb->getQuery()->getResult();
    }

    public function hasData($shift_id)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select("p.id as patient,
                     i.id as iv,
                     v.id as vital,
                     a.id as airway,
                     o.id as other,
                     c.id as cardiac,
                     m.id as med,
                     l.id as lab,
                     n.id as narrative,
                     sa.id as attachment")
            ->from("\Fisdap\Entity\ShiftLegacy", "s")
            ->leftJoin("s.patients", "p")
            ->leftJoin("s.meds", "m")
            ->leftJoin("s.airways", "a")
            ->leftJoin("s.ivs", "i")
            ->leftJoin("s.cardiac_interventions", "c")
            ->leftJoin("s.other_interventions", "o")
            ->leftJoin("s.narratives", "n")
            ->leftJoin("s.vitals", "v")
            ->leftJoin("s.practice_items", "l")
            ->leftJoin("s.attachments", "sa")
            ->where("s.id = ?1")
            ->groupBy("s.id")
            ->having("p.id IS NOT NULL OR
                      i.id IS NOT NULL OR
                      c.id IS NOT NULL OR
                      v.id IS NOT NULL OR
                      a.id IS NOT NULL OR
                      o.id IS NOT NULL OR
                      m.id IS NOT NULL OR
                      l.id IS NOT NULL OR
                      n.id IS NOT NULL OR
                      sa.id IS NOT NULL")
            ->setParameter(1, $shift_id);

        $result = $qb->getQuery()->getScalarResult();

        if(!empty($result)){
            $has_data = true;
        } else {
            // double check make sure there are no airway_managements for this shift, either
            $qb = $this->_em->createQueryBuilder();
            $qb->select('am')
                ->from('\Fisdap\Entity\AirwayManagement', 'am')
                ->where('am.shift = ?1')
                ->setParameter(1, $shift_id);

            $airway_managements = $qb->getQuery()->getScalarResult();

            $has_data = !empty($airway_managements);

        }

        return $has_data;
    }
}
