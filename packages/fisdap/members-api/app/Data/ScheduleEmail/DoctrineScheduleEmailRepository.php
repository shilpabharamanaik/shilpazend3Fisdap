<?php namespace Fisdap\Data\ScheduleEmail;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineScheduleEmailRepository
 *
 * @package   Fisdap\Data\ScheduleEmail
 * @copyright 1996-2015 Headwaters Software, Inc.
 */
class DoctrineScheduleEmailRepository extends DoctrineRepository implements ScheduleEmailRepository
{
    /**
     * @inheritdoc
     */
    public function getScheduleEmails($program_id)
    {
        $qb = $this->createQueryBuilder('se');

        $qb->where('se.program = ?1')
            ->andWhere('se.filter IS NOT NULL')
            ->setParameter(1, $program_id)
            ->orderBy('se.title, se.id');

        return $qb->getQuery()->getResult();
    }


    /**
     * @inheritdoc
     */
    public function getActiveScheduleEmails()
    {
        $qb = $this->createQueryBuilder('se');

        $qb->where('se.active = 1')
            ->andWhere('se.filter IS NOT NULL');

        return $qb->getQuery()->iterate();
    }
}
