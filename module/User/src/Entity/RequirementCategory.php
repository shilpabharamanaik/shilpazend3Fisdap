<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * Requirement Category
 * 
 * @Entity(repositoryClass="Fisdap\Data\Requirement\DoctrineRequirementCategoryRepository")
 * @Table(name="fisdap2_requirement_category")
 */
class RequirementCategory extends Enumerated
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
}