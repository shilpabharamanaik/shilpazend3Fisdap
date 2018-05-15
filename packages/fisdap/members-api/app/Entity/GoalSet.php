<?php namespace Fisdap\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\PostLoad;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;
use Doctrine\ORM\Mapping\Table;
use Fisdap\Data\Age;
use Fisdap\EntityUtils;

/**
 * Entity class for Goal requirements
 *
 * @Entity(repositoryClass="Fisdap\Data\Goal\DoctrineGoalRepository")
 * @Table(name="fisdap2_goal_sets")
 * @HasLifecycleCallbacks
 *
 * Age handling:
 *
 *  IMPORTANT: DON'T USE GETTERS SETTERS FOR AGE RANGES DIRECTLY, use $this->ages
 *
 * 	This entity loads age values and sets them automatically.
 * 		(Warning: It doesn't set entity values in real time. Only on load/save)
 *
 * 	@todo implement listener to get notified about changes to age range fields
 * 		(currently there needs to be other field saved to fire update)
 */
class GoalSet extends GoalBase
{
    const OTHER=-1;
    const PEDIATRIC=0;
    const NEWBORN=1;
    const INFANT=2;
    const TODDLER=3;
    const PRESCHOOLER=4;
    const SCHOOL_AGE=5;
    const ADOLESCENT=6;
    const ADULT=7;
    const GERIATRIC=8;
    
    protected static $ageFields = array(
        //self::PEDIATRIC => 'pediatric',
        self::NEWBORN => 'newborn',
        self::INFANT => 'infant',
        self::TODDLER => 'toddler',
        self::PRESCHOOLER => 'preschooler',
        self::SCHOOL_AGE => 'school_age',
        self::ADOLESCENT => 'adolescent',
        self::ADULT => 'adult',
        self::GERIATRIC => 'geriatric',
    );
    
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;
    
    /**
     * @OneToMany(targetEntity="Goal", mappedBy="goalSet", cascade={"persist","remove"})
     */
    protected $goals;

    /**
     * @var \Fisdap\Entity\ProgramLegacy
     * @ManyToOne(targetEntity="ProgramLegacy", inversedBy="goalSets")
     * @JoinColumn(name="program_id", referencedColumnName="Program_id")
     */
    protected $program;
    
    /**
     * @Column(type="string", length=60)
     */
    protected $name='';
    
    /**
     * @Column(type="string", length=20)
     */
    protected $account_type='';

    /**
     * @Column(type="string", length=255)
     * Params for goals in goalDef
     */
    protected $params='';

    protected $newborn_start_age = 0;
    
    /**
     * @Column(type="integer")
     */
    protected $infant_start_age=1;

    /**
     * @Column(type="integer")
     */
    protected $toddler_start_age=1;

    /**
     * @Column(type="integer")
     */
    protected $preschooler_start_age=4;

    /**
     * @Column(type="integer")
     */
    protected $school_age_start_age=6;
    
    /**
     * @Column(type="integer")
     */
    protected $adolescent_start_age=13;
    
    /**
     * @Column(type="integer")
     */
    protected $adult_start_age=18;
    
    /**
     * @Column(type="integer")
     */
    protected $geriatric_start_age=65;
    
    /**
     * @Column(type="boolean")
     */
    protected $soft_deleted = false;
    
    /**
     * @Column(type="boolean", nullable=true)
     */
    protected $default_goalset = false;
    
    /**
     * @ManyToOne(targetEntity="GoalSet")
     */
    protected $goalset_template;
    
    protected $agelimit_start_age=256;
    
    protected $ages;
    
    
    public static function getForStudent($student)
    {
        // currently user's goal set is that of his/her program
        return self::getByProgram($student->program);
    }

    public function getAirwayManagementDefinition()
    {
        $am_def = false;


        if ($this->goals) {
            foreach ($this->goals as $goal) {
                if ($goal->def->id == 92) {
                    $am_def = $goal->def;
                }
            }
        }

        return $am_def;
    }

    public function getAirwayManagementNumberRequired()
    {
        $num_required = false;

        if ($this->goals) {
            foreach ($this->goals as $goal) {
                if ($goal->def->id == 92) {
                    $num_required = $goal->number_required;
                }
            }
        }

        return $num_required;
    }

    public static function getForUser($user)
    {
        $user = User::getUser($user);
        
        $role = $user->getCurrentRoleName();
        
        if ($role != 'student') {
            return null;
        }
        
        $student = $user->getCurrentRoleData();
        
        // currently user's goal set is that of his/her program
        return self::getByProgram($student->program);
    }
    
