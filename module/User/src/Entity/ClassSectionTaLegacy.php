<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Legacy Entity class for Class Section TA Students.
 *
 * @Entity
 * @Table(name="SectTAs")
 */
class ClassSectionTaLegacy extends EntityBaseClass
{
    /**
     * @Id
     * @Column(name="SectTA_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="ClassSectionLegacy")
     * @JoinColumn(name="Section_id", referencedColumnName="Sect_id")
     */
    protected $section;
    
    /**
     * @ManyToOne(targetEntity="StudentLegacy")
     * @JoinColumn(name="TA_Student_id", referencedColumnName="Student_id")
     */
    protected $ta_student;
    
    public function set_section($value)
    {
        $this->section = self::id_or_entity_helper($value, "ClassSectionLegacy");
    }
    
    public function set_ta_student($value)
    {
        $this->ta_student = self::id_or_entity_helper($value, "StudentLegacy");
    }
}
