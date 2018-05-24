<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;


/**
 * Class defining entities representing workshops for workshop events
 *
 * @Entity
 * @Table(name="fisdap2_ws_workshops")
 */
class Workshop extends EntityBaseClass
{
	/**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
	protected $id;
	
	/**
     * @Column(type="decimal")
     */
	protected $cost;
	
	/**
     * @Column(type="date")
     */
	protected $deadline;
	
	/**
     * @Column(type="string")
     */
	protected $duration;
	
	/**
     * @Column(type="string")
     */
	protected $email_subject;
	
	/**
     * @Column(type="text")
     */
	protected $email_text;
	
	/**
     * @Column(type="string")
     */
	protected $location;
	
	/**
     * @Column(type="date")
     */
	protected $date;
	
	/**
     * @OneToMany(targetEntity="Attendee", mappedBy="workshop", cascade={"persist","remove"})
     **/
    protected $attendees;


	public function __construct() {
        $this->attendees = new ArrayCollection();
    }
	
}
