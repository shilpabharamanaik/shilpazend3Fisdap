<?php namespace Fisdap\Api\Shifts\Queries;

use Carbon\Carbon;
use DateTimeZone;
use Fisdap\Api\Queries\Parameters\CommonQueryParameters;
use Fisdap\Api\Queries\Parameters\IdentifiedByInstructors;
use Fisdap\Api\Queries\Parameters\IdentifiedByPrograms;
use Fisdap\Api\Queries\Parameters\IdentifiedByStudents;
use Fisdap\Api\Shifts\Exceptions\InvalidShiftState;
use Fisdap\Api\Shifts\Exceptions\InvalidShiftType;

/**
 * Encapsulates and validates query parameter data for shifts
 *
 * @package Fisdap\Api\Shifts
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo extract interface and mark final
 */
class ShiftQueryParameters extends CommonQueryParameters
{
    use IdentifiedByStudents, IdentifiedByInstructors, IdentifiedByPrograms;


    /**
     * @var \DateTime|null
     */
    protected $startingOnOrAfter = null;

    /**
     * @var \DateTime|null
     */
    protected $startingOnOrBefore = null;

    /**
     * @var string[]
     */
    protected static $possibleStates = [
        'locked',
        'unlocked',
        'late',
        'past',
        'future'
    ];

    /**
     * @var string[]|null
     */
    protected $states = null;

    /**
     * @var string[]
     */
    protected static $possibleTypes = [
        'clinical',
        'field',
        'lab'
    ];

    /**
     * @var string|null
     */
    protected $type = null;

    /**
     * @var bool
     */
    protected $includeLocked = true;

    /**
     * @var string|null
     */
    protected $dateFrom = null;

    /**
     * @return bool|null
     */
    public function getIncludeLocked()
    {
        return $this->includeLocked;
    }

    /**
     * @param String|null $includeLocked
     */
    public function setIncludeLocked($includeLocked)
    {
        $this->includeLocked = ($includeLocked === "false" ? false : true);
    }

    /**
     * @return string|null
     */
    public function getDateFrom()
    {
        return $this->dateFrom;
    }

    /**
     * @param string|null $dateFrom
     */
    public function setDateFrom($dateFrom)
    {
        if ($dateFrom) {
            $dateFrom = date_create_from_format('Y-m-d H:i:s', $dateFrom, new DateTimeZone('UTC'));

            if ($dateFrom == false) {
                throw new \InvalidArgumentException('Parameter \'dateFrom\' formatted incorrectly. ' .
                    'UTC timestamp expected. Ex.: 2017-01-31 00:00:00');
            } else {
                $dateFrom->setTimezone(new DateTimeZone('America/Chicago'));
            }
        } else {
            $dateFrom = null;
        }

        $this->dateFrom = $dateFrom;
    }

    /**
     * @return \DateTime
     */
    public function getStartingOnOrAfter()
    {
        return $this->startingOnOrAfter;
    }


    /**
     * @return \DateTime
     */
    public function getStartingOnOrBefore()
    {
        return $this->startingOnOrBefore;
    }


    /**
     * @param \DateTime $onOrAfter
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setStartingOnOrAfter(\DateTime $onOrAfter)
    {
        $this->startingOnOrAfter = $onOrAfter;

        return $this;
    }


    /**
     * @param \DateTime $onOrBefore
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setStartingOnOrBefore(\DateTime $onOrBefore)
    {
        $this->startingOnOrBefore = $onOrBefore;

        return $this;
    }


    /**
     * @param Carbon[] $startingBetween
     *
     * @return $this
     */
    public function setStartingBetween(array $startingBetween = null)
    {
        if ($startingBetween === null) {
            return $this;
        }

        if (count($startingBetween) !== 2) {
            throw new \InvalidArgumentException('startingBetween must be an array of exactly two \DateTime values');
        }

        $this->setStartingOnOrAfter($startingBetween[0]);
        $this->setStartingOnOrBefore($startingBetween[1]);

        return $this;
    }


    /**
     * @return string[]
     * @codeCoverageIgnore
     */
    public static function getPossibleStates()
    {
        return self::$possibleStates;
    }


    /**
     * @return string[]
     */
    public function getStates()
    {
        return $this->states;
    }


    /**
     * @param string[] $states
     *
     * @return $this
     * @throws InvalidShiftState
     */
    public function setStates(array $states = null)
    {
        if ($states === null) {
            return $this;
        }

        foreach ($states as $state) {
            if (! in_array($state, self::$possibleStates)) {
                throw new InvalidShiftState(
                    "'$state' is not a valid shift state.  Valid states are: " . implode(
                        ', ',
                        self::$possibleStates
                    )
                );
            }
        }

        $this->states = $states;

        return $this;
    }


    /**
     * @return string[]
     * @codeCoverageIgnore
     */
    public static function getPossibleTypes()
    {
        return self::$possibleTypes;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }


    /**
     * @param string $type
     *
     * @return $this
     * @throws InvalidShiftType
     */
    public function setType($type = null)
    {
        if ($type === null) {
            return $this;
        }

        if (! in_array($type, self::$possibleTypes)) {
            throw new InvalidShiftType(
                "'$type' is not a valid shift type.  Valid types are: " . implode(
                    ', ',
                    self::$possibleTypes
                )
            );
        }

        $this->type = $type;

        return $this;
    }
}
