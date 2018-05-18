<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for Shared Event Preferences
 *
 * @Entity
 * @Table(name="ProgEventPrefs")
 */
class SharedEventPreferenceLegacy extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(name="EventPrefs_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
	
    /**
     * @var \Fisdap\Entity\ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program;
    
    /**
     * @ManyToOne(targetEntity="EventLegacy")
     * @JoinColumn(name="Event_id", referencedColumnName="Event_id")
     */
    protected $event;
    
    /**
     * @Column(type="integer");
     */
    protected $student_can_switch;
    
    /**
     * @Column(type="integer");
     */
    protected $switch_needs_permission;
    
    /**
     * @var integer
     * @Column(name="DropPermissions", type="integer")
     */
    protected $DropPermissions = 0;
     
    /**
     * @var integer
     * @Column(name="TradePermissions", type="integer")
     */
    protected $TradePermissions = 0;
    
    /**
     * @var string
     * @Column(name="StudentNotes", type="string", nullable=true)
     */
    protected $notes;

}