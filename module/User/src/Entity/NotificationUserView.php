<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity
 * @Table(name="fisdap2_notification_user_views")
 */
class NotificationUserView extends EntityBaseClass
{
    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="Notification")
     */
    protected $notification;

    /**
     * @ManyToOne(targetEntity="UserContext", inversedBy="notification_user_views")
     * @JoinColumn(name="user_role_id", referencedColumnName="id")
     */
    protected $user_context;

    /**
     * @Column(type="boolean")
     */
    protected $viewed = false;
}
