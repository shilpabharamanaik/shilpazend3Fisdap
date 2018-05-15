<?php namespace Fisdap\Data\Permission;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrinePermissionHistoryLegacyRepository
 *
 * @package Fisdap\Data\Permission
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrinePermissionHistoryLegacyRepository extends DoctrineRepository implements PermissionHistoryLegacyRepository
{
    public function getAllByInstructor($instructorId)
    {
        $qb = $this->_em->createQueryBuilder();

        $instructor = \Fisdap\EntityUtils::getEntity("InstructorLegacy", $instructorId);
        
        $qb->select('record')
           ->from('\Fisdap\Entity\PermissionHistoryLegacy', 'record')
           ->where('record.changed_instructor = ?1')
           ->add('orderBy', 'record.entry_time DESC')
           ->setParameter(1, $instructor);
        
        return $qb->getQuery()->getResult();
    }
}
