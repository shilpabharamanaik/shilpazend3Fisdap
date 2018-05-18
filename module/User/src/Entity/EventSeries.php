<?php namespace User\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;


/**
 * @Entity
 * @Table(name="fisdap2_event_series")
 */
class EventSeries extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(name="id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var int
     * @Column(type="boolean")
     */
    protected $repeating;
    
    /**
     * @var integer
     * @Column(type="integer")
     */
    protected $repeat_frequency;
    
    /**
     * @var \Fisdap\Entity\FrequencyType
     * @ManyToOne(targetEntity="FrequencyType")
     */
    protected $repeat_frequency_type;
    
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $repeat_start_date;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $repeat_end_date;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="EventLegacy", mappedBy="series", cascade={"persist"})
     */
    protected $events;

    public function init()
    {
        $this->events = new ArrayCollection;
    }
    
    public function set_repeat_start_date($datetime)
    {
        $this->repeat_start_date = self::string_or_datetime_helper($datetime);
    }
    
    public function set_repeat_end_date($datetime)
    {
        $this->repeat_end_date = self::string_or_datetime_helper($datetime);
    }
    
    public function set_frequency_type($value)
    {
        $this->repeat_frequency_type = self::id_or_entity_helper($value, 'FrequencyType');
    }
    
    /**
     * Add association between the series and an event
     *
     * @param EventLegacy $event
     */
    public function addEvent(EventLegacy $event)
    {
        // first remove the event from its current series (if it has one)
        if($event->series){
            $event->series->events->removeElement($event);
            $event->series = null;
        }
        
        $this->events->add($event);
        $event->series = $this;
    }
    
}
