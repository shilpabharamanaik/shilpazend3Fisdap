<?php namespace Fisdap\Data\Slot;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;


/**
 * Interface SlotAssignmentRepository
 *
 * @package Fisdap\Data\Slot
 */
interface SlotAssignmentRepository extends Repository
{
    /**
     * @param int       $userContextId
     * @param \DateTime $startdate
     * @param \DateTime $enddate
     *
     * @param bool      $returnArray
     *
     * @return array
     */
    public function getUserContextAssignmentsByDate($userContextId, $startdate = null, $enddate = null, $returnArray = false);


    /**
     * @param int       $userContextId
     * @param \DateTime $startdate
     * @param \DateTime $enddate
     * @param integer   $shiftId Ignore this shift ID when searching for assignments
     *
     * @return array
     */
    public function getUserContextAssignmentIdsByDate($userContextId, $startdate = null, $enddate = null, $shiftId = null);
}