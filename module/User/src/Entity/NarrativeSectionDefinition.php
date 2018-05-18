<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * NarrativeSectionDefinition
 *
 * @Entity(repositoryClass="Fisdap\Data\Narrative\DoctrineNarrativeSectionDefinitionRepository")
 * @Table(name="fisdap2_narrative_section_definitions")
 */
class NarrativeSectionDefinition extends EntityBaseClass
{
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $program_id;

    /**
     * @var int
     * @Column(type="integer", nullable=true)
     */
    protected $section_order = 1;

    /**
     * @var string
     * @Column(type="text", nullable=true)
     */
    protected $name = "Narrative";

    /**
     * @var int
     * @Column(type="integer", nullable=true)
     */
    protected $size = 32;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $seeded = false;

    /**
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $active = true;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getProgramId()
    {
        return $this->program_id;
    }

    /**
     * @param int $program_id
     */
    public function setProgramId($program_id)
    {
        $this->program_id = $program_id;
    }

    /**
     * @return int
     */
    public function getSectionOrder()
    {
        return $this->section_order;
    }

    /**
     * @param int $section_order
     */
    public function setSectionOrder($section_order)
    {
        $this->section_order = $section_order;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return bool
     */
    public function getSeeded()
    {
        return $this->seeded;
    }

    /**
     * @param bool $seeded
     */
    public function setSeeded($seeded)
    {
        $this->seeded = $seeded;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    public function toArray()
    {
        return array(
            "id"            => $this->getId(),
            "program_id"    => $this->getProgramId(),
            "section_order" => $this->getSectionOrder(),
            "name"          => $this->getName(),
            "size"          => $this->getSize(),
            "seeded"        => $this->getSeeded(),
            "active"        => $this->getActive(),
        );
    }
}
