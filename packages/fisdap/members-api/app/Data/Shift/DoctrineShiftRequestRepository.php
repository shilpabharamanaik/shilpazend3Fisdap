<?php namespace Fisdap\Data\Shift;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineShiftRequestRepository
 *
 * @package Fisdap\Data\Shift
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineShiftRequestRepository extends DoctrineRepository implements ShiftRequestRepository
{
    /**
     * @param $programID
     * @param bool $pendingApprovalOnly if true return only requests pending approval
     * @param bool $completedAlso if true, used to apply logic for only pending requests and also grab completed requests
     * @param string $start_date lower bound of date constraint
     * @param string $end_date upper bound of date constraint
     * @return array
     */
    public function getRequestsByProgram($programID, $pendingApprovalOnly = TRUE, $completedAlso = FALSE, $start_date = NULL, $end_date = NULL)
    {
        $qb = $this->_em->createQueryBuilder();

        if (is_null($start_date)) {
            $start_date = date("Y-m-d", strtotime("-1 Years"));
        }

        if (is_null($end_date)) {
            $end_date = date("Y-m-d", strtotime("+1 Days"));
        }

        $qb->select('sr')
            ->from('\Fisdap\Entity\ShiftRequest', 'sr')
            ->join('sr.owner', 'o')
            ->where('o.program = ?1')
            ->andWhere('sr.sent >= ?2')
            ->andWhere('sr.sent <= ?3')
            ->orderBy('sr.sent', 'DESC')
            ->setParameter(1, $programID)
            ->setParameter(2, $start_date)
            ->setParameter(3, $end_date);

        $requests = $qb->getQuery()->getResult();

        $pending = array();
        $completed = array();

        foreach ($requests as $request) {
            if ($request->isPending()) {
                if ($pendingApprovalOnly) {
                    if ($request->accepted->name == 'accepted' && $request->approved->name == 'unset') {
                        $pending[] = $request;
                    }
                } else {
                    $pending[] = $request;
                }
            } else {
                $completed[] = $request;
            }
        }

        if ($pendingApprovalOnly) {
            if(!$completedAlso) {
                return array('pending' => $pending);
            }else{
                return array('pending' => $pending, 'completed' => $completed);
            }
        }

        return array('pending' => $pending, 'completed' => $completed);
    }

    public function getPendingRequestCountByProgram($programID)
    {
        $all_requests = $this->getRequestsByProgram($programID);
        $requests = (is_array($all_requests['pending'])) ? $all_requests['pending'] : array();

        // loop through the requests and see how many are pending
        // we do it this way instead of a direct query because the isPending() method automatically expires past due requests for us
        $pendingRequestCount = 0;
        foreach ($requests as $request) {
            if ($request->isPending()) {
                $pendingRequestCount++;
            }
        }

        return $pendingRequestCount;
    }


    public function getPendingRequestCountByOwner($userContextId)
    {
        $all_requests = $this->getRequestsByOwner($userContextId);
        $requests = (is_array($all_requests['pending'])) ? $all_requests['pending'] : array();

        // loop through the requests and see how many are pending
        // we do it this way instead of a direct query because the isPending() method automatically expires past due requests for us
        $pendingRequestCount = 0;
        foreach ($requests as $request) {
            if ($request->isPending()) {
                $pendingRequestCount++;
            }
        }

        return $pendingRequestCount;
    }


    public function getRequestsByOwner($userContextId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('sr')
            ->from('\Fisdap\Entity\ShiftRequest', 'sr')
            ->where('sr.owner = ?1')
            ->orWhere('sr.recipient = ?1')
            ->orderBy('sr.sent', 'DESC')
            ->setParameter(1, $userContextId);

        $requests = $qb->getQuery()->getResult();

        $pending = array();
        $completed = array();

        foreach ($requests as $request) {
            if ($request->isPending()) {
                $pending[] = $request;
            } else {
                $completed[] = $request;
            }
        }

        return array('pending' => $pending, 'completed' => $completed);
    }
}
