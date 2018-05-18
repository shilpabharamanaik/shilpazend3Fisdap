<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for Instructor Permission History Legacy
 * 
 * @Entity(repositoryClass="Fisdap\Data\Permission\DoctrinePermissionHistoryLegacyRepository")
 * @Table(name="InstPermHistory")
 */
class PermissionHistoryLegacy extends EntityBaseClass
{
	/**
	 * @var integer
	 * @Id
	 * @Column(name="PermHist_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @var \DateTime
	 * @Column(name="EntryTime", type="datetime")
	 */
	protected $entry_time;
	
	/**
	 * @var \Fisdap\Entity\InstructorLegacy
	 * @ManyToOne(targetEntity="InstructorLegacy")
	 * @JoinColumn(name="Changed_Inst_id", referencedColumnName="Instructor_id")
	 */
	protected $changed_instructor;
	
	/**
	 * @var \Fisdap\Entity\InstructorLegacy
	 * @ManyToOne(targetEntity="InstructorLegacy")
	 * @JoinColumn(name="Changing_Inst_id", referencedColumnName="Instructor_id")
	 */
	protected $changer;

    /**
     * @var integer a bitmask representing permissions
     * * @Column(name="Permissions", type="integer")
     */
    protected $permissions = 0;

	
	public function __construct()
	{
		$this->entry_time = new \DateTime();
	}


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @param int $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}


	/**
	 * @return \DateTime
	 */
	public function getEntryTime()
	{
		return $this->entry_time;
	}


	/**
	 * @param \DateTime $entry_time
	 */
	public function setEntryTime($entry_time)
	{
		$this->entry_time = $entry_time;
	}


	/**
	 * @return int
	 */
	public function getPermissions()
	{
		return $this->permissions;
	}


	/**
	 * @param int $permissions
	 */
	public function setPermissions($permissions)
	{
		$this->permissions = $permissions;
	}


	/**
	 * @param InstructorLegacy $changedInstructor
	 */
	public function setChangedInstructor(InstructorLegacy $changedInstructor)
	{
		$this->changed_instructor = $changedInstructor;
	}


	/**
	 * @param InstructorLegacy $changer
	 */
	public function setChanger(InstructorLegacy $changer)
	{
		$this->changer = $changer;
	}


	/**
	 * @param $value
	 *
	 * @throws \Exception
	 * @codeCoverageIgnore
	 * @deprecated
	 */
	public function set_changed_instructor($value)
	{
		$this->changed_instructor = self::id_or_entity_helper($value, "InstructorLegacy");
	}


	/**
	 * @param $value
	 *
	 * @throws \Exception
	 * @codeCoverageIgnore
	 * @deprecated
	 */
	public function set_changer($value)
	{
		$this->changer = self::id_or_entity_helper($value, "InstructorLegacy");
	}
	
}
