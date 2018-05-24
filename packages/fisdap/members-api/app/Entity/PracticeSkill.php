<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * Practice Skill
 *
 * @Entity(repositoryClass="Fisdap\Data\Practice\DoctrinePracticeSkillRepository")
 * @Table(name="fisdap2_practice_skill")
 */
class PracticeSkill extends EntityBaseClass
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
     * @Column(type="string")
     */
    protected $entity_name;
    
    /**
     * @var string serialized associative array of the fields to set for this skill
     * @Column(type="array", nullable=true);
     */
    protected $fields;
}