<?php namespace Fisdap\Data\Practice;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrinePracticeCategoryRepository
 *
 * @package Fisdap\Data\Practice
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrinePracticeCategoryRepository extends DoctrineRepository implements PracticeCategoryRepository
{
    public function getAllByProgram($programId, $certLevelId = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
        $certLevel = \Fisdap\EntityUtils::getEntity("CertificationLevel", $certLevelId);
        
        $qb->select('category')
           ->from('\Fisdap\Entity\PracticeCategory', 'category')
           ->where('category.program = ?1')
           ->andWhere($this->getCertLevelQuery($certLevel))
           ->setParameter(1, $program);

        if ($certLevel) {
            $qb->setParameter(2, $certLevel);
        }
        
        return $qb->getQuery()->getResult();
    }
    
    private function getCertLevelQuery($certLevel)
    {
        if ($certLevel) {
            return 'category.certification_level = ?2';
        } else {
            return "";
        }
    }
}
