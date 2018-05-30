<?php namespace Fisdap\Data\Report;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class ReportConfigurationRepository
 *
 * @package Fisdap\Data\Report
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineReportConfigurationRepository extends DoctrineRepository implements ReportConfigurationRepository
{
    /**
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return \Doctrine\ORM\Query|mixed
     */
    public function getByTimeRangeQuery(\DateTime $start, \DateTime $end)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('r')
            ->from('\Fisdap\Entity\ReportConfiguration', 'r')
            ->where('r.created > ?1')
            ->andWhere('r.created < ?2')
            ->setParameter(1, $start)
            ->setParameter(2, $end)
            ->orderBy('r.created', 'ASC');

        $query = $qb->getQuery();

        return $query;
    }
}
