<?php namespace Fisdap\Data\Slot;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineSlotAssignmentRepository
 *
 * @package Fisdap\Data\Slot
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineSlotAssignmentRepository extends DoctrineRepository implements SlotAssignmentRepository
{
    /**
     * @inheritdoc
     */
    public function getUserContextAssignmentsByDate($userContextId, $startdate = null, $enddate = null, $returnArray = false)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('a', 's', 'e')
            ->from('\Fisdap\Entity\SlotAssignment', 'a')
            ->join('a.slot', 's')
            ->join('s.event', 'e')
            ->where('a.user_context = ?1')
            ->setParameter(1, $userContextId)
            ->orderBy('e.start_datetime', 'ASC');

        if ($startdate) {
            $qb->andWhere('e.end_datetime >= ?2')
                ->setParameter(2, $startdate->format("Y-m-d H:i:s"));
        }
        if ($enddate) {
            $qb->andWhere('e.start_datetime <= ?3')
                ->setParameter(3, $enddate->format("Y-m-d H:i:s"));
        }

        if ($returnArray) {
            return $qb->getQuery()->getArrayResult();
        } else {
            return $qb->getQuery()->getResult();
        }
    }


    /**
     * @inheritdoc
     */
    public function getUserContextAssignmentIdsByDate($userContextId, $startdate = null, $enddate = null, $shiftId = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('a.id')
            ->from('\Fisdap\Entity\SlotAssignment', 'a')
            ->join('a.slot', 's')
            ->join('s.event', 'e')
            ->leftJoin("a.shift", "shift")
            ->where('a.user_context = ?1')
            ->setParameter(1, $userContextId)
            ->orderBy('e.start_datetime', 'ASC');
        if ($startdate) {
            $qb->andWhere('e.end_datetime >= ?2')
                ->setParameter(2, $startdate->format("Y-m-d H:i:s"));
        }
        if ($enddate) {
            $qb->andWhere('e.start_datetime <= ?3')
                ->setParameter(3, $enddate->format("Y-m-d H:i:s"));
        }

        if ($shiftId) {
            $qb->andWhere("shift.id != ?4")
                ->setParameter(4, $shiftId);
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function getStudentAssignmentsByEvent($event)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select("partial a.{id}", "partial s.{id}", "partial ur.{id}", "partial st.{id}")
            ->from('\Fisdap\Entity\SlotAssignment', "a")
            ->leftJoin("a.user_context", "ur")
            ->leftJoin("a.shift", "s")
            ->leftJoin("a.slot", "sl")
            ->leftJoin("s.student", "st")
            ->where("sl.event = ?1")
            ->andWhere("sl.slot_type = 1")
            ->setParameter(1, $event);

        return $qb->getQuery()->getResult();
    }

    /**
     * Given an array of StudentLegacy IDs, determine if any conflicts exits in the given date range.
     *
     * @param array     $students
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     *
     * @return array
     */
    public function getConflicts(array $students, \DateTime $startDate, \DateTime $endDate)
    {
        $conflicts = array();

        $qb = $this->_em->createQueryBuilder();
        $qb->select('stu.id as student_id, u.first_name, u.last_name, s.start_datetime, s.hours, s.type, site.name as site_name, base.name as base_name')
            ->from('\Fisdap\Entity\ShiftLegacy', 's')
            ->join('s.site', 'site')
            ->join('s.base', 'base')
            ->join('s.student', 'stu')
            ->join('stu.user', 'u')
            ->andWhere($qb->expr()->in('s.student', $students))
            ->andWhere('s.end_datetime >= ?1 AND s.start_datetime <= ?2')
            ->setParameters(array(1 => $startDate, 2 => $endDate));

        $results = $qb->getQuery()->getResult();
        foreach ($results as $result) {
            $conflicts[$result['student_id']][] = $result;
        }

        return $conflicts;
    }
}
