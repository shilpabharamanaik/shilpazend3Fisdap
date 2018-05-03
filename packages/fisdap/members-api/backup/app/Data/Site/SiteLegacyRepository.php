<?php namespace Fisdap\Data\Site;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;


/**
 * Interface SiteLegacyRepository
 *
 * @package Fisdap\Data\Site
 */
interface SiteLegacyRepository extends Repository
{
    /**
     * @param $site_ids
     * @param $program_id
     *
     * @return mixed
     */
    public function getUserContextsAttendingSites($site_ids, $program_id);


    /**
     * @param $site_id
     *
     * @return mixed
     */
    public function getUserContextsAttendingSharedSite($site_id);


    /**
     * @param $userContextId
     *
     * @return mixed
     */
    public function getScheduledSitesByUserContext($userContextId);
}