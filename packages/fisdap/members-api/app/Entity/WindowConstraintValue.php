<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Window Constraint Value
 *
 * @Entity
 * @Table(name="fisdap2_window_constraint_values")
 */
class WindowConstraintValue extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var WindowConstraint
     * @ManyToOne(targetEntity="WindowConstraint", inversedBy="values")
     */
    protected $constraint;

    /**
     * @var integer
     * @Column(type="integer")
     */
    protected $value;
    
    /**
     * @Column(type="string")
     */
    protected $description;
}
