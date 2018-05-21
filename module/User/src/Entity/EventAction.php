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
 * @Table(name="ShiftHistory")
 */
class EventAction extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(name="History_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var \Fisdap\Entity\ActionType
     * @ManyToOne(targetEntity="ActionType")
     */
    protected $action_type;
     
    /**
     * @var \DateTime
     * @Column(name="EntryTime", type="datetime")
     */
    protected $time;
    
    /**
     * @ManyToOne(targetEntity="EventLegacy", inversedBy="actions")
     * @JoinColumn(name="Event_id", referencedColumnName="Event_id")
     */
    protected $event;
    
    /**
     * @var \Fisdap\Entity\UserContext
     * @ManyToOne(targetEntity="UserContext")
     */
    protected $initiator;
    
    /**
     * @var \Fisdap\Entity\UserContext
     * @ManyToOne(targetEntity="UserContext")
     */
    protected $recipient;
    
    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var integer
     * @Column(name="ActionCode", type="integer")
     */
    protected $ActionCode;
    
    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var \Fisdap\Entity\StudentLegacy
     * @ManyToOne(targetEntity="StudentLegacy")
     * @JoinColumn(name="Student_id", referencedColumnName="Student_id")
     */
    protected $Student_id;
    
    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var \Fisdap\Entity\InstructorLegacy
     * @ManyToOne(targetEntity="InstructorLegacy")
     * @JoinColumn(name="Instructor_id", referencedColumnName="Instructor_id")
     */
    protected $Instructor_id;
        
    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var \Fisdap\Entity\StudentLegacy
     * @ManyToOne(targetEntity="StudentLegacy")
     * @JoinColumn(name="OriginalOwner", referencedColumnName="Student_id")
     */
    protected $OriginalOwner;
    
    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var \Fisdap\Entity\StudentLegacy
     * @ManyToOne(targetEntity="StudentLegacy")
     * @JoinColumn(name="TradeRecipient", referencedColumnName="Student_id")
     */
    protected $TradeRecipient;
        
    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var string
     * @Column(name="Type", type="string")
     */
    protected $Type = 'trade';
    
    public function set_type($value)
    {
        $this->action_type = self::id_or_entity_helper($value, 'ActionType');
    }
   
    // this is only used to override the automatic timestamp when updating,
    // generally you shouldn't need to explicitly set the time
    public function set_time($value)
    {
        $this->time = self::string_or_datetime_helper($value);
    }
}
