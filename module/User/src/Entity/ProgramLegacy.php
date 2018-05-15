<?php namespace User\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\Table;
use Fisdap\Api\Programs\Entities\Traits\Accreditation;
use Fisdap\Api\Programs\Entities\Traits\Address;
use Fisdap\Api\Programs\Entities\Traits\Bases;
use Fisdap\Api\Programs\Entities\Traits\Billing;
use Fisdap\Api\Programs\Entities\Traits\Commerce;
use Fisdap\Api\Programs\Entities\Traits\Contact;
use Fisdap\Api\Programs\Entities\Traits\DeprecatedProgramProperties;
use Fisdap\Api\Programs\Entities\Traits\EmailNotifications;
use Fisdap\Api\Programs\Entities\Traits\Phones;
use Fisdap\Api\Programs\Entities\Traits\Preceptors;
use Fisdap\Api\Programs\Entities\Traits\Requirements;
use Fisdap\Api\Programs\Entities\Traits\Skills;
use Fisdap\Api\Programs\Entities\Traits\Referral;
use Fisdap\Api\Programs\Entities\Traits\Reports;
use Fisdap\Api\Programs\Entities\Traits\ShiftDeadlines;
use Fisdap\Api\Programs\Entities\Traits\Sites;
use Fisdap\Api\Programs\Entities\Traits\StudentPermissions;
use Fisdap\Api\Programs\Entities\Traits\Types;
use Fisdap\EntityUtils;

/**
 * Legacy Entity class for Programs.
 *
 * @Entity(repositoryClass="Fisdap\Data\Program\DoctrineProgramLegacyRepository")
 * @Table(name="ProgramData")
 * @HasLifecycleCallbacks
 */
class ProgramLegacy extends EntityBaseClass
{
    use Address, Phones, Contact, Billing, Referral, Types, Accreditation;
    use StudentPermissions, EmailNotifications, ShiftDeadlines, Commerce, Reports;
    use Sites, Bases, Preceptors, Skills, Requirements;
    use DeprecatedProgramProperties;
    
    
    /**
     * @var int
     * @Id
     * @Column(name="Program_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(name="ProgramName", type="string")
     */
    protected $name;

    /**
     * @var string
     * @Column(name="ProgramAbrv", type="string")
     */
    protected $abbreviation;

    /**
     * @var string
     * @Column(name="ClassSize", type="string", nullable=true)
     */
    protected $class_size;
    
    /**
     * @var string
     * @Column(name="ProgramWebsite", type="string", nullable=true)
     */
    protected $website = "Unspecified";

    /**
     * @var bool
     * @Column(name="Active", type="boolean")
     */
    protected $active = true;

    /**
     * @var \DateTime
     * @Column(name="DateCreated", type="datetime")
     */
    protected $created;

    /**
     * @var Profession
     * @ManyToOne(targetEntity="Profession")
     */
    protected $profession;

    /**
     * @var ProgramSettings
     * @OneToOne(targetEntity="ProgramSettings", mappedBy="program", cascade={"persist","remove","detach"},
     *     fetch="EAGER")
     */
    protected $program_settings;


    public function __construct()
    {
        $this->program_site_associations = new ArrayCollection;
        $this->program_base_associations = new ArrayCollection;
        $this->site_staff_members = new ArrayCollection;

        $this->airway_procedures = new ArrayCollection;
        $this->cardiac_procedures = new ArrayCollection;
        $this->iv_procedures = new ArrayCollection;
        $this->med_types = new ArrayCollection;
        $this->other_procedures = new ArrayCollection;
        $this->lab_assessments = new ArrayCollection;

        $this->goalsets = new ArrayCollection;

        $this->practice_definitions = new ArrayCollection;
        $this->practice_categories = new ArrayCollection;

        $this->program_types = new ArrayCollection;

        $this->program_settings = new ProgramSettings;
        $this->program_settings->program = $this;

        $this->site_shares = new ArrayCollection;

        $this->requirement_notifications = new ArrayCollection;

        //Set default profession to EMS
        $this->set_profession(1);

        $this->reports = new ArrayCollection;
    }


    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @return mixed
     */
    public function getAbbreviation()
    {
        return $this->abbreviation;
    }


    /**
     * @param mixed $abbreviation
     */
    public function setAbbreviation($abbreviation)
    {
        $this->abbreviation = $abbreviation;
    }

    
    public function generateAbbreviation()
    {
        $words = str_word_count($this->name, 1);

        if (count($words) > 1) {
            $firstLetters = array_map(function ($word) {
                return substr(ucfirst($word), 0, 1);
            }, $words);

            $abbreviation = implode('', $firstLetters);
        } else {
            $abbreviation = strtoupper(substr($this->name, 0, 3));
        }

        $this->abbreviation = $abbreviation;
    }

