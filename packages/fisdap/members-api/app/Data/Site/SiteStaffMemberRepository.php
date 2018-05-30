<?php
/**
 * Created by PhpStorm.
 * User: khanson
 * Date: 10/2/14
 * Time: 10:03 AM
 */

namespace Fisdap\Data\Site;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\SiteLegacy;

/**
 * Interface SiteStaffMemberRepository
 *
 * @package Fisdap\Data\Site
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
interface SiteStaffMemberRepository extends Repository
{

    /**
     * Get the staff members associated with this site and program (or network)
     *
     * @param SiteLegacy $site the site
     * @param array $programs an array of program ids for this program or the programs in this network
     * @return array an array of SiteStaffMembers
     */
    public function getStaffMembersBySiteAndProgram(SiteLegacy $site, array $programs);
}
