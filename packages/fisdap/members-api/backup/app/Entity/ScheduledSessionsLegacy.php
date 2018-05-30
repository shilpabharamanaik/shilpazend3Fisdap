<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Fisdap\Data\ScheduledSession\DoctrineScheduledSessionsLegacyRepository")
 * @Table(name="ScheduledSessions")
 */
class ScheduledSessionsLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="ScheduledSession_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @OneToMany(targetEntity="ScheduledSessionSignupsLegacy", mappedBy="scheduled_session")
     * @JoinColumn(name="ScheduledSession_id", referencedColumnName="ScheduledSession_id")
     */
    protected $signups;
    
    /**
     * @ManyToOne(targetEntity="ScheduledSessionTypesLegacy", inversedBy="signups")
     * @JoinColumn(name="Type", referencedColumnName="type_id")
     */
    protected $type;
    
    /**
     * @Column(name="Date", type="date")
     */
    protected $date;
    
    /**
     * @Column(name="StartTime", type="string")
     */
    protected $start_time;
    
    /**
     * @Column(name="TotalSlots", type="integer")
     */
    protected $total_slots;
    
    /**
     * @Column(name="Duration", type="integer")
     */
    protected $duration;
    
    /**
     * @Column(name="URL", type="string")
     */
    protected $url;
    
    /**
     * @Column(name="PhoneNumber", type="string")
     */
    protected $phone_number;
    
    /**
     * @Column(name="PhoneCode", type="string")
     */
    protected $phone_area_code;
    
    /**
     * @Column(name="Notes", type="text")
     */
    protected $notes;
    
    /**
     * @Column(name="Topic", type="string")
     */
    protected $topic;
    
    /**
     * @Column(name="timezone", type="string")
     */
    protected $timezone;
}
