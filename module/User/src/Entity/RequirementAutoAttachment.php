<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Requirement Auto Attachment
 *
 * @Entity(repositoryClass="Fisdap\Data\Requirement\DoctrineRequirementAutoAttachmentRepository")
 * @Table(name="fisdap2_requirement_auto_attachments")
 */
class RequirementAutoAttachment extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;
    
    /**
     * @var Requirement
     * @ManyToOne(targetEntity="Requirement")
     */
    protected $requirement;
    
    /**
     * @var Role
     * @ManyToOne(targetEntity="Role")
     */
    protected $role;
    
    /**
     * @var CertificationLevel
     * @ManyToOne(targetEntity="CertificationLevel")
     */
    protected $certification_level;
    
    public function set_program($value)
    {
        $this->program = self::id_or_entity_helper($value, "ProgramLegacy");
        return $this;
    }
    
    public function set_requirement($value)
    {
        $this->requirement = self::id_or_entity_helper($value, "Requirement");
    }
    
    public function set_role($value)
    {
        $this->role = self::id_or_entity_helper($value, "Role");
        return $this;
    }
    
    public function set_certification_level($value)
    {
        $this->certification_level = self::id_or_entity_helper($value, "CertificationLevel");
        return $this;
    }
}
