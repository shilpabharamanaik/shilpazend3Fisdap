<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;


/**
 * Entity class for Legacy Instructors.
 * 
 * @Entity(repositoryClass="Fisdap\Data\Instructor\DoctrineInstructorLegacyRepository")
 * @Table(name="InstructorData")
 */
class InstructorLegacy extends RoleData
{
	CONST INSTRUCTOR_MAILING_LIST_ID = 1;
	
	/**
	 * @Id
	 * @Column(name="Instructor_id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;

    /**
	 * @ManyToOne(targetEntity="ProgramLegacy")
	 * @JoinColumn(name="ProgramId", referencedColumnName="Program_id")
	 * @codeCoverageIgnore
	 * @deprecated
	 */
	protected $program;
    
    /**
	 * @codeCoverageIgnore
	 * @deprecated
     * @Column(name="Email", type="string", nullable=true)
     */
    protected $email;
    
    /**
     * @Column(name="EmailEventFlag", type="boolean", nullable=false)
     */
    protected $email_event_flag = false;
    
    /**
	 * @codeCoverageIgnore
     * @deprecated
     * @Column(name="PrimaryContact", type="boolean", nullable=false)
     */
    protected $primary_contact = false;
    
    /**
     * @Column(name="EmailList", type="boolean", nullable=false)
	 * @codeCoverageIgnore
     * @deprecated Use onMailingList() instead to determine if they're on the mailing list
     */
    protected $fisdap_mailing_list = false;
    
    /**
     * @Column(name="OfficePhone", type="string", nullable=true)
     */
    protected $office_phone;
    
    /**
     * @Column(name="CellPhone", type="string", nullable=true)
     */
    protected $cell_phone;
    
    /**
     * @Column(name="Pager", type="string", nullable=true)
     */
    protected $pager;
    
    /**
     * @Column(name="BigBroEmails", type="boolean", nullable=false)
     */
    protected $receive_field_late_data_emails = false;
    
    /**
     * @Column(name="ClinicalBigBroEmails", type="boolean", nullable=false)
     */
    protected $receive_clinical_late_data_emails = false;
    
    /**
     * @Column(name="Permissions", type="integer", nullable=false)
     */
    protected $permissions = 0;
    
    /**
     * @Column(name="AcceptedAgreement", type="boolean", nullable=false)
     */
    protected $accepted_agreement = false;
    
    /**
     * @Column(name="LabBigBroEmails", type="boolean", nullable=false)
     */
    protected $receive_lab_late_data_emails = false;
    
    /**
     * @Column(name="Reviewer", type="boolean", nullable=false)
     */
    protected $is_reviewer = false;
    
    /**
     * @Column(name="ActiveReviewer", type="integer", nullable=false)
     */
    protected $is_active_reviewer = 0;
    
    /**
     * @Column(name="ReviewerNotes", type="string", nullable=false)
     */
    protected $reviewer_notes = "";
	
	/**
	 * @codeCoverageIgnore
	 * @deprecated
	 * @var ArrayCollection
	 * 
     * @ManyToMany(targetEntity="PermissionSubRole")
     * @JoinTable(name="fisdap2_instructors_sub_roles",
     *  joinColumns={@JoinColumn(name="instructor_id", referencedColumnName="Instructor_id")},
     *  inverseJoinColumns={@JoinColumn(name="permission_sub_role_id",referencedColumnName="id")})
     */
	protected $permissionSubRoles;
	
	/**
	 * @OneToMany(targetEntity="ClassSectionInstructorLegacy", mappedBy="instructor", cascade={"persist","remove"})
	 */
	protected $classSectionInstructors;

    /**
     * @var array
     */
    private static $permissionCache = array();


	public function __construct()
	{
		$this->permissionSubRoles = new ArrayCollection();
		$this->classSectionInstructors = new ArrayCollection();
	}


	/**
	 * @return mixed
	 */
	public function getEmailEventFlag()
	{
		return $this->email_event_flag;
	}


	/**
	 * @param mixed $email_event_flag
	 */
	public function setEmailEventFlag($email_event_flag)
	{
		$this->email_event_flag = $email_event_flag;
	}


	/**
	 * @return mixed
	 */
	public function getReceiveFieldLateDataEmails()
	{
		return $this->receive_field_late_data_emails;
	}


	/**
	 * @param mixed $receive_field_late_data_emails
	 */
	public function setReceiveFieldLateDataEmails($receive_field_late_data_emails)
	{
		$this->receive_field_late_data_emails = $receive_field_late_data_emails;
	}


	/**
	 * @return mixed
	 */
	public function getReceiveClinicalLateDataEmails()
	{
		return $this->receive_clinical_late_data_emails;
	}


	/**
	 * @param mixed $receive_clinical_late_data_emails
	 */
	public function setReceiveClinicalLateDataEmails($receive_clinical_late_data_emails)
	{
		$this->receive_clinical_late_data_emails = $receive_clinical_late_data_emails;
	}


	/**
	 * @return mixed
	 */
	public function getReceiveLabLateDataEmails()
	{
		return $this->receive_lab_late_data_emails;
	}


	/**
	 * @param mixed $receive_lab_late_data_emails
	 */
	public function setReceiveLabLateDataEmails($receive_lab_late_data_emails)
	{
		$this->receive_lab_late_data_emails = $receive_lab_late_data_emails;
	}


    /**
     * @param bool $flush
     */
	public function remove_groups($flush = false)
	{
		foreach ($this->classSectionInstructors as $section)
		{
			$this->classSectionStudent->removeElement($section);
			$section->delete($flush);
		}
	}


