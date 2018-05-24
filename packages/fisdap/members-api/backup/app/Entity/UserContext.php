<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\OrderBy;
use Fisdap\Data\Slot\SlotAssignmentRepository;
use Fisdap\EntityUtils;


/**
 * Entity class for User Roles.
 *
 * @Entity(repositoryClass="Fisdap\Data\User\UserContext\DoctrineUserContextRepository")
 * @Table(name="fisdap2_user_roles")
 * @HasLifecycleCallbacks
 */
class UserContext extends EntityBaseClass
{
    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @Column(type="date", nullable=true)
     */
    protected $start_date;

    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $end_date;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $active = true;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $email;

    /**
     * @var CertificationLevel
     * @ManyToOne(targetEntity="CertificationLevel", cascade={"detach"}, fetch="EAGER")
     */
    protected $certification_level;

    /**
     * @var User
     * @ManyToOne(targetEntity="User", inversedBy="userContexts", cascade={"detach"}, fetch="EAGER")
     */
    protected $user;

    /**
     * @var Role
     * @ManyToOne(targetEntity="Role", cascade={"detach"}, fetch="EAGER")
     */
    protected $role;

    /**
     * @var ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy", cascade={"detach"}, fetch="EAGER")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @var int
     * @Column(type="integer", nullable=true)
     */
    protected $courseId = null;

    /**
     * @var ArrayCollection
     * @ManyToMany(targetEntity="Permission")
     * @JoinTable(name="fisdap2_user_role_permission",
     *  joinColumns={@JoinColumn(name="permission_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="user_role_id",referencedColumnName="id")})
     */
    protected $permissions;