    /**
     * @return mixed
     */
    public function getClassSize()
    {
        return $this->class_size;
    }


    /**
     * @param mixed $class_size
     */
    public function setClassSize($class_size)
    {
        $this->class_size = $class_size;
    }


    /**
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->website;
    }


    /**
     * @param mixed $website
     */
    public function setWebsite($website)
    {
        $this->website = $website;
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
     * Set the profession for this program
     * @param mixed $value integer | \Fisdap\Entity\Profession
     * @return \Fisdap\Entity\ProgramLegacy
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_profession($value)
    {
        $this->profession = self::id_or_entity_helper($value, "Profession");
        return $this;
    }


    /**
     * @return Profession
     */
    public function getProfession()
    {
        return $this->profession;
    }
    

    /**
     * @param Profession $profession
     */
    public function setProfession(Profession $profession)
    {
        $this->profession = $profession;
    }


    /**
     * @PrePersist
     */
    public function setCreated()
    {
        $this->created = new \DateTime;
    }


    /**
     * @return ProgramSettings
     */
    public function getProgramSettings()
    {
        return $this->program_settings;
    }
    
    /**
     * @return ProgramLegacy|false
     * @codeCoverageIgnore
     * @deprecated
     * @todo - refactor - current program should be retrieved from current context in Session
     */
    public static function getCurrentProgram()
    {
        if ($user = \Fisdap\Entity\User::getLoggedInUser()) {
            return EntityUtils::getEntity('ProgramLegacy', $user->getProgramId());
        } else {
            return false;
        }
    }


    public function get_possible_graduation_years($null_value = true)
    {
        $years = array();

        $query = "SELECT distinct(YEAR(end_date)) AS graduation_year FROM fisdap2_user_roles WHERE program_id = " . $this->id . " AND role_id = 1 ORDER BY graduation_year";
        $db = \Zend_Registry::get('db');
        $result = $db->fetchAll($query);

        if ($null_value) {
            $years["0"] = "Year";
        }

        foreach ($result as $year) {
            $graduation_year = $year['graduation_year'];
            if (is_numeric($graduation_year)) {
                $years[$graduation_year] = $graduation_year;
            }
        }

        return $years;
    }

    
    public function get_possible_graduations_years_from_sn($null_value = true)
    {
        $years = array();

        $query = "SELECT distinct(YEAR(graduation_date)) AS graduation_year FROM SerialNumbers WHERE Program_id = " . $this->id;
        $db = \Zend_Registry::get('db');
        $result = $db->fetchAll($query);

        if ($null_value) {
            $years["0"] = "Year";
        }

        foreach ($result as $year) {
            $graduation_year = $year['graduation_year'];
            if (is_numeric($graduation_year)) {
                $years[$graduation_year] = $graduation_year;
            }
        }

        return $years;
    }
    
    
    /**
     * Get an array of what shift types the current user is allowed to create
     * What students see is based off of program settings
     * What instructors see is based off of their permissions
     *
     * @param \Fisdap\Entity\User
     * @return array containg shift types
     */
    public static function getStudentAllowedShiftTypes($user = null)
    {
        $types = array();

        if (is_null($user)) {
            $user = \Fisdap\Entity\User::getLoggedInUser();
        }

        if ($user->getCurrentRoleName() == "student") {
            $program = $user->getCurrentRoleData()->program;

            if ($program->get_can_students_create_field()) {
                $types[] = 'field';
            }

            if ($program->get_can_students_create_clinical()) {
                $types[] = 'clinical';
            }

            if ($program->get_can_students_create_lab()) {
                $types[] = 'lab';
            }
        } elseif ($user->getCurrentRoleName() == "instructor") {
            $instructor = $user->getCurrentRoleData();

            if ($instructor->hasPermission("Edit Field Schedules")) {
                $types[] = 'field';
            }

            if ($instructor->hasPermission("Edit Clinic Schedules")) {
                $types[] = 'clinical';
            }

            if ($instructor->hasPermission("Edit Lab Schedules")) {
                $types[] = 'lab';
            }
        }

        return $types;
    }
    

    /**
     * Create a demo account for a program and email the instructor that ordered it
     *
     * @param \Fisdap\Entity\User $instructor
     * @return integer the ID of the demo account
     * @codeCoverageIgnore
     * @deprecated
     */
    public function createDemoAccount($instructor)
    {
        // Create new user entity
        $user = new User();
        $user->first_name = $this->abbreviation;
        $user->last_name = "Student";

        $user->email = $instructor->email;

        $user->username = $this->product_code_id;
        $user->password = "12345";
        $user->demo = true;

        //Transition Course stuff
        $user->license_number = "12345";
        $user->license_expiration_date = new \DateTime("+1 year");
        $user->state_license_number = "12345";
        $user->license_state = $this->state;
        $user->state_license_expiration_date = new \DateTime("+1 year");

        $serial = SerialNumberLegacy::getDemoSerial($this);
        $serial->save(false);

        // Create student entity to attach to user
        $student = new StudentLegacy();
        $user->addUserContext("student", $student, $serial->program->id, $serial->certification_level->id, null, $serial->graduation_date);

        $user->save();

        // Activate the serial number for this student, this needs to be called after the student is created
        $student->activateSerialNumber($serial);
        $student->save();

        // Email the login information
        $mail = new \Fisdap_TemplateMailer();
        $mail->addTo($user->email)
            ->setSubject("A new demo Fisdap account has been created for you")
            ->setViewParam('serial', $serial)
            ->setViewParam('orderer', $instructor)
            ->setViewParam('urlRoot', \Util_HandyServerUtils::getCurrentServerRoot())
            ->setViewParam('user', $user)
            ->setViewParam('password', "12345")
            ->sendHtmlTemplate('new-account-invitation.phtml');

        return $user->id;
    }


    /**
     * @return array
     */
    public static function getFormOptions()
    {
        $returnArray = array();
        $programOptions = EntityUtils::getRepository("ProgramLegacy")->getAllPrograms();
        foreach ($programOptions as $id => $option) {
            $returnArray[$id] = $option['name'];
        }
        return $returnArray;
    }

    public function toArray()
    {
        return [
            "id"                            => $this->getId(),
            "name"                          => $this->getName(),
            "abbreviation"                  => $this->getAbbreviation(),
            "class_size"                    => $this->getClassSize(),
            "website"                       => $this->getWebsite(),
            "active"                        => $this->getActive(),
            "created"                       => $this->created,
            "address"                       => $this->getAddress(),
            "address2"                      => $this->getAddress2(),
            "address3"                      => $this->getAddress3(),
            "city"                          => $this->getCity(),
            "state"                         => $this->getState(),
            "zip"                           => $this->getZip(),
            "country"                       => $this->getCountry(),
            "phone"                         => $this->getPhone(),
            "fax"                           => $this->getFax(),
            "program_contact"               => ($this->getProgramContact() ? $this->getProgramContactName() : null),
            "contact_email"                 => ($this->getProgramContact() ? $this->getProgramContact()->email : null),
            "billing_email"                 => $this->getBillingEmail(),
            "billing_contact"               => $this->getBillingContact(),
            "billing_address"               => $this->getBillingAddress(),
            "billing_address2"              => $this->getBillingAddress2(),
            "billing_address3"              => $this->getBillingAddress3(),
            "billing_city"                  => $this->getBillingCity(),
            "billing_state"                 => $this->getBillingState(),
            "billing_zip"                   => $this->getBillingZip(),
            "billing_country"               => $this->getBillingCountry(),
            "billing_phone"                 => $this->getBillingPhone(),
            "billing_fax"                   => $this->getBillingFax(),
            "referral"                      => $this->getReferral(),
            "ref_description"               => $this->getRefDescription(),
            "accredited"                    => $this->getAccredited(),
            "coaemsp_program_id"            => $this->getCoaemspProgramId(),
            "year_accredited"               => $this->getYearAccredited(),
            "can_students_create_field"     => $this->get_can_students_create_field(),
            "can_students_create_clinical"  => $this->get_can_students_create_clinical(),
            "can_students_create_lab"       => $this->get_can_students_create_lab(),
            "student_view_full_calendar"    => $this->getStudentViewFullCalendar(),
            "can_students_pick_field"       => $this->getCanStudentsPickField(),
            "can_students_pick_clinical"    => $this->getCanStudentsPickClinical(),
            "can_students_pick_lab"         => $this->getCanStudentsPickLab(),
            "allow_absent_with_permission"  => $this->getAllowAbsentWithPermission(),
            "include_narrative"             => $this->include_narrative,
            "send_late_shift_emails"        => $this->getSendLateShiftEmails(),
            "send_critical_thinking_emails" => $this->getSendCriticalThinkingEmails(),
            "late_field_deadline"           => $this->getLateFieldDeadline(),
            "late_clinical_deadline"        => $this->getLateClinicalDeadline(),
            "late_lab_deadline"             => $this->getLateLabDeadline(),
            "customer_id"                   => $this->customer_id,
            "customer_name"                 => $this->getCustomerName(),
            "requires_po"                   => $this->getRequiresPo(),
            "product_code_id"               => $this->getProductCodeId(),
            "program_settings"              => $this->getProgramSettings()->toArray(),
        ];
    }
}
