<?php namespace Fisdap\Data\Practice;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrinePracticeSkillRepository
 *
 * @package Fisdap\Data\Practice
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrinePracticeSkillRepository extends DoctrineRepository implements PracticeSkillRepository
{
	public function getAllFormOptions($categorize)
	{
		$qb = $this->_em->createQueryBuilder();

		$qb->select('skill')
		   ->from('\Fisdap\Entity\PracticeSkill', 'skill');
		   
		$results = $qb->getQuery()->getResult();
		$returnValues = array();
		
		if($categorize) {
			foreach($results as $skill){
				
				$optGroup = $skill->entity_name;
				
				if($optGroup == "Iv"){
					$optGroup = "Venous Access";
				}
				else if(strpos($optGroup,'Intervention') !== false){
					$optGroup = substr($optGroup, 0, -12);
				}
				
				if($returnValues[$optGroup]){
					$returnValues[$optGroup][$skill->id] = $skill->name;
				}
				else {
					$returnValues[$optGroup] = array();
					$returnValues[$optGroup][$skill->id] = $skill->name;
				}
			}
		}
		else {
			foreach($results as $skill){
				$returnValues[$skill->id] = $skill->name;
			}
		}
		
		return $returnValues;
	}
}