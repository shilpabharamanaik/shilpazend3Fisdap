<?php namespace Fisdap\Data\Maintenance;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineMaintenanceRepository
 *
 * @package Fisdap\Data\Maintenance
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineMaintenanceRepository extends DoctrineRepository implements MaintenanceRepository
{
	/*
	 * Function to retrieve any currently enabled and relevant maintenance warning
	 * Relevant = between warning_starts and downtime_starts
	 */
	public function getCurrentMaintenance() {
		$now = new \DateTime('now');
		
        // construct the query
        $qb = $this->_em->createQueryBuilder();
        
        // Joining instructors so that Doctrine doesn't lazy load them for each
        // model (one-to-ones get lazy loaded).
        $qb->select('maintenance')
           ->from('\Fisdap\Entity\Maintenance', 'maintenance')
           ->where('maintenance.warning_starts <= ?1')
		   ->andWhere('maintenance.downtime_starts > ?1')
		   ->andWhere('maintenance.enabled = 1')
           ->setParameter(1, $now, \Doctrine\DBAL\Types\Type::DATETIME)
		   ->orderBy('maintenance.downtime_starts', 'ASC');
           
        $query = $qb->getQuery();
        //$sql = $query->getSQL();
        $results = $query->getResult();
		
		if (empty($results)) {
			return FALSE;
		} else {
			return current($results);
		}
	}
}