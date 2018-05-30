<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
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
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\OrderBy;
use Fisdap\Api\Users\CurrentUser\CurrentUser;
use Fisdap\Api\Users\Entity\Traits\Address;
use Fisdap\Api\Users\Entity\Traits\Certifications;
use Fisdap\Api\Users\Entity\Traits\Contact;
use Fisdap\Api\Users\Entity\Traits\Licenses;
use Fisdap\Api\Users\Entity\Traits\Phones;
use Illuminate\Contracts\Auth\Authenticatable;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Entity class for Users.
 *
 * @Entity(repositoryClass="Fisdap\Data\User\DoctrineUserRepository")
 *
 * @Table(name="fisdap2_users")
 * @HasLifecycleCallbacks
 */
class User
{
    use Address, Phones, Contact, Certifications, Licenses;

    /**
     * @var int
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(type="uuid", length=16, unique=true, nullable=true)
     */
    //   protected $psg_user_id;

    /**
     * @var string
     * @Column(type="string", unique=true, nullable=true)
     */
    protected $lti_user_id;

    /**
     * @var string
     * @Column(type="string")
     */
    public $first_name;

    /**
     * @var string
     * @Column(type="string")
     */
    public $last_name;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $password;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $password_salt;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $password_hint;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $legacy_password;

    /**
     * @var string
     * @Column(type="string", unique=true)
     */
    protected $username;

    /**
     * @var string
     * @Column(type="string")
     */
    public $email;

    /**
     * @var UserContext
     * @OneToOne(targetEntity="UserContext", cascade={"persist", "remove", "detach"}, fetch="EAGER")
     */
    protected $current_user_context = null;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $demo = false;

    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $accepted_agreement = false;

    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $birth_date;

    /**
     * @var string
     * @Column(name="gender", type="string", nullable=true)
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    protected $old_gender;

    /**
     * @var Gender|null
     * @ManyToOne(targetEntity="Gender", fetch="EAGER")
     */
    //  protected $gender = null;

    /**
     * @var Ethnicity|null
     * @ManyToOne(targetEntity="Ethnicity", fetch="EAGER")
     */
    //protected $ethnicity = null;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $updated_on;

    /**
     * @var ArrayCollection|UserContext[]
     * @OneToMany(targetEntity="UserContext", mappedBy="user", cascade={"persist","remove","detach"}, fetch="EAGER")
     */
    public $userContexts;

    /**
     * @var ArrayCollection|SerialNumberLegacy[]
     * @OneToMany(targetEntity="SerialNumberLegacy", mappedBy="user", cascade={"detach"}, fetch="LAZY")
     * @JoinColumn(name="id", referencedColumnName="User_id")
     * @OrderBy({"configuration" = "DESC"})
     *
     * @codeCoverageIgnore
     * @deprecated
     */
     public $serial_numbers;

    /**
     * @var Staff|null
     * @OneToOne(targetEntity="Staff", mappedBy="user", cascade={"persist","remove","detach"}, fetch="EAGER")
     */
    protected $staff = null;

    /**
     * @ManyToMany(targetEntity="MailingList")
     * @JoinTable(name="fisdap2_users_mailing_lists",
     *  joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *  inverseJoinColumns={@JoinColumn(name="mailing_list_id",referencedColumnName="id")})
     */
    //protected $mailing_lists;

    /**
     * @var \MailChimp_Wrapper
     * @todo refactor this - violation of SRP - User does not need to "know" about MailChimp
     */
    private $mailchimp;


    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $deleted = false;


    /**
     * @var string|null OAuth2 access token used for authentication
     */
    private $accessToken = null;


