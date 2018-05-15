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
 * This helper will display a modal to delete an array of events
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_EventDeleteModal extends Fisdap_Form_BaseJQuery
{
    
    /**
     * @var array
     */
    public $events;
    
    /**
     * @var Entity
     */
    public $event;
    
    /**
     * @var array
     */
    public $students;
    
    /**
     * @var integer
     */
    public $student_count;
    
    /**
     * @var array
     */
    public $students_lose_data;
    
    /**
     * @var array
     */
    public $save_shift_data;
    
    /**
         * @var array decorators for hidden elements
         */
    public static $hiddenDecorators = array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'div', 'class' => 'hidden')),
        );
    
    /**
         * @var Doctrine\ORM\EntityManager
         */
    protected $em;
    
    
    /**
     *
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($event_ids = array())
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $user->getProgramId());
        $this->em = \Fisdap\EntityUtils::getEntityManager();
        $this->events = $event_ids;
                
        if (count($this->events) == 1) {
            $event_id = array_shift($event_ids);
            $event = \Fisdap\EntityUtils::getEntity("EventLegacy", $event_id);
            $this->event = $event;
        }
            
        // loop through the events and get any associated students
        $students = array($program->name => array());
        $students_lose_data = array();
        $save_shift_data = array();
        foreach ($this->events as $event_id) {
            $shifts = \Fisdap\EntityUtils::getRepository("ShiftLegacy")->getShiftsByEvent($event_id);
            foreach ($shifts as $shift) {
                $students[$shift->student->program->name][$shift->student->id] = $shift->student->user->getName();
                
                if ($shift->hasData()) {
                    // if this student belongs to the user, delete the data
                    if ($shift->student->program->id == $program->id) {
                        $students_lose_data[$shift->student->id] = $shift->student->user->getName();
                    } else {
                        // otherwise, save the shift data
                        $save_shift_data[$event_id][] = $shift->id;
                    }
                }
            }
        }
            
        // count up the unique students by loooping through each unique program
        $count = 0;
        foreach ($students as $program_name => $program_students) {
            $count += count($program_students);
            if ($program_name != $program->name) {
                $students[$program_name] = count($program_students);
            }
        }
        
        $this->students = $students;
        $this->students_lose_data = $students_lose_data;
        $this->save_shift_data = $save_shift_data;
        $this->student_count = $count;
        
        parent::__construct();
    }
    
    public function init()
    {
        parent::init();
    
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $event = \Fisdap\EntityUtils::getEntity("EventLegacy", $this->events[0]);
        
        $this->addJsFile("/js/library/Scheduler/Form/event-delete-modal.js");
        //$this->addCssFile("/css/library/Scheduler/Form/event-delete-modal.css");
        
        // create form elements
        $event_ids = new Zend_Form_Element_Hidden('event_ids');
        $event_count = new Zend_Form_Element_Hidden('event_count');
        
        // Add elements
        $this->addElements(array($event_ids, $event_count));
    
        // set defaults
        $this->setDefaults(array(
            'event_ids' => implode(',', $this->events),
            'event_count' => count($this->events),
                ));
        $this->setElementDecorators(self::$hiddenDecorators, array('event_ids', 'event_count'));
        
        $this->setDecorators(array(
                        'PrepareElements',
                        array('ViewScript', array('viewScript' => "eventDeleteModal.phtml")),
                        'Form',
                        array('DialogContainer', array(
                                'id'          => 'eventDeleteDialog',
                                'class'          => 'eventDeleteDialog',
                                'jQueryParams' => array(
                                        'tabPosition' => 'top',
                                        'modal' => true,
                                        'autoOpen' => false,
                                        'resizable' => false,
                                        'width' => 700,
                                        'title' => 'Delete shift',
                ),
            )),
        ));
    }
    
    /**
     * Validate the form, if valid, send or perform the request, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($form_data)
    {
        if ($this->isValid($form_data)) {
            $user = \Fisdap\Entity\User::getLoggedInUser();
            $program_id = $user->getProgramId();
            
            // we'll need to track who gets an email about what
            $student_emails = array();
            $other_emails = array();

            //grab the series from the first event
            $series = \Fisdap\EntityUtils::getEntity("EventLegacy", current($this->events))->series;
            $series_id = $series->id;
            
            // go through and delete each event, saving shifts w/ data, if necessary
            $counter = 0;
            $batch_size = 40;
            foreach ($this->events as $event_id) {
                $event = \Fisdap\EntityUtils::getEntity("EventLegacy", $event_id);
                
                // get info for the emails
                $student_slot = $event->getSlotByType('student');
                if ($student_slot) {
                    if ($student_slot->assignments) {
                        // if there are student assignments...
                        foreach ($student_slot->assignments as $assignment) {
                            if (is_array($this->save_shift_data[$event_id])) {
                                // if we're not saving the data, send the email
                                if (!in_array($assignment->shift->id, $this->save_shift_data[$event_id])) {
                                    $student_emails[$assignment->user_context->getRoleData()->id][$event_id] = $event->getOptionText();
                                }
                            } else {
                                $student_emails[$assignment->user_context->getRoleData()->id][$event_id] = $event->getOptionText();
                            }
                        }
                    }
                }
                $email_list = $event->getInstructorEmails();
                foreach ($email_list as $other_email) {
                    $other_emails[$other_email][$event_id] = $event->getOptionText();
                }
                
                // BATMAN all the shifts we need to keep
                if (key_exists($event_id, $this->save_shift_data)) {
                    foreach ($this->save_shift_data[$event_id] as $shift_id) {
                        $shift = \Fisdap\EntityUtils::getEntity("ShiftLegacy", $shift_id);
                        $shift->removeFromEvent();
                    }
                }
                
                // delete the event!
                $event->delete(false);
                
                // remove the event from the series, too, if this is applicable
                if ($series->id > 0) {
                    $series->events->removeElement($event);
                }
                
                $counter++;
                
                if ($counter >= $batch_size) {
                    $this->em->flush();
                    $counter = 0;
                }
            }
            
            // final flush
            $this->em->flush();
            
            //Delete the series if there's only one event left.
            if ($series->id > 0 && count($series->events) <= 1) {
                $series->events->first()->series = null;
                $series->delete(true);
            }
            
            // send emails
            foreach ($student_emails as $student_id => $event_info) {
                $student = \Fisdap\EntityUtils::getEntity("StudentLegacy", $student_id);
                $mail = new \Fisdap_TemplateMailer();
                $count = count($event_info);
                $subject = ($count == 1) ? "A shift has been removed from your schedule" : "$count shifts have been removed from your schedule";
                $mail->addTo($student->email)
                     ->setSubject($subject)
                     ->setViewParam("event_info", $event_info)
                     ->setViewParam("student", $student)
                     ->setViewParam("count", $count)
                     ->sendHtmlTemplate("bulk-shift-removal.phtml");
            }
            foreach ($other_emails as $email => $event_info) {
                $mail = new \Fisdap_TemplateMailer();
                $count = count($event_info);
                $subject = ($count == 1) ? "A shift has been removed from the schedule" : "$count shifts have been removed from the schedule";
                $mail->addTo($email)
                     ->setSubject($subject)
                     ->setViewParam("event_info", $event_info)
                     ->setViewParam("count", $count)
                     ->sendHtmlTemplate("bulk-shift-removal-others.phtml");
            }
            
            return true;
        }

        return $this->getMessages();
    }
}
