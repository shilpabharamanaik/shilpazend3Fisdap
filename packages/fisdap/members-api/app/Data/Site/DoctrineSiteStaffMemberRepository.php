<?php
/**
 * Created by PhpStorm.
 * User: khanson
 * Date: 10/2/14
 * Time: 10:06 AM
 */

namespace Fisdap\Data\Site;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Entity\SiteLegacy;


/**
 * Class DoctrineSiteStaffMemberRepository
 *
 * @package Fisdap\Data\Site
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineSiteStaffMemberRepository extends DoctrineRepository implements SiteStaffMemberRepository
{
    /**
     * Get the staff members associated with this site and program (or network)
     *
     * @param SiteLegacy $site the site
     * @param array $programs an array of program ids for this program or the programs in this network
     * @return array an array of SiteStaffMembers
     */
    public function getStaffMembersBySiteAndProgram(SiteLegacy $site, array $programs) {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('ssm')
            ->from('\Fisdap\Entity\SiteStaffMember', 'ssm')
            ->where('ssm.site = ?1')
            ->andWhere($qb->expr()->in('ssm.program', $programs))
            ->setParameter(1, $site->id)
            ->orderBy('ssm.last_name, ssm.first_name');

        return $qb->getQuery()->getResult();
    }

}
