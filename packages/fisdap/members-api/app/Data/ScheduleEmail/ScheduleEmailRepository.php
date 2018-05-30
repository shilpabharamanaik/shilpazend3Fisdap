<?php namespace Fisdap\Data\ScheduleEmail;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Doctrine\ORM\Internal\Hydration\IterableResult;
use Fisdap\Data\Repository\Repository;

/**
 * Interface ScheduleEmailRepository
 *
 * @package Fisdap\Data\ScheduleEmail
 * @copyright 1996-2015 Headwaters Software, Inc.
 */
interface ScheduleEmailRepository extends Repository
{
    /**
     * Get all recurring emails
     *
     * @param int $program_id
     *
     * @return array
     */
    public function getScheduleEmails($program_id);


    /**
     * Get all active recurring emails
     *
     * @return IterableResult
     */
    public function getActiveScheduleEmails();
}
