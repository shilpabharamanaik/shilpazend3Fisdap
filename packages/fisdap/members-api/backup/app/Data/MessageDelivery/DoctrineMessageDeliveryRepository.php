<?php namespace Fisdap\Data\MessageDelivery;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;

/**
 * Class DoctrineMessageDeliveryRepository
 *
 * @package Fisdap\Data\MessageDelivery
 * @copyright 1996-2014 Headwaters Software, Inc.
 */
class DoctrineMessageDeliveryRepository extends DoctrineRepository implements MessageDeliveryRepository
{
    /**
     * Returns an array-hydrated set of values necessary for the Productivity Widget. Will only fetch messages where this user is the recipient.
     *
     * @param integer $userId the ID of the user for whom we are retreiving messages
     * @param array $messageIds Optional: a set of IDs for which corresponding messages should be fetched.
     *
     * @return array Array of message arrays
     */
    public function getMessagesByUser($userId, $messageIds = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select(array(
                        'md.id', 'md.is_read', 'md.archived', 'md.priority',
                        'm.title', 'm.body', 'm.created', 'm.updated',
                        'u.id AS author',
                        'atype.name AS author_type',
                        'due.start AS due_start', 'due.end AS due_end',
                        'event.start AS event_start', 'event.end AS event_end',
                        'todo.notes', 'todo.completed',
                        'u.first_name AS author_first_name', 'u.last_name AS author_last_name',
                    ))
           ->from('\Fisdap\Entity\MessageDelivery', 'md')
           ->innerJoin('md.message', 'm')
           ->leftJoin('m.due', 'due')
           ->leftJoin('m.event', 'event')
           ->leftJoin('m.author', 'u')
           ->innerJoin('m.author_type', 'atype')
           ->leftJoin('md.todo', 'todo')
           ->leftJoin('u.userContexts', 'ur')
           ->where('md.recipient = ?1')
           ->setParameter(1, $userId);

        // if we have messageIds, then specify additional WHERE clause
        if (is_array($messageIds)) {
            // make sure we escape, we're not sure if doctrine
            foreach ($messageIds as $key => $id) {
                $messageIds[$key] = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $id) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
            }
            $qb->andWhere($qb->expr()->in('md.id', $messageIds));
        }

        $query = $qb->getQuery();
        $sql = $query->getSQL();
        $results = $query->getResult();

