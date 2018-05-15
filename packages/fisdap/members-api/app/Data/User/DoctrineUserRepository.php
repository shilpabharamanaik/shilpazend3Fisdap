<?php namespace Fisdap\Data\User;

/*
 * This file is subject to the terms and conditions defined in
 * file 'LICENSE.txt', which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Entity\User;

/**
 * Class DoctrineUserRepository
 *
 * @package Fisdap\Data\User
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineUserRepository extends DoctrineRepository implements UserRepository
{
    /*
     * This function takes a $qb and standard $filters input (ie, from MultiStudentPicker)
     * and finishes up a query. THis allows you to start a new repository query with custom select/join stuff
     * and still feed it in here to apply the standard student graduation year, class section, etc. filters
     *
     * It does requre that the StudentLegacy table is joined, that student.certification_level is joined as c,
     * and that UserContext.end_date is in the SELECT list as end_date
     */
    private function completeStandardFilteredUserQuery($qb, $filters, $just_userContextId = false)
    {
        // Join Class Section tables if either of the section filters are active, otherwise omit
        // Join Class Section tables if either of the section filters are active, otherwise omit
        // joining class sections without a corresponding WHERE as a LEFT JOIN causes duplicates
        if ((array_key_exists('sectionYear', $filters) && $filters['sectionYear'] != 'all')
            || (array_key_exists('section', $filters) && $filters['section'] > 0)) {
            $qb->leftJoin('st.classSectionStudent', 'css');
            $qb->leftJoin('css.section', 'cs');
        }

        if (array_key_exists('sectionYear', $filters) && $filters['sectionYear'] != 'all') {
            $qb->andWhere('cs.year = ?3');
            $qb->setParameter(3, $filters['sectionYear']);
        }

        if (array_key_exists('section', $filters) && $filters['section'] > 0) {
            $qb->andWhere('cs.id = ?4');
            $qb->setParameter(4, $filters['section']);
        }

        //Exclude any given user IDs from the result set
        if (array_key_exists('exclude', $filters) && count($filters['exclude']) > 0) {
            $qb->andWhere($qb->expr()->notIn('usr.id', $filters['exclude']));
        }

        //Include only given Student IDs from the result set
        if (array_key_exists('includeStudentIds', $filters) && count($filters['includeStudentIds']) > 0) {
            $qb->andWhere($qb->expr()->in('st.id', $filters['includeStudentIds']));
        }

        // Add in a check for certification levels...
        if (array_key_exists('certificationLevels', $filters) && count($filters['certificationLevels']) > 0) {
            //$qb->leftJoin('ur.certification_level', 'c');
            $qb->andWhere($qb->expr()->in('c.id', $filters['certificationLevels']));
        }

        // Add in a check for search string
        if (array_key_exists('searchString', $filters) && $filters['searchString'] != '') {
            $searchTerms = preg_split("/\s+|,/", str_replace("'", "", $filters['searchString']));

            foreach ($searchTerms as $term) {
                if (trim($term) != '') {
                    $term = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $term) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
                    $qb->andWhere("usr.first_name LIKE '%$term%' OR usr.last_name LIKE '%$term%' OR usr.username LIKE '%$term%' OR usr.email LIKE '%$term%'");
                }
            }
        }

        // Check for graduation status
        if (array_key_exists('graduationStatus', $filters) && count($filters['graduationStatus']) > 0) {
            $qb->leftJoin('st.graduation_status', 'status');

            // if we have the value 1 in play, then we need to include an "OR graduation_status IS NULL"
            // because there are some NULL values in StudentData and we need to assume that NULL = "is active" (aka same a sstatus ID 1)
            if (in_array(1, $filters['graduationStatus'])) {
                $qb->andWhere($qb->expr()->orx(
                        'st.graduation_status IS NULL', //$qb->expr()->isNull('st.graduation_status'),
                        $qb->expr()->in('status.id', $filters['graduationStatus'])
                    ));
            } else {
                $qb->andWhere($qb->expr()->in('status.id', $filters['graduationStatus']));
            }
        }
        //echo $qb->getQuery()->getSQL();
        //die();
        $results = $qb->getQuery()->getResult();

        if ($filters['graduationMonth'] > 0) {
            foreach ($results as $id => $result) {
                if ($result['end_date'] instanceof \DateTime && $result['end_date']->format('m') != $filters['graduationMonth']) {
                    unset($results[$id]);
                }
            }
            $results = array_values($results);
        }

        if ($filters['graduationYear'] > 0) {
            foreach ($results as $id => $result) {
                if ($result['end_date'] instanceof \DateTime && $result['end_date']->format('Y') != $filters['graduationYear']) {
                    unset($results[$id]);
                }
            }
            $results = array_values($results);
        }

        return $results;
    }

    /**
     * @param int $programID
     * @param array $filters - filter settings from a saved scheduler filter set
     * @param bool $just_userContextId - return only user role id and end date
     * @param bool $only_active - only return students that have a grad date in the future
     * @return array of student information
     */
    public function getAllStudentsByProgram($programID, $filters = array(), $just_userContextId = false, $only_active = false)
    {
        $qb = $this->_em->createQueryBuilder();

        // We are no longer pulling entities in this query, so this is commented out
        //$qb->select('st, usr, css, cs, ur')

        if ($just_userContextId) {
            $qb->select('ur.id, ur.end_date');
        } else {
            $qb->select(
                'st.id',
                'usr.first_name',
                'usr.last_name',
                'usr.id AS user_id',
                'ur.end_date',
                'c.configuration_blacklist',
                's.configuration',
                'gs.name as graduation_status',
                'st.good_data',
                'ur.id AS userContextId',
                'c.bit_value as cert_bit',
                'c.description as cert_description',
                'st.graduation_month',
                'st.graduation_year',
                'st.field_shift_limit',
                'st.clinical_shift_limit'
            );
        }

        // instead get array-hydration of just three simple fields
        $qb->from('\Fisdap\Entity\StudentLegacy', 'st')
            ->leftJoin('st.user', 'usr')
            ->leftJoin('st.user_context', 'ur')
            ->leftJoin('ur.serialNumbers', 's')
            ->leftJoin('ur.certification_level', 'c')
            ->leftJoin('st.graduation_status', 'gs')
            ->where('st.program = ?1')
            ->andWhere('st.username != ?2')
            ->andWhere('usr.id IS NOT NULL')
            ->andWhere('s.student_id > 0')
            ->orderBy('st.last_name, st.first_name', 'ASC')
            ->setParameter(1, $programID)
            ->setParameter(2, "NotActiveYet");

        if ($only_active) {
            $qb->andWhere('ur.end_date > CURRENT_DATE()');
        }

        $results = $this->completeStandardFilteredUserQuery($qb, $filters, $just_userContextId);

        return $results;
    }

    // studentIds must be a flat array
    public function getStudentNames($studentIds)
    {
        $qb = $this->_em->createQueryBuilder();

        // instead get array-hydration of just two simple fields
        $qb->select('st.id', 'usr.first_name', 'usr.last_name')
            ->from('\Fisdap\Entity\StudentLegacy', 'st')
            ->leftJoin('st.user', 'usr')
            ->leftJoin('st.user_context', 'ur')
            ->add('where', $qb->expr()->in('st.id', $studentIds))
            ->orderBy('usr.last_name, usr.first_name', 'ASC');
        $results = $qb->getQuery()->getResult();

        return $results;
    }


    /**
     * @param int[] $userContextIds
     *
     * @return array
     */
    public function getNamesByContextIds(array $userContextIds)
    {
        $qb = $this->_em->createQueryBuilder();

        // instead get array-hydration of just two simple fields
        $qb->select('ur.id', 'usr.first_name', 'usr.last_name')
            ->from('\Fisdap\Entity\UserContext', 'ur')
            ->leftJoin('ur.user', 'usr')
            ->add('where', $qb->expr()->in('ur.id', $userContextIds))
            ->orderBy('usr.last_name, usr.first_name', 'ASC');

        return $qb->getQuery()->getResult();
    }


    /**
     * Get all students that have exactly ONE shift on the given date
     * @return array containing student ID, user ID, first and last name
     */
    public function getAllStudentsByShiftDate($programId, $shiftDate, $filters = array())
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('st.id', 'usr.first_name', 'usr.last_name', 'usr.id AS user_id')
            ->from('\Fisdap\Entity\StudentLegacy', 'st')
            ->leftJoin('st.user', 'usr')
            ->leftJoin('st.shifts', 'shift')
            ->leftJoin('st.user_context', 'ur')
            ->leftJoin('usr.serial_numbers', 's')
            ->leftJoin('ur.certification_level', 'c')
            ->where('st.program = ?1')
            ->andWhere('usr.id IS NOT NULL')
            ->andWhere('shift.start_date = ?2')
            ->andWhere('shift.type = ?3')
            ->having('count(shift) = 1')
            ->orderBy('st.last_name, st.first_name', 'ASC')
            ->groupBy('st.id')
            ->setParameter(1, $programId)
            ->setParameter(2, $shiftDate)
            ->setParameter(3, "lab");

        $results = $this->completeStandardFilteredUserQuery($qb, $filters);

        return $results;
    }

    public function getAllStudentsByProgramWithProductData($programID, $filters = array())
    {
        $qb = $this->_em->createQueryBuilder();

        // We are no longer pulling entities in this query, so this is commented out
        //$qb->select('st, usr, css, cs, ur')

        // instead get array-hydration of just three simple fields
        $qb->select('st.id', 'usr.username', 'usr.first_name', 'usr.last_name', 'usr.id AS user_id', 'ur.end_date', 'sn.configuration')
            ->from('\Fisdap\Entity\StudentLegacy', 'st')
            ->leftJoin('st.user', 'usr')
            ->leftJoin('st.user_context', 'ur')
            ->leftJoin('ur.certification_level', 'c')
            ->leftJoin('usr.serial_numbers', 'sn')
            ->where('st.program = ?1')
            ->andWhere('st.username != ?2')
            ->andWhere('usr.id IS NOT NULL')
            ->orderBy('st.last_name, st.first_name', 'ASC')
            ->setParameter(1, $programID)
            ->setParameter(2, "NotActiveYet");

        $results = $this->completeStandardFilteredUserQuery($qb, $filters);

        $keyedResults = array();
        foreach ($results as $userInfo) {
            $keyedResults[$userInfo['user_id']] = $userInfo;
        }
        unset($results);

        return $keyedResults;
    }

    public function findStudents($program_id, $searchString = "", $limit=null)//, $certification_levels = array(), $graduation_date = array('month' => -1, 'year' => -1))
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('st, usr')
            ->from('\Fisdap\Entity\StudentLegacy', 'st')
            ->leftJoin('st.user', 'usr')
            ->where('st.program = ?1')
            ->add('orderBy', 'usr.last_name ASC, usr.first_name ASC')
            ->setParameter(1, $program_id);

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        //create keyword search
        $searchTerms = preg_split("/\s+|,/", str_replace("'", "", $searchString));

        if ($searchString) {
            foreach ($searchTerms as $term) {
                if (trim($term) != '') {
                    $term = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $term) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
                    $qb->andWhere("usr.first_name LIKE '%$term%' OR usr.last_name LIKE '%$term%' OR usr.username LIKE '%$term%' OR usr.email LIKE '%$term%'");
                }
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function getAllInstructorsByProgram($programID, $filters = array(), $just_userContextId = false)
    {
        $qb = $this->_em->createQueryBuilder();

        if ($just_userContextId) {
            $qb->select('ur.id');
        } else {
            $qb->select('usr.first_name', 'usr.last_name', 'i.id', 'ur.id as userContextId');
        }

        $qb->from('\Fisdap\Entity\InstructorLegacy', 'i')
            ->leftJoin('i.user', 'usr')
            ->leftJoin('i.user_context', 'ur')
            ->where('ur.program = ?1')
            ->andWhere('usr.id IS NOT NULL')
            ->andWhere('usr.id NOT IN (\'SELECT user_id FROM staffData\')')
            ->orderBy('usr.last_name, usr.first_name', 'ASC')
            ->setParameter(1, $programID);

        return $qb->getQuery()->getResult();
    }

    public function getAllPeopleFormOptions($program_id)
    {
        $instrutors = $this->getAllInstructorsByProgram($program_id);
        $students = $this->getAllStudentsByProgram($program_id);


        $options = array();

        if ($instrutors) {
            $options['Instructors'] = array();
            foreach ($instrutors as $instructor) {
                $options['Instructors'][$instructor['userContextId']] = $instructor['first_name'] . " " . $instructor['last_name'] . ", Instructor";
            }
        }

        if ($students) {
            $options['Students'] = array();
            foreach ($students as $student) {
                $options['Students'][$student['userContextId']] = $student['first_name'] . " " . $student['last_name'] . ", " . $student['cert_description'];
            }
        }

        return $options;
    }

    public function getStudentEntity($userId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('stu')
            ->from('\Fisdap\Entity\StudentLegacy', 'stu')
            ->leftJoin('stu.user', 'usr')
            ->where('usr.id = ?1')
            ->setParameter(1, $userId);

        return $qb->getQuery()->getResult();
    }

    public function findInstructors($program_id, $searchString = "", $limit=null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('i.id, usr.first_name, usr.last_name')
            ->from('\Fisdap\Entity\InstructorLegacy', 'i')
            ->leftJoin('i.user', 'usr')
            ->leftJoin('i.user_context', 'ur')
            ->where('ur.program = ?1')
            ->add('orderBy', 'usr.last_name ASC, usr.first_name ASC')
            ->setParameter(1, $program_id);

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        //create keyword search
        $searchTerms = preg_split('/\s+|,/', str_replace("'", "", $searchString));

        if ($searchString) {
            foreach ($searchTerms as $term) {
                if (trim($term) != '') {
                    $term = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $term) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
                    $qb->andWhere("usr.first_name LIKE '%$term%' OR usr.last_name LIKE '%$term%' OR usr.username LIKE '%$term%' OR usr.email LIKE '%$term%'");
                }
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function getStudentsByEmail($email)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('st, usr')
            ->from('\Fisdap\Entity\StudentLegacy', 'st')
            ->leftJoin('st.user', 'usr')
            ->where('usr.email = ?1')
            ->add('orderBy', 'usr.last_name ASC, usr.first_name ASC')
            ->setParameter(1, $email);


        return $qb->getQuery()->getResult();
    }

    /**
     * @param $email
     *
     * @return array
     * @codeCoverageIgnore
     * @deprecated
     */
    public function getUsersByEmail($email)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('u')
            ->from('\Fisdap\Entity\User', 'u')
            ->where('u.email = ?1')
            ->add('orderBy', 'u.last_name ASC, u.first_name ASC')
            ->setParameter(1, $email);


        return $qb->getQuery()->getResult();
    }

    public function getUserConfiguration($userId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('config.configuration')
            ->from('\Fisdap\Entity\SerialNumberLegacy', 'config')
            ->where('config.student = ?1')
            ->setParameter(1, $userId);


        return array_pop($qb->getQuery()->getResult());
    }

    /*
     * Searches for user accounts ($searchString = username). Not just students.
     * passing $selectFields causes arrays to be returned instead of entity objects!!
     */
    public function findUsers($program_id = null, $searchString = "", $limit=null, $includeRoles = array('student', 'instructor', 'staff'), $selectFields = array())
    {
        $qb = $this->_em->createQueryBuilder();

        if (!empty($selectFields)) {
            // Select using the fields listed in selectFields.
            // Fields should be prefixed with u.* or one of the joined tables (if join applies): st.*, inst.*, staff.*
            $qb->select($selectFields)
                ->from('\Fisdap\Entity\User', 'u');
        } else {
            $qb->select('u')
                ->from('\Fisdap\Entity\User', 'u');
        }

        $qb->leftJoin('u.userContexts', 'ur')
            ->leftJoin('ur.role', 'r');

        $whereIsSet = false;

        if ($program_id) {
            $qb->where('ur.program = ?1');
            $whereIsSet = true;

            if (in_array('student', $includeRoles)) {
                $qb->andWhere('r.id = 1');
            }

            if (in_array('instructor', $includeRoles)) {
                $qb->andWhere('r.id = 2');
            }

            // staff are ignored when program ID is set, because staff have no program

            $qb->setParameter(1, $program_id);
        } elseif (count($includeRoles) < 3) {  // assume there are three roles possible
            // limit by roles without a program ID being set
            if (in_array('student', $includeRoles)) {
                if ($whereIsSet) {
                    $qb->orWhere('r.id = 1');
                } else {
                    $qb->where('r.id = 1');
                    $whereIsSet = true;
                }
            }
            if (in_array('instructor', $includeRoles)) {
                if ($whereIsSet) {
                    $qb->orWhere('r.id = 2');
                } else {
                    $qb->where('r.id = 2');
                    $whereIsSet = true;
                }
            }
            if (in_array('staff', $includeRoles)) {
                $qb->leftJoin('u.staff', 'staff');
                if ($whereIsSet) {
                    $qb->orWhere('staff.staffId > 0');
                } else {
                    $qb->where('staff.staffId > 0');
                    $whereIsSet = true;
                }
            }
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        //create keyword search
        $searchTerms = preg_split("/\s+|,/", str_replace("'", "", $searchString));

        if ($searchString) {
            foreach ($searchTerms as $term) {
                if (trim($term) != '') {
                    $term = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $term) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
                    $qb->andWhere("u.username LIKE '%$term%'");
                }
            }
        }

        // add orderBy
        $qb->orderBy('u.email', 'ASC');


        $query = $qb->getQuery();

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $username
     * @return mixed
     * @codeCoverageIgnore
     * @deprecated - there's no need for this specific method, because Doctrine supports magic calls
     *  with findBy() and findByOne() methods on Repositories.
     * @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/working-with-objects.html#by-simple-conditions
     *
     * findOneByUsername() is equivalent to this method.
     */
    public function getUserByUsername($username)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('u')
            ->from('\Fisdap\Entity\User', 'u')
            ->where('u.username = ?1')
            ->setParameter(1, $username);

        $result = $qb->getQuery()->getResult();

        return array_pop($result);
    }
    /*
     * Searches for user accounts by searchString
     */
    public function searchUsers($searchString)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('u')
            ->from('\Fisdap\Entity\User', 'u')
            ->leftJoin('u.serial_numbers', 's')
            ->leftJoin('u.userContexts', 'ur')
            ->leftJoin('ur.program', 'p')
            ->leftJoin('ur.role', 'r');

        //create keyword search
        $searchTerms = preg_split("/\s+|,/", str_replace("'", "", $searchString));

        foreach ($searchTerms as $term) {
            if (trim($term) != '') {
                $term = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $term) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
                $clause = "(u.first_name LIKE '%$term%' OR ".
                    "u.last_name LIKE '%$term%' OR ".
                    "p.name LIKE '%$term%' OR ".
                    "u.username LIKE '%$term%' OR ".
                    "u.email LIKE '%$term%' OR ".
                    "u.home_phone LIKE '%$term%' OR ".
                    "u.work_phone LIKE '%$term%' OR ".
                    "s.number LIKE '%$term%' OR ".
                    "s.student_id LIKE '%$term%' OR ".
                    "s.instructor_id LIKE '%$term%' OR ".
                    "s.dist_method LIKE '%$term%' OR ".
                    "s.purchase_order LIKE '%$term%')
					  AND p.id IS NOT NULL";
                $qb->andWhere($clause);
            }
        }

        // add orderBy
        $qb->orderBy('u.email', 'ASC');
        $qb->setMaxResults(500);
        $query = $qb->getQuery();
        return $qb->getQuery()->getResult();
    }

    /**
     * Get certain fields for all users, return keyed array of the resulting data
     */
    public function getAllUsers($fields = array('id'))
    {
        // if id is not included, add it to $fields
        if (!in_array('id', $fields)) {
            $fields[] = 'id';
        }

        // reformat fields so that they start with "u."
        foreach ($fields as $key => $name) {
            $fields[$key] = 'u.' . $name;
        }

        $qb = $this->_em->createQueryBuilder();

        $qb->select($fields)
            ->from('\Fisdap\Entity\User', 'u')
            ->add('orderBy', 'u.email ASC');
        $result = $qb->getQuery()->getResult();

        $keyedResults = array();
        foreach ($result as $user) {
            $keyedResults[$user['id']] = $user;
        }

        return $keyedResults;
    }

    /**
     * Get array-hydrated user data filtered by certain user criteria.
     * Don't prepend table name to fieldnames, as this function will do it for you
     * Very similar to getAllUsers() above, proabbly could be combined
     * @param array $criteria array of fieldname => value to filter by
     * @param array $fields array of fieldnames
     */
    public function getCertainUsers($criteria = array(), $fields = array('id'))
    {
        // if id is not included, add it to $fields
        if (!in_array('id', $fields)) {
            $fields[] = 'id';
        }

        // reformat fields so that they start with "u."
        foreach ($fields as $key => $name) {
            $fields[$key] = 'u.' . $name;
        }


        $qb = $this->_em->createQueryBuilder();

        $qb->select($fields)
            ->from('\Fisdap\Entity\User', 'u')
            ->add('orderBy', 'u.email ASC');

        // add criteria
        $count = 1;
        foreach ($criteria as $fieldName => $value) {
            if (is_array($value)) {
                $qb->andWhere($qb->expr()->in('u.' . $fieldName, $value));
            } else {
                $qb->andWhere('u.' . $fieldName . ' = ?' . $count);
                $qb->setParameter($count, $value);
                $count++;
            }
        }

        $result = $qb->getQuery()->getResult();

        $keyedResults = array();
        foreach ($result as $user) {
            $keyedResults[$user['id']] = $user;
        }

        return $keyedResults;
    }

    /**
     * Enroll a particular user into a moodle course
     * Lovely SQL since we only use it in one circumstance
     */
    public function enrollInMoodleCourse($product, $username)
    {
        if ($product->id == 9) { // preceptor training
            $tableName = "moodle_PrecepTrainingEnroll";
        } elseif ($product->category->id == 3) { // study tools
            $tableName = "moodle_StudyToolsEnroll";
        } elseif ($product->category->id == 2) { // secure testing
            $tableName = "moodle_SecureTestingEnroll";
        } else { // something is wrong
            return false;
        }
        $conn = $this->_em->getConnection();

        // Is this user ALREADY enrolled?
        $checkSql = "SELECT COUNT(*) AS count FROM " . $tableName . " WHERE moodlecourse = '" . $product->moodle_course_id . "' AND username = '" . $username . "'";
        $checkResult = $conn->query($checkSql);
        $count = $checkResult->fetch();
        if ($count['count'] == 0) {
            // we actually need to enroll this user.

            // in addition to enrollment, we need to clear out any old Moodle Quiz Overrides for this user
            // which might have, in the past, allowed or prevented the user from accessing extra quiz attempts
            // if we don't remove any lingering overrides, the user may not be able to take quizzes
            \Fisdap\MoodleUtils::removeUserQuizOverrides(array(array('username' => $username)), $product->moodle_quizzes);

            $sql = "
					INSERT INTO " . $tableName . "
					(moodlecourse, username)
					VALUES ('" . $product->moodle_course_id . "', '" . $username . "')
					";

            $result = $conn->query($sql);
            return $result;
        }
    }


    /**
     * DISenroll a particular user into a moodle course
     * Lovely SQL since we only use it in one circumstance
     * Keep in mind that disenrolling will remove the user's access to ALL courses within that context
     */
    public function disenrollInMoodleCourse($product, $username)
    {
        if ($product->id == 9) { // preceptor training
            $tableName = "moodle_PrecepTrainingEnroll";
        } elseif ($product->category->id == 3) { // study tools
            $tableName = "moodle_StudyToolsEnroll";
        } elseif ($product->category->id == 2) { // secure testing
            $tableName = "moodle_SecureTestingEnroll";
        } else { // something is wrong
            return false;
        }

        $sql = "
				DELETE FROM " . $tableName . "
				WHERE moodlecourse = " . $product->moodle_course_id . " AND username = '" . $username . "'
				";

        $conn = $this->_em->getConnection();
        $result = $conn->query($sql);
        return $result;
    }


    /**
     * @param string $username
     * @return array|User
     */
    public function getOneByUsername($username)
    {
        return $this->findOneByUsername($username);
    }

    /**
     * @param array $usernames
     *
     * @return array
     */
    public function getByUsername(array $usernames)
    {
        return $this->findByUsername($usernames);
    }
}
