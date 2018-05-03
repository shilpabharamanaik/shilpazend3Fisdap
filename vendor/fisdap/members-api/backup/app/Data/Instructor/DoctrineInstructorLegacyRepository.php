<?php namespace Fisdap\Data\Instructor;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineInstructorLegacyRepository
 *
 * @package Fisdap\Data\Instructor
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineInstructorLegacyRepository extends DoctrineRepository implements InstructorLegacyRepository
{
    /**
     * This function takes a bitmask and returns any instructor who has a product configuration which
     * matches that mask.
     * @param integer $config
     */
    public function getInstructorsByProductCodeConfig($config, $programId)
    {
        if (!is_numeric($config)) {
            $config = 0;
        }

        $sql = "
			SELECT
				id.Instructor_id,
				id.FirstName,
				id.LastName
			FROM
				fisdap2_users u
				INNER JOIN InstructorData id ON id.user_id = u.id
				INNER JOIN SerialNumbers sn ON sn.User_id = u.id
			WHERE
				sn.Configuration & $config > 0
				AND id.ProgramId = $programId
			ORDER BY
				id.LastName, id.FirstName
		";

        // run MySQL query with Zend DB driver
        $db = \Zend_Registry::get('db');
        $res = $db->query($sql);

        $instructors = array();
        while ($row = $res->fetch()) {
            $instructors[$row['Instructor_id']] = $row['LastName'] . ", " . $row['FirstName'];
        }

        return $instructors;
    }


    /**
     * Get an array of Instructor names keyed by ID given a list of IDs
     *
     * @param array $instructorIds
     *
     * @return array
     */
    public function getInstructorNames(array $instructorIds)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("i.id, u.first_name, u.last_name")
            ->from('\Fisdap\Entity\InstructorLegacy', 'i')
            ->join('i.user', 'u')
            ->where($qb->expr()->in('i.id', $instructorIds));

        $results = $qb->getQuery()->getResult();

        $instructors = [];
        foreach ($results as $result) {
            $instructors[$result['id']] = $result['first_name'] . " " . $result['last_name'];
        }

        return $instructors;
    }
}
