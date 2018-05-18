<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Legacy Entity class for Class Section Students.
 * 
 * @Entity
 * @Table(name="SectStudents")
 */
class ClassSectionStudentLegacy extends EntityBaseClass
{
	/**
	 * @var int
	 * @Id
	 * @Column(name="SectStud_id", type="integer")
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
	 * @var StudentLegacy
	 * @ManyToOne(targetEntity="StudentLegacy")
	 * @JoinColumn(name="Student_id", referencedColumnName="Student_id")
	 */
	protected $student;
    
    public function set_section($value)
    {
        $this->section = self::id_or_entity_helper($value, "ClassSectionLegacy");
    }
    
    public function set_student($value)
    {
        $this->student = self::id_or_entity_helper($value, "StudentLegacy");
    }
}