    /**
     * @param integer $value the bit value representing the permissions to set
     * @return $this
     */
    public function set_permissions($value)
    {
        $this->permissions = $value;
        //If the user does not have permission to 'View Schedules', he/she should lose their calendar subscriptions
        if (!($value & 128)) {
            $this->user_context->removeCalendarSubscriptions();
        }

        return $this;
    }


    /**
     * Determines whether the instructor has a given permission
     *
     * @param mixed $permission either ID or name of the permission
     * @return boolean
     */
    public function hasPermission($permission)
    {
		//Load the permission cache if it hasn't been initialized
		if (empty(self::$permissionCache)) {
			self::loadPermissionCache();
		}
		
		//Check to see if they have that permission
        if (array_key_exists($permission, self::$permissionCache)) {
            return self::$permissionCache[$permission]->bit_value & $this->permissions;
        }
        
		//Double check against the database if they asked for the ID of the permission instead of the name
        if (is_int($permission)) {
            $permission = EntityUtils::getEntity('Permission', $permission);
        } else {
            $permission = EntityUtils::getRepository('Permission')->findOneByName($permission);
        }
        
        return $this->permissions & $permission->bit_value;
    }


    /**
     * @return array
     */
	public function getAllPermissionBits()
	{
		$bit_values = array();
		
		//Load the permission cache if it hasn't been initialized
		if (empty(self::$permissionCache)) {
			self::loadPermissionCache();
		}
		
		foreach (self::$permissionCache as $permission) {
			if ($permission->bit_value & $this->permissions) {
				$bit_values[] = $permission->bit_value;
			}
		}
		return $bit_values;
	}
	

    /**
	 * Grab all the permissions from the DB and add them to our static cache
	 * @return void
	 */
	public static function loadPermissionCache()
	{
		$permissions = EntityUtils::getRepository("Permission")->findAll();
		foreach($permissions as $permission) {
			self::$permissionCache[$permission->name] = $permission;
		}
	}


	/**
	 *	This may be for future use, here for compatibility with
	 *	userLegacy::canViewData()
	 *	Checks if other user can view this User's data.)
	 *	Could handle anonymous uses too, but not currently
	 *	@returns boolean $canViewData
	 */
	public function dataCanBeViewedBy($viewingUser = null)
	{
		$viewingUser = User::getUser($viewingUser);
		if (is_null($viewingUser)) {
			return false;
		}
		
		// for instructors only same instructor can view data
		return ($this->user->id == $viewingUser->id);
	}


    /**
     * Determines whether the instructor cares about a given student
     *
     * @param mixed $student_id 
     * @return boolean
     */
    public function isRelevantStudent($student_id)
    {
		$relevant = FALSE;
		$students_relevant_instructors = StudentLegacy::getRelevantInstructorIds($student_id);
        foreach ($students_relevant_instructors as $inst_id) {
			if ($inst_id == $this->id) {
				$relevant = TRUE;
			}
		}

		return $relevant;
    }


	/**
	 * Add this instructor to the Fisdap Instructor Mailing list
	 *
	 * @param mixed $list integer | \Fisdap\Entity\MailingList
	 * @return \Fisdap\Entity\InstructorLegacy
	 */
	public function addToMailingList($list = self::INSTRUCTOR_MAILING_LIST_ID)
	{
		$this->fisdap_mailing_list = true;
		$this->user->addMailingList($list, $this->user_context);
		return $this;
	}


	/**
	 * Remove this instructor from the Fisdap Instructor Mailing list
	 *
	 * @param mixed $list integer | \Fisdap\Entity\MailingList
	 * @return \Fisdap\Entity\InstructorLegacy
	 */
	public function removeFromMailingList($list = self::INSTRUCTOR_MAILING_LIST_ID)
	{
		$this->fisdap_mailing_list = false;
		$this->user->removeMailingList($list);
		return $this;
	}


	/**
	 * Is the instructor on the Fisdap Instructor Mailing list
	 * 
	 * @param mixed $list integer | \Fisdap\Entity\MailingList
	 * @return boolean
	 */
	public function onMailingList($list = self::INSTRUCTOR_MAILING_LIST_ID)
	{
		return $this->user->onMailingList($list);
	}


	/**
	 * Set the permission subroles for this instructor
	 * @param array $subroleIds
	 * @return \Fisdap\Entity\InstructorLegacy
	 * @codeCoverageIgnore
	 * @deprecated - this was never implemented in the UI and should no longer be needed
	 */
	public function setPermissionSubRoles($subroleIds)
	{
        if (!$subroleIds) {
            $subroleIds = array();
        }
        
		$this->permissionSubRoles->clear();
		foreach ($subroleIds as $subroleId) {
			$subrole = EntityUtils::getEntity("PermissionSubRole", $subroleId);
			$this->permissionSubRoles->add($subrole);
		}
		return $this;
	}


	/**
	 * Get the IDs of each subrole attached to this instructor
	 * @return array containing all the subrole IDs
	 */
	public function getPermissionSubRoleIds()
	{
		$ids = array();
		foreach ($this->permissionSubRoles as $subrole) {
			$ids[] = $subrole->id;
		}
		return $ids;
	}


	/**
	 * Activate a serial number and tie it to this instructor
	 *
	 * @param SerialNumberLegacy
	 * @return InstructorLegacy
	 * @codeCoverageIgnore
	 * @deprecated
	 */
	public function activateSerialNumber(SerialNumberLegacy $serial)
	{
		$serial->instructor_id = $this->id;
		parent::activateSerialNumber($serial);
		
		return $this;
	}


    /**
     * @return mixed
     */
	public function getPermissionsHistory()
	{
		return EntityUtils::getRepository("PermissionHistoryLegacy")->getAllByInstructor($this->id);
	}
}
