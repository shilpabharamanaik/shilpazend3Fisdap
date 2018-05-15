<?php namespace Fisdap\Data\Narrative;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineNarrativeSectionDefinitionRepository
 *
 * @package Fisdap\Data\Narrative
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineNarrativeSectionDefinitionRepository extends DoctrineRepository implements NarrativeSectionDefinitionRepository
{
    public function getNarrativeSectionsByProgram($programID, $activeOnly = false)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('sd')
           ->from('\Fisdap\Entity\NarrativeSectionDefinition', 'sd')
           ->where('sd.program_id = ?1')
           ->orderBy('sd.section_order, sd.id', 'ASC')
           ->setParameter(1, $programID);

        if ($activeOnly) {
            $qb->andWhere('sd.active = 1');
        }

        $results = $qb->getQuery()->getResult();

        // re-key the array so it's keyed by section id
        $sections = array();
        foreach ($results as $section) {
            $sections[$section->id] = $section;
        }

        return $sections;
    }
}
