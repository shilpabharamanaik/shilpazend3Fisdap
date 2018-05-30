<?php

/**
 * Script to convert a program to new scheduler
 */
class Util_SchedulerProgramConverter
{

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $db;

    /**
     * @var Doctrine\ORM\EntityManager
     */
    protected $em;
    
    /**
     * @var array of the program autoEmails
     */
    public $autoEmails = array();
    
    /**
     * @var array the event this program recieves
     */
    public $received_events = array();

    /**
     * @var \Fisdap\Entity\ProgramLegacy
     */
    public $program;
    
    /**
     * @var array containing all cert levels
     */
    public $certLevels;
    
    /**
     * @var array containing this programs cert levels
     */
    public $programCertLevels;
    
    /**
     * @var integer bit value representing all possible cert levels
     */
    public $allCertsBitValue;
    
    public $user;
    
    public $program_id;
    
    /**
     * @var boolean Is this data only being preconverted
     */
    public $preconversion;

    public static $counter = 1;
    public static $received_counter = 1;

    public function __construct($program, $updating_constraint_values = false, $preconversion = false)
    {
        $this->db = Zend_Registry::get('db');
        $this->preconversion = $preconversion;
        
        if (!$updating_constraint_values) {
            if ($this->preconversion) {
                $query = "SELECT sd.Shift_id, sd.StartDate, sd.StartTime, sd.Hours FROM EventData ed INNER JOIN ShiftData sd ON ed.Event_id = sd.Event_id WHERE ed.StartDate <= '2012-12-01' AND ed.Program_id = " . $program->id;
            } else {
                $query = "SELECT sd.Shift_id, sd.StartDate, sd.StartTime, sd.Hours FROM EventData ed INNER JOIN ShiftData sd ON ed.Event_id = sd.Event_id WHERE ed.site_id IS NULL AND ed.Program_id = " . $program->id;
            }
            
            $this->shifts = $this->db->query($query);
        }
        echo "After getting shifts: ".$this->convert(memory_get_peak_usage()) . "\n";
        
        $this->em = \Fisdap\EntityUtils::getEntityManager();
        $this->program = $program;
        $this->user = $program->getProgramContact()->user;
        $this->program_id = $program->id;
        $this->populateProgramCertLevels($program);
        
        // now create a default window for any incoming shared events
        //$this->received_events = \Fisdap\EntityUtils::getRepository("EventLegacy")->getSharedEvents($program->id, null, null, null, true);
    }
    
    public function updateWindowConstraintValues()
    {
        $b_size = 50;
        $c = 0;
        
        $windows = \Fisdap\EntityUtils::getRepository("EventLegacy")->getWindowsByProgram($this->program->id);
            
        foreach ($windows as $window) {
            foreach ($window->constraints as $constraint) {
                $entity_name = $constraint->constraint_type->entity_name;
                foreach ($constraint->values as $val) {
                    $value_entity = \Fisdap\EntityUtils::getEntity($entity_name, $val->value);
                    $val->description = ($entity_name == "CertificationLevel") ? $value_entity->description : $value_entity->name;
                    
                    $val->save(false);
                    
                    $c++;
                    if ($c >= $b_size) {
                        $this->em->flush();
                        $c = 0;
                    }
                }
            }
        }
        
        $this->em->flush();
    }

    public function convertProgramToNewScheduler()
    {
        if (!$this->preconversion) {
            $this->doInitialConversionStuff();
        }
        
        $this->convertEvents();
        echo "Peak memory after converting ".self::$counter." events: ".$this->convert(memory_get_peak_usage()) . "\n";
        $this->convertRepeatingEvents();
        echo "After converting repeating events: ".$this->convert(memory_get_usage(true)) . "\n";
        $this->convertShifts();
        echo "After converting shifts: ".$this->convert(memory_get_usage(true)) . "\n";
        
        if (!$this->preconversion) {
            $this->convertAutoEmails();
            $this->doFinalConversionStuff();
        }
        $this->em->flush();
    }
    
    private function doInitialConversionStuff()
    {
        $this->convertProgramSettings();
        $this->program->scheduler_beta = 1;
        
        $mail = new \Fisdap_TemplateMailer();
        $mail->addTo($this->user->email)
             ->addBcc("schedulerconversion@fisdap.net")
             ->setSubject("Scheduler Conversion Start")
             ->setViewParam('user', $this->user)
             ->sendHtmlTemplate("preconversion-notification.phtml");
    }
    
