<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;


/**
 * @Entity
 * @Table(name="ScheduledSessionTypes")
 */
class ScheduledSessionTypesLegacy extends EntityBaseClass
{
	/**
	 * @Id
	 * @Column(name="type_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;
	
	/**
	 * @Column(name="type_name", type="string")
	 */
	protected $name;
}