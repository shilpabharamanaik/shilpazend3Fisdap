<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;


/**
 * @Entity
 * @Table(name="fisdap2_slots")
 */
class Slot extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(name="id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var \Fisdap\Entity\SlotType
     * @ManyToOne(targetEntity="SlotType")
     */
    protected $slot_type;

    /**
     * @var integer
     * @Column(type="integer")
     */
    protected $count = 0;
    
    /**
     * @ManyToOne(targetEntity="EventLegacy", inversedBy="slots")
     * @JoinColumn(name="event_id", referencedColumnName="Event_id")
     */
    protected $event;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="SlotAssignment", mappedBy="slot", cascade={"persist","remove"})
     */
    protected $assignments;
    
    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Window", mappedBy="slot", cascade={"persist","remove"})
     */
    protected $windows;
    
    public function init()
    {
        $this->assignments = new ArrayCollection;
        $this->windows = new ArrayCollection;
    }
    
    public function set_slot_type($value)
    {
        $this->slot_type = self::id_or_entity_helper($value, 'SlotType');
    }
    
    /**
     * Add association between Slot and SlotAssignment
     *
     * @param \Fisdap\Entity\SlotAssignment $assignment
     */
    public function addAssignment(SlotAssignment $assignment)
    {
        $this->assignments->add($assignment);
        $assignment->slot = $this;
    }

    /**
     * Add association between Slot and Window
     *
     * @param \Fisdap\Entity\Window $window
     */
    public function addWindow(Window $window)
    {
        $this->windows->add($window);
        $window->slot = $this;
    }
    
    /**
     * Get the windows for this slot for the given program
     *
     * @param int the program id
     * @return an array of window entities
     */
    public function getWindowsForProgram($programId)
    {
        $windows = array();
        
        if($this->windows){
            foreach($this->windows as $window){
                if($window->program->id == $programId){
                    if($window->active){
                        $windows[] = $window;
                    }
                }
            }
            
        }
        
        return $windows;
    }
    
    /**
     * Get the assignments for this slot for the given program
     *
     * @param int the program id
     * @return an array of assignments entities
     */
    public function getAssignmentsForProgram($programId)
    {
        $assignments = array();

        if ($this->assignments) {
            foreach ($this->assignments as $assignment) {
                if ($assignment->user_context->program->id == $programId) {
                    $assignments[] = $assignment;
                }
            }
            
        }
        
        return $assignments;
    }
    
    // this will create an array of values for a default window
    // used only when creating a new window from the Event form
    // @param $user the current logged in user
    public function getDefaultWindowArray($user, $window_id = null)
    {
        $has_existing_window = false;
		
		if($window_id){
			$window = EntityUtils::getEntity('Window', $window_id);
			$has_existing_window = true;
		}
		
		// will set to our standard default values
		if(!$has_existing_window){
			$today = new \DateTime();
			$window = EntityUtils::getEntity('Window');
			$window->program = $program;
			$window->set_offset_type_start(EntityUtils::getEntity('OffsetType', 1));
			$window->set_offset_type_end(2);
			$window->offset_value_start = array($today->format("Y-m-d"));
			$window->offset_value_end = array(1, "week");
			
			$constraintWindow = $window;
			
			// we'll always need to limit by cert level
			$levelConstraint = EntityUtils::getEntity('WindowConstraint');
			$levelConstraint->set_constraint_type(2);
			$certLevelConstraints = $user->getCurrentRoleData()->program->profession->certifications;
			
			foreach ($certLevelConstraints as $cert) {
				$constraintValue = EntityUtils::getEntity('WindowConstraintValue');
				$constraintValue->value = $cert->id;
				$levelConstraint->addValue($constraintValue);
			}
			$constraintWindow->addConstraint($levelConstraint);
		}
		
		$return_array = $window->toArray();
		$return_array['offset_type_start'] = $window->offset_type_start->toArray();
		$return_array['offset_type_end'] = $window->offset_type_end->toArray();
		
		if($has_existing_window){
			$return_array['cert_constraint'] = array();
			$return_array['group_constraint'] = array();
			
			foreach($window->constraints as $constraint){
				$constraint_type = ($constraint->constraint_type->id == 1) ? "cert_constraint" : "group_constraint";
				foreach($constraint->values as $val){
					$return_array[$type][] = $val->value;
				}
			}
		}
		else {
			$return_array['cert_constraint'] = $levelConstraint->toArray();
			$return_array['cert_constraint']['values'] = array();
			
			foreach($levelConstraint->values as $val){
				$return_array['cert_constraint']['values'][] = $val->toArray();
			}
		}
		
		return $return_array;
    }
    
}
