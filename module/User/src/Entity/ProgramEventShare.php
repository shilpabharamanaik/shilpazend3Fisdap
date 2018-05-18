<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;


/**
 * @Entity
 * @Table(name="EventSharesData")
 */
class ProgramEventShare extends EntityBaseClass
{
    /**
     * @var integer
     * @Id
     * @Column(name="EventShare_id", type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @ManyToOne(targetEntity="EventLegacy", inversedBy="event_shares")
     * @JoinColumn(name="Event_id", referencedColumnName="Event_id")
     */
    protected $event;
    
    /**
     * @var ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="Receiving_Program_id", referencedColumnName="Program_id")
     */
    protected $receiving_program;
    
    /**
     * @Column(name="retired", type="integer");
     */
    protected $retired;
    
    public function set_receiving_program($value)
    {
	$this->receiving_program = self::id_or_entity_helper($value, 'ProgramLegacy');
    }
    
    /*
     * $no_drop_program_id - if an assignment has matches this program id, they will not be dropped even if $drop_students is true
     *
     * returns an array of affected UserContext IDs
     */
    public function removeShare($drop_students, $flush = true, $no_drop_program_id = null)
	{
		$affected_users = array();
		
		// if this program owns the event, just delete the share
		if ($this->event->program->id == $this->receiving_program->id) {
			$this->event->event_shares->removeElement($this);
			$this->delete($flush);  
		} else {
			// if this program does not own the event, completely remove the event for this program
			$student_slot = $this->event->getSlotByType('student');
		
			// if there is a student slot, do all the fancy stuff
			if ($student_slot) {
				
				$students = $student_slot->getAssignmentsForProgram($this->receiving_program->id);
				if (count($students) == 0 || $drop_students) {
					// drop the students
					foreach ($students as $student) {
                        $drop = true;
                        if($no_drop_program_id){
                            $drop = ($student->user_context->program->id == $no_drop_program_id) ? false : true;
                        }
                        
                        if ($drop) {
                            $student->remove($flush);
                        } else {
							// keep track of the ones we didn't drop
							$affected_users[] = $student->user_context->id;
						}
					}
					
					// remove the window
					$windows = $student_slot->getWindowsForProgram($this->receiving_program->id);
					foreach ($windows as $window) {
						$student_slot->windows->removeElement($window);
						$window->delete($flush);
					}
						
					// delete program-specific preferences
					$preferences = $this->event->getPreferencesForProgram($this->receiving_program->id);
					if ($preferences) {
						$this->event->shared_preferences->removeElement($preferences);
						$preferences->delete($flush);
					}
						
					// delete the sharing link
					$this->event->event_shares->removeElement($this);
					$this->delete($flush);
						
				} else {
					// if we're keeping the students, simply retire the sharing link
					$this->retired = 1;
					$this->save($flush);
				}
			} else {
				// if there was no student slot
				// this is probably a legacy scheduler program
				if ($drop_students) {
					$shifts = EntityUtils::getRepository("ShiftLegacy")->getShiftsByEvent($this->event->id);
					foreach ($shifts as $shift) {
						$shift->delete($flush);
					}
				}
				
				$preferences = $this->event->getPreferencesForProgram($this->receiving_program->id);
				if ($preferences) {
					$this->event->shared_preferences->removeElement($preferences);
					$preferences->delete($flush);
				}
				
				$this->event->event_shares->removeElement($this);
				$this->delete($flush);
			}
        }
		
		return $affected_users;
    }
    
}
