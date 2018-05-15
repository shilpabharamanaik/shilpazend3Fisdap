<?php namespace Fisdap\Data\GuidedTour;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineGuidedTourRepository
 *
 * @package Fisdap\Data\GuidedTour
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineGuidedTourRepository extends DoctrineRepository implements GuidedTourRepository
{
    public function getTourHistoryByUser($tour_id, $userContextId)
    {
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('h.id')
           ->from('Fisdap\Entity\GuidedTourHistory', 'h')
           ->andWhere('h.guided_tour = ?1')
           ->andWhere('h.user_context = ?2')
           ->setParameter(1, $tour_id)
           ->setParameter(2, $userContextId);
           
        return $qb->getQuery()->getResult();
    }
}
