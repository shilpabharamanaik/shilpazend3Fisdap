<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for assigning reports to a program.
 * 
 * @Entity
 * @Table(name="fisdap2_program_report")
 */
class ProgramReport extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @ManyToOne(targetEntity="ProgramLegacy")
	 * @JoinColumn(name="program_id", referencedColumnName="Program_id")
	 */
	protected $program;
	
	/**
	 * @ManyToOne(targetEntity="Report")
	 */
	protected $report;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $active = true;
	
}