        return $results;
    }

    /**
     * Gets the specified user's upcoming (future) shifts. Returns an array-hydrated set of values that the Productivity Widget can use as "messages".
     *
     * @param string $scope The scope in which to retreive shifts. Current options: 'program' or 'user'
     * @param integer $scopeId the ID identifying the scope for which we are retreiving shifts (user ID or program ID)
     * @param integer $numDays The number of days into the future for which to retreive shifts (includes today as day 1)
     * @param string $entityType The type of entity that is being specified with IDs, current options: 'shift' or 'event'
     * @param array $entityIds Optional: a set of IDs for which corresponding shifts should be fetched.
     *
     * @return array Array of message arrays
     */
    public function getShiftPseudoMessagesByUser($scope, $scopeId, $numDays = 4, $entityType = 'shift', $entityIds = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $user = \Fisdap\Entity\User::getLoggedInUser();
        $schedulerBeta = $user->getCurrentProgram()->scheduler_beta;

        // starting date for finding shifts is today
        $today = new \DateTime('now');
        $todayParam = $today->format('Y-m-d');

        // set the end date based on the numDays parameter
        $today->add(new \DateInterval('P' . $numDays . 'D'));
        $endParam = $today->format('Y-m-d');

        $qb->select(array(
                        'shift.id AS shift_id', 'shift.start_date', 'shift.start_time', 'shift.start_datetime', 'shift.hours', 'shift.type', 'shift.event_id', 'shift.entry_time',
                        'site.name AS site_name',
                        'site.abbreviation AS site_abbreviation',
                        'base.name AS base_name',
                        'stu.first_name', 'stu.last_name'
                    ))
           ->from('\Fisdap\Entity\ShiftLegacy', 'shift')
           ->innerJoin('shift.site', 'site')
           ->innerJoin('shift.base', 'base')
           ->innerJoin('shift.student', 'stu');

        if ($schedulerBeta) {
            $qb->where('shift.start_datetime >= ?1') // we only want shifts in the next four days
               ->andWhere('shift.start_datetime < ?2');
        } else {
            $qb->where('shift.start_date >= ?1') // we only want shifts in the next four days
               ->andWhere('shift.start_date < ?2');
        }
        $qb->setParameter(1, $todayParam)
           ->setParameter(2, $endParam)
           ->andWhere('shift.trade = 0'); // @todo IS THIS ACCURATE?

        if ($scope == 'context') {
            $qb->innerJoin('stu.user_context', 'context')
                ->andWhere('context.id = ?3')
                ->setParameter(3, $scopeId);
        } elseif ($scope == 'program') {
            $qb->innerJoin('stu.program', 'program')
                ->andWhere('program.id = ?3')
                ->setParameter(3, $scopeId);
        }

        // if we have entityIds, then specify additional WHERE clause
        if (is_array($entityIds)) {
            // make sure we escape, we're not sure if doctrine does escaping on its own
            foreach ($entityIds as $key => $id) {
                $entityIds[$key] = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $id) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
            }
            if ($entityType == 'shift') {
                $entityCol = 'shift.id';
            } elseif ($entityType == 'event') {
                $entityCol = 'shift.event_id';
            }
            $qb->andWhere($qb->expr()->in($entityCol, $entityIds));
        }

        $query = $qb->getQuery();
        $sql = $query->getSQL();
        $results = $query->getResult();

        // Tuck in the preceptor stuff here...
        $preceptorResults = array();

        $shiftLegacyRepo = \Fisdap\EntityUtils::getRepository('ShiftLegacy');

        foreach ($results as $key => $result) {
            //Convert the start_datetime into two separate fields if we're using the beta
            if ($schedulerBeta) {
                if (is_string($result['start_datetime'])) {
                    $startDatetime = new \DateTime($result['start_datetime']);
                } else {
                    $startDatetime = $result['start_datetime'];
                }
                
                $result['start_date'] = $startDatetime->format('Y-m-d');
                $result['start_time'] = $startDatetime->format('Hi');
            } else {
                $result['start_date'] = $result['start_date']->format("Y-m-d");
            }

            $preceptorResults[$key] = $result;
            $preceptorResults[$key]['preceptor_name'] = $shiftLegacyRepo->getShiftEventPreceptor($result['shift_id']);
        }

        usort($preceptorResults, array(get_class($this), 'sortByDateTimeSite'));
        return $preceptorResults;
    }

    /**
     * Pull actually-valid recipients from the database, based on attempted recipients submitted by a user
     *
     * @param array $attemptedRecipients The array of recipient User ID numbers
     * @param integer $programId Optional: A program in which to limit recipients
     *
     * @return array Array of actual valid recipient User IDs
     */
    public function getValidRecipients($attemptedRecipients = array(), $programId = null)
    {
        // escape incoming data
        foreach ($attemptedRecipients as $key => $recipient) {
            $id = ($recipient instanceof \Fisdap\Entity\User) ? $recipient->id : $recipient;
            if (is_numeric($id)) {
                $attemptedRecipients[$key] = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $id) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
            }
        }

        // construct the query
        $qb = $this->_em->createQueryBuilder();

        // Joining instructors so that Doctrine doesn't lazy load them for each
        // model (one-to-ones get lazy loaded).
        $qb->select(array('u.id'))
           ->from('\Fisdap\Entity\User', 'u')
           ->leftJoin('u.userContexts', 'ur')
           ->leftJoin('ur.program', 'p')
           ->where($qb->expr()->in('u.id', $attemptedRecipients));

        // add clauses for program ID if supplied
        if ($programId != null && is_numeric($programId)) {
            $qb->andWhere("p.id = ?1")
               ->setParameter(1, $programId);
        }

        // get results
        $query = $qb->getQuery();
        //$sql = $query->getSQL();
        $results = $query->getResult();

        // format back into a flat array
        $flatResults = array();
        foreach ($results as $result) {
            $flatResults[] = $result['id'];
        }

        return $flatResults;
    }

    /*
     * Get valid recipients directly via MYSQL query, no doctrine
     */
    public function getValidRecipientsNoDoctrine($attemptedRecipients = array(), $programId = null)
    {
        // escape incoming data
        foreach ($attemptedRecipients as $key => $id) {
            if (is_numeric($id)) {
                $attemptedRecipients[$key] = ((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], $id) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : ""));
            }
        }

        $params = array();
        $query = "SELECT u.id FROM fisdap2_users AS u
			LEFT JOIN fisdap2_user_roles AS ur ON ur.user_id = u.id
			LEFT JOIN ProgramData AS p ON ur.program_id = p.Program_id
			WHERE u.id IN (" . implode(', ', $attemptedRecipients) . ")";

        if ($programId != null && is_numeric($programId)) {
            $query .= " AND p.Program_id = ?";
            $params[] = $programId;
        }

        //  run with Zend's $db adapter for mysql
        $db = \Zend_Registry::get('db');

        // insert
        $statement = $db->query($query, $params);

        // format back into a flat array
        $flatResults = array();
        while ($row = $statement->fetch()) {
            $flatResults[] = $row['id'];
        }

        return $flatResults;
    }


    /**
     * Get all delivery entities associated with a message
     *
     * @param integer $messageId The ID of the message
     *
     * @return array Array of MessageDelivery entities
     */
    public function getByMessage($messageId)
    {
        // construct the query
        $qb = $this->_em->createQueryBuilder();

        // Joining instructors so that Doctrine doesn't lazy load them for each
        // model (one-to-ones get lazy loaded).
        $qb->select('delivery')
           ->from('\Fisdap\Entity\MessageDelivery', 'delivery')
           ->where('delivery.message = ?1')
           ->setParameter(1, $messageId);

        $query = $qb->getQuery();
        //$sql = $query->getSQL();
        $results = $query->getResult();

        return $results;
    }

    /**
     * Function to be used as a callback to a call to usort.  Sorts an array
     * of skills based on their stored orders.
     * @param type $a
     * @param type $b
     * @return 0 if the elements are equal, -1 if A comes before B, and 1 if B
     * comes before A.
     */
    public static function sortByDateTimeSite($a, $b)
    {
        // if the dates are the same, order by time
        if ($a['start_date'] == $b['start_date']) {
            $padded_a_time = str_pad($a['start_time'], 4, '0', STR_PAD_LEFT);
            $padded_b_time = str_pad($b['start_time'], 4, '0', STR_PAD_LEFT);
            // if the date and time is the same, order by site
            if ($padded_a_time == $padded_b_time) {
                // if the site is the same, too, we don't need to order these
                if ($a['site_abbreviation'] == $b['site_abbreviation']) {
                    return 0;
                }

                // order by site
                return $a['site_abbreviation'] < $b['site_abbreviation'] ? -1 : 1;
            }

            // order by time
            return $padded_a_time < $padded_b_time ? -1 : 1;
        }

        // order by date
        return $a['start_date'] < $b['start_date'] ? -1 : 1;
    }
}