    /**
     * @var ArrayCollection|SerialNumberLegacy[]
     * @OneToMany(targetEntity="SerialNumberLegacy", mappedBy="userContext", cascade={"detach"}, fetch="LAZY")
     * @OrderBy({"configuration" = "DESC"})
     */
    protected $serialNumbers;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="SlotAssignment", mappedBy="user_context", cascade={"persist","remove"})
     */
    protected $slot_assignments;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="RequirementAttachment", mappedBy="user_context", cascade={"persist","remove"})
     */
    protected $requirement_attachments;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="CalendarFeed", mappedBy="user_context", cascade={"persist","remove"})
     */
    protected $subscriptions;

    /**
     * @var ArrayCollection
     * @OneToMany(targetEntity="NotificationUserView", mappedBy="user_context")
     */
    protected $notification_user_views;

    /**
     * @var StudentLegacy
     * @OneToOne(targetEntity="StudentLegacy", mappedBy="user_context", cascade={"detach"}, fetch="EAGER")
     */
    protected $studentRoleData;

    /**
     * @var InstructorLegacy
     * @OneToOne(targetEntity="InstructorLegacy", mappedBy="user_context", cascade={"detach"}, fetch="EAGER")
     */
    protected $instructorRoleData;


    public function __construct()
    {
        $this->permissions = new ArrayCollection();
        $this->serialNumbers = new ArrayCollection();
        $this->slot_assignments = new ArrayCollection();
        $this->requirement_attachments = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
        $this->notification_user_views = new ArrayCollection();
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Get the name of this user including the certification and program info
     *
     * @return string
     */
    public function getContextualName()
    {
        return $this->user->getFullName() . " - " . $this->getCertification() . " (" . $this->program->name . ")";
    }


    /**
     * Get a short description of this context
     *
     * @return string
     */
    public function getDescription()
    {
        $description = $this->getCertification();
        if ($this->getCertification() != "Instructor") {
            $description .= " " . $this->role->getName();
        }
        $description .= " at " . $this->program->name;

        return $description;
    }


    /**
     * @return ProgramLegacy
     */
    public function getProgram()
    {
        return $this->program;
    }


    /**
     * @param $value
     *
     * @throws \Exception
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_program($value)
    {
        $this->program = self::id_or_entity_helper($value, "ProgramLegacy");

        if (isset($this->role)) {
            $this->getRoleData()->program = $this->program;
        }
    }


    /**
     * @param ProgramLegacy $programLegacy
     */
    public function setProgram(ProgramLegacy $programLegacy)
    {
        $this->program = $programLegacy;
    }


    /**
     * @return int
     */
    public function getCourseId()
    {
        return $this->courseId;
    }


    /**
     * @param int $courseId
     */
    public function setCourseId($courseId)
    {
        $this->courseId = $courseId;
    }


    /**
     * @return CertificationLevel
     */
    public function getCertificationLevel()
    {
        return $this->certification_level;
    }


    /**
     * Set the certification level for this user role
     *
     * @param mixed $cert integer | \Fisdap\Entity\CertificationLevel
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_certification_level($cert)
    {
        $this->certification_level = self::id_or_entity_helper($cert, 'CertificationLevel');
    }


    /**
     * @param CertificationLevel $certificationLevel
     */
    public function setCertificationLevel(CertificationLevel $certificationLevel)
    {
        $this->certification_level = $certificationLevel;
    }


    /**
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->start_date;
    }


    /**
     * @param \DateTime|null $startDate
     */
    public function setStartDate(\DateTime $startDate = null)
    {
        $this->start_date = $startDate;
    }


    /**
     * @return \DateTime|null
     */
    public function getEndDate()
    {
        return $this->end_date;
    }


    /**
     * Set the end date of this user role
     * If this role is a student, also set the roleData
     *
     * @param \DateTime $date
     *
     * @return \Fisdap\Entity\UserContext
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_end_date(\DateTime $date)
    {
        $this->end_date = $date;

        if (isset($this->role)) {
            if ($this->role->name == "student") {
                $this->getRoleData()->graduation_month = $date->format('n');
                $this->getRoleData()->graduation_year = $date->format('Y');
            }
        }

        return $this;
    }


    /**
     * @param \DateTime|null $endDate
     */
    public function setEndDate(\DateTime $endDate = null)
    {
        $this->end_date = $endDate;
    }


    /**
     * @param string $field
     *
     * @return string
     */
    public function getCertification($field = "description")
    {
        if (isset($this->certification_level)) {
            return $this->certification_level->{$field};
        } else {
            return "Instructor";
        }
    }


    /**
     * @return RoleData|null
     * @throws \Exception
     */
    public function getRoleData()
    {
        if ($this->role === null) {
            return null;
        }

        switch ($this->role->getEntityName()) {
            case 'StudentLegacy':
                return $this->studentRoleData;
                break;
            case 'InstructorLegacy':
                return $this->instructorRoleData;
                break;
            default:
                throw new \Exception("Role data for {$this->role->getName()} is not yet supported");
                break;
        }
    }


    /**
     * @param RoleData $roleData
     *
     * @throws \Exception
     */
    public function setRoleData(RoleData $roleData)
    {
        $roleDataClassName = get_class($roleData);

        switch ($roleDataClassName) {
            case StudentLegacy::class:
                $this->studentRoleData = $roleData;
                break;
            case InstructorLegacy::class:
                $this->instructorRoleData = $roleData;
                break;
            default:
                throw new \Exception("Role data for $roleDataClassName is not yet supported");
                break;
        }
    }


    /**
     * This function can be used to attach a new role to a user.
     *
     * @param String   $roleName       Name of the role to add
     * @param RoleData $roleDataEntity Any role data entity
     *                                 down.  Greatly depends on the role type.
     *
     * @return Integer ID of the newly created RoleData entity.
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function addRole($roleName, RoleData $roleDataEntity)
    {
        $this->role = EntityUtils::getEntityByName('Role', $roleName);

        $roleDataEntity->user = $this->user;
        $roleDataEntity->username = $this->user->username;
        $roleDataEntity->program = $this->program;
        $roleDataEntity->first_name = $this->user->first_name;
        $roleDataEntity->last_name = $this->user->last_name;
        $roleDataEntity->email = $this->user->email;

        //Be sure to persist the new entity
        $roleDataEntity->save(false);

        //Sync some extra information that is deprecated in the student/instructor entities
        if ($this->role->name == "student") {
            $roleDataEntity->setGraduationDate($this->end_date);
            $roleDataEntity->home_phone = $this->user->home_phone;
            $roleDataEntity->cell_phone = $this->user->cell_phone;
            $roleDataEntity->work_phone = $this->user->work_phone;
            $roleDataEntity->address = $this->user->address;
            $roleDataEntity->city = $this->user->city;
            $roleDataEntity->state = $this->user->state;
            $roleDataEntity->zip = $this->user->zip;
            $roleDataEntity->contact_name = $this->user->contact_name;
            $roleDataEntity->contact_phone = $this->user->contact_phone;
            $roleDataEntity->contact_relation = $this->user->contact_relation;

            // set the correct property for this entity type
            $this->studentRoleData = $roleDataEntity;

        } else {
            if ($this->role->name == "instructor") {
                $roleDataEntity->cell_phone = $this->user->cell_phone;
                $roleDataEntity->office_phone = $this->user->work_phone;

                // set the correct property for this entity type
                $this->instructorRoleData = $roleDataEntity;
            }
        }

        return $roleDataEntity;
    }


    /**
     * This function can be used to see if a user has a scheduling conflict
     *
     * @param \DateTime Start datetime
     * @param \DateTime End   datetime
     *
     * @return boolean true if there is a conflict, false otherwise
     */
    public function hasConflict($startdate, $enddate, $shiftId = null)
    {
        /** @var SlotAssignmentRepository $slotAssignmentRepository */
        $slotAssignmentRepository = EntityUtils::getRepository("SlotAssignment");
        $assignments = $slotAssignmentRepository->getUserContextAssignmentIdsByDate($this->id, $startdate, $enddate,
            $shiftId);

        if (count($assignments) > 0) {
            return true;
        }

        // if this is a student, check their shifts, too
        if ($this->role->name == "student") {
            $shifts = EntityUtils::getRepository("ShiftLegacy")->getShiftsByDateRange($this->id, $startdate, $enddate,
                $shiftId);
            if (count($shifts) > 0) {
                return true;
            }
        }

        return false;
    }


    /**
     * This function can be used to see if a user is scheduled for a given event
     *
     * @param int $event_id
     *
     * @return boolean true if the user is scheduled, false otherwise
     */
    public function isScheduled($event_id)
    {
        foreach ($this->slot_assignments as $scheduled_assignment) {
            if ($event_id == $scheduled_assignment->slot->event->id) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param      $req
     * @param null $expirationDate
     * @param int  $completed
     * @param null $dueDate
     * @param null $notes
     * @param null $assigner_userContextId
     * @param bool $sendNotification
     *
     * @return bool|EntityBaseClass
     * @codeCoverageIgnore
     * @deprecated
     */
    public function assignRequirement(
        $req,
        $expirationDate = null,
        $completed = 0,
        $dueDate = null,
        $notes = null,
        $assigner_userContextId = null,
        $sendNotification = false
    ) {
        if ($this->hasRequirement($req)) {
            return false;
        }

        $attachment = EntityUtils::getEntity("RequirementAttachment");
        $attachment->requirement = $req;
        $attachment->user_context = $this;
        $attachment->set_completed($completed, $assigner_userContextId);

        if ($dueDate) {
            $attachment->set_due_date($dueDate, $assigner_userContextId);
        }

        if ($expirationDate) {
            $attachment->set_expiration_date($expirationDate, $assigner_userContextId);
        }

        $attachment->recordHistory(4, $notes, $assigner_userContextId);
        $this->requirement_attachments[] = $attachment;

        $usersToNotify = [];
        if ($sendNotification == true) {
            $usersToNotify[$this->id][] = [
                "name"            => $this->user->getName(),
                "email"           => $this->user->email,
                "requirementName" => $attachment->requirement->name,
                "status"          => "assigned",
                "due_date"        => $attachment->due_date->format("M j, Y")
            ];

            RequirementNotification::sendNotifications($usersToNotify, "requirement-assigned-notification.phtml");
        }

        return $attachment;
    }


    /**
     * @param     $req
     *
     * @return $this
     */
    public function removeRequirement($req)
    {
        if ($attachment = $this->getAttachment($req)) {
            $this->requirement_attachments->removeElement($attachment);
            $attachment->remove(false);
        }

        return $this;
    }


    /**
     * @param $req
     *
     * @return bool
     * @todo this should be refactored to use the ArrayCollection and/or Criteria API properly
     */
    public function hasRequirement($req)
    {
        foreach ($this->requirement_attachments as $attachment) {
            if ($attachment->requirement->id == $req->id && $attachment->archived == 0) {
                return true;
            }
        }

        return false;
    }


    /**
     * @param $req
     *
     * @return mixed|null
     * @todo this should be renamed getRequirementAttachment
     * @todo this should be refactored to use the ArrayCollection and/or Criteria API properly
     */
    public function getAttachment($req)
    {
        foreach ($this->requirement_attachments as $attachment) {
            if ($attachment->requirement->id == $req->id) {
                return $attachment;
            }
        }

        return null;
    }


    /**
     * Given a specific list of requirements, determine if this user is compliant with them
     * If no requirements are given, return true
     *
     * @var mixed $requirements \Fisdap\Entity\Requirement | array of \Fisdap\Entity\Requirement
     * @var boolean
     * @return bool
     */
    public function isCompliant($requirements = [])
    {
        if ( ! is_array($requirements)) {
            $requirements = [$requirements];
        }

        //Return true early if there are no reqs to check against
        if (empty($requirements)) {
            return true;
        }

        //Get an array of just the requirement IDs to compare against
        $reqIds = [];
        foreach ($requirements as $requirement) {
            $reqIds[] = $requirement->id;
        }

        //Loop over this user's attachments and figure out if he/she is out of compliance
        foreach ($this->requirement_attachments as $attachment) {
            if (in_array($attachment->requirement->id, $reqIds)) {
                if ( ! $attachment->isCompliant()) {
                    return false;
                }
            }
        }

        return true;
    }


    /**
     * @return mixed
     */
    public function isProgramCompliant()
    {
        return EntityUtils::getRepository("Requirement")->isProgramCompliant($this->id);
    }


    /**
     * @param bool $updateCompliance
     *
     * @return bool
     * @codeCoverageIgnore
     * @deprecated
     */
    public function autoAttachRequirements($updateCompliance = true)
    {
        $requirements = EntityUtils::getRepository("RequirementAutoAttachment")->findBy([
            "role"                => $this->role,
            "program"             => $this->program,
            "certification_level" => $this->certification_level
        ]);

        //Flag to see if compliance status has changed for this user role
        $complianceChanged = false;

        foreach ($requirements as $auto) {
            //If at least one requirement is successfully attached, we should update compliance
            if ($this->assignRequirement($auto->requirement, null, 0, null, "new account created")) {
                $complianceChanged = true;
            }
        }

        if ($updateCompliance) {
            EntityUtils::getRepository("Requirement")->updateCompliance([$this->id]);
        }

        return $complianceChanged;
    }


    /**
     * Delete all of the calendar subscriptions created by this user
     *
     * @return void
     */
    public function removeCalendarSubscriptions()
    {
        foreach ($this->subscriptions as $sub) {
            $this->subscriptions->removeElement($sub);
            $sub->delete(false);
        }
    }


    /**
     * Is this user role an instructor
     *
     * @return bool
     */
    public function isInstructor()
    {
        return $this->role->id == 2;
    }


    /**
     * Is this user role a student
     *
     * @return bool
     */
    public function isStudent()
    {
        return $this->role->id == 1;
    }


    /**
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }


    /**
     * @param Role $role
     */
    public function setRole(Role $role)
    {
        $this->role = $role;
    }


    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }


    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }


    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }


    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }


    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }


    /**
     * @return ArrayCollection|SerialNumberLegacy[]
     */
    public function getSerialNumbers()
    {
        return $this->serialNumbers;
    }

    /**
     * @return SerialNumberLegacy
     */
    public function getPrimarySerialNumber()
    {
        // the first serial number is the primary one
        return $this->serialNumbers[0];
    }


    /**
     * @param SerialNumberLegacy $serialNumber
     */
    public function addSerialNumber(SerialNumberLegacy $serialNumber)
    {
        $this->getSerialNumbers()->add($serialNumber);
    }


    /**
     * @param SerialNumberLegacy $serialNumberLegacy
     */
    public function removeSerialNumber(SerialNumberLegacy $serialNumberLegacy)
    {
        $this->getSerialNumbers()->removeElement($serialNumberLegacy);
    }


    public function clearSerialNumbers()
    {
        $this->getSerialNumbers()->clear();
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }


    /**
     * This function checks to see if the user role has the requested
     * permission.
     *
     * @param mixed $permission This is one of three possible things-
     *                          - \Fisdap\Entity\Permission object representing the permission
     *                          - Integer ID of the permission to check
     *                          - String name of the permission to check
     *
     * @return Boolean true if the user role has the permission, false if not.
     */
    public function hasPermission($permission)
    {
        // a non-instructor is never going to have permissions, so always return false
        if ( ! $this->isInstructor()) {
            return false;
        }

        $permissionObject = null;

        $repos = \Fisdap\EntityUtils::getEntityManager()->getRepository('\Fisdap\Entity\Permission');

        if (is_string($permission)) {
            $permissionObject = $repos->findOneByName($permission);
        } else {
            if (is_int($permission)) {
                $permissionObject = $repos->findOneById($permission);
            } else {
                if (is_a($permission, '\Fisdap\Entity\Permission')) {
                    $permissionObject = $permission;
                } else {
                    return false;
                }
            }
        }

        if (is_a($permissionObject, '\Fisdap\Entity\Permission')) {
            return ($this->instructorRoleData->permissions & $permissionObject->bit_value);
        }

        return false;
    }

    /**
     * This function returns an array containing the available tabs and sub-nav
     * bits for a given user
     *
     * @return Array containing whether or not to include the given tab/sub-nav
     * links.
     * @todo this is presentation logic and should be moved to a more appropriate view-related class
     * @codeCoverageIgnore
     * @deprecated
     */
    public function getIncludedTabs(){
        $includedTabs = [];

        if ($this->isInstructor()) {
            // Check to see if the instructor has permission to edit student data.
            if($this->hasPermission('View All Data') || ($this->hasPermission('View Schedules'))) {
                $includedTabs['home'] = ["dashboard", "student_portfolios"];
            }else{
                $includedTabs['home'] = ["dashboard"];
            }

            $includedTabs['shifts'] = ["schedule", "skills_and_patients"];
            $includedTabs['learning_center'] = ['schedule_exam', 'retrieve_scores'];
            if ($this->hasPermission('View Reports')) {
                $includedTabs['reports'] = ["legacy_reports", "new_reports"];
            }
            $includedTabs['account'] = true;
            $includedTabs['community'] = true;
            $includedTabs['help'] = true;
        } else {
            $serial_number = $this->getPrimarySerialNumber();

            // all students see these
            $includedTabs['learning_center'] = ['learning_center_home'];
            $includedTabs['account'] = true;
            $includedTabs['community'] = true;
            $includedTabs['help'] = true;

            // if the student has scheduler, add the dashboard, schedule and reports tabs
            if ($serial_number->hasScheduler()){
                $includedTabs['home'] = ["dashboard"];
                $includedTabs['shifts'] = ["schedule"];
                $includedTabs['reports'] = true;
            }

            // if the student has tracking, add the dashboard/portfolio, schedule/skills, and reports tabs
            if ($serial_number->hasSkillsTracker()) {
                $includedTabs['home'] = ["dashboard", "my_portfolio"];
                $includedTabs['shifts'] = ["schedule", "skills_and_patients"];
                $includedTabs['reports'] = true;
            }

            // if the student didn't get their portfolio via tracker, but they have scheduler
            // give them the portfolio link, too
            if (!$serial_number->hasSkillsTracker() && $serial_number->hasScheduler()) {
                $includedTabs['home'][] = "my_portfolio";
            }
        }

        //Remove certain tabs for non EMS programs
        if ($this->getProgram()->profession->id != 1) {
            unset($includedTabs['learning_center']);
            unset($includedTabs['community']);
        }

        // Aussies get testing
        if ($this->getProgram()->profession->name == "EMS (AU)") {
            $includedTabs['learning_center'] = ['learning_center_home'];
        }

        return $includedTabs;
    }

}
