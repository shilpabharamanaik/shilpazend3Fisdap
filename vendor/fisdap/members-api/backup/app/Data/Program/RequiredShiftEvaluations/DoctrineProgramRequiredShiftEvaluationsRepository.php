<?php namespace Fisdap\Data\Program\RequiredShiftEvaluations;

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Entity\ProgramRequiredShiftEvaluations;

/**
 * Class DoctrineProgramRequiredShiftEvaluationsRepository
 *
 * @package Fisdap\Data\Program\RequiredShiftEvaluations
 * @author  Scott McIntyre <smcintyre@fisdap.net>
 */
class DoctrineProgramRequiredShiftEvaluationsRepository extends DoctrineRepository implements ProgramRequiredShiftEvaluationsRepository
{
    /**
     * Get all the required evaluations for a program and optional shift type
     * @param $program_id int, $shift_type string
     * @return mixed
     */

    public function getByProgram($program_id, $shift_type = null, $eval_def_id = null)
    {

        $qb = $this->_em->createQueryBuilder();

        $qb->select('re')
            ->from(ProgramRequiredShiftEvaluations::class, 're')
            ->where('re.program = ?1')
            ->setParameter(1, $program_id);

        if(!is_null($shift_type)){
            $qb->andWhere('re.shift_type = ?2')
                ->setParameter(2,$shift_type);
        }

        if(!is_null($eval_def_id)){
            $qb->andWhere('re.eval_def = ?3')
                ->setParameter(3,$eval_def_id);
        }

        return $qb->getQuery()->getResult();

    }

}
