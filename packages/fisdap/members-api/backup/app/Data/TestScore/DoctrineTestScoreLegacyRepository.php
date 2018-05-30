<?php namespace Fisdap\Data\TestScore;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineTestScoreLegacyRepository
 *
 * @package Fisdap\Data\TestScore
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineTestScoreLegacyRepository extends DoctrineRepository implements TestScoreLegacyRepository
{
    /*
     * Get submitted NREMT scores for all students in a program
     *
     * @param mixed $programId numerical ID of the program or else a ProgramDataLegacy entity
     * @param boolean $hasPassFail If TRUE, method will only return results that show submitted pass or fail data
     *
     * @return array Array of flat values keyed by User ID (UserLegacy)
     */
    public function getNremtScoresByProgram($program, $hasPassFail = false)
    {
        if ($program instanceof \Fisdap\Entity\ProgramLegacy) {
            $programId = $program->id;
        } else {
            $programId = $program;
        }
        
        $qb = $this->_em->createQueryBuilder();
        
        $qb->select('u.id, tt.id AS type_id, tt.test_name, tt.certification_level, ts.id AS ts_id, ts.test_score, ts.pass_or_fail, ts.entry_time')
        ->from('\Fisdap\Entity\TestScoreLegacy', 'ts')
        ->innerJoin('ts.student', 's')
        ->innerJoin('s.user', 'u')
        ->innerJoin('s.program', 'p')
        ->innerJoin('ts.test_type', 'tt')
        ->where('p.id = ?1')
        ->andWhere('ts.test_type IN (1, 2, 3, 4, 21, 22, 23)')
        ->setParameter(1, $programId)
        ->orderBy('ts.entry_time');
        
        if ($hasPassFail) {
            $qb->andWhere('ts.pass_or_fail != -1');
        }
        
        $queryResults = $qb->getQuery()->getResult();
        
        $results = array();
        foreach ($queryResults as $result) {
            $results[$result['id']][$result['ts_id']] = $result;
        }
        
        return $results;
    }

    /**
     * @param int $user
     * @return mixed
     */
    public function getNremtScoresByUser($user)
    {
        if ($user instanceof \Fisdap\Entity\User) {
            $userid = $user->id;
        } else {
            $userid = $user;
        }

        $qb = $this->_em->createQueryBuilder();

        $qb->select('ts')
            ->from('\Fisdap\Entity\TestScoreLegacy', 'ts')
            ->innerJoin('ts.student', 's')
            ->innerJoin('s.user', 'u')
            ->where('u.id = ?1')
            ->andWhere('ts.test_type IN (1, 2, 3, 4, 21, 22, 23)')
            ->setParameter(1, $userid)
            ->orderBy('ts.entry_time');

        $results = $qb->getQuery()->getResult();

        return $results;
    }
}
