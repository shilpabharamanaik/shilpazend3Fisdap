<?php namespace User\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Fisdap\EntityUtils;

/**
 * Legacy Entity class for Serial Numbers.
 *
 * In order for this to be effective, we need to run the script in
 * scripts\seedData\populateSerialNumberRoleIDs.php .  It migrates all
 * of the student and instructor user IDs into the newly created "user_id"
 * column.
 *
 * @Entity(repositoryClass="Fisdap\Data\SerialNumber\DoctrineSerialNumberLegacyRepository")
 * @Table(name="SerialNumbers")
 */
class SerialNumberLegacy extends EntityBaseClass
{
    const SERIAL_PATTERN = '/^([0-9]{2})[-]?([A-Za-z0-9]{13})[-]?([A-Za-z0-9]{4})$/';
    const ALT_SERIAL_PATTERN = '/^([0-9]{1})[-]?([A-Za-z0-9]{13})[-]?([A-Za-z0-9]{4})$/';
    const START_COUNTER_VAL = 4959;
    const MAX_COUNTER_VAL = 39000;

    public static $counter = self::START_COUNTER_VAL;

    /**
     * @var int
     * @Id
     * @Column(name="SN_id", type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var string
     * @Column(name="Number", type="string", length=26, unique=true)
     */
    protected $number;

    // See comment at the top of this document if users aren't being pulled up
    // correctly.
    /**
     * @var User
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="User_id", referencedColumnName="id")
     * @codeCoverageIgnore
     * @deprecated
     */
    protected $user;

    /**
     * @var UserContext
     * @ManyToOne(targetEntity="UserContext", inversedBy="serialNumbers")
     */
    protected $userContext;

    /**
     * @var int
     * @Column(name="Student_id", type="integer")
     * @codeCoverageIgnore
     * @deprecated
     */
    protected $student_id = -15;

    /**
     * @var int
     * @Column(name="Instructor_id", type="integer")
     * @codeCoverageIgnore
     * @deprecated
     */
    protected $instructor_id = -15;

    /**
     * @Column(name="DistMethod", type="string")
     */
    protected $dist_method = "unassigned";

    /**
     * @var ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy")
     * @JoinColumn(name="Program_id", referencedColumnName="Program_id")
     */
    protected $program;

    /**
     * @Column(name="EntryTime", type="datetime")
     */
    protected $created;

    /**
     * @Column(name="PurchaseOrder", type="string",  nullable=true)
     */
    protected $purchase_order;

    /**
     * @Column(name="AccountType", type="string")
     */
    protected $account_type = "paramedic";

    /**
     * @Column(name="Scheduler", type="boolean")
     */
    protected $scheduler_access = false;

    /**
     * @Column(name="PDAAccess", type="boolean")
     */
    protected $pda_access = false;

    /**
     * @Column(name="HasSyncedPDA", type="boolean")
     */
    protected $synced_pda_access = false;

    /**
     * @var int
     * @Column(name="Configuration", type="integer")
     */
    protected $configuration = 0;

    /**
     * @codeCoverageIgnore
     * @deprecated
     * @Column(name="ConfigLimit", type="integer")
     */
    protected $configuration_limit = 0;

    /**
     * @Column(name="OrderDate", type="datetime", nullable=true)
     */
    protected $order_date = null;

    /**
     * @var \DateTime|null
     * @Column(name="ActivationDate", type="datetime", nullable=true)
     */
    protected $activation_date = null;

    /**
     * @var CertificationLevel
     * @ManyToOne(targetEntity="CertificationLevel", fetch="EAGER")
     */
    protected $certification_level;

    /**
     * @var \DateTime
     * @Column(type="date", nullable=true)
     */
    protected $graduation_date;

    /**
     * @var ClassSectionLegacy
     * @ManyToOne(targetEntity="ClassSectionLegacy")
     * @JoinColumn(name="group_id", referencedColumnName="Sect_id")
     */
    protected $group;

    /**
     * @var OrderConfiguration
     * @ManyToOne(targetEntity="OrderConfiguration", inversedBy="serial_numbers")
     */
    protected $order_configuration;

    /**
     * @var Order
     * @ManyToOne(targetEntity="Order", inversedBy="serial_numbers")
     */
    protected $order;

