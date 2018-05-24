<?php namespace Fisdap\Data\Event;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;


/**
 * Interface EventLegacyRepository
 *
 * @package Fisdap\Data\Event
 */
interface EventLegacyRepository extends Repository
{
    /**
     * Get the first event at each site assigned to a given UserContext
     *
     * @param int $userContextId
     *
     * @return array keyed by site_id
     */
    public function getUpcomingEventsByUserContextId($userContextId);
} 