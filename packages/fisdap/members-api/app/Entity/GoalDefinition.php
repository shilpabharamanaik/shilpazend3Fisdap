<?php namespace Fisdap\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * Entity class for Goal definitions
 *
 * @Entity(repositoryClass="Fisdap\Data\Goal\DoctrineGoalRepository")
 * *InheritanceType("SINGLE_TABLE")
 * *DiscriminatorColumn(name="goal_type", type="string")
 * *DiscriminatorMap({"" = "GoalDefinition", "List" = "GoalDefinitionTest"})
 * @Table(name="fisdap2_goal_definitions")
 * @HasLifecycleCallbacks
 */
class GoalDefinition extends GoalBase
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @Column(type="string")
     */
    protected $name='';
    
    /**
     * @Column(type="string")
     */
    protected $category='';
    
    protected $goal_type;
    
    public function set_goal_type($type)
    {
        $this->goal_type=$type;
    }
}
