<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * Maintenance
 *
 * Maintenance is a record that Fisdap will be down for planned maintenance/downtime
 * This triggers a warning in the user interface for XX minutes before the downtime.
 * 
 * @Entity(repositoryClass="Fisdap\Data\Maintenance\DoctrineMaintenanceRepository")
 * @Table(name="fisdap2_maintenance")
 */
class Maintenance extends EntityBaseClass
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
	
    /**
     * @Column(type="boolean")
     * Is this maintenance warning enabled (active)?
     */
    protected $enabled;

    /**
     * @Column(type="text", nullable=true)
     * Optional: Any notes specific to this maintenance that should be displayed
     */
    protected $notes;
	
    /**
     * @Column(type="datetime")
     * When the downtime period begins
     */
    protected $downtime_starts;
	
    /**
     * @Column(type="datetime")
     * When the downtime period ends
     */
    protected $downtime_ends;
	
    /**
     * @Column(type="datetime")
     */
    protected $warning_starts;
	
	
    /**
     * Setters
     */
	public function set_enabled($bool) {
		if ($bool) {
			$this->enabled = TRUE;
		} else {
			$this->enabled = FALSE;
		}
	}
	
    public function set_notes($text) {
        $this->notes = $text;
    }
    
    public function set_completed($completed) {
        $this->completed = ($completed) ? 1 : 0;
    }
    
	public function set_downtime_starts($datetime = FALSE) {
		if ($datetime instanceof \DateTime) {
			$this->downtime_starts = $datetime;
		} else {
			$this->downtime_starts = new \DateTime('now');
		}
	}
	
	public function set_downtime_ends($datetime = FALSE) {
		if ($datetime instanceof \DateTime) {
			$this->downtime_ends = $datetime;
		} else {
			$this->downtime_ends = new \DateTime('now');
		}
	}
	
	public function set_warning_starts($datetime = FALSE) {
		if ($datetime instanceof \DateTime) {
			$this->warning_starts = $datetime;
		} else {
			$this->warning_starts = new \DateTime('now');
		}
	}
}
