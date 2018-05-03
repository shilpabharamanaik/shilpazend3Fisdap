<?php namespace Fisdap\Data;

/**
 *    Implements Age ranges/groups based on ages.
 *    (Ages program uses vary and are defined for each goal set)
 *
 *    Setting null for any age bane, disables that age range
 *
 * @todo   'newborn' cannot be disabled
 * @todo   Months need to be supported
 *
 * @todo figure out what this class does and document it ~bgetsug
 *
 * @author Maciej
 */
class Age
{
    const OTHER = -1;
    const PEDIATRIC = 0;
    const NEWBORN = 1;
    const INFANT = 2;
    const TODDLER = 3;
    const PRESCHOOLER = 4;
    const SCHOOL_AGE = 5;
    const ADOLESCENT = 6;
    const ADULT = 7;
    const GERIATRIC = 8;

    /**
     *    All named age ranges/limits/statuses. Not all are actual age range labels
     */
    protected static $ageNames = array(
        'pediatric',
        'newborn',
        'infant',
        'toddler',
        'preschooler',
        'school_age',
        'adolescent',
        'adult',
        'geriatric',
        'agelimit',
        -1 => 'other',
    );
    protected static $ageIds;

    /**
     *    Default values, but also:
     *        - all continuous age range ids
     *        - database expected to have these field if using this class
     *    (no pediatric here since it's only nickname for other age ranges)
     */
    protected static $defaultAges = array(
        self::NEWBORN => 0,
        self::INFANT => 1,
        self::TODDLER => 1,
        self::PRESCHOOLER => 4,
        self::SCHOOL_AGE => 6,
        self::ADOLESCENT => 13,
        self::ADULT => 18,
        self::GERIATRIC => 65,
    );

    /**
     *    Ages based on other age ranges
     */
    protected static $otherAges = array(
        self::PEDIATRIC => array(self::NEWBORN, self::ADULT),
    );

    protected $ages;


    /**
     * @param mixed $ageRanges (optional)
     *                         if null, default values will be set
     *                         if false, no default values will be set
     */
    public function __construct($ageRanges = null)
    {
        if (is_null(self::$ageIds)) {
            self::$ageIds = array_flip(self::$ageNames);
        }

        // set range values
        if (is_null($ageRanges)) {
            $this->ages = self::$defaultAges;
        } else {
            if (is_array($ageRanges)) {
                foreach ($ageRanges as $ageName => $startAge) {
                    $this->setStartAge($ageName, $startAge);
                }
            } else {
                if (!$ageRanges === false) {
                    throw new \InvalidArgumentException(
                        "Age constuctor: invalid argument. Accepted values: null, array or age ranges or false"
                    );
                }
            }
        }
    }


    public static function getAgeNames()
    {
        return self::$ageNames;
    }


    public static function getAgeIds()
    {
        return self::$ageIds;
    }


    public function setStartAge($ageName, $startAge)
    {
        if (!isset(self::$ageIds[$ageName])) {
            throw new \InvalidArgumentException('Invalid age name. Valid are ' . implode(',', self::$ageNames));
        }
        $this->ages[self::$ageIds[$ageName]] = $startAge;
    }


    /**
     * @param boolean $indexByIds , if false, indexes are age range names.
     *                            Note: this returns starting ages in continuous range, meaning derivatives like 'pediatric'
     *                            are not used here
     *
     * @return array
     */
    public function getAllStartAges($indexByIds = false)
    {
        if ($indexByIds) {
            // if user didn't initialize ages do it now, just null values
            if (is_null($this->ages)) {
                foreach (self::$defaultAges as $id => $startAge) {
                    $this->ages[$id] = null;
                }
            }

            return $this->ages;
        } else {
            $ret = array();
            //foreach (self::$ageIds as $ageName => $def) {
            foreach (self::$defaultAges as $ageId => $defaultAge) {
                $ret[self::$ageNames[$ageId]] = $this->ages[$ageId];
            }

            return $ret;
        }
    }


    public function getAgeRanges()
    {
        return array();    //"NOT IMPLEMENTED YET";
    }


    public function getAgeRange($ageGroup)
    {
        $ageGroupId = self::$ageIds[$ageGroup];
        if (is_null($ageGroupId) || $ageGroupId < 0 || $ageGroupId > 8) {
            return '(?)';
        }

        if ($ageGroupId == 0) {    //pediatric
            $from = $this->ages[self::NEWBORN];
            $to = $this->ages[self::ADULT];
        } else {
            $from = $this->ages[$ageGroupId];
            $to = $this->ages[$ageGroupId + 1];
        }

        $toAdjusted = ($from == $to) ? $from : $to - 1;

        return '(' . $from . '-' . $toAdjusted . ')';
    }


    /**
     * @param boolean $returnId , if false, returns inde are age range names
     *
     * @return int
     */
    public function getAgeGroupForAge($patientAge, $patientMonths, $returnId = false)
    {
        //Set patient years to zero if ONLY months has been set
        if ($patientAge === null && is_numeric($patientMonths)) {
            $patientAge = 0;
        }

        if (is_numeric($patientAge)) {
            // loop down through the age groups starting with the oldest
            for ($i = self::GERIATRIC; $i >= 0; $i--) {
                // if we're testing for infants, check month instead of year
                // Edit: Actually, this only works if toddler starts at 1 year. We need to check month OR year > 0
                // - NK 3/15/2017
                if ($i == self::INFANT && ($patientAge > 0 || $patientMonths >= $this->ages[$i])) {
                    break;
                }
                // otherwise, we've reached the right group if the year age is
                // greater than or equal to the group starting age
                else {
                    if ($patientAge >= $this->ages[$i]) {
                        break;
                    }
                }
            }
        } else {
            $i = self::OTHER;
        }

        return ($returnId) ? $i : self::$ageNames[$i];
    }


    /**
     *    The only age range independent of others right now
     */
    public function isPediatricAge($age, $months = null, $returnId = false)
    {
        //Use age of 0 if we have set months
        if ($age === null && is_numeric($months)) {
            $age = 0;
        }

        if (is_numeric($age)) {
            return ($age < $this->ages[self::ADULT]);
        } else {
            return null;
        }
    }


    /**
     *    The only age range independent of others right now
     */
    public function isAdultAge($age, $months = null, $returnId = false)
    {
        //Use age of 0 if we have set months
        if ($age === null && is_numeric($months)) {
            $age = 0;
        }

        if (is_numeric($age)) {
            return ($age >= $this->ages[self::ADULT] && $age < $this->ages[self::GERIATRIC]);
        } else {
            return null;
        }
    }


    /**
     *    The only age range independent of others right now
     */
    public function isGeriatricAge($age, $months = null, $returnId = false)
    {
        //Use age of 0 if we have set months
        if ($age === null && is_numeric($months)) {
            $age = 0;
        }

        if (is_numeric($age)) {
            return ($age >= $this->ages[self::GERIATRIC]);
        } else {
            return null;
        }
    }


    /**
     * @param array $results (<ageRangeId => <whatever value>)
     *
     * @returns array $results (<ageName> => <preserved input value>)
     */
    public function getNamesForIds($results)
    {
        $res = [];

        foreach ($results as $ageRangeId => $keepValue) {
            $res[self::$ageNames[$ageRangeId]] = $keepValue;
        }

        return $res;
    }
}
