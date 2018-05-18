<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for mapping Programs and Products to a particular moodle group (specifically for the Transistion Course)
 * 
 * @Entity
 * @Table(name="fisdap2_moodle_course_overrides")
 */
class MoodleCourseOverride extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @ManyToOne(targetEntity="Product")
	 */
	protected $product;
	
	/**
	 * @ManyToOne(targetEntity="ProgramLegacy")
	 * @JoinColumn(name="program_id", referencedColumnName="Program_id")
	 */
	protected $program;
	
	/**
	 * @Column(type="integer")
	 */
	protected $moodle_course_id;
}