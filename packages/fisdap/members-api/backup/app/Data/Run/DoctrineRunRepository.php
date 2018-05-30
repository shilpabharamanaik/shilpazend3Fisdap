<?php namespace Fisdap\Data\Run;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineRunRepository
 *
 * @package Fisdap\Data\Run
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineRunRepository extends DoctrineRepository implements RunRepository
{
    public function getRunsByShift($shiftId, $filters = null)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('r, s')
           ->from('\Fisdap\Entity\Run', 'r')
           ->leftJoin('r.shift', 's')
           ->where('r.shift = ?1')
           ->setParameter(1, $shiftId);
        
        return $qb->getQuery()->getResult();
    }
    
    public function getPatientsNotTeamLead($studentId, $type, $filters = array())
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select("p.team_lead, p.interview, p.exam, s.id as shift_id, p.age, g.name as gender, e.name as ethnicity, s.start_datetime, r.id as run_id")
           ->from("\Fisdap\Entity\Patient", "p")
           ->join("p.shift", "s")
           ->join("p.run", "r")
           ->join("p.gender", "g")
           ->leftjoin("p.ethnicity", "e")
           ->andWhere("s.type = ?1")
           ->andWhere("p.student = ?2")
           ->setParameter(1, $type)
           ->setParameter(2, $studentId);
        
        if ($type != "clinical") {
            $qb->andWhere("(p.exam != 1 OR p.exam IS NULL) OR (p.interview != 1 OR p.interview IS NULL) OR (p.team_lead != 1 OR p.team_lead IS NULL)");
        } else {
            $qb->andWhere("(p.exam != 1 OR p.exam IS NULL) OR (p.interview != 1 OR p.interview IS NULL)");
        }
        
        if ($filters['startDate']) {
            $qb->andWhere("s.start_datetime >= ?3")
               ->setParameter(3, new \DateTime($filters['startDate']));
        }
        
        if ($filters['endDate']) {
            $endDate = new \DateTime($filters['endDate']);
            $endDate->setTime(23, 59);
            $qb->andWhere("s.end_datetime <= ?4")
               ->setParameter(4, $endDate);
        }
        
        if ($filters['subject']) {
            $qb->andWhere($qb->expr()->in('p.subject', $filters['subject']));
        }
        
        if ($filters['sites']) {
            $qb->andWhere($qb->expr()->in('s.site', $filters['sites']));
        }
        
        
        
        return $qb->getQuery()->getResult();
    }
}
