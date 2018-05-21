<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Legacy Entity class for Class Section Instructors.
 *
 * @Entity
 * @Table(name="SectInstructors")
 */
class ClassSectionInstructorLegacy extends EntityBaseClass
{
    /**
     * @var int
     * @Id
     * @Column(name="SectInst_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var ClassSectionLegacy
     * @ManyToOne(targetEntity="ClassSectionLegacy")
     * @JoinColumn(name="Section_id", referencedColumnName="Sect_id")
     */
    protected $section;
    
    /**
     * @var InstructorLegacy
     * @ManyToOne(targetEntity="InstructorLegacy")
     * @JoinColumn(name="Instructor_id", referencedColumnName="Instructor_id")
     */
    protected $instructor;
    
    public function set_section($value)
    {
        $this->section = self::id_or_entity_helper($value, "ClassSectionLegacy");
    }
    
    public function set_instructor($value)
    {
        $this->instructor = self::id_or_entity_helper($value, "InstructorLegacy");
    }
}
