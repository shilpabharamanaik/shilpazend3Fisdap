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
use Fisdap\Entity\RequirementNotification;
use Fisdap\Entity\UserContext;
use Fisdap\EntityUtils;

/**
 * Entity class for Legacy Events.
 *
 * @Entity(repositoryClass="Fisdap\Data\Event\DoctrineEventLegacyRepository")
 * @Table(name="EventData")
 */
class EventLegacy extends EntityBaseClass
{
    protected static $siteRequirementCache = array();

    /**
     * @var integer
     * @Id
     * @Column(name="Event_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var \Fisdap\Entity\SiteLegacy
     * @ManyToOne(targetEntity="SiteLegacy")
     * @JoinColumn(name="site_id", referencedColumnName="AmbServ_id")
     */
    protected $site;

    /**
     * @var \Fisdap\Entity\BaseLegacy
     * @ManyToOne(targetEntity="BaseLegacy")
     * @JoinColumn(name="StartBase_id", referencedColumnName="Base_id")
     */
    protected $base;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $start_datetime;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $end_datetime;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @var \DateTime
     * @Column(name="StartDate", type="date", nullable=true)
     */
    protected $start_date;

    /**
     * @var float
     * @Column(name="Hours"), type="float", precision=5, scale=2)
     */
    protected $duration = 0.00;

    /**
     * @var string
     * @Column(name="Type", type="string")
     */
    protected $type = "field";

    /**
     * @var \Fisdap\Entity\ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @var \Fisdap\Entity\EventSeries
     * @ManyToOne(targetEntity="EventSeries")
     */
    protected $series;

    /**
     * @var array
     * @Column(type="array", nullable=true)
     */
    protected $email_list;

