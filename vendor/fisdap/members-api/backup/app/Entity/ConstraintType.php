<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * Constraint Type
 * 
 * @Entity(repositoryClass="Fisdap\Data\ConstraintType\DoctrineConstraintTypeRepository")
 * @Table(name="fisdap2_constraint_type")
 */
class ConstraintType extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var string
     * @Column(type="string")
     */
    protected $name;
    
    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $entity_name;
	
	/**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $description;

}