    public static function getByProgram($program)
    {
        $repo = EntityUtils::getRepository('Goal'); //->_em->getRepository();
        $goalSet = $repo->getGoalSetsByProgram($program);
        return $goalSet;
    }
    
    public function init() // __construct()
    {
        $this->goals = new ArrayCollection();
        
        // age setup, this puts default age ranges FROM AGE CLASS (NOT THIS ENTITY)
        $this->ages = new Age();
    }

    /**
     * Set this goal set as the default for this program and certification
     *
     * @param boolean $bool default or not?
     * @return \Fisdap\Entity\GoalSet
     */
    public function set_default_goalset($bool)
    {
        if ($bool) {
            $this->clearDefaultGoalSets();
        }
        
        $this->default_goalset = $bool;
        return $this;
    }

    /**
     * Loop through every goalset for this program and clear any that match
     * this goalset's certification.
     *
     * @return void
     */
    private function clearDefaultGoalSets()
    {
        foreach ($this->program->goalsets as $goalset) {
            if ($this->account_type == $goalset->account_type) {
                $goalset->default_goalset = false;
            }
        }
    }
    
    public function addGoal(Goal $goal)
    {
        $goal->goalSet = $this;
        
        $this->goals->add($goal);
    }
    
    public function set_infant_start_age($value)
    {
        $this->infant_start_age = $value;
        $this->ages->setStartAge('infant', $value);
    }
    
    public function set_toddler_start_age($value)
    {
        $this->toddler_start_age = $value;
        $this->ages->setStartAge('toddler', $value);
    }
    
    public function set_preschooler_start_age($value)
    {
        $this->preschooler_start_age = $value;
        $this->ages->setStartAge('preschooler', $value);
    }
    
    public function set_school_age_start_age($value)
    {
        $this->school_age_start_age = $value;
        $this->ages->setStartAge('school_age', $value);
    }
    
    public function set_adolescent_start_age($value)
    {
        $this->adolescent_start_age = $value;
        $this->ages->setStartAge('adolescent', $value);
    }
    
    public function set_adult_start_age($value)
    {
        $this->adult_start_age = $value;
        $this->ages->setStartAge('adult', $value);
    }
    
    public function set_geriatric_start_age($value)
    {
        $this->geriatric_start_age = $value;
        $this->ages->setStartAge('geriatric', $value);
    }
    
    public function set_goalset_template($value)
    {
        $this->goalset_template = self::id_or_entity_helper($value, "GoalSet");
        return $this;
    }

    /**
     * @PreUpdate
     * @PrePersist
     *
     * Saves Age range values from Age object to Entity fields
     */
    public function saveAgeRangeValues()
    {
        // ages: set from age object
        $ageValues = $this->ages->getAllStartAges();
        
        foreach (self::$ageFields as $id => $groupAgeName) {
            if (isset($ageValues[$groupAgeName])) {
                $ageVar = $groupAgeName . '_start_age';
                $this->$ageVar = $ageValues[$groupAgeName];
            } else {
                throw new \InvalidArgumentException('Can\'t save age: ' . $groupAgeName);
            }
        }
    }
    
    /**
     * @PostLoad
     */
    public function onDataLoad()
    {
        $startAges = array();

        foreach (self::$ageFields as $id => $groupAgeName) {
            $ageVar = $groupAgeName . '_start_age';
            $age = $this->$ageVar;
            $startAges[$groupAgeName] = $age;
        }

        // ages: initialize ages object: save current values as settings
        $this->ages = new Age($startAges);
    }
    
    public static function getAllAgeFieldNames()
    {
        return array_map(
            function ($ageRange) {
                return $ageRange . '_start_age';
            },
            self::$ageFields
        );
    }
    
    public function getAgeNames()
    {
        return self::$ageFields;
    }
    
    public function getAllStartAges($idIsAgeGroup = true)
    {
        return $this->ages->getAllStartAges($idIsAgeGroup);
        
        // direct way:
        //foreach (self::$ageFields as $ageField) {
        //	$field = $ageField . '_start_age';
        //	$ret[$field] = $this->$field;
        //}	return $ret;
    }
    
    /**
     *	@todo do something about that count function here.
     */
    public function getSummary()
    {
        return $this->name . ' ('
            . (($this->account_type) ? $this->account_type : '') . ")";
    }
    
    /**
     *	Allowing some properties to be accessed directly: ages
     */
    public function __get($property)
    {
        if ($property=='ages') {
            // hack to save age range values. (forces preUpdate to fire). should be fixed
            $this->infant_start_age = $this->infant_start_age;
            
            return $this->ages;
        } else {
            return parent::__get($property);
        }
    }
    