    private function doFinalConversionStuff()
    {
        $mail = new \Fisdap_TemplateMailer();
        $mail->addTo($this->user->email)
             ->addBcc("schedulerconversion@fisdap.net")
             ->setSubject("Scheduler Conversion End")
             ->setViewParam('user', $this->user)
             ->sendHtmlTemplate("postconversion-notification.phtml");
    }
    
    private function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024, ($i=floor(log($size, 1024)))), 2).' '.$unit[$i];
    }

    private function populateProgramCertLevels($program)
    {
        $this->certLevels = $this->db->query("SELECT * FROM fisdap2_certification_levels")->fetchAll();
        
        foreach ($this->certLevels as $cert) {
            $this->allCertsBitValue += $cert['bit_value'];
            
            if ($cert['profession_id'] == $this->program->profession->id) {
                $this->programCertLevels[] = $cert;
            }
        }
    }
    
    private function convertProgramSettings()
    {
        $this->program->program_settings->student_view_full_calendar = $this->program->student_view_full_calendar;

        $types = array('field', 'clinical', 'lab');

        foreach ($types as $type) {
            $this->program->program_settings->{'student_pick_'.$type} = $this->program->{'can_students_pick_'.$type};
            $this->program->program_settings->{'student_switch_'.$type} = $this->convert_codes($this->program->{$type.'_trade_code'}, $this->program->{$type.'_drop_code'}, 'ability');
            $this->program->program_settings->{'switch_'.$type.'_needs_permission'} = $this->convert_codes($this->program->{$type.'_trade_code'}, $this->program->{$type.'_drop_code'}, 'permission');
        }

        //$this->program->scheduler_beta = 1;
        $this->program->save();
        echo "Converted program settings\n";
    }

    private function convert_codes($trade_code, $drop_code, $type)
    {
        $drop_value = ($this->{'parse_'.$type}($drop_code)) * 1;
        $cover_value = ($this->{'parse_'.$type}($trade_code)) * 2;
        $swap_value = ($this->{'parse_'.$type}($trade_code)) * 4;
        return $drop_value + $cover_value + $swap_value;
    }

    private function parse_ability($code)
    {
        if ($code > 0) {
            return 1;
        } else {
            return 0;
        }
    }

    private function parse_permission($code)
    {
        if ($code == 4) {
            return 0;
        } else {
            return 1;
        }
    }
    
    private function convertEvents()
    {
        // we have to do this a batch at a time because some programs have so many events
        if ($this->preconversion) {
            //Grab old shifts for preconversion
            $query = "SELECT count(*) as eventCount FROM EventData WHERE StartDate <= '2012-12-01' AND Program_id = " . $this->program->id;
        } else {
            //Grab all shifts that have not yet been converted
            $query = "SELECT count(*) as eventCount FROM EventData WHERE site_id IS NULL AND Program_id = " . $this->program->id;
        }
        $result = $this->db->query($query)->fetch();
        $eventCount = $result['eventCount'];
        $limit = 1000;
        $offset = 0;
        echo "$eventCount events\n";
        
        while ($offset < $eventCount) {
            //echo "Converting events $minId through $maxId\n";
            $events =  \Fisdap\EntityUtils::getRepository("EventLegacy")->getAllByProgram($this->program->id, $offset, $limit);
            echo "$offset: After fetching ".count($events)." events: ".$this->convert(memory_get_usage(true)) . "\n";
            foreach ($events as $event) {
                $this->convertSingleEvent($event);
            }
            $events = null;
            $offset = $offset + $limit;
        }
    }
    
    private function convertRepeatingEvents()
    {
        $query = "SELECT DISTINCT(RepeatCode) FROM EventData WHERE series_id IS NULL AND Program_id = ".$this->program->id;
        $repeats = $this->db->query($query)->fetchAll();
        foreach ($repeats as $repeat) {
            if ($repeat['RepeatCode'] > 0) {
                // get the repeat info
                $query = "SELECT RepeatType, RepeatData, RepeatStartDate, RepeatEndDate ".
                     "FROM RepeatInfo ".
                     "WHERE Repeat_id = ".$repeat['RepeatCode'];
                $repeat_info = $this->db->query($query)->fetch();
                
                // make the series
                $series = \Fisdap\EntityUtils::getEntity('EventSeries');
                if ($repeat_info['RepeatType'] == 1 || $repeat_info['RepeatType'] == 2) {
                    $series->repeating = true;
                    $series->set_repeat_start_date($repeat_info['RepeatStartDate']);
                    $series->set_repeat_end_date($repeat_info['RepeatEndDate']);
                    if ($repeat_info['RepeatType'] == 1) {
                        $frequency_info = explode(",", $repeat_info['RepeatData']); // repeat data looks something like this: "3,Days"
                        $series->repeat_frequency = $frequency_info[0];
                        if ($frequency_info[1] == "Weeks") {
                            $series->set_frequency_type(2);
                        } else {
                            $series->set_frequency_type(1);
                        }
                    }
                    
                    if ($repeat_info['RepeatType'] == 2) {
                        $series->repeat_frequency = 1;
                        $series->set_frequency_type(2);
                    }
                } else {
                    $series->repeating = false;
                }
                $series->save();
                
                // add the series id to the events
                $query = "UPDATE EventData ".
                    "SET series_id = ".$series->id." ".
                    "WHERE RepeatCode = ".$repeat['RepeatCode'];
                $result = $this->db->query($query);
                
                //echo "Converted repeating events from group ".$repeat['RepeatCode']." into series ".$series->id."\n";
            }
        }
    }

    private function convertShifts()
    {
        foreach ($this->shifts as $shift) {
            //Convert the old start_date and start_time to new date fields
            $datetime = new \DateTime($shift['StartDate']);
            $paddedTime = str_pad($shift['StartTime'], 4, '0', STR_PAD_LEFT);
            $hours = substr($paddedTime, 0, 2);
            $minutes = substr($paddedTime, 2, 2);
            $datetime->setTime($hours, $minutes);
            $updateData['start_datetime'] = $datetime->format('Y-m-d H:i:s');

            //Calculate end datetime
            $unixTime = $datetime->format("U");
            $unixTime += ($shift['Hours'] * 3600);
            $updateData['end_datetime'] = date('Y-m-d H:i:s', $unixTime);

            //Update field in database
            $this->db->update("ShiftData", $updateData, "Shift_id = " . $shift['Shift_id']);
        }
    }

    public function convertAutoEmails()
    {
        $this->autoEmails = $this->em->getRepository('\Fisdap\Entity\ScheduleEmail')->findByProgram($this->program_id);
        
        foreach ($this->autoEmails as $autoEmail) {
            // convert or re-convert OLD emails only; old emails must have chosen and/or available fields set
            if ($autoEmail->legacy_available_shifts || $autoEmail->legacy_chosen_shifts) {
            
                // update existing filter set OR create a new one
                if ($autoEmail->filter) {
                    $filterSet = $autoEmail->filter;
                } else {
                    $filterSet = \Fisdap\EntityUtils::getEntity("SchedulerFilterSet");
                }
                
                $filterSet->user_context = null; // so we don't override the user's session filters
            
                // what used to be month view becomes list view, list view stays the same
                $view_type = 'list';
                $filterSet->setViewTypeByName($view_type);
                
                // filter by chosen/available, and site/base/preceptor, if applicable
                if ($autoEmail->site_id > 0) {
                    $sites = array($autoEmail->site_id);
                    if ($autoEmail->base_id > 0) {
                        $bases = array($autoEmail->base_id);
                    } else {
                        // get all the bases for this site
                        $baseOptions = \Fisdap\EntityUtils::getRepository('BaseLegacy')->getFormOptionsByProgram($this->program_id, null, null, $autoEmail->site_id, true);
                        $bases = array_keys($baseOptions);
                        
                        // no bases = all bases
                        if (empty($bases)) {
                            $bases = 'all';
                        }
                    }
                } else {
                    $sites = "all";
                    $bases = ($autoEmail->base_id > 0) ? array($autoEmail->base_id) : "all";
                }
                $preceptors = ($autoEmail->preceptor_id > 0) ? array($autoEmail->preceptor_id) : "all";
                $filters = array(
                                 "sites" => $sites,
                                 "bases" => $bases,
                                 "preceptors" => $preceptors,
                                 "show_avail" => $autoEmail->legacy_available_shifts,
                                 "avail_certs" => "all",
                                 "avail_groups" => "all",
                                 "avail_open_window" => 0,
                                 "show_chosen" => $autoEmail->legacy_chosen_shifts,
                                 "gradYear" => "All years",
                                 "gradMonth" => "All months",
                                 "certs" => "",
                                 "groups" => "all",
                                 "chosen_students" => "all",
                                 );
                $filterSet->filters = $filters;
                $autoEmail->filter = $filterSet;
            }
        }
    }

    private function getDefaultWindow($event, $program)
    {
        $start_date = $event['ReleaseDate'];
        if ($start_date == '0000-00-00') {
            $start_date = '1997-01-01';
        }
        $end_date = substr($event['ExpirationDate'], 0, 11) . "23:59:59"; // end of the day on that day
        if ($end_date == '0000-00-00 23:59:59') {
            $end_date = substr($event['start_datetime'], 0, 11) . "23:59:59";
        }
        $offset_type_start = $this->convertOffset($event['offset_type_start']);
        $offset_type_end = $this->convertOffset($event['offset_type_end']);

        $event_date = $event['StartDate'];
        $offset_value_start = $this->calulateOffsetValue($offset_type_start, $start_date, $event_date);
        $offset_value_end = $this->calulateOffsetValue($offset_type_end, $end_date, $event_date);

        // create window
        $window = \Fisdap\EntityUtils::getEntity('Window');
        $window->program = $program;
        $window->set_start_date($start_date);
        $window->set_end_date($end_date);
        $window->set_offset_type_start($offset_type_start);
        $window->set_offset_type_end($offset_type_end);
        $window->offset_value_start = $offset_value_start;
        $window->offset_value_end = $offset_value_end;

        return $window;
    }

    private function convertOffset($old_offset)
    {
        switch ($old_offset) {
            case null:
                return 1; // default to static
            case 0:
                return 2; // interval
            case 1:
                return 1; // static
            case 2:
                return 3 ; // previous_month
        }
    }

    private function calulateOffsetValue($offset_type, $date, $event_date)
    {
        $date = substr($date, 0, 10); // we only want the first part
        switch ($offset_type) {
            case 1: // static
                return array($date);

            case 2: // interval
                $diff_in_days = round((strtotime($event_date) - strtotime($date))/86400);
                if ($diff_in_days > 7) {
                    return array(($diff_in_days/7), "week");
                }
                return array($diff_in_days, "day");

            case 3: // previous_month
                return array(substr($date, 8, 2));
        }
    }

    private function getClassSectionConstraints($event_id, $program_id)
    {
        $constraints = array();
        $query = "SELECT DISTINCT(ClassSection_id) FROM EventCSAccess WHERE Event_id = $event_id AND Program_id = $program_id";
        $sections = $this->db->query($query);
        while ($section = $sections->fetch()) {
            $constraints[] = $section["ClassSection_id"];
        }
        return $constraints;
    }

    private function getCertLevelConstraints($event_id, $program_id, $forEvent = false)
    {
        $constraints = array();
        $query = "SELECT MAX(AccessCode) FROM EventTypeAccess WHERE Event_id = $event_id AND Program_id = $program_id";
        $result = $this->db->query($query)->fetch();
        $code = $result['MAX(AccessCode)'];
        
        if ($forEvent) {
            return $code;
        }
        
        // if there is no record for limits, default to all three levels
        if ($code == null) {
            $certs = array();
            
            foreach ($this->programCertLevels as $cert) {
                $certs[] = $cert['id'];
            }
            
            return $certs;
        }

        foreach ($this->programCertLevels as $cert) {
            if ($cert['bit_value'] & $code) {
                $constraints[] = $cert['id'];
            }
        }
        return $constraints;
    }

    private function updateAction($event_action)
    {
        $event_action->set_time($event_action->time->format('Y-m-d H:i:s')); // doing a manual time assignment overrides the auto timestamp
        
        try {
            $instructor = $event_action->Instructor_id->user_context;
        } catch (Exception $e) {
            $instructor = null;
        }
        
        try {
            $student = $event_action->Student_id->user_context;
        } catch (Exception $e) {
            $student = null;
        }
        
        try {
            $original_owner = $event_action->OriginalOwner->user_context;
        } catch (Exception $e) {
            $original_owner = null;
        }
        
        try {
            $trade_recipient = $event_action->TradeRecipient->user_context;
        } catch (Exception $e) {
            $trade_recipient = null;
        }
        
        switch ($event_action->ActionCode) {
            case 0:
                switch ($event_action->Type) {
                    case "picked":
                        $event_action->set_type(3);
                        $event_action->initiator = $student;
                        break;
                    case "assigned":
                        $event_action->set_type(2);
                        $event_action->initiator = $instructor;
                        $event_action->recipient = $original_owner;
                        break;
                    case "created":
                        $event_action->set_type(1);
                        $event_action->initiator = $instructor;
                        break;
                }
                break;
            case 3:
                if ($event_action->Type == 'drop') {
                    $event_action->set_type(11);
                    $event_action->initiator = $student;
                }
                break;
            case 5:
                $event_action->set_type(14);
                $event_action->initiator = $original_owner;
                $event_action->recipient = $trade_recipient;
                break;
            case 7:
                $event_action->set_type(13);
                $event_action->initiator = $initiator;
                $event_action->recipient = $original_owner;
                break;
            case 8:
                $event_action->set_type(12);
                $event_action->initiator = $initiator;
                $event_action->recipient = $original_owner;
                break;
            case 10:
                $event_action->set_type(14);
                $event_action->initiator = $original_owner;
                $event_action->recipient = $trade_recipient;
                break;
            case 12:
                switch ($event_action->Type) {
                    case "trade":
                        $event_action->set_type(13);
                        $event_action->initiator = $instructor;
                        $event_action->recipient = $original_owner;
                        break;
                    case "drop":
                        $event_action->set_type(10);
                        $event_action->initiator = $instructor;
                        $event_action->recipient = $original_owner;
                        break;
                }
                break;
            case 13:
                switch ($event_action->Type) {
                    case "delete":
                    case "drop":
                        $event_action->set_type(9);
                        $event_action->initiator = $instructor;
                        $event_action->recipient = $original_owner;
                        break;
                    case "trade":
                        $event_action->set_type(12);
                        $event_action->initiator = $instructor;
                        $event_action->recipient = $original_owner;
                        break;
                    }
                break;
            case 14:
                $event_action->set_type(4);
                $event_action->initiator = $instructor;
                $event_action->recipient = $original_owner;
                break;
        }
    }

    private function convertSingleEvent($event)
    {
        $event_id = $event['Event_id'];
        $newEvent = \Fisdap\EntityUtils::getEntity('EventLegacy', $event_id);
    
        // update the event
        $oldAmbServ_id = $event['AmbServ_id'];
        $datetime = new DateTime($event['start_datetime']);
        $start_datetime = $datetime->format('Y-m-d H:i:s');
        $duration = new DateInterval('PT'. ($event['Hours'] * 3600) .'S');
        $end_datetime = $datetime->add($duration);
        $email_list = str_replace(" ", "", str_replace(";", ",", $event['EmailList']));
        //echo $email_list."\n";
    
        $newEvent->set_site($oldAmbServ_id);
        $newEvent->set_start_datetime($start_datetime);
        $newEvent->set_end_datetime($end_datetime);
        $newEvent->student_can_switch = $this->convert_codes($newEvent->TradePermissions, $newEvent->DropPermissions, 'ability');
        $newEvent->switch_needs_permission = $this->convert_codes($newEvent->TradePermissions, $newEvent->DropPermissions, 'permission');
        $newEvent->cert_levels = $this->getCertLevelConstraints($event_id, $event['Program_id'], true);
        $newEvent->email_list = explode(",", $email_list);
        
        if (is_null($newEvent->cert_levels)) {
            $newEvent->cert_levels = $this->allCertsBitValue;
        }
    
        // update event history
        foreach ($newEvent->actions as $event_action) {
            $this->updateAction($event_action);
        }
    
        // update permissions for programs who share this event
        foreach ($newEvent->shared_preferences as $shared_preference) {
            $shared_preference->student_can_switch = $this->convert_codes($shared_preference->TradePermissions, $shared_preference->DropPermissions, 'ability');
            $shared_preference->switch_needs_permission = $this->convert_codes($shared_preference->TradePermissions, $shared_preference->DropPermissions, 'permission');
        }
    
        // create a new slot
        $oldTotalSlots = $event['TotalSlots'];
    
        $newSlot = \Fisdap\EntityUtils::getEntity('Slot');
        $newSlot->set_slot_type(1); // these will all be student slots
        $newSlot->count = $oldTotalSlots;
    
        // add slot assignments
        $shifts = \Fisdap\EntityUtils::getRepository("ShiftLegacy")->getShiftsByEvent($event_id);
        foreach ($shifts as $shift) {
            $slot_assignment = \Fisdap\EntityUtils::getEntity('SlotAssignment');
            try {
                $slot_assignment->user_context = $shift->student->user_context;
            } catch (Exception $e) {
                continue;
            }
            $newSlot->addAssignment($slot_assignment);
            $shift->slot_assignment = $slot_assignment;
        }
    
        // add default windows for each program that is sharing
        if (count($newEvent->event_shares) > 0) {
            
            // if there is no share for THIS program, make one really quickly
            if (!$newEvent->getEventShareByProgram($event['Program_id'])) {
                $owner_event_share = \Fisdap\EntityUtils::getEntity('ProgramEventShare');
                $owner_event_share->set_receiving_program($event['Program_id']);
                $newEvent->addShare($owner_event_share);
            }
            
            foreach ($newEvent->event_shares as $share) {
                $constraintWindow = $this->getDefaultWindow($event, \Fisdap\EntityUtils::getEntity('ProgramLegacy', $share->receiving_program->id));
                
                $certLevelConstraints = $this->getCertLevelConstraints($event_id, $share->receiving_program->id);
                
                // compare the number of cert level constraints to the number (3) of cert levels in hte EMS profession
                if (count($certLevelConstraints) != 3) {
                    $levelConstraint = \Fisdap\EntityUtils::getEntity('WindowConstraint');
                    $levelConstraint->set_constraint_type(2);
                    foreach ($certLevelConstraints as $level_value) {
                        $constraintValue = \Fisdap\EntityUtils::getEntity('WindowConstraintValue');
                        $constraintValue->value = $level_value;
                        $constraintValue->description = \Fisdap\EntityUtils::getEntity('CertificationLevel', $level_value)->description;
                        $levelConstraint->addValue($constraintValue);
                    }
                    $constraintWindow->addConstraint($levelConstraint);
                }
                
                // we may need to limit by class section, too
                $classSectionConstraints = $this->getClassSectionConstraints($event_id, $share->receiving_program->id);
                if (count($classSectionConstraints) > 0) {
                    $csConstraint = \Fisdap\EntityUtils::getEntity('WindowConstraint');
                    $csConstraint->set_constraint_type(1);
                    foreach ($classSectionConstraints as $level_value) {
                        $constraintValue = \Fisdap\EntityUtils::getEntity('WindowConstraintValue');
                        $constraintValue->value = $level_value;
                        $constraintValue->description = \Fisdap\EntityUtils::getEntity('ClassSectionLegacy', $level_value)->name;
                        $csConstraint->addValue($constraintValue);
                    }
                    $constraintWindow->addConstraint($csConstraint);
                }
                $newSlot->addWindow($constraintWindow);
            }
        } else {
            // NO SHARING
            $constraintWindow = $this->getDefaultWindow($event, \Fisdap\EntityUtils::getEntity('ProgramLegacy', $event['Program_id']));
            
            $certLevelConstraints = $this->getCertLevelConstraints($event_id, $event['Program_id']);
            // compare the number of cert level constraints to the number (3) of cert levels in hte EMS profession
            if (count($certLevelConstraints) != 3) {
                $levelConstraint = \Fisdap\EntityUtils::getEntity('WindowConstraint');
                $levelConstraint->set_constraint_type(2);
                foreach ($certLevelConstraints as $level_value) {
                    $constraintValue = \Fisdap\EntityUtils::getEntity('WindowConstraintValue');
                    $constraintValue->value = $level_value;
                    $constraintValue->description = \Fisdap\EntityUtils::getEntity('CertificationLevel', $level_value)->description;
                    $levelConstraint->addValue($constraintValue);
                }
                $constraintWindow->addConstraint($levelConstraint);
            }
                
            // we may need to limit by class section, too
            $classSectionConstraints = $this->getClassSectionConstraints($event_id, $event['Program_id']);
            if (count($classSectionConstraints) > 0) {
                $csConstraint = \Fisdap\EntityUtils::getEntity('WindowConstraint');
                $csConstraint->set_constraint_type(1);
                foreach ($classSectionConstraints as $level_value) {
                    $constraintValue = \Fisdap\EntityUtils::getEntity('WindowConstraintValue');
                    $constraintValue->value = $level_value;
                    $constraintValue->description = \Fisdap\EntityUtils::getEntity('ClassSectionLegacy', $level_value)->name;
                    $csConstraint->addValue($constraintValue);
                }
                $constraintWindow->addConstraint($csConstraint);
            }
            $newSlot->addWindow($constraintWindow);
        }
    
        // add new slot with attending windows and assignments to the event
        $newEvent->addSlot($newSlot);
        $newEvent->save(false);
        
        //echo "Converted event #$event_id\n";
    
        if ((self::$counter % 50) == 0) {
            $this->em->flush();
            $this->em->clear();
            //echo "Doctrine cleared\n";
            echo "After converting ".self::$counter." events: ".$this->convert(memory_get_usage(true)) . "\n";
        }
        self::$counter++;
    }
}