    /**
     * @var \DateTime
     * @Column(type="datetime", nullable=true)
     */
    protected $distribution_date;

    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $distribution_email;

    /**
     * @var array
     */
    public static $configurationOptions = array(
        'tracking' => 1,
        'scheduler' => 2,
        'pda' => 4,
        'testing' => 8,
        'prep' => 16,
        'classroom' => 32,
        'preceptor_training' => 64,
        'emtb_study_tools' => 128,
        'emtb_comp_exams' => 256,
        'para_comp_exams' => 512,
        'emtb_unit_exams' => 1024,
        'para_unit_exams' => 2048,
        'limited_tracking' => 4096,
        'limited_scheduler' => 8192,
        'entrance_exam' => 262144,
        'aus_comp_exams' => 2097152,
        'aemt_comp_exams' => 4194304,
    );

    /**
     * @var array
     */
    public static $packages = array(
        'all_testing' => array('prep', 'emtb_study_tools', 'para_comp_exams', 'emtb_comp_exams', 'para_unit_exams', 'emtb_unit_exams', 'entrance_exam', 'aus_comp_exams', 'aemt_comp_exams'),
        'secure_testing' => array(
            'para_comp_exams',
            'emtb_comp_exams',
            'para_unit_exams',
            'emtb_unit_exams',
            'entrance_exam',
            'aus_comp_exams',
            'aemt_comp_exams'
        ),
        'study_tools' => array('prep', 'emtb_study_tools'),
    );


