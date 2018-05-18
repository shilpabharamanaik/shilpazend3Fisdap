<?php namespace User\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\OneToMany;


/**
 * Entity class for Goal Definitions defining trackable goals out of data points
 * 
 * @Entity(repositoryClass="Fisdap\Data\Goal\DoctrineGoalRepository")
 * @Table(name="fisdap2_goal_defs")
 */
class GoalDef extends GoalBase // TrackableGoals
{
	/**
	 * @Id
	 * @Column(type="integer", length=2)
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * @OneToMany(targetEntity="Goal", mappedBy="def")
	 */
	protected $goals;

	/**
	 * Duplicated id: Id is used by goals programs and needs to stay unchanged.
	 * When moving data across databases using doctrine id's are not always preserved.
	 * @Column(type="integer", length=2)
	 */
	protected $def_id;

	/**
	 * @Column(type="string", length=40)
	 */
	protected $type;

	/**
	 * @Column(type="string", length=40)
	 */
	protected $def_category;
	
	/**
	 * @Column(type="string", length=40, nullable=true)
	 */
	protected $def_sub_category;
	
	/**
	 * @Column(type="string", length=40)
	 */
	protected $group_name;

	/**
	 * @Column(type="string", length=60)
	 */	
	protected $name='';

	/**
	 * @Column(type="string", length=60)
	 */	
	protected $short_name='';
	
	/**
	 * @Column(type="string", length=25)
	 */
	protected $category='';

	/**
	 * @Column(type="integer", length=2)
	 *	Don't need to link it. Used in search for goals available to a program.
	 * 0 means: all programs
	 */
	protected $program_id;
	
	/**
	 * @Column(type="string")
	 */
	protected $params='';
	
	/**
	 * @Column(type="boolean")
	 */
	protected $team_lead=false;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $interview=false;

	/**
	 * @Column(type="boolean")
	 */
	protected $exam=false;
	
	/**
	 *	Following 3 fields: default values that will be set for new goal
	 */
	
	/**
	 * @Column(type="integer")
	 */
	protected $def_goal_number_required=0;
	
	/**
	 * @Column(type="integer", nullable=true)
	 */
	protected $def_goal_max_last_data;
	
	/**
	 * @Column(type="decimal", scale=2, precision=3, nullable=true)
	 */
	protected $def_goal_percent_successful;
	
	/**
	 * @Column(type="string")
	 */
	protected $dev_comment = '';
    
    /**
     * @Column(type="integer")
     */
    protected $display_order;
	
	/**
	 * @Column(type="boolean")
	 */
	protected $use_op;
}
?>
