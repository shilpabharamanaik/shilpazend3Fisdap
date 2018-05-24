<?php namespace Fisdap\Data\ScheduledSession;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;


/**
 * Class DoctrineScheduledSessionsLegacyRepository
 *
 * @package Fisdap\Data\ScheduledSession
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineScheduledSessionsLegacyRepository extends DoctrineRepository implements ScheduledSessionsLegacyRepository
{
    public function getUpcomingSessions()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('s')
            ->from('\Fisdap\Entity\ScheduledSessionsLegacy', 's')
            ->where('s.type = 1')
            ->andWhere('s.date >= CURRENT_DATE()');

        return $qb->getQuery()->getResult();
    }

    public function userAlreadySubscribed($instructorId, $sessionId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('s')
            ->from('\Fisdap\Entity\ScheduledSessionSignupsLegacy', 's')
            ->where('s.scheduled_session = ?1')
            ->andWhere('s.instructor = ?2')
            ->andWhere('s.cant_come = 0');

        $qb->setParameter(1, $sessionId);
        $qb->setParameter(2, $instructorId);

        $result = $qb->getQuery()->getResult();

        if (count($result) > 0) {
            return $result[0]->id;
        }

        return false;
    }

    public function getUsedSlotsCount($sessionId)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('s')
            ->from('\Fisdap\Entity\ScheduledSessionSignupsLegacy', 's')
            ->where('s.scheduled_session = ?1')
            ->andWhere('s.cant_come = 0')
            ->andWhere('s.attended = -1')
            ->setParameter(1, $sessionId);

        return count($qb->getQuery()->getResult());
    }

    public function getCustomReviewItems($userId)
    {
        $conn = $this->_em->getConnection();

        // sanity check
        if (!is_int($userId) && $userId <= 0) {
            // if this isn't a valid user id, return an empty result
            return $conn->query("SELECT NULL LIMIT 0");
        }

        $sql = "
			SELECT 
				rad.ReviewAssignment_id, 
				ad.Data_id,
				DATE_FORMAT(rad.DateReviewDue, '%m-%d-%Y') as DateReviewDue
			FROM 
				ReviewAssignmentData rad 
				INNER JOIN Asset_def ad ON ad.AssetDef_id = rad.AssetDef_id 
			WHERE 
				rad.UserAuth_id = " . $userId . "
				AND ad.DataType_id = 17
				AND rad.DateReviewReceived = '0000-00-00'
				AND rad.Active = 1
		";

        $result = $conn->query($sql);

        return $result;
    }
}