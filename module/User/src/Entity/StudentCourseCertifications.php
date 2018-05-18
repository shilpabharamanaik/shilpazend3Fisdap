<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for Card Course Certifications.
 * 
 * @Entity
 * @Table(name="fisdap2_student_course_certifications")
 */
class StudentCourseCertifications extends EntityBaseClass
{
	/**
	 * @var integer
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @ManyToOne(targetEntity="StudentLegacy", inversedBy="studentCourseCertifications")
	 * @JoinColumn(name="student_id", referencedColumnName="Student_id")
	 */
	protected $student;
	
	/**
	 * @ManyToOne(targetEntity="CourseCertification")
	 */
	protected $course_certification;
	
	/**
	 * @Column(type="datetime", nullable=true)
	 */
	protected $expires;
	
	/**
	 * @Column(type="datetime", nullable=true)
	 */
	protected $certification_date;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $not_applicable = false;
}