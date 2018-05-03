<?php namespace Fisdap\Data\Requirement;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineRequirementHistoryRepository
 *
 * @package Fisdap\Data\Requirement
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineRequirementHistoryRepository extends DoctrineRepository implements RequirementHistoryRepository
{
	public function getFullAttachmentHistory($requirementAttachment)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select("h")
		   ->from("\Fisdap\Entity\RequirementHistory", "h")
		   ->join("h.requirement", "r")
		   ->leftJoin("h.requirement_attachment", "ra")
		   ->andWhere("ra = ?1 OR r = ?2")
		   ->orderBy("r.timestamp")
		   ->setParameters(array(1 => $requirementAttachment, 2 => $requirementAttachment->requirement));
		
		return $qb->getQuery()->getResult();
	}
}