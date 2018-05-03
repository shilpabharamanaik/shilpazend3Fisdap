<?php namespace Fisdap\Data\Program;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineProgramLegacyRepository
 *
 * @package Fisdap\Data\Program
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineProgramLegacyRepository extends DoctrineRepository implements ProgramLegacyRepository
{
    /**
     * Simple function to get aggregate program data without pulling hundreds of Entity objects
     *
     * @param array $fields Array of fields, should match property names on the ProgramLegacy entity
     *
     * @return array An array, keyed by program ID, of your results
     */
    public function getAllPrograms($fields = array('id', 'name'))
    {
        // if id is not included, add it to $fields
        if (!in_array('id', $fields)) {
            $fields[] = 'id';
        }

        // reformat fields so that they start with "p."
        foreach ($fields as $key => $name) {
            $fields[$key] = 'p.' . $name;
        }

        $qb = $this->_em->createQueryBuilder();

        $qb->select($fields)
            ->from('\Fisdap\Entity\ProgramLegacy', 'p')
            ->orderBy('p.name', 'ASC');
        $result = $qb->getQuery()->getResult();

        $keyedResults = array();
        foreach ($result as $program) {
            $keyedResults[$program['id']] = $program;
        }

        return $keyedResults;
    }

    /**
     * Get all the programs in a given profession
     * @param $professionId
     * @return mixed
     */
    public function getByProfession($professionId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p')
            ->from('\Fisdap\Entity\ProgramLegacy', 'p')
            ->where('p.profession = ?1')
            ->orderBy('p.name', 'ASC')
            ->setParameter(1, $professionId);
        return $qb->getQuery()->getResult();
    }

    public function getActiveStudents($program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('partial s.{id, first_name, last_name}, partial ur.{id}, partial u.{id}')
            ->from('\Fisdap\Entity\StudentLegacy', 's')
            ->leftJoin('s.graduation_status', 'grad_status')
            ->leftJoin('s.user_context', 'ur')
            ->leftJoin('ur.user', 'u')
            ->where('s.program = ?1')
            ->andWhere('ur.end_date > CURRENT_DATE()')
            ->andWhere('grad_status.id = 1');

        $qb->orderBy('s.last_name, s.first_name')
            ->setParameter(1, $program_id);

        //$qb->getQuery()->getSQL()

        return $qb->getQuery()->getResult();
    }

    public function getProgramByOrderId($orderId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('pro')
            ->from('\Fisdap\Entity\ProgramLegacy', 'pro')
            ->where('pro.product_code_id = ?1')
            ->setParameter(1, $orderId);

        return $qb->getQuery()->getResult();
    }

    public function getProgramsByCreatedRange($start, $end)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('pro')
            ->from('\Fisdap\Entity\ProgramLegacy', 'pro')
            ->where('pro.created >= ?1')
            ->andWhere('pro.created <= ?2')
            ->setParameter(1, $start->format('Y-m-d H:i:s'))
            ->setParameter(2, $end->format('Y-m-d H:i:s'));

        return $qb->getQuery()->getResult();

    }

    public function getAssociationCountByPreceptor($preceptor_id, $program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('count(p.id)')
            ->from('\Fisdap\Entity\ProgramPreceptorLegacy', 'p')
            ->where('p.preceptor = ?1')
            ->andWhere('p.program = ?2')
            ->setParameter(1, $preceptor_id)
            ->setParameter(2, $program_id);

        $result = $qb->getQuery()->getSingleResult();
        return array_pop($result);
    }

    public function getAssociatedPreceptors($program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('p, pp')
            ->from('\Fisdap\Entity\ProgramPreceptorLegacy', 'p')
            ->join('p.preceptor', 'pp')
            ->andWhere('p.program = ?1')
            ->orderBy('pp.first_name', 'ASC')
            ->setParameter(1, $program_id);

        return $qb->getQuery()->getResult();
    }

    public function getAssociatedUsers($program_id, $roleName = null)
    {
        $qb = $this->_em->createQueryBuilder();

        return [];
    }

    public function getStudents($program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('s')
            ->from('\Fisdap\Entity\StudentLegacy', 's')
            ->Where('s.program = ?1')
            ->setParameter(1, $program_id);

        return $qb->getQuery()->getResult();
    }


    /**
     * IDS RETURNED ARE UserContext IDS!
     *
     * @param            $program_id
     * @param bool|true  $includeCerts
     * @param bool|true  $includeGradDate
     * @param bool|true  $includeGroups
     * @param bool|false $active_and_recent_grads
     *
     * @return array
     */
    public function getCompleteStudentFormOptions($program_id, $includeCerts = true, $includeGradDate = true, $includeGroups = true, $active_and_recent_grads = false)
    {
        $students = $this->getActiveStudentsByProgramOptimized($program_id);
        $student_group_repo = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy');
        $three_months_ago = new \DateTime("-3 months");

        $options = array();
        foreach ($students as $student) {
            $userContext = $student['user_context'];
            if ($userContext['id']) {

                $include_user = false;

                if ($active_and_recent_grads) {
                    $grad_date_object = new \DateTime($userContext['end_date']->format("Y-m-t"));
                    $grad_date_object->setTime(23, 59, 59);
                    $grad_flag = $student['graduation_status']['id'];

                    if ($grad_flag == 2 || $grad_flag == 3 || $grad_flag == 4 || $three_months_ago > $grad_date_object) {
                        $include_user = false;
                    } else {
                        $include_user = true;
                    }
                } else {
                    $include_user = true;
                }

                if ($include_user) {
                    $studentString = $userContext['user']['first_name'] . " " . $userContext['user']['last_name'];
                    $studentString .= ($includeCerts) ? "|" . $userContext['certification_level']['description'] : "";
                    $studentString .= ($includeGradDate) ? "|" . $student['graduation_month'] . "|" . $student['graduation_year'] : "";

                    if ($includeGroups) {
                        $groups = array();
                        foreach ($student['classSectionStudent'] as $classSectionStudent) {
                            $groups[] = $classSectionStudent['section']['id'];
                        }
                        $studentString .= "|" . implode(",", $groups);
                    }

                    $options[$userContext['id']] = $studentString;
                }
            }
        }

        return $options;
    }

    // IDS RETURNED ARE USER ROLE IDS!
    public function getActiveStudentsByProgramOptimized($program_id, $cert_levels = null, $grad_year = null, $grad_month = null, $groups = null)
    {
        //var_dump($program_id);
        //var_dump($cert_levels);
        //var_dump($grad_year);
        //var_dump($grad_month);
        //var_dump($groups);


        $qb = $this->_em->createQueryBuilder();

        $qb->select('partial s.{id, graduation_year, graduation_month}, partial grad_status.{id}, partial p.{id}, partial ur.{id,end_date}, partial c.{id,description}, partial u.{id,first_name,last_name}, partial css.{id}, partial sect.{id}')
            ->from('\Fisdap\Entity\StudentLegacy', 's')
            ->leftJoin('s.graduation_status', 'grad_status')
            ->leftJoin('s.user_context', 'ur')
            ->leftJoin('ur.certification_level', 'c')
            ->leftJoin('ur.user', 'u')
            ->leftJoin('ur.program', 'p')
            ->leftJoin('s.classSectionStudent', 'css')
            ->leftJoin('css.section', 'sect')
            ->where('s.program = ?1');

        if ($cert_levels) {
            $qb->andWhere('c.id IN (' . implode($cert_levels, ",") . ')');
        }

        if ($groups) {
            $qb->andWhere('sect.id IN (' . implode($groups, ",") . ')');
        }
        if ($grad_year) {
            $qb->andWhere('SUBSTRING(ur.end_date,1,4) = ' . $grad_year);
        }
        if ($grad_month) {
            $qb->andWhere('SUBSTRING(ur.end_date,6,2) = ' . $grad_month);
        }

        $qb->orderBy('u.last_name, u.first_name')
            ->setParameter(1, $program_id);

        //$qb->getQuery()->getSQL()

        return $qb->getQuery()->getArrayResult();
    }

    //Implementing an alternate function to retrieve only students whose grad date hasn't been passed by more than 3 months.  Originally only intended to be used only for the request change modal to accomodate large schools.
    //Returns user role IDs like getActiveStudentsByProgramOptimized().
    public function getReasonableStudentsByProgram($program_id, $cert_levels = null, $grad_year = null, $grad_month = null, $groups = null)
    {

        $qb = $this->_em->createQueryBuilder();

        $qb->select('partial s.{id, graduation_year, graduation_month}, partial grad_status.{id}, partial p.{id}, partial ur.{id,end_date}, partial c.{id,description}, partial u.{id,first_name,last_name}, partial css.{id}, partial sect.{id}')
            ->from('\Fisdap\Entity\StudentLegacy', 's')
            ->leftJoin('s.graduation_status', 'grad_status')
            ->leftJoin('s.user_context', 'ur')
            ->leftJoin('ur.certification_level', 'c')
            ->leftJoin('ur.user', 'u')
            ->leftJoin('ur.program', 'p')
            ->leftJoin('s.classSectionStudent', 'css')
            ->leftJoin('css.section', 'sect')
            ->where('s.program = ?1');

        if ($cert_levels) {
            $qb->andWhere('c.id IN (' . implode($cert_levels, ",") . ')');
        }

        if ($groups) {
            $qb->andWhere('sect.id IN (' . implode($groups, ",") . ')');
        }
        if ($grad_year) {
            $qb->andWhere('SUBSTRING(ur.end_date,1,4) = ' . $grad_year);
        }
        if ($grad_month) {
            $qb->andWhere('SUBSTRING(ur.end_date,6,2) = ' . $grad_month);
        }

        $date = date('Y-m-d', strtotime("-3 months"));

        $qb->andWhere("ur.end_date >= '" . $date . "'");

        $qb->orderBy('u.last_name, u.first_name')
            ->setParameter(1, $program_id);

        return $qb->getQuery()->getArrayResult();
    }


    public function getInstructors($program_id, $optimize = false)
    {
        $qb = $this->_em->createQueryBuilder();

        if ($optimize) {
            $qb->select("partial i.{id}, partial u.{id,first_name,last_name}");
        } else {
            $qb->select('i', 'u');
        }

        $qb->from('\Fisdap\Entity\InstructorLegacy', 'i')
            ->leftJoin('i.user', 'u')
            // ->leftJoin('u.student', 'st')
            ->Where('i.program = ?1')
            ->orderBy('i.last_name, i.first_name', 'ASC')
            ->setParameter(1, $program_id);

        return ($optimize) ? $qb->getQuery()->getArrayResult() : $qb->getQuery()->getResult();
    }

    /**
     * Returns an array of instructors that
     * @param $program_id
     * @param null $bitmask
     * @return array Non staff instructors array
     */
    public function getNonStaffInstructorsByPermission($program_id, $bitmask = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("i.id as instructor_id, u.first_name, u.last_name")
            ->from('\Fisdap\Entity\InstructorLegacy', 'i')
            ->join('i.user', 'u')
            ->leftJoin('u.staff', 'staff')
            ->where('staff.staffId IS NULL')
            ->andWhere('i.program = :program_id')
            ->andWhere('BIT_AND(i.permissions, :bitmask) > 0')
            ->setParameters(array(
                'program_id' => $program_id,
                'bitmask' => $bitmask
            ));

        return $qb->getQuery()->getArrayResult();
    }

    public function getInstructorFormOptions($program_id)
    {
        $instructors = $this->getInstructors($program_id, true);
        $options = array();
        foreach ($instructors as $instructor) {
            $options[$instructor['id']] = $instructor['user']['first_name'] . " " . $instructor['user']['last_name'];
        }

        return $options;
    }

    public function getAssociationCountBySite($site_id, $program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('count(p.id)')
            ->from('\Fisdap\Entity\ProgramSiteLegacy', 'p')
            ->where('p.site = ?1')
            ->andWhere('p.program = ?2')
            ->setParameter(1, $site_id)
            ->setParameter(2, $program_id);

        $result = $qb->getQuery()->getSingleResult();
        return array_pop($result);
    }

    public function getMaxCustomerId()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('max(p.customer_id)')
            ->from('\Fisdap\Entity\ProgramLegacy', 'p');

        $result = $qb->getQuery()->getSingleResult();
        return array_pop($result);
    }

    public function addTransferNote($msg, $programFromID, $date)
    {
        $db = \Util_Db::getDBInstance();
        $sql = 'INSERT INTO note (note_created_on, note_content, note_sticky)
				VALUES("' . $date->format("Y-m-d H:i:s") . '", "' . $msg . '", 0);
				INSERT INTO note_link (note_link_note_id, note_link_table_name, note_link_table_index, note_link_table_id)
				VALUES(LAST_INSERT_ID(), "ProgramData", "Program_id", ' . $programFromID . ');';

        $db->query($sql);
    }


    public function getProgramTypes($program_id)
    {
        $sql = "SELECT ptt.ProgramType_id, ptt.ProgramType_desc FROM ProgramTypeTable ptt JOIN ProgramTypeData ptd ON ptt.ProgramType_id = ptd.ProgramType_id WHERE ptd.Program_id = " . $program_id . ";";
        $db = \Zend_Registry::get('db');
        $arr = array();
        $res = $db->query($sql);

        while ($row = $res->fetch()) {
            $arr[$row['ProgramType_id']] = $row['ProgramType_desc'];
        }

        return $arr;
    }

    /**
     * Get a DateTime object representing the most recent order placed by this program
     *
     * @param $program_id
     * @return \DateTime
     */
    public function getMostRecentOrderDate($program_id)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('max(o.order_date)')
            ->from('\Fisdap\Entity\Order', 'o')
            ->where('o.program = ?1')
            ->setParameter(1, $program_id);

        $result = $qb->getQuery()->getSingleResult();

        return array_pop($result);

    }


}
