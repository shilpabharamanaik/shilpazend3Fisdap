<?php namespace Fisdap\Data\Evals;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineEvalProgramHooksLegacyRepository
 *
 * @package Fisdap\Data\Evals
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineEvalProgramHooksLegacyRepository extends DoctrineRepository implements EvalProgramHooksLegacyRepository
{
    /**
     * Determines if evals exist for a given program and hook or set of hooks,
     * if none exist, return default hook settings
     *
     * @param mixed $hook_ids integer or array of integers representing hook ids
     * @param integer $program_id
     * @return integer the number of evals for the given set of hooks
     */
    public function hasEvalsForHook($hook_ids, $program_id)
    {
	    if (empty($hook_ids)) {
		    return 0;
	    }
	    
	    $qb = $this->_em->createQueryBuilder();
	    
	    $qb->select('COUNT(eph.id)')
	    ->from('\Fisdap\Entity\EvalProgramHooksLegacy', 'eph');
	    
	    if (is_array($hook_ids)) {
		    //$qb->where('st.id IN (?1)');
		    $qb->add('where', $qb->expr()->in('eph.hook', $hook_ids));
			$qb->andWhere('eph.program = ?1');
		    $qb->setParameter(1, $program_id);
	    } else if (is_numeric($hook_ids)) {
		    $qb->where('eph.hook = ?1');
			$qb->andWhere('eph.program = ?2');
			$qb->setParameter(1, $hook_ids);
			$qb->setParameter(2, $program_id);
		}
    
        $hasEvals = $qb->getQuery()->getSingleScalarResult();
		
		if ($hasEvals) {
			return $hasEvals;
		}
		
		//We didn't have any program specific eval hooks, so let's check out the defaults
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('COUNT(ehd.id)')
		   ->from('\Fisdap\Entity\EvalHookDefaultsLegacy', 'ehd');
		   
		if (is_array($hook_ids)) {
		    //$qb->where('st.id IN (?1)');
		    $qb->add('where', $qb->expr()->in('ehd.hook', $hook_ids));
	    } else if (is_numeric($hook_ids)) {
		    $qb->where('ehd.hook = ?1');
			$qb->setParameter(1, $hook_ids);
		}
		
		return $qb->getQuery()->getSingleScalarResult();
    }
}