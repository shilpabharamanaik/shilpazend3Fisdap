<?php namespace Fisdap\Data\TCMapState;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineTCMapStatesRepository
 *
 * @package Fisdap\Data\TCMapState
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineTCMapStatesRepository extends DoctrineRepository implements TCMapStatesRepository
{

	public function getStates()
    {
	    $results = $this->findAll();			
		return $results;
    }
	
	public function getUsedColors()
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('distinct state.color')
		   ->from('\Fisdap\Entity\TCMapState', 'state');
		   
		
		return $qb->getQuery()->getResult();
	}
	
	public function getUsedStatuses()
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('distinct state.status')
		   ->from('\Fisdap\Entity\TCMapState', 'state');
		   
		
		return $qb->getQuery()->getResult();
	}

}