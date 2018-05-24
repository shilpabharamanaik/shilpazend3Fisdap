<?php namespace Fisdap\Data\Report;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;


/**
 * Interface ReportConfigurationRepository
 *
 * @package Fisdap\Data\Report
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
interface ReportConfigurationRepository extends Repository {

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     *
     * @return mixed
     */
    public function getByTimeRangeQuery(\DateTime $start, \DateTime $end);
}