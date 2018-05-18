<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Requirement Association
 * 
 * @Entity
 * @Table(name="fisdap2_requirement_associations")
 */
class RequirementAssociation extends EntityBaseClass
{
	/**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @var Requirement
	 * @ManyToOne(targetEntity="Requirement", inversedBy="requirement_associations")
	 */
	protected $requirement;
	
	/**
	 * @var ProgramLegacy
	 * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="requirement_associations")
	 * @JoinColumn(name="program_id", referencedColumnName="Program_id")
	 */
	protected $program;
	
	/**
	 * @var SiteLegacy
	 * @ManyToOne(targetEntity="SiteLegacy", inversedBy="requirement_associations")
	 * @JoinColumn(name="site_id", referencedColumnName="AmbServ_id")
	 */
	protected $site;
	
	/**
	 * @var BaseLegacy
	 * @ManyToOne(targetEntity="BaseLegacy", inversedBy="requirement_associations")
	 * @JoinColumn(name="base_id", referencedColumnName="Base_id")
	 */
	protected $base;
	
	/**
	 * @var \DateTime
	 * @Column(type="date")
	 */
	protected $start_date;
	
	/**
	 * @var \DateTime
	 * @Column(type="date")
	 */
	protected $end_date;
	
	/**
	 * @var boolean
	 * @Column(type="boolean")
	 */
	protected $active = 1;
	
	/**
	 * @var boolean
	 * @Column(type="boolean")
	 */
	protected $global = 0;
	
	
	public function set_program($value)
	{
		$this->program = self::id_or_entity_helper($value, "ProgramLegacy");
		return $this;
	}
	
	public function set_site($value)
	{
		$this->site = self::id_or_entity_helper($value, "SiteLegacy");
		return $this;
	}
}
