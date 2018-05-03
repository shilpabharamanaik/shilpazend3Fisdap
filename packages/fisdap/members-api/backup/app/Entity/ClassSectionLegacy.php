<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;


/**
 * Legacy Entity class for Class Sections.
 * 
 * @Entity(repositoryClass="Fisdap\Data\ClassSection\DoctrineClassSectionLegacyRepository")
 * @Table(name="ClassSections")
 */
class ClassSectionLegacy extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(name="Sect_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @Column(name="Name", type="string")
	 */
	protected $name;
	
    /**
	 * @ManyToOne(targetEntity="ProgramLegacy")
	 * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
	 */
	protected $program;
    
    /**
     * @Column(name="Year", type="string")
     */
    protected $year;
    
    /**
	 * @var \DateTime
     * @Column(name="start_date", type="date")
     */
    protected $start_date;
    
    /**
	 * @var \DateTime
     * @Column(name="end_date", type="date")
     */
    protected $end_date;
    
    /**
     * @Column(name="Type", type="string")
     */
    protected $type = "both";
    
    /**
     * @Column(name="GenEmails", type="integer")
     */
    protected $generate_emails = 0;
    
    /**
     * @var ArrayCollection|ClassSectionInstructorLegacy[]
     * @OneToMany(targetEntity="ClassSectionInstructorLegacy", mappedBy="section", cascade={"persist","remove"})
     */
	protected $section_instructor_associations;
    
    /**
	 * @var ArrayCollection
	 * @OneToMany(targetEntity="ClassSectionStudentLegacy", mappedBy="section", cascade={"persist","remove"})
	 */
	protected $section_student_associations;
    
    /**
	 * @var ArrayCollection
	 * @OneToMany(targetEntity="ClassSectionTaLegacy", mappedBy="section", cascade={"persist","remove"})
	 */
	protected $section_ta_associations;
    
    public function init()
    {
        $this->section_instructor_associations = new ArrayCollection();
		$this->section_student_associations = new ArrayCollection();
		$this->section_ta_associations = new ArrayCollection();
    }
    
    public function set_program($value)
    {
        $this->program = self::id_or_entity_helper($value, "ProgramLegacy");
    }
    
    public function addInstructor(InstructorLegacy $instructor)
	{
		if ($this->getEntityRepository()->getAssociationCountByInstructor($instructor->id, $this->id)) {
			return;
		}
		
		$association = new ClassSectionInstructorLegacy();
		$association->section = $this;
		$association->instructor = $instructor;
		$association->save(false);
		
		$this->section_instructor_associations->add($association);
	}
	
	public function removeInstructor(InstructorLegacy $instructor)
	{
		foreach ($this->section_instructor_associations as $association) {
			if ($association->instructor->id == $instructor->id) {
				$this->section_instructor_associations->removeElement($association);
				$association->delete(false);
			}
		}
	}
    
    public function addStudent(StudentLegacy $student)
	{
		if ($this->getEntityRepository()->getAssociationCountByStudent($student->id, $this->id)) {
			return;
		}
		
		$association = new ClassSectionStudentLegacy();
		$association->section = $this;
		$association->student = $student;
		$association->save(false);
		
		$this->section_student_associations->add($association);
	}
	
	public function removeStudent(StudentLegacy $student)
	{
		foreach ($this->section_student_associations as $association) {
			if ($association->student->id == $student->id) {
				$this->section_student_associations->removeElement($association);
				$association->delete(false);
			}
		}
	}
    
    public function addTa(StudentLegacy $student)
	{
		if ($this->getEntityRepository()->getAssociationCountByTa($student->id, $this->id)) {
			return;
		}
		
		$association = new ClassSectionTaLegacy();
		$association->section = $this;
		$association->ta_student = $student;
		$association->save(false);
		
		$this->section_ta_associations->add($association);
	}
	
	public function removeTa(StudentLegacy $student)
	{
		foreach ($this->section_ta_associations as $association) {
			if ($association->ta_student->id == $student->id) {
				$this->section_ta_associations->removeElement($association);
				$association->delete(false);
			}
		}
	}
	
	public function isActive(){
		$curTime = time();
		$startTime = $this->start_date->format('U');
		$endTime = $this->end_date->format('U');
		
		if($startTime <= $curTime && $curTime <= $endTime){
			return true;
		}else{
			return false;
		}
	}


	/**
	 * @return \DateTime
	 */
	public function getStart_date()
	{
		return $this->start_date;
	}


	/**
	 * @param \DateTime $start_date
	 */
	public function setStart_date(\DateTime $start_date)
	{
		$this->start_date = $start_date;
	}


	/**
	 * @return \DateTime
	 */
	public function getEnd_date()
	{
		return $this->end_date;
	}


	/**
	 * @param \DateTime $end_date
	 */
	public function setEnd_date(\DateTime $end_date)
	{
		$this->end_date = $end_date;
	}
}
