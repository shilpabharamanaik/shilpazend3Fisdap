<?php namespace Fisdap\Data\Goal;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineGoalRepository
 *
 * @package Fisdap\Data\Goal
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineGoalRepository extends DoctrineRepository implements GoalRepository
{	
	public function getGoalSetsByProgram($program)
	{
		$programId = $program->id;
		
		$goalSet = $this->_em->getRepository('\Fisdap\Entity\GoalSet')->findOneBy(array('program' => $programId));
		
		return $goalSet;
	}

    public function getGoalSetById($id)
    {
        $goalSet = $this->_em->getRepository('\Fisdap\Entity\GoalSet')->find($id);

        return $goalSet;
    }
	
	/**
	 *	$programId=0 in goal def table means goals for any program
	 *	then each program may have their goals.
	 */
	public function getGoalDefsForProgram($programId = 0)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('gdef')
			->from('\Fisdap\Entity\GoalDef', 'gdef')
			->where('gdef.program_id = ?1 or gdef.program_id = ?2')
		    ->orderBy('gdef.display_order', 'ASC')
		   //->orderBy('usr.first_name', 'ASC')
		   ->setParameter(1, $programId)
		   ->setParameter(2, 0);
		
		$results = $qb->getQuery()->getResult();
		
		return $results;
	}
	
	public function getGoalsForGoalSet($goalSetId)
	{
		$qb = $this->_em->createQueryBuilder();
		
		$qb->select('gs, g, gdef')
			->from('\Fisdap\Entity\GoalSet', 'gs')
			->leftJoin('gs.goals', 'g')
			->leftJoin('g.def', 'gdef')
			->where('gs.id = ?1')
			->orderBy('gdef.category', 'ASC')
			->setParameter(1, $goalSetId);

		$results = $qb->getQuery()->getResult();

		return array_pop($results);
	}

    public function getProgramGoalSets($programId, $includeStdCurr=false, $requiredGoalDefs=null)
    {
        $qb = $this->_em->createQueryBuilder();

        $stdNationalCurr = ($includeStdCurr) ? ' or gs.program = 0' : '';

        $qb->select('gs')
            ->from('\Fisdap\Entity\GoalSet', 'gs')
            ->leftJoin('\Fisdap\Entity\Goal', 'g', 'WITH', 'g.goalSet = gs')
            ->addSelect('CASE WHEN gs.name = ?2 THEN CASE WHEN gs.goalset_template = 1 THEN 0 ELSE 1 END ELSE 1 END AS HIDDEN nscCondition')
            ->where('gs.program = ?1' . $stdNationalCurr)
            ->andWhere('gs.id != 2')
            ->andWhere('gs.id != 3')
            ->orderBy('gs.program, nscCondition, gs.name', 'ASC')	// show standard national curriculum first
            ->setParameter(1, $programId)
            ->setParameter(2, "National Standard Curriculum");

        if ($requiredGoalDefs) {
            $qb->andWhere('g.def IN (?3)')
                ->setParameter(3, $requiredGoalDefs);
        }

        $results = $qb->getQuery()->getResult();
        return $results;
    }
	
	/**
	 *	Default goal set for program Id,
	 *	if no program Id given, it will be bare goal set for any program
	 */
	public function getNewGoalSet($programId = null)
	{
		return self::getNewGoalSetFromDefs($programId);
	}
	
	/**
	 *	@param mixed \Fisdap\Entity\GoalSet or goalSetId
	 */	
	public function getNewGoalSetFromOtherGoalSet($otherGoalSet = 1)
	{
		if (is_integer($otherGoalSet)) {
			$otherGoalSetId = $goalSet;
		} else {
			$otherGoalSetId->id;
		}
		
		//	$goalSet = \Fisdap\EntityUtils::getEntity('GoalSet', $goalSet);
		$otherGoalSet = $self::getGoalsForGoalSet($otherGoalSetId);
		
		
	}
	
	public function getNewGoalSetFromDefs($programId = null)
	{
		$program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $programId);
		
		$defs = self::getGoalDefsForProgram($programId);
		
		$goalSet = \Fisdap\EntityUtils::getEntity('GoalSet');
		$goalSet->program = $program;
		
		foreach ($defs as $def) {
			//$def = \Fisdap\EntityUtils::getEntity('GoalDef');
			$goal = \Fisdap\EntityUtils::getEntity('Goal');
			$goal->def = $def;
			
			// default values from def to goal
			$goal->number_required = $def->def_goal_number_required;
			$goal->max_last_data = $def->def_goal_max_last_data;
			$goal->percent_successful = $def->def_goal_percent_successful;
			$goal->team_lead = $def->team_lead;
			$goal->interview = $def->interview;
			$goal->exam = $def->exam;
			
			$goal->goalSet = $goalSet;
			$goalSet->goals->add($goal);
		}
		
		return $goalSet;
	}
}
