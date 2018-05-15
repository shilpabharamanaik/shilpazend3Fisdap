<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Requirement Notification Warnings
 *
 * @Entity
 * @Table(name="fisdap2_requirement_notification_warnings")
 */
class RequirementNotificationWarning extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var \Fisdap\Entity\RequirementNotification
     * @ManyToOne(targetEntity="RequirementNotification", inversedBy="warnings")
     */
    protected $notification;
    
    /**
     * @var boolean
     * @Column(type="boolean")
     */
    protected $send_warning_notification = 0;
    
    /**
     * @var integer
     * @Column(type="integer", nullable=true)
     */
    protected $warning_offset_value;
    
    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $warning_offset_type;
    
    public function set_notification($value)
    {
        $this->notification = self::id_or_entity_helper($value, "RequirementNotification");
    }
}
