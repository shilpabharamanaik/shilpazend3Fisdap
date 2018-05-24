<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * @Entity
 * @Table(name="ScheduledSessionSignups")
 */
class ScheduledSessionSignupsLegacy extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(name="Signup_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @ManyToOne(targetEntity="InstructorLegacy")
	 * @JoinColumn(name="Instructor_id", referencedColumnName="Instructor_id")
	 */
	protected $instructor;
	
	/**
	 * @ManyToOne(targetEntity="ScheduledSessionsLegacy", inversedBy="signups")
	 * @JoinColumn(name="ScheduledSession_id", referencedColumnName="ScheduledSession_id")
	 */
	protected $scheduled_session;
	
	/**
	 * @Column(name="Attended", type="integer")
	 */
	protected $attended;
	
    /**
     * @Column(name="CantCome", type="integer")
     */
    protected $cant_come;
    
    /**
     * @Column(name="Notes", type="text")
     */
    protected $notes;
}