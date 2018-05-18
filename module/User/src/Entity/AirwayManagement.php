<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * AirwayManagement
 * 
 * @Entity(repositoryClass="Fisdap\Data\AirwayManagement\DoctrineAirwayManagementRepository")
 * @Table(name="fisdap2_airway_managements")
 * @HasLifecycleCallbacks
 */
class AirwayManagement extends EntityBaseClass
{
    /**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
    
    /**
     * @var \Fisdap\Entity\AirwayManagementSource
     * @ManyToOne(targetEntity="AirwayManagementSource")
     */
    protected $airway_management_source;
	
	/**
     * @var \Fisdap\Entity\Patient
     * @OneToOne(targetEntity="Patient")
     */
    protected $patient;
	
	/**
     * @var \Fisdap\Entity\PracticeItem
     * @OneToOne(targetEntity="PracticeItem")
     */
    protected $practice_item;
	
	/**
     * @var \Fisdap\Entity\Airway
     * @OneToOne(targetEntity="Airway", cascade={"persist"})
     */
    protected $airway;
	
	
	/**
	 * @ManyToOne(targetEntity="ShiftLegacy")
	 * @JoinColumn(name="shift_id", referencedColumnName="Shift_id")
	 */
	protected $shift;
	
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $success;
	
	/**
     * @Column(type="boolean", nullable=true)
     */
    protected $performed_by;
	
	/**
     * @var \Fisdap\Entity\Subject
     * @ManyToOne(targetEntity="Subject")
     */
    protected $subject;
	

	
	public function init()
	{
		
	}

	public function setAirwayManagementSource(AirwayManagementSource $airwayManagementSource)
	{
		$this->airway_management_source = $airwayManagementSource;
	}

	public function setPracticeItem(PracticeItem $practiceItem = null)
	{
		$this->practice_item = $practiceItem;
	}

	public function setAirway(Airway $airway) {
	    $this->airway = $airway;
    }

	public function toArray()
    {
        return [
            'uuid' => $this->getUUID(),
            'id' => $this->id,
            'airwayManagementSourceId' => ($this->airway_management_source ? $this->airway_management_source->getId() : null),
            'practiceItemId' => ($this->practice_item ? $this->practice_item->id : null),
            'success' => $this->success,
            'performed' => $this->performed_by
        ];
    }
}
