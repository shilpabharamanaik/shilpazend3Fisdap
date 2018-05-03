<?php namespace Fisdap\Data\Requirement;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineRequirementCategoryRepository
 *
 * @package Fisdap\Data\Requirement
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineRequirementCategoryRepository extends DoctrineRepository implements RequirementCategoryRepository
{
	public function getFormOptions()
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select("c")
		   ->from("\Fisdap\Entity\RequirementCategory", "c");
		   
		$data = $qb->getQuery()->getArrayResult();
		$return_vals = array();
		foreach($data as $cat){
			$return_vals[$cat['id']] = $cat['name'];
		}

		return $return_vals;
	}
}