    protected static $goalsCategorized;
    
    /**
     * @return array 'category' => array(Goals)
     */
    public function getCategories()
    {
        if (is_null(self::$goalsCategorized)) {
            self::$goalsCategorized = array();
            foreach ($this->goals as $i => $goal) {
                self::$goalsCategorized[$goal->def->category][$i] = $goal;
            }
        }
        return self::$goalsCategorized;
    }

    /**
     * @return Goal
     */
    public function getGoalById($id)
    {
        foreach ($this->goals as $goal) {
            if ($goal->def->id == $id) {
                return $goal;
            }
        }

        return false;
    }

    /**
     * @return Goal
     */
    public function getGoalByName($name)
    {
        foreach ($this->goals as $goal) {
            if ($goal->name == $name) {
                return $goal;
            }
        }
        
        return false;
    }
    
    public function getCertificationLevel()
    {
        switch ($this->account_type) {
            case "emt_b":
                return "EMT";
            case "emt_i":
                return "AEMT";
            case "paramedic":
                return "Paramedic";
            default:
                return $this->account_type;
        }
    }

    /**
     * the NSC has a program id of 0 and is sometimes included in lists of goalsets
     * let's differentiate it, shall we?
     *
     * @return bool
     */
    public function isStandard()
    {
        try {
            $isStandard = $this->program->id < 1;
        } catch (EntityNotFoundException $e) {
            $isStandard = true;
        }

        return $isStandard;
    }
    
    public static function defaultGoalSetExists($id, $programId, $certification)
    {
        $goalsets = EntityUtils::getRepository('GoalSet')->findBy(array('program' => $programId, 'account_type' => $certification, 'default_goalset' => true));

        $logger = \Zend_Registry::get('logger');
        
        $logger->debug($id);
        $logger->debug(current($goalsets)->id);

        if ($id && $id == current($goalsets)->id) {
            return null;
        }

        $logger->debug(count($goalsets));

        if (count($goalsets) > 0) {
            return array_pop($goalsets)->name;
        }
        
        return null;
    }
    
    /*
     * Returns the string of the name of the age field (as defined by this goal set) for the age provided
     *
     * @param Int $age the age to test
     * @param Boolean $coa_translation will translate this goal set's ages into the CoA's 4 categories:
     * 		- neonate
     * 		- infant
     * 		- pediatric
     * 		- adult
     *
     * @return String a slash separated list of all applicable age field names
     */
    public function getAgeFieldName($age, $coa_translation = false)
    {
        $name = "";
        
        // because apparently the infant start age is in months (wtf?) let's convert it to years for this function
        $infant_start_years = $this->infant_start_age / 12;
        
        
        $name = ($age >= $this->newborn_start_age) ? "Newborn" : $name;
        $name = ($age >= $infant_start_years) ? "Infant" : $name;
        $name = ($age >= $this->toddler_start_age) ? "Toddler" : $name;
        $name = ($age >= $this->preschooler_start_age) ? "Preschooler" : $name;
        $name = ($age >= $this->school_age_start_age) ? "School age" : $name;
        $name = ($age >= $this->adolescent_start_age) ? "Adolescent" : $name;
        $name = ($age >= $this->adult_start_age) ? "Adult" : $name;
        $name = ($age >= $this->geriatric_start_age) ? "Geriatric" : $name;
        
        if ($coa_translation) {
            $coa_result = "";
            $coa_result = ($name == "Newborn") ? "Neonate" : $coa_result;
            $coa_result = ($name == "Infant") ? "Infant" : $coa_result;
            $coa_result = ($name == "Toddler" || $name == "Preschooler" || $name == "School age" || $name == "Adolescent") ? "Pediatric" : $coa_result;
            $coa_result = ($name == "Adult" || $name == "Geriatric") ? "Adult" : $coa_result;
            $name = $coa_result;
        }
        
        return $name;
    }

    public function toArray()
    {
        $array = [
            'id' => $this->id,
            'name' => $this->name,
            'account_type' => $this->account_type,
            'infant_start_age' => $this->infant_start_age,
            'toddler_start_age' => $this->toddler_start_age,
            'preschooler_start_age' => $this->preschooler_start_age,
            'school_age_start_age' => $this->school_age_start_age,
            'adolescent_start_age' => $this->adolescent_start_age,
            'adult_start_age' => $this->adult_start_age,
            'geriatric_start_age' => $this->geriatric_start_age
        ];

        return $array;
    }
}
