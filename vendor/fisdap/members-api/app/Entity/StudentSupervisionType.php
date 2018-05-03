<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * Entity class for student supervision type.
 * 
 * @Entity
 * @Table(name="fisdap2_student_supervision_type")
 * @HasLifecycleCallbacks
 */
class StudentSupervisionType extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @Column(type="string", nullable=true)
	 */
	protected $name;
	
}