<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This produces a modal form for assigning students to a shift
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_AssignModal extends Fisdap_Form_BaseJQuery
{
	/**
	 * @var \Fisdap\Entity\User
	 */
	public $user;
	
	/**
	 * @var \Fisdap\Entity\EventLegacy
	 */
	public $event;
	
	/**
	 * @var
	 */
	public $date;
	
	/**
	 * @var string the location of the event
	 */
	public $location;
	
	/**
	 * @var integer the number of slots
	 */
	public $total_slot_count;

    /**
     * @var integer the number of shifts being assigned to
     */
    public $total_shift_count = 1;

    /**
     *
     */
    public $multistudent_picklist;
	
	/**
	 * @var string $limited_message
	 */
	public $limited_message;
	
	/**
	 *
	 */
	public $event_cert_levels;

    /**
     * @var bool
     */
    public $has_data;

    /**
     *
     * @param int  $event_id
     * @param null  $msp
     * @param array $data
     * @param       $options mixed additional Zend_Form options
     */
	public function __construct($event_id = null, $msp = null, $data = array(), $options = null)
	{
		$this->user = \Fisdap\Entity\User::getLoggedInUser();
		$this->multistudent_picklist = $msp;
		
		if($event_id){
			$this->event = \Fisdap\EntityUtils::getEntity("EventLegacy", $event_id);
			$this->total_slot_count = $this->event->getStudentSlot()->count;
            $this->total_shift_count = 1;
			$this->location = $this->event->getLocation();
			$this->date = $this->event->getDetailViewDate();
			$this->event_cert_levels = $this->event->cert_levels;
			$this->has_data = true;
		}
		else {
			// do we have the info we need anyways?
			// mostly for the add/edit shift interface
			if(isset($data)){
				$this->total_slot_count = $data['slot_count'];
				$this->total_shift_count = $data['shift_count'];
				$this->event_cert_levels = \Fisdap\Entity\CertificationLevel::getConfiguration();
			}
			$this->has_data = true;
		}

		parent::__construct($options);
	}
	
	public function init()
	{
		parent::init();
		$this->addJsFile("/js/library/Scheduler/Form/assign-modal.js");
		$this->addCssFile("/css/library/Scheduler/Form/assign-modal.css");
		
		if($this->has_data){
			if($this->event) {
				$eventId = new Zend_Form_Element_Hidden("event_id");
				$eventId->setValue($this->event->id);

                //Boolean to remember if we've already checked for conflicts
                $conflictCheck = new Zend_Form_Element_Hidden("conflict_check");
                $conflictCheck->setValue(0);
				$this->addElements(array($eventId, $conflictCheck));
			}
			else {
				$cert_levels = new Zend_Form_Element_Hidden("hidden_cert_levels");
				$cert_levels->setValue($this->event_cert_levels);
				$this->addElements(array($cert_levels));
			}
			
			$slots = new Zend_Form_Element_Hidden("total_slots");
			$slots->setValue($this->total_slot_count);
			$this->addElements(array($slots));
			
			if(\Fisdap\Entity\CertificationLevel::getConfiguration() != $this->event_cert_levels){
				// get the cert levels this shift is limited to
				$event_certs = array();
				foreach(\Fisdap\Entity\CertificationLevel::getAll() as $cert){
					if ($cert->bit_value & $this->event_cert_levels){
						// it's included
						$event_certs[] = $cert->description;
					}
				}
				
				$cert_description = (count($event_certs) > 1) ? implode("/", $event_certs) : $event_certs[0];
				$this->limited_message = "This shift is limited to " . $cert_description . " students.";
			}
			else {
				$this->limited_message = null;
			}
		}
		
		//Set the decorators for the form
		$this->setDecorators(array(
			'FormErrors',
			'PrepareElements',
			array('ViewScript', array('viewScript' => "assignModal.phtml")),
		));
	}

    /**
     * Process the form
     *
     * @param $post
     * @return mixed
     */
	public function process($post)
	{
        /** @var \Fisdap\Entity\EventLegacy $event */
        $event = \Fisdap\EntityUtils::getEntity("EventLegacy", $post['event_id']);
        $students = $post['students'];

		$flush = false;
		$counter = 0;
		$batch_size = 70;

		// do a bit of brief validation - how does the number of students on our list compare to the number of slots?
		if($event->getStudentSlot()->count - count($students) < 0){
			return false;
		}
		
		else {
			$students_already_assigned = array();
			
			// for each current assignment
			// if this assignment does not match one of our chosen students, drop them
			// if it does match the student is already on the shift don't do anything
			// if neither of hte above cases happened, they'll need to be assigned so
			// keep track of this record, we'll add them later
			foreach($event->getStudentSlot()->assignments as $assignment){
				
				$on_student_list = false;
				
				if($students){
					foreach($students as $studentId){
						
						$student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $studentId);
						if($assignment->user_context->id == $student->user_context->id){
							$on_student_list = true;
							$students_already_assigned[] = $studentId;
						}
						
					}
				}
				
				if(!$on_student_list)
				{
					$assignment->remove();
				}
				
			}
			
			// now compare the $students_already_assigned to what's on our $student list
			// anything left on the $student list needs to be added to the shift
			// remember user roles so that we can update compliance
			if($students){
				$updateCompliance = array();

				$new_students = array_diff($students, $students_already_assigned);

                //Now that we know who the new students are, check for conflicts
                if (count($new_students) > 0) {
                    $conflicts = \Fisdap\EntityUtils::getRepository("SlotAssignment")->getConflicts($new_students, $event->start_datetime, $event->end_datetime);
                    if (count($conflicts) > 0 && $post['conflict_check'] == 0) {
                        return $conflicts;
                    }
                }

				foreach($new_students as $studentId){
					$student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $studentId);
					$event->assign($student->user_context);
					$updateCompliance[] = $student->user_context->id;
				}
			}
		}
		
		return true;
	}
}