    public function __construct()
    {
        $this->userContexts = new ArrayCollection();
        $this->serial_numbers = new ArrayCollection();
        $this->mailing_lists = new ArrayCollection();
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * @return mixed
     */
    public function getPsgUserId()
    {
        return $this->psg_user_id;
    }


    /**
     * @param mixed $psg_user_id
     */
    public function setPsgUserId($psg_user_id)
    {
        $this->psg_user_id = $psg_user_id;
    }


    /**
     * @return string
     */
    public function getLtiUserId()
    {
        return $this->lti_user_id;
    }


    /**
     * @param string $lti_user_id
     */
    public function setLtiUserId($lti_user_id)
    {
        $this->lti_user_id = $lti_user_id;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_first_name($value)
    {
        $this->first_name = $value;
        $roleData = $this->getCurrentRoleData();
        if ($roleData) {
            $roleData->first_name = $value;
        }
        return $this;
    }


    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->first_name = $firstName;
    }


    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->first_name;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_last_name($value)
    {
        $this->last_name = $value;
        $roleData = $this->getCurrentRoleData();
        if ($roleData) {
            $roleData->last_name = $value;
        }
        return $this;
    }


    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->last_name = $lastName;
    }


    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->last_name;
    }


    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->first_name . ' ' . $this->last_name;
    }




    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * @param string $value
     *
     * @return $this
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_username($value)
    {
        $this->username = $value;
        $roleData = $this->getCurrentRoleData();
        if ($roleData) {
            $roleData->username = $value;
        }
        return $this;
    }


    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }


    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }


    /**
     * @param string $password
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_password($password)
    {
        $this->setPassword($password);
    }


    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_salt = self::createPasswordSalt();
        $this->password = md5($password . $this->password_salt);
        return $this->password;
    }

    public function hashedpassword($password, $password_salt = '')
    {
        if (!empty($password_salt)) {
            $this->password_salt = $password_salt;
        } else {
            $this->password_salt = self::createPasswordSalt();
        }
        $hashpass = md5($password . $this->password_salt);
        return $hashpass;
    }

    /**
     * @return string
     */
    public function getPasswordSalt()
    {
        return $this->password_salt;
    }


    /**
     * @return string
     */
    public static function createPasswordSalt()
    {
        $dynamicSalt = "";

        for ($i = 0; $i < 50; $i++) {
            $dynamicSalt .= chr(rand(33, 126));
        }

        return $dynamicSalt;
    }


    public function getPasswordHint()
    {
        /**
         * @return string
         */
        return $this->password_hint;
    }


    /**
     * @param string $password_hint
     */
    public function setPasswordHint($password_hint)
    {
        $this->password_hint = $password_hint;
    }


    /**
     * @param string $value
     *
     * @return $this - User
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_email($value)
    {
        if ($this->email && $this->email != $value) {
            $this->updateMailingLists($value, $this->email);
        }

        $this->email = $value;
        $roleData = $this->getCurrentRoleData();
        if ($roleData) {
            $roleData->email = $value;
        }

        return $this;
    }


    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }


    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }


    /**
     * @return boolean
     */
    public function isDemo()
    {
        return $this->demo;
    }


    /**
     * @param boolean $demo
     */
    public function setDemo($demo)
    {
        $this->demo = $demo;
    }


    /**
     * @return boolean
     */
    public function hasAcceptedAgreement()
    {
        return $this->accepted_agreement;
    }


    /**
     * @param boolean $accepted_agreement
     */
    public function setAcceptedAgreement($accepted_agreement)
    {
        $this->accepted_agreement = $accepted_agreement;
    }


    /**
     * @return Gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param Gender|null $gender
     */
    public function setGender(Gender $gender = null)
    {
        $this->gender = $gender;
    }


    /**
     * @return Ethnicity
     */
    public function getEthnicity()
    {
        return $this->ethnicity;
    }

    /**
     * @param Ethnicity|null $ethnicity
     */
    public function setEthnicity(Ethnicity $ethnicity = null)
    {
        $this->ethnicity = $ethnicity;
    }

    /**
     * @return \DateTime
     */
    public function getBirthDate()
    {
        return $this->birth_date;
    }

    /**
     * @param \DateTime|null $birthDate
     */
    public function setBirthDate(\DateTime $birthDate = null)
    {
        $this->birth_date = $birthDate;
    }

    /**
     * The user is changing their email address, so make sure
     * their new email address gets subscribed to the same mailing lists
     *
     * @param string $newEmail the user's new email address
     * @param string $oldEmail the user's old email address
     * @return void
     * @todo refactor this - violates SRP - User entity should not be responsible for mailing lists
     */
    public function updateMailingLists($newEmail, $oldEmail)
    {
        $mailchimp = $this->getMailChimpWrapper();
        foreach ($this->mailing_lists as $list) {
            $mailchimp->updateEmail($oldEmail, array('EMAIL' => $newEmail), $list->mailchimp_name);
        }
    }


    /**
     * @PrePersist
     * @PreUpdate
     */
    public function updated()
    {
        $this->updated_on = new \DateTime("now");
    }


    /**
     * Get the first and last name of this user
     * @return string
     *
     * @codeCoverageIgnore
     * @deprecated
     */
    public function getName()
    {
        return $this->first_name . " " . $this->last_name;
    }


    /**
     * @return ArrayCollection|UserContext[]
     */
    public function getAllUserContexts()
    {
        return $this->userContexts;
    }


    /**
     * @param Role                    $role
     * @param ProgramLegacy           $program
     * @param \DateTime|null          $endDate
     * @param CertificationLevel|null $certificationLevel
     * @param null                    $courseId
     *
     * @return bool
     */
    public function userContextExists(
        Role $role,
        ProgramLegacy $program,
        \DateTime $endDate = null,
        CertificationLevel $certificationLevel = null,
        $courseId = null
    ) {
        $criteria = Criteria::create();

        $criteria->where(Criteria::expr()->eq('role', $role))
            ->andWhere(Criteria::expr()->eq('program', $program));

        if (is_int($courseId)) {
            $criteria->andWhere(Criteria::expr()->eq('courseId', $courseId));
        } else {
            if ($endDate instanceof \DateTime) {
                $criteria->andWhere(Criteria::expr()->eq('end_date', $endDate));
            }

            if ($certificationLevel instanceof CertificationLevel) {
                $criteria->andWhere(Criteria::expr()->eq('certification_level', $certificationLevel));
            }
        }

        $userContexts = $this->getAllUserContexts()->matching($criteria);

        return $userContexts->count() > 0;
    }


    /**
     * This function iterates through all available user contexts and collates the data.
     *
     * @param String $roleName The name of the role to filter by.  If no name is provided, all contexts will be returned.
     *                         If a name is provided, only contexts of the matching role type will be returned.
     *
     * @return UserContext[]
     */
    public function getUserContexts($roleName = null)
    {
        $userContexts = [];

        if ($roleName !== null) {
            foreach ($this->userContexts as $userContext) {
                if (strtolower($userContext->role->name) == strtolower($roleName)) {
                    $userContexts[] = $userContext;
                }
            }

            return $userContexts;
        }

        return $this->getAllUserContexts();
    }


    /**
     * This function sets the current "active" context to the one specified by its ID.
     *
     * @param UserContext $userContext the context to set as active
     * @return bool true if context was valid, false if not.
     */
    public function setCurrentUserContext(UserContext $userContext)
    {
        if ($userContext->getUser()->getId() == $this->id) {
            $this->current_user_context = $userContext;
            return true;
        } else {
            return false;
        }
    }


    /**
     * Getter for the current user context
     *
     * @return UserContext|bool
     */
    public function getCurrentUserContext()
    {
        if (! $this->current_user_context) {
            if ($this->userContexts->count() > 0) {
                $this->current_user_context = $this->userContexts->first();
            } else {
                return false;
            }
        }

        return $this->current_user_context;
    }


    /**
     * @return bool|UserContext
     */
    public function context()
    {
        return $this->getCurrentUserContext();
    }


    /**
     * @return bool
     */
    public function hasCurrentUserContext()
    {
        return !is_null($this->current_user_context);
    }


    /**
     * @return bool
     */
    public function hasContext()
    {
        return $this->hasCurrentUserContext();
    }


    /**
     * This call gets delegated down to the UserContexts object- used to fetch its
     * role-specific data...
     *
     * @return RoleData
     * @codeCoverageIgnore
     * @deprecated
     * @todo refactor - a user's current role data should be accessed via the current context stored in the Session
     */
    public function getCurrentRoleData()
    {
        return is_null($this->current_user_context) ? null : $this->current_user_context->getRoleData();
    }


    /**
     * This function returns all available role data for the user.
     *
     * @return RoleData[]
     */
    public function getAllRoleData()
    {
        $roleData = [];

        foreach ($this->userContexts as $userContext) {
            $roleData[] = $userContext->getRoleData();
        }

        return $roleData;
    }


    /**
     * Get the user's program ID
     *
     * @return integer the Program ID of the user's current role
     * @codeCoverageIgnore
     * @deprecated
     * @todo refactor - user context info should be accessed through the session
     */
    public function getProgramId()
    {
        if ($this->current_user_context) {
            return $this->current_user_context->program->id;
        } else {
            return false;
        }
    }


    /**
     * Get the user's program entity
     *
     * @return ProgramLegacy the Program entity of the user's current role
     * @codeCoverageIgnore
     * @deprecated
     * @todo refactor - user context info should be accessed through the session
     */
    public function getProgram()
    {
        if ($this->current_user_context) {
            return $this->current_user_context->program;
        } else {
            return false;
        }
    }


    /**
     * Get the user's program name
     *
     * @param bool $abbreviation
     *
     * @return string the Program name of the user's current role
     * @codeCoverageIgnore
     * @deprecated
     * @todo refactor - user context info should be accessed through the session
     */
    public function getProgramName($abbreviation = false)
    {
        if ($this->current_user_context) {
            return ($abbreviation) ? $this->current_user_context->program->abbreviation : $this->current_user_context->program->name;
        } else {
            return false;
        }
    }


    /**
     * @param \Fisdap\Entity\UserContext $userContext
     */
    public function associateUserContext(UserContext $userContext)
    {
        $this->userContexts->add($userContext);
    }


    /**
     * This function can be used to attach a new context to a user.
     *
     * @param String $roleName Name of the type of role to add
     * @param mixed $roleData Role data entity, \Fisdap\Entity\StudentLegacy or \Fisdap\Entity\InstructorLegacy
     * down.  Greatly depends on the role type.
     * @param Integer $programID ID of the program to add this role to.
     * @param Integer $certificationLevelID ID of the certification level for
     * this role.
     * @param null $startDate
     * @param null $endDate
     * @codeCoverageIgnore
     * @deprecated
     *
     * @return UserContext
     */
    public function addUserContext($roleName, $roleData, $programID, $certificationLevelID = null, $startDate = null, $endDate = null)
    {
        //Set the default start date to today
        if (!$startDate) {
            $startDate = new \DateTime();
        }

        //Set the default start date to today
        if (!$endDate) {
            $endDate = new \DateTime();
        }

        $newUserContext = new UserContext;
        $newUserContext->program = $programID;
        $newUserContext->user = $this;
        $newUserContext->start_date = $startDate;
        $newUserContext->end_date = $endDate;
        $newUserContext->active = true;
        $newUserContext->certification_level = $certificationLevelID;

        // Add the role data entity (student or instructor) to the new user role
        $newUserContext->addRole($roleName, $roleData);

        $this->userContexts->add($newUserContext);
        $roleData->setUserContext($newUserContext);

        return $newUserContext;
    }


    /**
     * Mark this account as "deleted"
     * 1). Remove account from program in user role and user role data
     * 2). Turn off all email flags
     *
     * @param bool $flush
     * @todo refactor - violates SRP - should User be responsible for self-deletion?
     */
    public function delete($flush = true)
    {
        $this->removeFromAllMailingLists();
        $this->getCurrentUserContext()->program = null;
        $this->getCurrentRoleData()->program = null;
        $this->deleted = true;

        if ($this->isInstructor()) {
            $this->getCurrentUserContext()->removeCalendarSubscriptions();

            $instructor = $this->getCurrentRoleData();
            $instructor->receive_field_late_data_emails = false;
            $instructor->receive_clinical_late_data_emails = false;
            $instructor->receive_lab_late_data_emails = false;
            $instructor->email_event_flag = false;

            //Remove this instructor from all of his/her class sections
            foreach ($instructor->classSectionInstructors as $csi) {
                $csi->section->removeInstructor($instructor);
            }

            //Record a history for this delete
            $history = \Fisdap\EntityUtils::getEntity("UserDeleteHistory");
            $history->deleted_user = $this;
            $history->user = self::getLoggedInUser();
            $history->save();
        }
    }


    /**
     * Returns user entity of given username
     * @return User
     * @codeCoverageIgnore
     * @deprecated
     * @todo use of this should be replaced with the equivalent call to the UserRepository
     */
    public static function getByUsername($username)
    {
        $repo = \Fisdap\EntityUtils::getEntityManager()->getRepository('\Fisdap\Entity\User');
        return $repo->findOneByUsername($username);
    }


    /**
     * Returns user entity of logged in user or null if not logged in
     * @return User
     * @codeCoverageIgnore
     * @deprecated
     * @todo make sure this isn't used by Reports
     */
    public static function getLoggedInUser()
    {
        $userName = \Zend_Auth::getInstance()->getIdentity();

        if ($userName && \Zend_Registry::isRegistered('LoggedInUser')) {
            return \Zend_Registry::get('LoggedInUser');
        } elseif (is_null($userName)) {
            return null;
        }

        $user = self::getByUsername($userName);

        \Zend_Registry::set('LoggedInUser', $user);

        /** @var CurrentUser $currentUser */
        $currentUser = \Zend_Registry::get('container')->make(CurrentUser::class);
        $currentUser->setUser($user);

        return $user;
    }


    /**
     *	Get user entity for common values.
     *	Meant to be used for processing argument value of various functions.
     *		(or for convenience)
     *	It accepts user value in several different forms recognized by value type.
     *
     *	Also, shortcut for getting current user if $userValue is null,
     *	and $autoLogInUser is left at true (ex. doing: self::getUser() )
     *
     *	@param mixed $userValue
     *		Can be:			Then:
     *		 null		 	returns currently logged in user
     *		 integer	 	$userValue is user_id
     *		 string		 	$userValue is username
     *		 boolean-true	returns currently logged in  user
     *		 boolean-false	returns null
     * @return \Fisdap\Entity\User
     * @codeCoverageIgnore
     * @deprecated
     * @todo use of this should be replaced with the equivalent call to the UserRepository
     */
    public static function getUser($userValue=null, $autoLogInUser = true)
    {
        if ($userValue && $userValue!==true) {
            if (is_integer($userValue)) {	// user_id
                return \Fisdap\EntityUtils::getEntity('User', $userValue);
            //$repo = \Fisdap\EntityUtils::getEntityManager()->getRepository("\Fisdap\Entity\User");
                //return $repo->find('\Fisdap\Entity\User', $userValue);
            } elseif (is_string($userValue)) {	// username
                return self::getByUsername($userValue);
            } elseif ($autoLogInUser) {
                return $userValue; // user entity or other value
            } else {
                return null;
            }
        } else { // autoload logged in user (null/true value)
            if ($userValue === false || $autoLogInUser == false) {
                return null;
            } else {
                return self::getLoggedInUser();
            }
        }
    }


    /**
     * This function checks to see if the user object has the requested
     * permission.
     *
     * @param mixed $permission This is one of three possible things-
     * - \Fisdap\Entity\Permission object representing the permission
     * - Integer ID of the permission to check
     * - String name of the permission to check
     *
     * @return Boolean true if the user has the permission, false if not.
     * @codeCoverageIgnore
     * @deprecated permissions should be determined with the PermissionsFinder
     */
    public function hasPermission($permission)
    {
        // a non-instructor is never going to have permissions, so always return false
        if (!$this->isInstructor()) {
            return false;
        }

        $permissionObject = null;

        $repos = \Fisdap\EntityUtils::getEntityManager()->getRepository('\Fisdap\Entity\Permission');

        if (is_string($permission)) {
            $permissionObject = $repos->findOneByName($permission);
        } elseif (is_int($permission)) {
            $permissionObject = $repos->findOneById($permission);
        } elseif (is_a($permission, '\Fisdap\Entity\Permission')) {
            $permissionObject = $permission;
        } else {
            return false;
        }

        $hasPermission = false;

        if (is_a($permissionObject, '\Fisdap\Entity\Permission')) {
            return ($this->getCurrentRoleData()->permissions & $permissionObject->bit_value);
        }

        return false;
    }


    /**
     * This function is just a shorthand method to get back the name of the role
     * that the user is currently acting as.
     *
     * @return String containing the name of the current role.
     * @codeCoverageIgnore
     * @deprecated
     * @todo refactor - user context info should be accessed through the session
     */
    public function getCurrentRoleName()
    {
        //return 'instructor';
        return $this->getCurrentUserContext()->role->name;
    }


    /**
     * This function returns the appropriate serial number for the users role.
     *
     * @param Role $alternateRole role to get the SN for.  If
     * none is given, the current user role is used.
     *
     * @return SerialNumberLegacy containing the correct serial number for the specified
     * role, or boolean false if no matching SN is found.
     */
    public function getSerialNumberForRole($alternateRole = null)
    {
        $role = $this->getCurrentUserContext()->getRole();

        if ($alternateRole != null) {
            $role = $alternateRole;
        }

        // Get the name for the role...
        $roleName = $role->getName();

        // Loop over all serial numbers and return the role appropriate one...
        foreach ($this->serial_numbers as $sn) {
            if (($sn->student_id > 0 && $roleName == "student" && ($sn->certification_level->id == $this->getCurrentUserContext()->certification_level->id)) ||
                ($sn->instructor_id > 0 && $roleName == "instructor")
            ) {
                return $sn;
            }
        }

        return false;
    }


    /**
     * @return ArrayCollection|SerialNumberLegacy[]
     * @codeCoverageIgnore
     * @deprecated serial numbers should be associated directly with a UserContext
     */
    public function getSerialNumbers()
    {
        return $this->serial_numbers;
    }


    /**
     * @param SerialNumberLegacy $serialNumber
     * @codeCoverageIgnore
     * @deprecated
     */
    public function addSerialNumber(SerialNumberLegacy $serialNumber)
    {
        $this->getSerialNumbers()->add($serialNumber);
    }


    /**
     * @return mixed
     */
    public function getGoalSet()
    {
        return GoalSet::getForUser($this);
    }


    /**
     * Determine if the user is an instructor currently
     * @return boolean
     * @codeCoverageIgnore
     * @deprecated
     */
    public function isInstructor()
    {
        return $this->getCurrentRoleName() == "instructor";
    }


    /**
     * Get the current program entity
     *
     * @return ProgramLegacy
     * @codeCoverageIgnore
     * @deprecated
     */
    public function getCurrentProgram()
    {
        return $this->getCurrentUserContext()->getProgram();
    }


    /**
     * @return Staff|null
     * @codeCoverageIgnore
     * @deprecated
     */
    public function get_staff()
    {
        return $this->staff;
    }


    /**
     * @return Staff|null
     */
    public function getStaff()
    {
        return $this->staff;
    }


    /**
     * @return string
     *
     * @todo move this method to the user context
     */
    public function getRedirectionPage()
    {
        $currentContext = $this->getCurrentUserContext();

        // if this is an instructor, redirect to myFisdap
        if ($currentContext->isInstructor()) {
            return 'dashboard';
        } else {
            $serial_number = $currentContext->getPrimarySerialNumber();

            // First, if the student has access to any page in MyFisdap
            // (helpfully titled "home" in the array), redirect them to it.
            if ($serial_number->hasSkillsTracker() || $serial_number->hasScheduler()) {
                return 'dashboard';
            }

            // If the student still has nowhere to go, redirect them to the learning
            // center.  Technically this should encompass anyone who doesn't
            // have a home-page, and who presumably has at least one kind of testing account.
            return 'learning-center';
        }
    }


    /**
     * Is this user account a staff account?
     *
     * @return boolean
     */
    public function isStaff()
    {
        // return true;
        return ! is_null($this->staff);
    }


    /**
     * Determine if this user has access to a given product
     * @param string $productName the name of the product
     * @return boolean
     * @codeCoverageIgnore
     * @deprecated product access should be determined with the ProductsFinder
     */
    public function hasProductAccess($productName)
    {
        return $this->getCurrentUserContext()->getPrimarySerialNumber()->hasProductAccess($productName);
    }

    /**
     * Add a mailing list for this user
     *
     * @param mixed       $list integer | \Fisdap\Entity\MailingList
     * @param UserContext $userContext
     *
     * @return \Fisdap\Entity\User
     * @todo - refactor
     */
    public function addMailingList($list, $userContext = null)
    {
        if (is_null($userContext)) {
            $userContext = $this->getCurrentUserContext();
        }

        $mailchimp = $this->getMailChimpWrapper();
        $list = self::id_or_entity_helper($list, 'MailingList');

        //Return early if this user already has this mailing list
        if ($this->onMailingList($list)) {
            return;
        }

        //Add this user to the mailing list and subscribe them to MailChimp via the API
        $this->mailing_lists->add($list);
        if (!$mailchimp->isEmailSubscribed($this->email, $list->mailchimp_name)) {
            $data = [
                'FNAME' => $this->first_name,
                'LNAME' => $this->last_name,
                'PNAME' => $userContext->program->name,
                'STATE' => \MailChimp_Wrapper::$states[$userContext->program->state],
            ];
            $mailchimp->subscribeEmail($this->email, $list->mailchimp_name, $data);
        }

        return $this;
    }


    /**
     * Remove mailing list from this user
     *
     * @param mixed $list integer | \Fisdap\Entity\MailingList
     * @return \Fisdap\Entity\User
     */
    public function removeMailingList($list)
    {
        $mailchimp = $this->getMailChimpWrapper();
        $list = self::id_or_entity_helper($list, 'MailingList');

        //Return early if this user doesn't have this mailing list
        if (!$this->onMailingList($list)) {
            return;
        }

        //Remove this user from the mailing list and unsubscribe them to MailChimp via the API
        $this->mailing_lists->removeElement($list);
        if ($mailchimp->isEmailSubscribed($this->email, $list->mailchimp_name)) {
            $mailchimp->unsubscribeEmail($this->email, $list->mailchimp_name);
        }

        return $this;
    }


    /**
     * Remove this user from all mailing lists
     * @return \Fisdap\Entity\User
     */
    public function removeFromAllMailingLists()
    {
        $mailchimp = $this->getMailChimpWrapper();
        foreach ($this->mailing_lists as $list) {
            $this->removeMailingList($list);
        }
        return $this;
    }


    /**
     * Does this user have a mailing list?
     *
     * @param mixed $list integer | \Fisdap\Entity\MailingList
     * @return boolean
     */
    public function onMailingList($list)
    {
        $mailchimp = $this->getMailChimpWrapper();

        $list = self::id_or_entity_helper($list, 'MailingList');
        $subscribed = $this->mailing_lists->contains($list);

        //If the database says they're subscribed, but mailchimp says they're not, listen to mailchimp
        if ($subscribed && !$mailchimp->isEmailSubscribed($this->email, $list->mailchimp_name)) {
            $this->mailing_lists->removeElement($list);
            $this->save();
            $subscribed = false;
        }

        return $subscribed;
    }


    /**
     * Populate the mailchimp field, then return it
     */
    public function getMailChimpWrapper()
    {
        if (is_null($this->mailchimp)) {
            $this->mailchimp = new \MailChimp_Wrapper();
        }

        return $this->mailchimp;
    }


    public function addDashboardMessage($subject, $authorType, $body)
    {
        // Create a myFisdap message
        $message = new \Fisdap\Entity\Message();
        $message->set_title($subject);
        $message->set_author_type($authorType);
        $message->set_body($body);

        $to = [$this->id];
        $successfulRecipients = $message->deliver($to);
    }


    /**
     * Authenticate a user's login credentials using old or new authentication methods.
     * If they don't have a password using the new encryption method, create one.
     *
     * @param string $username
     * @param string $password
     * @param boolean $hashed has the password parameter already been hashed?
     * @return boolean
     */
    public static function authenticate_password($username, $password, $hashed = false)
    {
        $user = self::getByUsername($username);

        if ($user === null) {
            throw new ResourceNotFoundException('Invalid username/password', 403);
        }
        //Check to see if a new password has been created
        if (!$user->password) {

            //Authenticate using old credentials
            if (self::authenticate_legacy_password($username, $password, $hashed) === true) {
                //Assuming the password wasn't hashed, create new salt, re-encrypt, and save
                if ($hashed == false) {
                    $user->set_password($password);
                    $user->save();
                }
                //We're authenticated
                return true;
            } else {
                //NOPE!
                return false;
            }
            //Authenticate using new credentials
        } else {
            if (!$hashed) {
                $password = md5($password . $user->password_salt);
            }

            return $password == $user->password;
        }
    }


    /**
     * @param string $username
     * @param string $password
     * @param bool $hashed
     *
     * @return bool
     */
    public static function authenticate_legacy_password($username, $password, $hashed = false)
    {
        $user = self::getByUsername($username);

        // if this password has not yet been encrypted/hashed, then do so before comparing
        if (!$hashed) {
            $password = crypt($password, $user->legacy_password);
        }
        return $password == $user->legacy_password;
    }


    /**
     * @return mixed
     * @throws \Zend_Exception
     * @codeCoverageIgnore
     * @deprecated
     * @todo - refactor - violates SRP - User should not be responsible for accessing Session
     */
    public static function isSecure()
    {
        $session = \Zend_Registry::get('session');
        return $session->isSecure;
    }


    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id'                            => $this->getId(),
            'first_name'                    => $this->getFirstName(),
            'last_name'                     => $this->getLastName(),
            'password'                      => $this->getPassword(),
            'password_salt'                 => $this->getPasswordSalt(),
            'password_hint'                 => $this->getPasswordHint(),
            'username'                      => $this->getUsername(),
            'psg_user_id'                   => $this->getPsgUserId(),
            'email'                         => $this->getEmail(),
            'demo'                          => $this->isDemo(),
            'accepted_agreement'            => $this->hasAcceptedAgreement(),
            'address'                       => $this->getAddress(),
            'city'                          => $this->getCity(),
            'state'                         => $this->getState(),
            'country'                       => $this->getCountry(),
            'zip'                           => $this->getZip(),
            'home_phone'                    => $this->getHomePhone(),
            'work_phone'                    => $this->getWorkPhone(),
            'cell_phone'                    => $this->getCellPhone(),
            'birth_date'                    => $this->getBirthDate(),
            'gender'                        => $this->getGender(),
            'ethnicity'                     => $this->getEthnicity(),
            'contact_name'                  => $this->getContactName(),
            'contact_phone'                 => $this->getContactPhone(),
            'contact_relation'              => $this->getContactRelation(),
            'emt_grad'                      => $this->isEmtGrad(),
            'emt_grad_date'                 => $this->getEmtGradDate(),
            'emt_cert'                      => $this->isEmtCert(),
            'emt_cert_date'                 => $this->getEmtCertDate(),
            'license_number'                => $this->getLicenseNumber(),
            'license_expiration_date'       => $this->getLicenseExpirationDate(),
            'license_state'                 => $this->getLicenseState(),
            'state_license_number'          => $this->getStateLicenseNumber(),
            'state_license_expiration_date' => $this->getStateLicenseExpirationDate(),
        ];
    }


    /*
     * Support for Laravel Auth
     */

    /**
     * @inheritdoc
     */
    public function getAuthIdentifier()
    {
        return $this->getId();
    }


    /**
     * @inheritdoc
     */
    public function getAuthPassword()
    {
        return $this->getPassword();
    }


    /**
     * @inheritdoc
     */
    public function getRememberToken()
    {
        return '';
    }


    /**
     * @inheritdoc
     */
    public function setRememberToken($value)
    {
    }


    /**
     * @inheritdoc
     */
    public function getRememberTokenName()
    {
        return '';
    }


    /**
     * @return null|string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }


    /**
     * @param null|string $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }


    /**
     * Get the name of the unique identifier for the user.
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }
}
