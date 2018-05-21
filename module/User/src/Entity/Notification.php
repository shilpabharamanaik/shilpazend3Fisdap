<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Fisdap\Data\Notification\DoctrineNotificationRepository")
 * @Table(name="fisdap2_notifications")
 */
class Notification extends EntityBaseClass
{
    /**
     * @Column(type="integer")
     * @Id
     * @GeneratedValue
     */
    protected $id;

    /**
     * @ManyToOne(targetEntity="NotificationType")
     */
    protected $notification_type;

    /**
     * @Column(type="string", length=140)
     */
    protected $title;

    /**
     * @Column(type="text")
     */
    protected $message;

    /**
     * @Column(type="datetime")
     */
    protected $date_posted;

    /**
     * @Column(type="array")
     */
    protected $recipient_params;

    /**
     * @Column(type="boolean")
     */
    protected $active = true;
}