    public function __construct()
    {
        $this->order_date = new \DateTime();
        $this->created = new \DateTime();
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
    public function getNumber()
    {
        return $this->number;
    }


    /**
     * @param $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
    }


    /**
     * Set the user for this serial number
     * @param mixed $value integer | \Fisdap\Entity\User
     * @return \Fisdap\Entity\SerialNumberLegacy
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_user($value)
    {
        $this->user = self::id_or_entity_helper($value, 'User');
        return $this;
    }


    /**
     * @param User $user
     * @codeCoverageIgnore
     * @deprecated
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }


    /**
     * @return ProgramLegacy
     */
    public function getProgram()
    {
        return $this->program;
    }


    /**
     * @param ProgramLegacy $program
     */
    public function setProgram(ProgramLegacy $program)
    {
        $this->program = $program;
    }


    /**
     * @param UserContext $userContext
     */
    public function setUserContext(UserContext $userContext)
    {
        $this->userContext = $userContext;
    }


    /**
     * @return UserContext
     */
    public function getUserContext()
    {
        return $this->userContext;
    }


    /**
     * Set the student for this serial number. Hilariously, this just gets the ID off the student entity and puts it
     * into the integer property student_id. Because the legacy table has negative values, so proper entity association causes problems.
     * And we don't want to double-map DB columns because it causes problems under caching.
     * @param mixed $value integer | \Fisdap\Entity\StudentLegacy
     * @return \Fisdap\Entity\SerialNumberLegacy
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_student($value)
    {
        $student = self::id_or_entity_helper($value, 'StudentLegacy');
        $this->student_id = $student->id;
        return $this;
    }


    /**
     * @param StudentLegacy $student
     * @codeCoverageIgnore
     * @deprecated
     */
    public function setStudent(StudentLegacy $student)
    {
        $this->student_id = $student->getId();
    }


    /**
     * Set the instructor for this serial number. Hilariously, see note above for set_student()
     * @param mixed $value integer | \Fisdap\Entity\InstructorLegacy
     * @return \Fisdap\Entity\SerialNumberLegacy
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_instructor($value)
    {
        $instructor = self::id_or_entity_helper($value, 'InstructorLegacy');
        $this->instructor_id = $instructor->id;
        return $this;
    }


    /**
     * @param InstructorLegacy $instructor
     * @codeCoverageIgnore
     * @deprecated
     */
    public function setInstructor(InstructorLegacy $instructor)
    {
        $this->instructor_id = $instructor->getId();
    }


    /**
     * @return CertificationLevel
     */
    public function getCertificationLevel()
    {
        return $this->certification_level;
    }


    /**
     * @param CertificationLevel $certificationLevel
     */
    public function setCertificationLevel(CertificationLevel $certificationLevel)
    {
        $this->certification_level = $certificationLevel;

        //for legacy purposes, we also need to ensure that the account_type field is set properly
        $this->account_type = $this->certification_level->name ? $this->certification_level->name : "instructor";
    }


    /**
     * Set the certification for this serial number
     * Also set the @deprecated account_type field for legacy purposes
     * @param mixed $value integer | \Fisdap\Entity\CertificationLevel
     * @return \Fisdap\Entity\SerialNumberLegacy
     * @codeCoverageIgnore
     * @deprecated
     */
    public function set_certification_level($value)
    {
        $this->certification_level = self::id_or_entity_helper($value, "CertificationLevel");

        //Set this legacy field, if no certification is present, assume instructor
        $this->account_type = $this->certification_level->name ? $this->certification_level->name : "instructor";
        return $this;
    }


    /**
     * @return int
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }


    /**
     * @param int $configuration
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;
    }


    /**
     * @return \DateTime
     */
    public function getGraduationDate()
    {
        return $this->graduation_date;
    }


    /**
     * @param \DateTime|null $graduation_date
     */
    public function setGraduationDate(\DateTime $graduation_date = null)
    {
        $this->graduation_date = $graduation_date;
    }


    /**
     * @return ClassSectionLegacy
     */
    public function getGroup()
    {
        return $this->group;
    }


    /**
     * @param ClassSectionLegacy $group
     */
    public function setGroup(ClassSectionLegacy $group)
    {
        $this->group = $group;
    }


    /**
     * @return \DateTime|null
     */
    public function getActivationDate()
    {
        return $this->activation_date;
    }


    /**
     * @param \DateTime|null $dateTime
     */
    public function setActivationDate(\DateTime $dateTime = null)
    {
        $this->activation_date = $dateTime ?: new \DateTime;
    }

    /**
     * This function tests a serial number to see if it has access to the given
     * product.
     *
     * @param mixed $productName string | integer the name or ID of the product
     *
     * @return Boolean representing whether the serial number grants access
     * to the product.
     * @codeCoverageIgnore
     * @deprecated use ProductsFinder
     * @todo refactor
     */
    public function hasProductAccess($productName)
    {
        // Lou wants to always show all tabs for instructors for the time being
        if (array_key_exists($productName, self::$configurationOptions)) {
            return (boolean)($this->configuration & self::$configurationOptions[$productName]);
        } elseif (array_key_exists($productName, self::$packages)) {
            foreach (self::$packages[$productName] as $singleProduct) {
                if ($this->configuration & self::$configurationOptions[$singleProduct]) {
                    return true;
                }
            }
        }

        if (is_numeric($productName)) {
            $product = EntityUtils::getEntity("Product", $productName);
            return (boolean)($product->configuration & $this->configuration);
        }

        $product = EntityUtils::getRepository("Product")->findOneByName($productName);
        return (boolean)($product->configuration & $this->configuration);
    }

    /**
     * Does this serial have some version of Skills Tracker
     *
     * @return boolean
     * @codeCoverageIgnore
     * @deprecated
     */
    public function hasSkillsTracker()
    {
        return $this->hasProductAccess(1) || $this->hasProductAccess(10);
    }

    /**
     * Does this serial have some version of scheduler
     *
     * @return boolean
     * @codeCoverageIgnore
     * @deprecated
     */
    public function hasScheduler()
    {
        return $this->hasProductAccess(2) || $this->hasProductAccess(11);
    }

    /**
     * Does this serial have some version of the Transition Course
     *
     * @return boolean
     * @codeCoverageIgnore
     * @deprecated
     */
    public function hasTransitionCourse()
    {
        return $this->hasProductAccess(13) ||
        $this->hasProductAccess(14) ||
        $this->hasProductAccess(15);
    }

    /**
     * Does this serial have some version of Study Tools
     *
     * @return boolean
     * @codeCoverageIgnore
     * @deprecated
     */
    public function hasStudyTools()
    {
        return $this->hasProductAccess(7) ||
        $this->hasProductAccess(8);
    }

    /**
     * Does this serial have some version of Medrills videos
     *
     * @return boolean
     * @codeCoverageIgnore
     * @deprecated
     */
    public function hasMedrills()
    {
        return $this->hasProductAccess(41) || $this->hasProductAccess(42);
    }

    /**
     * Does this serial number have multiple products
     * @return boolean
     * @codeCoverageIgnore
     * @deprecated
     */
    public function hasMultipleProducts()
    {
        $products = Product::getProductArray($this->configuration, false, $this->program->profession->id);
        return count($products) > 1;
    }

    public function getProductSummary()
    {
        return Product::getProductSummary($this->configuration, $this->program->profession->id);
    }

    /**
     * Has this serial number been activated
     * @return boolean
     */
    public function isActive()
    {
        return ($this->user->id > 0);
    }


    /**
     * Generate a pseudo random string of characters to make the serial number
     *
     * @return SerialNumberLegacy
     * @throws \Exception
     * @codeCoverageIgnore
     * @deprecated - use generateUniqueNumber() instead
     */
    public function generateNumber()
    {
        if ($this->number) {
            throw new \Exception("This serial number has already been generated.");
        }

        switch ($this->certification_level->name) {
            case "paramedic":
                $certId = 11;
                break;

            case "emt-b":
                $certId = 21;
                break;

            case "emt-i":
            case "aemt":
                $certId = 31;
                break;

            case "":
                $certId = 91;
                break;

            default:
                $certId = '01';
                break;
        }

        $numberAttempts = 1000;

        while ($numberAttempts > 0) {
            $firstChunk = rand(4096, 39000) * 4096 + date('z');
            $secondChunk = rand(4096, 60535) * 256 + date('Y');

            $number = $certId . "-" . dechex($firstChunk) . dechex($secondChunk) . "-" . dechex(self::$counter);

            if (!self::getBySerialNumber($number)) {
                $this->number = $number;
                self::$counter++;

                //make sure we haven't exceeded our max counter
                if (self::$counter > self::MAX_COUNTER_VAL) {
                    self::resetCounter();
                }

                return $this->number;
            }
            $numberAttempts--;
        }

        //If we run out of attempts, throw an error
        throw new \Exception("A unique serial number could not be generated. We have too many.");
    }


    /**
     * Set the serial number to a unique ID
     */
    public function generateUniqueNumber()
    {
        $this->number = str_replace('.', '-', uniqid('01-', true));
    }


    /**
     * @codeCoverageIgnore
     * @deprecated
     */
    public static function resetCounter()
    {
        self::$counter = self::START_COUNTER_VAL;
    }

    public static function configurationHasProductAccess($configuration, $productName)
    {
        if (array_key_exists($productName, self::$configurationOptions)) {
            return (boolean)($configuration & self::$configurationOptions[$productName]);
        } else {
            return false;
        }
    }

    /**
     * Get a serial number entity with the number
     *
     * @param string $number
     * @return \Fisdap\Entity\SerialNumberLegacy|null
     * @codeCoverageIgnore
     * @deprecated
     */
    public static function getBySerialNumber($number)
    {
        if (!$number) {
            return null;
        }

        $repo = EntityUtils::getRepository("SerialNumberLegacy");
        $serial = $repo->findOneByNumber($number);

        if (!$serial->id) {
            return null;
        }
        return $serial;
    }

    public static function validateSerial($number)
    {
        $serial = self::getBySerialNumber($number);
        $isValid = true;
        $messages = array();

        if (!$serial->id) {
            $messages[] = "$number is not a valid serial number";
            $isValid = false;
        }

        if ($serial->isActive()) {
            $messages[] = "$number has already been used to activate another account";
            $isValid = false;
        }

        if ($isValid === true) {
            return $isValid;
        }

        return $messages;
    }

    /**
     * Just a check to see if the serial number is formatted correctly
     *
     * @param string $number
     * @param array $matches
     * @return boolean
     */
    public static function isSerialFormat($number, &$matches = array())
    {
        $serial = preg_match(self::SERIAL_PATTERN, $number, $matches);
        $altSerial = preg_match(self::ALT_SERIAL_PATTERN, $number, $matches);

        if ($serial) {
            return preg_match(self::SERIAL_PATTERN, $number, $matches);
        }

        if ($altSerial) {
            return preg_match(self::ALT_SERIAL_PATTERN, $number, $matches);
        }

        return preg_match(self::SERIAL_PATTERN, $number, $matches);
    }


    /**
     * Return the products associated with a this serial number
     * @return array
     */
    public function getProducts()
    {
        $products = EntityUtils::getRepository("Product")->getProducts($this->configuration, true, false, false, true, $this->program->profession->id);
        return $products;
    }

    public function getByStudent($studentId)
    {
        $sn = EntityUtils::getRepository("SerialNumberLegacy")->getByStudent($studentId);
        return $sn;
    }


    /**
     * Return the details for an account associated with a this serial number
     * @return array
     */
    public function getAccountDetails()
    {
        $config = EntityUtils::getEntity("OrderConfiguration", $this->order_configuration->id);

        // we have to do this the silly way, because we need to know about study tools products
        $products = $this->getProducts();
        $productDetails = array();
        $numberOfProducts = count($products);
        $studyTools = 0;
        foreach ($products as $product) {
            // if this is a student account, ignore the transition course
            if ($config->certification_level->id && $product->id == 9) {
                continue;
            }
            $productDetails[] = array(
                "name" => $product->name,
                "description" => $product->description);
            // check first to see if it's study tools
            if ($product->category->id == 3) {
                $studyTools++;
            }
        }

        // if there are only study tools
        $onlyStudyTools = ($studyTools == $numberOfProducts) ? true : false;

        // get all values we're interested in
        $accountDetails = array(
            "cert" => $config->certification_level->description,
            "certId" => $config->certification_level->id,
            "programName" => $config->order->program->name,
            "programId" => $config->order->program->id,
            "configuration" => $this->configuration,
            "products" => $productDetails,
            "cost" => $config->individual_cost,
            "code" => $this->number,
            "studyToolsOnly" => $onlyStudyTools
        );

        return $accountDetails;
    }

    /**
     * Is this serial number for an instructor?
     * If it has only preceptor training, it must be an instructor
     * @return boolean
     */
    public function isInstructorAccount()
    {
        return $this->configuration == 64;
    }

    /**
     * Does this serial number contain limited/unlimited products?
     * If it has Skill Tracker or Scheduler (limited/unlimited based on param) return true
     * @param boolean - if true we're searching for limited products, if false we're searching for unlimited products
     * @return boolean
     */
    public function hasProductLimits($limited = true)
    {
        $found = false;

        // set to configuration
        if ($limited) {
            // limited product IDs
            $skillsTracker = 4096;
            $scheduler = 8192;
        } else {
            // unlimited product IDs
            $skillsTracker = 1;
            $scheduler = 2;
        }

        // see if the configuration includes either product
        if ($this->configuration & $skillsTracker || $this->configuration & $scheduler) {
            $found = true;
        }

        return $found;
    }

    /**
     * Does this serial number contain a particular product?
     * @param the configuration of the product we're searching for
     * @return boolean
     */
    public function hasProduct($productConfig)
    {
        foreach ($this->getProducts() as $product) {
            if ($product->configuration == $productConfig) {
                return true;
            }
        }

        return false;
    }


    /**
     * Apply any additional features:
     * 1). Apply any product limits
     * 2). Enroll the user in various Moodle courses
     * 3). Apply auto-assigned scheduler requirements if not already applied
     * @codeCoverageIgnore
     * @deprecated
     */
    public function applyExtras()
    {
        // 1). Apply product limits
        // are there limited products?
        if ($this->hasProductLimits(true)) {
            // if there aren't any unlimited products (in a case where a student may have both)
            if (!$this->hasProductLimits(false)) {
                $this->userContext->getRoleData()->field_shift_limit = 10;
                $this->userContext->getRoleData()->clinical_shift_limit = 10;
            }
        }

        // 2). Enroll the user in various Moodle courses
        $products = EntityUtils::getRepository("Product")->getProductsWithMoodleCourses();

        foreach ($products as $product) {
            if ($this->hasProduct($product->configuration)) {
                //Deal with the transition course separately
                if ($product->category->id == 4) {

                    //Get the moodle API for this moodle context
                    $moodleAPI = new \Util_MoodleAPI($product->moodle_context);

                    //Look for moodle override
                    $moodleOverride = EntityUtils::getRepository("MoodleCourseOverride")->findOneBy(array("product" => $product->id, "program" => $this->program->id));

                    if ($moodleOverride->id) {
                        $courseId = $moodleOverride->moodle_course_id;
                    } else {
                        $courseId = $product->moodle_course_id;
                    }

                    //Enroll the user in the correct course
                    $result = $moodleAPI->enrollCourse($this->user, $courseId);

                //Get possible moodle groups to add this user to
                    //$moodleGroups = \Fisdap\EntityUtils::getRepository("MoodleGroup")->findBy(array("product" => $product->id, "program" => $this->program->id));

                    //foreach ($moodleGroups as $group) {
                    //	$result = $moodleAPI->addGroupMember($this->user, $group->moodle_group_id);
                    //}
                } else {
                    // add to db
                    $addToDb = false;
                    if ($product->id == 9) {
                        // we have preceptor training, make sure the account is an instructor before we add to the db
                        $addToDb = ($this->user->getCurrentRoleName() == "instructor") ? true : false;
                    } else {
                        $addToDb = true;
                    }

                    if ($addToDb) {
                        EntityUtils::getRepository("User")->enrollInMoodleCourse($product, $this->user->username);
                    }
                }
            }
        }

        //3). Apply auto-assigned scheduler requirements if not already applied

        if ($this->hasScheduler()) {
            $need_auto = true;

            $requirements = EntityUtils::getRepository("RequirementAutoAttachment")->findBy([
                "role"                => 1,
                "program"             => $this->program,
                "certification_level" => $this->certification_level
            ]);

            foreach ($requirements as $auto_requirement) {
                if ($this->user->getCurrentUserContext()->hasRequirement($auto_requirement->requirement->id)) {
                    $need_auto = false;
                }
            }

            if ($need_auto) {
                $this->user->getCurrentUserContext()->autoAttachRequirements();
            }
        }
    }

    /**
     * Create a demo serial number for a new school
     * @param \Fisdap\Entity\ProgramLegacy $program
     * @return \Fisdap\Entity\SerialNumberLegacy
     * @codeCoverageIgnore
     * @deprecated
     */
    public static function getDemoSerial(ProgramLegacy $program)
    {
        //Create new serial number
        $serial = EntityUtils::getEntity("SerialNumberLegacy");
        $serial->program = $program;
        $serial->graduation_date = new \DateTime("+4 years");

        //If we're dealing with EMS, default to paramedic, otherwise get the first cert level
        if ($program->profession->id == 1) {
            $serial->set_certification_level(3);
        } else {
            $serial->certification_level = $program->profession->certifications->first();
        }

        //Get all products and assign any applicable products to this account
        $products = EntityUtils::getRepository("Product")->getProducts($serial->certification_level->configuration_blacklist, false, false, true, false, $program->profession->id);

        foreach ($products as $product) {
            $serial->configuration += $product->configuration;
        }

        $serial->generateNumber();

        return $serial;
    }

    
    /**
     *
     * @param SerialNumberLegacy $mergeNumber
     * @return bool
     */
    public function mergeSerialNumbers(SerialNumberLegacy $mergeNumber)
    {
        // make sure these serial numbers are compatible
        if ($this->program != $mergeNumber->program) {
            return false;
        }

        if ($this->account_type != $mergeNumber->account_type) {
            return false;
        }

        if ($this->certification_level != $mergeNumber->certification_level) {
            return false;
        }


        // figure out the new product configuration
        $newConfig = $this->configuration | $mergeNumber->configuration;

        // update this serial number with the new configuration
        $this->configuration = $newConfig;
        $this->save();

        // update the merged serial number with all the new info
        $mergeNumber->activation_date = new \DateTime();
        $mergeNumber->setUserContext($this->getUserContext());
        $mergeNumber->setUser($this->getUserContext()->getUser());
        $mergeNumber->setStudent($this->getUserContext()->getRoleData());
        $mergeNumber->save();

        // enroll the user in any new moodle courses
        $this->applyExtras();

        return true;
    }


    public function toArray()
    {
        return [
            'uuid' => $this->getUUID(),
            'id' => $this->getId(),
            'number' => $this->getNumber(),
            'dist_method' => $this->dist_method,
            'account_type' => $this->account_type,
            'configuration' => $this->configuration,
        ];
    }
}
