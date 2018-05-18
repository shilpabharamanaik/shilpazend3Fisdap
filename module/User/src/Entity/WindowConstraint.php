<?php namespace User\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;


/**
 * Window Constraint
 * 
 * @Entity
 * @Table(name="fisdap2_window_constraints")
 */
class WindowConstraint extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @var Window
     * @ManyToOne(targetEntity="Window", inversedBy="constraints")
     */
    protected $window;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="WindowConstraintValue", mappedBy="constraint", cascade={"persist","remove"})
     */
    protected $values;
    
    /**
     * @var ConstraintType
     * @ManyToOne(targetEntity="ConstraintType")
     */
    protected $constraint_type;
    
    public function init()
    {
        $this->values = new ArrayCollection;
    }

    public function set_constraint_type($value)
    {
        $this->constraint_type = self::id_or_entity_helper($value, 'ConstraintType');
    }

    /**
     * Add association between Constraint and Value
     *
     * @param WindowConstraintValue $value
     */
    public function addValue(WindowConstraintValue $value)
    {
        $this->values->add($value);
        $value->constraint = $this;
    }

}
