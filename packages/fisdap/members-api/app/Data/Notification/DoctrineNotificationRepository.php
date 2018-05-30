<?php namespace Fisdap\Data\Notification;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\DoctrineRepository;
use Fisdap\Entity\Notification;
use Fisdap\Entity\UserContext;

/**
 * Class DoctrineNotificationRepository
 *
 * @package Fisdap\Data\Notification
 */
class DoctrineNotificationRepository extends DoctrineRepository implements NotificationRepository
{
    /**
     * @inheritdoc
     */
    public function getUnviewedNotificationsByUserContext(UserContext $userContext, array $active = array(1))
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('n.date_posted, v.id as user_view_id, nt.class, nt.name as notification_type_name, nt.id as notification_type_id, n.id, n.title, n.message, n.recipient_params')
            ->from('\Fisdap\Entity\NotificationUserView', 'v')
            ->join('v.notification', 'n')
            ->join('n.notification_type', 'nt')
            ->andWhere('v.viewed = 0')
            ->andWhere('v.user_context = ?1')
            ->andWhere($qb->expr()->in('n.active', $active))
            ->orderBy('n.date_posted', 'DESC')
            ->setParameter(1, $userContext);

        $results = $qb->getQuery()->getArrayResult();

        return $results;
    }


    /**
     * @inheritdoc
     */
    public function getViewDataByNotification($notificationId)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select('v.viewed, count(v) as view_count')
            ->from('\Fisdap\Entity\NotificationUserView', 'v')
            ->where('v.notification = ?1')
            ->groupBy('v.viewed')
            ->orderBy('v.viewed')
            ->setParameter(1, $notificationId);

        $results = $qb->getQuery()->getArrayResult();

        return $results;
    }


    /**
     * @inheritdoc
     */
    public function getAllNotifications($offset = null, $limit = null)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('n.date_posted, nt.name as type, nt.class, n.id, n.title, n.message, n.recipient_params, n.active')
            ->from('\Fisdap\Entity\Notification', 'n')
            ->orderBy('n.date_posted', 'DESC')
            ->leftJoin('n.notification_type', 'nt');

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        $results = $qb->getQuery()->getArrayResult();

        return $results;
    }

    /**
     * @inheritdoc
     */
    public function getRecipientsForNotification(Notification $notification)
    {
        $recipients = [];

        //Grab recipient params from the notification
        $params = $notification->recipient_params;

        //Let's start by querying for students
        if ($params['students'] == 1) {
            $qb = $this->_em->createQueryBuilder();
            $qb->select('distinct ur.id')
                ->from('\Fisdap\Entity\StudentLegacy', 's')
                ->join('s.user_context', 'ur')
                ->join('ur.program', 'p')
                ->join('ur.certification_level', 'c')
                ->join('ur.user', 'u')
                ->join('u.serial_numbers', 'sn')
                ->andWhere('ur.role = 1')
                ->andWhere('s.graduation_status = 1');

            //Filter by profession
            if (count($params['professions']) > 0) {
                $qb->andWhere($qb->expr()->in('p.profession', $params['professions']));
            }

            //Filter by certification level
            if (count($params['cert_levels']) > 0) {
                $qb->andWhere($qb->expr()->in('c.id', $params['cert_levels']));
            }

            //Filter by products
            if ($params['products'] > 0) {
                $qb->andWhere('BIT_AND(sn.configuration, :products) > 0')
                    ->setParameter('products', $params['products']);
            }

            //Get results and stick ids into return array
            $results = $qb->getQuery()->getArrayResult();
            foreach ($results as $result) {
                $recipients[] = $result['id'];
            }
        }

        //Now let's query for instructors
        if ($params['instructors'] == 1) {
            $qb = $this->_em->createQueryBuilder();
            $qb->select('distinct ur.id')
                ->from('\Fisdap\Entity\InstructorLegacy', 'i')
                ->join('i.user_context', 'ur')
                ->join('ur.program', 'p')
                ->andWhere('ur.role = 2');

            //Filter by profession
            if (count($params['professions']) > 0) {
                $qb->andWhere($qb->expr()->in('p.profession', $params['professions']));
            }

            //Filtering by permissions
            if ($params['permissions'] > 0) {
                $qb->andWhere('BIT_AND(i.permissions, :permissions) > 0')
                    ->setParameter('permissions', $params['permissions']);
            } else {
                //if not, make sure that we don't include instructors with zero permissions
                $qb->andWhere('i.permissions > 0');
            }

            //Get results and stick ids into return array
            $results = $qb->getQuery()->getArrayResult();
            foreach ($results as $result) {
                $recipients[] = $result['id'];
            }
        }

        //Finally, let's query for preceptor training accounts
        if ($params['preceptors'] == 1) {
            $qb = $this->_em->createQueryBuilder();
            $qb->select('distinct ur.id')
                ->from('\Fisdap\Entity\InstructorLegacy', 'i')
                ->join('i.user_context', 'ur')
                ->join('ur.program', 'p')
                ->join('ur.user', 'u')
                ->join('u.serial_numbers', 'sn')
                ->andWhere('BIT_AND(sn.configuration, 64) > 0')
                ->andWhere('ur.role = 2');

            if (count($params['professions']) > 0) {
                $qb->andWhere($qb->expr()->in('p.profession', $params['professions']));
            }

            //Get results and stick ids into return array
            $results = $qb->getQuery()->getArrayResult();
            foreach ($results as $result) {
                $recipients[] = $result['id'];
            }
        }

        //Return an array of unique user role ids because it's possible there may be overlap between instructors
        //and preceptor training accounts
        return array_unique($recipients);
    }


    /**
     * @inheritdoc
     */
    public function sendNotification(Notification $notification)
    {
        $tableName = $this->_em->getClassMetadata('Fisdap\Entity\NotificationUserView')->getTableName();
        $recipients = $this->getRecipientsForNotification($notification);
        $notificationId = $notification->id;

        //If we have no one to send to, just return early
        if (count($recipients) <= 0) {
            return;
        }

        //Start building a mass insert query
        $massInsert = 'INSERT INTO ' . $tableName . ' (`notification_id`, `user_role_id`, `viewed`) VALUES ';

        //Loop over recipients and create array of individual inserts to be added to our mass insert
        $insertValues = [];
        foreach ($recipients as $recipient) {
            $insertValues[] = '(' . $notificationId . ', ' . $recipient . ', 0)';
        }

        //Implode the individual inserts into one string and append to our mass insert
        $massInsert .= implode(',', $insertValues);

        //Now prepare and execute the query
        $stmt = $this->getEntityManager()->getConnection()->prepare($massInsert);
        $stmt->execute();
    }
}