    /**
     * @var integer
     * @Column(name="RepeatCode", type="integer")
     */
    protected $repeat_code = -1;


    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="Slot", mappedBy="event", cascade={"persist","remove"})
     * @JoinColumn(name="Event_id", referencedColumnName="event_id")
     */
    protected $slots;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ProgramEventShare", mappedBy="event", cascade={"persist","remove"})
     * @JoinColumn(name="Event_id", referencedColumnName="Event_id")
     */
    protected $event_shares;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="EventAction", mappedBy="event", cascade={"persist","remove"})
     * @JoinColumn(name="Event_id", referencedColumnName="Event_id")
     */
    protected $actions;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="SharedEventPreferenceLegacy", mappedBy="event", cascade={"persist","remove"})
     * @JoinColumn(name="Event_id", referencedColumnName="Event_id")
     */
    protected $shared_preferences;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="ShiftRequest", mappedBy="event", cascade={"persist","remove"})
     * @JoinColumn(name="Event_id", referencedColumnName="event_id")
     */
    protected $requests;

    /**
     * @var string
     * @Column(name="Notes", type="string", nullable=true)
     */
    protected $notes;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $name;

    /**
     * @Column(type="integer");
     */
    protected $cert_levels;

    /**
     * @Column(type="integer");
     */
    protected $student_can_switch;

    /**
     * @Column(type="integer");
     */
    protected $switch_needs_permission;

    /**
     * @var integer
     * @Column(name="DropPermissions", type="integer")
     */
    protected $DropPermissions = 0;

    /**
     * @var integer
     * @Column(name="TradePermissions", type="integer")
     */
    protected $TradePermissions = 0;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="EventPreceptorLegacy", mappedBy="event", cascade={"persist","remove"})
     */
    protected $preceptor_associations;

    public function init()
    {
        $this->slots = new ArrayCollection;
        $this->actions = new ArrayCollection;
        $this->shared_preferences = new ArrayCollection;
        $this->requests = new ArrayCollection;
        $this->preceptor_associations = new ArrayCollection;
        $this->event_shares = new ArrayCollection;
    }

    public function set_site($value)
    {
        $this->site = self::id_or_entity_helper($value, 'SiteLegacy');
    }

    public function set_base($value)
    {
        $this->base = self::id_or_entity_helper($value, 'BaseLegacy');
    }

    public function set_start_datetime($datetime)
    {
        $this->start_datetime = self::string_or_datetime_helper($datetime);
        $this->start_date = $this->start_datetime;
    }

    public function set_end_datetime($datetime)
    {
        $this->end_datetime = self::string_or_datetime_helper($datetime);
    }

    /**
     * Add association between Event and PreceptorLegacy
     *
     * @param $preceptor
     */
    public function addPreceptor($preceptor)
    {
        $association = EntityUtils::getEntity('EventPreceptorLegacy');
        $association->set_preceptor($preceptor);

        $this->preceptor_associations->add($association);
        $association->event = $this;
    }

    public function getFirstPreceptor()
    {
        return $this->preceptor_associations->first()->preceptor;
    }


    public function addSharedPreferences(SharedEventPreferenceLegacy $shared_preference)
    {
        $this->shared_preferences->add($shared_preference);
        $shared_preference->event = $this;
    }

    /**
     * Add association between Event and ProgramEventShare
     *
     * @param \Fisdap\Entity\ProgramEventShare $share
     */
    public function addShare(ProgramEventShare $share)
    {
        $this->event_shares->add($share);
        $share->event = $this;
    }

    /**
     * Add association between Event and Slot
     *
     * @param \Fisdap\Entity\Slot $slot
     */
    public function addSlot(Slot $slot)
    {
        $this->slots->add($slot);
        $slot->event = $this;
    }

    /**
     * Add association between Event and EventAction
     *
     * @param \Fisdap\Entity\EventAction $action
     */
    public function addAction(EventAction $action)
    {
        $this->actions->add($action);
        $action->event = $this;
    }

    /**
     * Get only the relevant actions (this is a legacy table, so it's cluttered with junk)
     *
     */
    public function getRelevantActions()
    {
        $relevant_actions = array();
        foreach ($this->actions as $action) {
            // if this hasn't been converted from legacy correctly, we don't care about it
            if (!$action->action_type || !$action->initiator) {
                continue;
            }
            $relevant_actions[] = $action;
        }
        return $relevant_actions;
    }

    /*
     * Get the "student" slot record
     */
    public function getStudentSlot()
    {
        $slot = null;

        foreach ($this->slots as $slot) {
            if ($slot->slot_type->name == "student") {
                $slot = $slot;
                break;
            }
        }

        return $slot;
    }

    /*
     * Is this event a shared event? Does it have an 'event_shares' collection?
     */
    public function isShared()
    {
        return $this->event_shares->count();
    }

    /*
     * Returns true if there is at least 1 slot open
     */
    public function hasOpenStudentSlot()
    {
        $student_slot = $this->getStudentSlot();
        $total_slots = $student_slot->count;
        $total_assignments = count($student_slot->assignments);

        if ($total_slots > $total_assignments) {
            return true;
        }

        return false;
    }

    /**
     * Returns a string for emails that looks like "Filled slots (# of #)" with an optional
     * list of student names below it.
     */
    public function getStudentSlotText($includeStudentNames=false, $text_only = false)
    {
        $student_slot = $this->getStudentSlot();
        $total_slots = $student_slot->count;
        $total_assignments = count($student_slot->assignments);

        $outStr = ($text_only) ? "" : "<div>";

        $outStr .= "Filled slots ($total_assignments of $total_slots)";
        $outStr .= ($text_only) ? "" : "<br />";

        if ($includeStudentNames) {
            foreach ($student_slot->assignments as $a) {
                $outStr .= $a->user_context->getContextualName() . "<br />";
            }
        }

        $outStr .= ($text_only) ? "" : "</div>";

        return $outStr;
    }

    /*
     * get a list of students (from a given program) that can be assigned to this event
     * @param int $programId the given program
     * @param array $filters an addtional filter set for the students
     * @param bool $alreadyAssigned return two arrays? one with already assigned students and one with assignable students?
     * @return array $returnData keyed array of students
     */
    public function getAssignableStudents($programId, $filters = array(), $alreadyAssigned = false)
    {
        $students = EntityUtils::getRepository('User')->getAllStudentsByProgram($programId, $filters);
        $program = EntityUtils::getEntity("ProgramLegacy", $programId);

        $assignable = array();
        $assigned = array();
        $hidden_students = array();
        $assigned_different_program = array();
        $hasData = array();

        //$assignmentsToCheck = $this->getStudentSlot()->assignments;
        $assignmentsToCheck = EntityUtils::getRepository("SlotAssignment")->getStudentAssignmentsByEvent($this);


        foreach ($students as $student) {
            $show = false;
            $config = $student['configuration'];

            $hasValidProduct = $this->hasProductForSignUp($config, $student['id']);

            if ($hasValidProduct) {
                // do we meet the broad qualitifcations (from Casey - has nothing to do with windows since an instructor can override those with assignments)
                $show = ($this->cert_levels & $student['cert_bit']) ? true : false;
            }

            if ($alreadyAssigned) {
                foreach ($assignmentsToCheck as $assignment) {
                    if ($assignment->user_context->id == $student['userContextId']) {
                        $assigned[$student['id']]  = $student['first_name'] . " " . $student['last_name'];
                        $assigned[$student['id']] .= ($show) ? "" : "*";
                    }
                }
            }

            if ($show) {
                $assignable[$student['id']] = $student['first_name'] . " " . $student['last_name'];
            } else {
                $hidden_students[$student['id']] = $student['first_name'] . " " . $student['last_name'];
            }
        }

        foreach ($assignmentsToCheck as $assignment) {
            if ($assignment->shift->hasData()) {
                $hasData[] = $assignment->shift->student->id;
            }
        }

        // now add assignments from a differnet program (if there are any)
        // if the number of assignments is equal to the number of 'assigned' skip this step
        if (count($assignmentsToCheck) != count($assigned)) {
            $assigned_different_program = $this->getAssignedFromDifferentProgram($assignmentsToCheck, $assigned);
        }

        if ($alreadyAssigned) {
            return array("assignable" => $assignable, "assigned" => $assigned, "hidden_students" => $hidden_students, "different_program_students" => $assigned_different_program, "has_data" => $hasData);
        }

        return array("assignable" => $assignable, "hidden_students" => $hidden_students);
    }

    /**
     * get the students assigned to this event that are from a different program
     * they are not already on a list of students (from a pseicified program)
     */
    public function getAssignedFromDifferentProgram($assignments, $assigned)
    {
        $assigned_different_program = array();
        $current_users_program = EntityUtils::getEntity("ProgramLegacy", User::getLoggedInUser()->getProgramId());
        foreach ($assignments as $assignment) {
            $assignment_user = $assignment->user_context->user;
            $assignment_student_id = $assignment_user->getCurrentRoleData()->id;

            $program = $assignment->user_context->program;

            if (!isset($assigned[$assignment_student_id])) {
                if ($current_users_program->seesSharedStudents($this->site->id)) {
                    $name = $assignment_user->first_name . " " . $assignment_user->last_name;
                } else {
                    $name = "Student from " . $program->name;
                }

                // add an * if we're Casey
                if ($current_users_program->isAdmin($this->site->id)) {
                    $name .= "*";
                }

                $assigned_different_program[$assignment_student_id] = $name;
            }
        }

        return $assigned_different_program;
    }

    /**
     * Determine if the given config has the full scheduler product
     * @param $config
     * @return bool
     */
    public static function hasFullScheduler($config)
    {
        return (boolean)($config & 2);
    }

    /**
     * Determine if the config has the limited scheduler product
     * @param $config
     * @return bool
     */
    public static function hasLimitedScheduler($config)
    {
        return (boolean)($config & 8192);
    }

    /**
     * Determine if the given config has EITHER the limited or full scheduler products
     * @param $config
     * @return bool
     */
    public static function hasEitherScheduler($config)
    {
        return self::hasLimitedScheduler($config) || self::hasFullScheduler($config);
    }

    public function hasProductForSignUp($config, $student_id)
    {
        return self::hasProductForSignUpByEventType($config, $student_id, $this->type);
    }

    /**
     * @param $config The product configuration that we're interested in
     * @param $student_id The student that we're currently analyzing
     * @param null $event_type The explicit event type that we want to
     * @return bool
     */
    public static function hasProductForSignUpByEventType($config, $student_id, $event_type = null)
    {

        // By default, assume the student doesn't have a valid product
        $hasValidProduct = false;

        // Get the event type for this event
        $type = ($event_type) ? $event_type : false;

        // Continue, only if this event has an event type
        if (!$type) {
            return false;
        }

        if (self::hasLimitedScheduler($config)) {

            // Get the student entity
            $student_entity = EntityUtils::getEntity("StudentLegacy", $student_id);

            // Assuming the student isn't over their limit, return true
            if (!$student_entity->atLimit($type)) {
                $hasValidProduct = true;
            }
        } else {
            if (self::hasFullScheduler($config)) {
                $hasValidProduct = true;
            }
        }

        return $hasValidProduct;
    }

    /**
     * Get the string we use for the date and time in titles
     *
     */
    public function getTitleDateTime()
    {
        return $this->start_datetime->format('M j, Y, Hi');
    }

    public function getAvailableUsers($program_id, $entities = false)
    {
        // get alls active students for this program
        $programRepo = EntityUtils::getRepository('ProgramLegacy');
        $student_group_repo = EntityUtils::getRepository('ClassSectionLegacy');

        //$students = $programRepo->getActiveStudentsByProgramOptimized($program_id);
        //We're using an alternative function in order to deal with schools that don't graduate their students.
        $students = $programRepo->getReasonableStudentsByProgram($program_id);
        $availableUsers = array();

        foreach ($students as $student) {
            $userContext = ($entities) ? EntityUtils::getEntity('UserContext', $student['user_context']['id']) : $student['user_context']['id'];
            $cert = $student['user_context']['certification_level']['id'];
            $groups = $student_group_repo->getProgramGroups($program_id, null, $student['id'], true, true);

            if ($this->isAvailableTo($cert, $groups, $program_id, false, $student['user_context']['user']['id'])) {
                $availableUsers[$student['user_context']['id']] = ($entities) ? $userContext : $student['user_context']['user']['first_name'] . " " . $student['user_context']['user']['last_name'];
            }
        }

        return $availableUsers;
    }


    /**
     * @param User $user
     *
     * @return bool
     */
    public function studentCanViewDetails(User $user)
    {
        $user_has_permission = false;
        $userContext = $user->getCurrentUserContext();
        $user_program = $userContext->program;
        $user_cert = $userContext->certification_level->id;
        $user_groups = EntityUtils::getRepository('ClassSectionLegacy')->getProgramGroups($user_program->id, null, $user->getCurrentRoleData()->id, true, true);


        // the event must be available to them
        if ($this->isAvailableTo($user_cert, $user_groups, $user_program, false, $user)) {
            $user_has_permission = true;
        }
        // they must be attending the event
        elseif ($this->isAttending($userContext->id)) {
            $user_has_permission = true;
        } else {
            // else, they need permission to see other students schedules and someone from their program must be attending
            if ($user_program->program_settings->student_view_full_calendar) {
                if ($this->getSlotByType('student')->assignments) {
                    foreach ($this->getSlotByType('student')->assignments as $assignment) {
                        if ($assignment->user_context->program->id == $user_program->id) {
                            $user_has_permission = true;
                            break;
                        }
                    }
                }
            }
        }

        return $user_has_permission;
    }

    public function isAttending($userContextId, $return_entity = false)
    {
        $attending = false;
        foreach ($this->slots as $slot) {
            if ($slot->assignments) {
                foreach ($slot->assignments as $assignment) {
                    if ($assignment->user_context->id == $userContextId) {
                        return ($return_entity) ? $assignment : true;
                    }
                }
            }
        }

        return $attending;
    }

    public function isAvailableTo($userCertId, $userGroups, $user_program, $now = false, $user)
    {
        $hasCert = false;
        $hasGroup = false;
        $passedAWindow = false;

        // determine first if this user even has scheduler
        if (is_numeric($user)) {
            $user = EntityUtils::getEntity('User', $user);
        }

        // if there's still no user, this student is old and corrupt; bail
        if (is_null($user)) {
            return false;
        }

        $student_id = $user->getCurrentRoleData()->id;

        //The first serial associated with a user should have a configuration that includes all of their available products
        $serial = $user->serial_numbers->first();

        $config = $serial->configuration;

        $hasValidProduct = $this->hasProductForSignUp($config, $student_id);

        if ($hasValidProduct) {
            // make sure the user meets the high level event certification level requriements
            $has_event_cert_level = false;
            $cert_level_entity = EntityUtils::getEntity('CertificationLevel', $userCertId);
            if ($this->cert_levels & $cert_level_entity->bit_value) {
                $has_event_cert_level = true;
            }

            if ($has_event_cert_level) {
                foreach ($this->slots as $slot) {
                    if (!is_numeric($user_program)) {
                        $user_program = $user_program->id;
                    }


                    $windows = EntityUtils::getRepository('Window')->getActiveWindowsBySlot($user_program, $slot->id);

                    if ($windows) {
                        foreach ($windows as $window) {

                            // this will get us any array of details about who is part of the window
                            // we'll get a who['description'] (not helpful here), a who['certs'] that will be
                            // an array of ids, and a who['groups'] which is also an array of group ids
                            $who = $window->getWhoDescription();

                            if (count($who['certs']) > 0) {
                                if (in_array($userCertId, $who['certs'])) {
                                    $hasCert = true;
                                }
                            } else {
                                // they aren't limiting by certs, so we can call it a pass
                                $hasCert = true;
                            }

                            if (count($who['groups']) > 0) {
                                if (count($userGroups) > 0) {

                                    // a student can be part of multiple active groups - check each of them
                                    foreach ($userGroups as $group_id) {
                                        if (in_array($group_id, $who['groups'])) {
                                            $hasGroup = true;
                                        }
                                    }
                                }
                            } else {
                                // they aren't limiting by group, so go ahead and pass
                                $hasGroup = true;
                            }


                            // did we make it in this window?
                            if ($hasCert && $hasGroup) {
                                if ($now) {
                                    if ($window->getStatus() == 'open') {
                                        $passedAWindow = true;
                                    }
                                } else {
                                    $passedAWindow = true;
                                }
                            }
                        }
                    }
                    // if there were no windows, there is no student sign up ever, so it should fail
                }
            }
        }
        $em = EntityUtils::getEntityManager();

        $em->clear($user);

        // as long as they passed once, it's a go
        return $passedAWindow;
    }

    /**
     * Get the string we use for the form options
     *
     */
    public function getOptionText()
    {
        return $this->start_datetime->format('M j, Y, Hi') . " (" . $this->getDurationText() . ") " . $this->site->name;
    }

    public function getLocation()
    {
        $location = $this->site->name . ": " . $this->base->name;
        if ($this->name != '') {
            $location .= ", ".$this->name;
        }
        return $location;
    }

    public function getZoomViewTitle()
    {
        return $this->start_datetime->format("Hi") . " (" . $this->getDurationText() . ") " . $this->getLocation();
    }

    public function getDetailViewDate()
    {
        return $this->getTitleDateTime() . " (" . $this->getDurationText() . ")";
    }

    public function getDurationText()
    {
        $title = $this->duration . "hr";
        if ($this->duration > 1) {
            $title .= "s";
        }
        return $title;
    }

    public function getPreceptorText()
    {
        $preceptors = array();
        if (count($this->preceptor_associations) > 0) {
            $title = (count($this->preceptor_associations) == 1) ? "Preceptor: " : "Preceptors: ";
            foreach ($this->preceptor_associations as $pa) {
                $preceptors[] = $pa->preceptor->first_name . " " . $pa->preceptor->last_name;
            }
        }
        return $title . implode(", ", $preceptors);
    }

    public function getInstructorText()
    {
        $instructors = array();

        $instructorAssignments = $this->getSlotByType("instructor")->assignments;

        $title = '';
        if (count($instructorAssignments) > 0) {
            $title = (count($instructorAssignments) == 1) ? "Instructor: " : "Instructors: ";
            foreach ($instructorAssignments as $ia) {
                $instructors[] = $ia->user_context->user->first_name . " " . $ia->user_context->user->last_name;
            }
        }

        return $title . implode(", ", $instructors);
    }

    // get a specific slot
    public function getSlotByType($slot_type)
    {
        foreach ($this->slots as $slot) {
            if ($slot_type == $slot->slot_type->name) {
                return $slot;
            }
        }
        return false;
    }


    /**
     * assign a user to an event
     * @param UserContext                $assign_user_context the UserContext being assigned to the event
     * @param boolean                    $send_emails   should emails be sent to all parties involved
     * @param boolean                    $flush         should we flush changes to doctrine
     * @param boolean                    $recordHistory should history of this assignment be recorded
     * @param boolean                    $emailAssignee should the person being assigned get an email
     * @return \Fisdap\Entity\SlotAssignment
     */
    public function assign(UserContext $assign_user_context, $send_emails = true, $flush = true, $recordHistory = true, $emailAssignee = true)
    {
        // make sure there is at least 1 open slot available
        $assign_ur_role_name = $assign_user_context->role->name;
        $can_assign = true;

        if ($assign_ur_role_name == "student") {
            if (! $this->hasOpenStudentSlot()) {
                $can_assign = false;
            }
        }

        if ($can_assign) {
            $this_user = User::getLoggedInUser();
            $this_user_context = $this_user->getCurrentUserContext();

            // is this an assignment or a sign up?
            if ($this_user_context->id == $assign_user_context->id) {
                // sign up
                $action_type = 3;
                $mail_template = "shift-signup.phtml";
                $subject = "Shift signup";
                $initiator = $assign_user_context;
                $recipient = null;
            } else {
                // assign
                $action_type = 2;
                $mail_template = "shift-assignment.phtml";
                $subject = "Shift assigned";
                $initiator = $this_user_context;
                $recipient = $assign_user_context;
            }

            //Compliance Stuff!!!
            //get requirements for this event from cache or DB
            $shared = $assign_user_context->program->sharesSite($this->site->id);

            if (isset(self::$siteRequirementCache[$this->site->id])) {
                $requirements = self::$siteRequirementCache[$this->site->id]['local'];
                $globalRequirements = self::$siteRequirementCache[$this->site->id]['global'];
            } else {
                $requirements = EntityUtils::getRepository("Requirement")->getLocalRequirementsBySite($this->site->id, $assign_user_role->program->id);

                // Get shared requirements if the shift is at a shared site, otherwise get local reqs
                if ($shared) {
                    $globalRequirements = EntityUtils::getRepository("Requirement")->getGlobalRequirementsBySite($this->site->id, $assign_user_role->program->id);
                }

                //Cache these for other students
                self::$siteRequirementCache[$this->site->id]['local'] = $requirements;
                self::$siteRequirementCache[$this->site->id]['global'] = $globalRequirements;
            }

            //Notifications
            $notifications = array();

            foreach ($requirements as $requirement) {
                $attachment = $assign_user_context->assignRequirement($requirement, null, 0, null, "shift at " . $this->site->name, "Fisdap Robot");
                if ($attachment && $assign_user_context->program->sendNewRequirementNotification($requirement->id)) {
                    $notifications[] = $attachment;
                }
            }

            if ($shared) {
                foreach ($globalRequirements as $requirement) {
                    $attachment = $assign_user_context->assignRequirement($requirement, null, 0, null, "shift at " . $this->site->name, "Fisdap Robot");
                    if ($attachment && $assign_user_context->program->sendNewRequirementNotification($requirement->id)) {
                        $notifications[] = $attachment;
                    }
                }
            }

            $fullName = $assign_user_context->user->getName();
            $email = $assign_user_context->user->email;

            $usersToNotify = array();
            foreach ($notifications as $attachment) {
                $usersToNotify[$assign_user_context->id][] = array(
                    "name" => $fullName,
                    "email" => $email,
                    "requirementName" => $attachment->requirement->name,
                    "status" => "assigned",
                    "due_date" => $attachment->due_date->format("M j, Y"),
                );
            }

            RequirementNotification::sendNotifications($usersToNotify, "requirement-assigned-notification.phtml");

            // assign the user to the slot
            $slot = $this->getSlotByType($assign_ur_role_name);
            $assignment = EntityUtils::getEntity('SlotAssignment');
            $assignment->user_context = $assign_user_context;

            //Check program compliance along with site compliance
            $assignment->compliant = $assign_user_context->isProgramCompliant() && $assign_user_context->isCompliant($requirements);

            //If the site is shared, check compliance against global requirements, else just mark as true because Bob doesn't care
            $assignment->global_site_compliant = $shared ? $assign_user_context->isCompliant($globalRequirements) : 1;
            $slot->addAssignment($assignment);

            // log the action in event history
            if ($recordHistory) {
                $action = EntityUtils::getEntity("EventAction");
                $action->set_type($action_type);
                $action->initiator = $initiator;
                $action->recipient = $recipient;
                $this->addAction($action);
            }

            $this->save($flush);

            $shift = null;

            // to assign a student, we also need to create a shift
            if ($assign_user_context->role->name == 'student') {
                $shift = EntityUtils::getEntity('ShiftLegacy');
                $shift->student = $assign_user_context->getRoleData();
                $shift->site = $this->site;
                $shift->base = $this->base;
                $shift->hours = $this->duration;
                $shift->type = $this->type;
                $shift->event_id = $this->id;
                $shift->start_datetime = $this->start_datetime;
                $shift->end_datetime = $this->end_datetime;
                $shift->slot_assignment = $assignment;
                $shift->save($flush);
            }

            if ($send_emails) {
                // send emails
                $mail = new \Fisdap_TemplateMailer();
                $mail->addTo($assign_user_context->user->email)
                    ->setSubject($subject)
                    ->setViewParam("event", $this)
                    ->setViewParam("initiator", $initiator)
                    ->setViewParam("recipient", $recipient);

                if ($emailAssignee && $assign_user_context->program->program_settings->send_scheduler_student_notifications) {
                    $mail->sendHtmlTemplate($mail_template);
                }

                $mail->clearRecipients();

                $email_list = $this->getInstructorEmails();
                foreach ($email_list as $email) {
                    $mail->addTo($email);
                }
                if (count($email_list) > 0) {
                    $mail->setViewParam("recipient", $assign_user_context)
                        ->sendHtmlTemplate("shift-assignment-others.phtml");
                }
            }
        }

        return $assignment;
    }

    // returns an array of all the unique email addresses of instructors assigned to this
    // event plus emails in the email list
    public function getInstructorEmails()
    {
        $emails = array();

        // add assigned instructors
        $instructor_slot = $this->getSlotByType('instructor');
        if ($instructor_slot) {
            foreach ($instructor_slot->assignments as $assignment) {
                //If the email exists and the instructor has email events turned on
                if ($assignment->user_context->user->email != "" && $assignment->user_context->getRoleData()->email_event_flag) {
                    $emails[] = $assignment->user_context->user->email;
                }
            }
        }

        // add assigned preceptors
        if (count($this->preceptor_associations) > 0) {
            foreach ($this->preceptor_associations as $pa) {
                if ($pa->preceptor->email != "") {
                    $emails[] = $pa->preceptor->email;
                }
            }
        }

        // add the emails from the email list
        if ($this->email_list) {
            foreach ($this->email_list as $email) {
                if ($email != "") {
                    $emails[] = $email;
                }
            }
        }

        return array_unique($emails);
    }

    /**
     * share this event with a given program
     * @param $program ProgramLegacy
     */
    public function share($program, $flush = true)
    {
        $beta = $program->scheduler_beta;

        // create a link for this event and program, or update an existing share
        $old_share = $this->getEventShareByProgram($program->id);

        if ($old_share) {
            $old_share->retired = 0;
        } else {
            $share = EntityUtils::getEntity("ProgramEventShare");
            $share->receiving_program = $program;
            $this->addShare($share);
        }

        // if this is a beta program AND not the owner of the event, do a bunch of other stuff
        if ($beta && $program->id != $this->program->id) {
            $slot = $this->getSlotByType('student');

            // create the window
            $default_window = $program->program_settings->{'default_' . $this->type . '_window'};

            $window = EntityUtils::getEntity('Window');
            $window->active = true;
            $window->program = $program;

            // Check to see if the program recieving has a default window for this shift type
            if ($default_window) {
                $window->set_offset_type_start($default_window->offset_type_start);
                $window->set_offset_type_end($default_window->offset_type_end);

                $window->offset_value_start = $default_window->offset_value_start;
                $window->offset_value_end = $default_window->offset_value_end;


                $window->set_start_date($window->calculateOffsetDate($default_window->offset_type_start->id, $default_window->offset_value_start, $this->start_datetime));
                $window->set_end_date($window->calculateOffsetDate($default_window->offset_type_end->id, $default_window->offset_value_end, $this->start_datetime));
            } else {

                // If they do not have a default for this shift type, do our usual "today" until "1 week"
                $window->set_start_date(new \DateTime());
                $window->set_end_date(date("Y-m-d", strtotime("-1 week", strtotime($this->start_datetime->format("Y-m-d")))));
                $window->set_offset_type_start(1);
                $window->set_offset_type_end(2);

                $window->offset_value_start = array(date('Y-m-d'));
                $window->offset_value_end = array(1, "week");
            }


            // do we need to create constraints? Only happens if this event has a cert_level bit configuration
            // less than $programs->profession->cert_levels total config
            $high_level_cert_data = $this->openToAllCertsInProfession($program->profession->id);

            if (!$high_level_cert_data['open']) {
                // create the constraints
                $levelConstraint = EntityUtils::getEntity('WindowConstraint');
                $levelConstraint->set_constraint_type(2);

                foreach ($high_level_cert_data['included_certs'] as $cert_id => $cert_description) {
                    $constraintValue = EntityUtils::getEntity('WindowConstraintValue');
                    $constraintValue->value = $cert_id;
                    $constraintValue->description = $cert_description;
                    $levelConstraint->addValue($constraintValue);
                }

                $window->addConstraint($levelConstraint);
            }

            $slot->addWindow($window);
        }

        $this->save($flush);
    }

    public function openToAllCertsInProfession($profession_id)
    {
        $open = false;

        // first check to see if they are limiting at all (there's a good chance they aren't so lets save a step if we can!)
        if (\Fisdap\Entity\CertificationLevel::getConfiguration() != $this->cert_levels) {
            $included_certs = array();
            $profession_cert_count = 0;
            // get all of the certificaiotn levels for the given profession and find out if its included in this evnet's cert bit config
            foreach (\Fisdap\Entity\CertificationLevel::getAllByProfession($profession_id) as $cert) {
                $profession_cert_count++;

                if ($cert->bit_value & $this->cert_levels) {
                    $included_certs[$cert->id] = $cert->description;
                }
            }

            // now, if the number of included certs does not equal the number of certifcaiton levels for this profession,
            // we'll need to create a window constraint (and values for each certificaiton level)
            if (count($included_certs) < $profession_cert_count) {
                $open = false;
            }
        } else {
            $open = true;
        }

        return array("open" => $open, "included_certs" => $included_certs);
    }

    /**
     * Get the preferences for this event for the given program
     *
     * @param int the program id
     * @return an array of SharedEventPreferenceLegacy entities
     */
    public function getPreferencesForProgram($programId)
    {
        if ($this->shared_preferences) {
            foreach ($this->shared_preferences as $preference) {
                if ($preference->program->id == $programId) {
                    return $preference;
                }
            }
        }

        return false;
    }

    /**
     * Get the sharing entity for this event and a given Program
     */
    public function getEventShareByProgram($program_id)
    {
        foreach ($this->event_shares as $event_share) {
            if ($event_share->receiving_program->id == $program_id) {
                return $event_share;
            }
        }
        return false;
    }

    public function isPast()
    {
        $now = new \DateTime;
        if ($this->start_datetime < $now) {
            return true;
        }
        return false;
    }

    public function removeUser($userContextId, $flush = true, $send_email = true)
    {
        $assignment = $this->isAttending($userContextId, true);
        $assignment->remove($flush, $send_email);
    }
}
