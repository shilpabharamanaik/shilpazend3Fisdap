<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Requirement History
 * 
 * @Entity(repositoryClass="Fisdap\Data\Requirement\DoctrineRequirementHistoryRepository")
 * @Table(name="fisdap2_requirement_history")
 */
class RequirementHistory extends EntityBaseClass
{
	/**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @var \Fisdap\Entity\Requirement
	 * @ManyToOne(targetEntity="Requirement", inversedBy="requirement_histories")
	 */
	protected $requirement;
	
	/**
	 * @var \Fisdap\Entity\RequirementAttachment
	 * @ManyToOne(targetEntity="RequirementAttachment", inversedBy="requirement_histories")
	 */
	protected $requirement_attachment;
	
	/**
	 * @var \Fisdap\Entity\RequirementHistoryChange
	 * @ManyToOne(targetEntity="RequirementHistoryChange")
	 */
	protected $change;
	
	/**
	 * @var \Fisdap\Entity\UserContext
	 * @ManyToOne(targetEntity="UserContext", inversedBy="requirement_attachments")
	 * @JoinColumn(name="user_role_id", referencedColumnName="id")
	 */
	protected $user_context;
	
	/**
	 * @var \DateTime
	 * @Column(type="datetime")
	 */
	protected $timestamp;
	
	/**
	 * @var boolean
	 * @Column(type="text", nullable=true)
	 */
	protected $notes;
	
	public function init()
	{
		$this->timestamp = new \DateTime();
	}
	
	public function set_user_context($value)
	{
		$this->user_context = self::id_or_entity_helper($value, "UserContext");
	}
	
	public function set_requirement($value)
	{
		$this->requirement = self::id_or_entity_helper($value, "Requirement");
	}
	
	public function set_requirement_attachment($value)
	{
		$this->requirement_attachment = self::id_or_entity_helper($value, "RequirementAttachment");
	}
	
	public function set_change($value)
	{
		$this->change = self::id_or_entity_helper($value, "RequirementHistoryChange");
	}
}