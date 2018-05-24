<?php namespace Fisdap\Data\Notification;

/*
 * This file is subject to the terms and conditions defined in the
 * 'COPYRIGHT.txt' file, which is part of this source code package.
 */

use Fisdap\Data\Repository\Repository;
use Fisdap\Entity\Notification;
use Fisdap\Entity\UserContext;


/**
 * Interface NotificationRepository
 *
 * @package Fisdap\Data\Notification
 */
interface NotificationRepository extends Repository
{
    /**
     * Uses query builder to return an array of notifications that have not been viewed by the current user logged in
     *
     * @param UserContext $userContext
     * @param array       $active which active status(es) to fetch
     *
     * @return array $results
     */
    public function getUnviewedNotificationsByUserContext(UserContext $userContext, array $active);


    /**
     * Uses query builder to return user view data for the given notification
     * @param $notificationId
     *
     * @return array $results
     */
    public function getViewDataByNotification($notificationId);


    /**
     * Pulls columns from the Notification entity in descending order for display in the Notification History table.
     * $offset and $limit params included for load-more functionality.
     * @param null $offset
     * @param null $limit
     * @return array
     */
    public function getAllNotifications($offset = null, $limit = null);


    /**
     * Get an array of user role ids based on the parameters of the notification
     *
     * @param Notification $notification
     *
     * @return array
     */
    public function getRecipientsForNotification(Notification $notification);


    /**
     * Populate the fisdap2_notification_user_views based on a given list of recipients.
     *
     * Please note that I'm using raw SQL here so that we can insert many rows in one statement, rather than running
     * thousands of queries.
     *
     * @param Notification $notification
     */
    public function sendNotification(Notification $notification);
}