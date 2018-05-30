<?php namespace Fisdap\Data\Practice;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrinePracticeDefinitionRepository
 *
 * @package Fisdap\Data\Practice
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrinePracticeDefinitionRepository extends DoctrineRepository implements PracticeDefinitionRepository
{
    public function getProgramDefinitions($program, $certLevel = null, $all = true, $active = true)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('def')
            ->from('\Fisdap\Entity\PracticeDefinition', 'def')
            ->join('def.category', 'cat');
                
        $qb->where('def.program = ?1');
        
        if (!$all) {
            $qb->andWhere('def.active = ?3');
        }
        
        $qb->setParameter(1, $program);
        

        
        
        if ($certLevel) {
            $qb->andWhere('def.certification_level = ?2')
            ->setParameter(2, $certLevel);
        }
        
        if (!$all) {
            $qb->setParameter(3, $active);
        }
        
        return $qb->getQuery()->getResult();
    }
}
