<?php

/**
 * Class Util_BusinessHoursCalculator
 * A class to help us deal with the business hours
 */
class Util_BusinessHoursCalculator
{
    const SECONDS_PER_MINUTE = 60;
    const SECONDS_PER_HOUR = 3600;
    const SECONDS_PER_DAY = 86400;

    /**
     * An array of the days that are not counted as business days (e.g., Saturday and Sunday)
     * Use the ISO-8601 numeric representation of the day of the week:
     * 1 (for Monday) through 7 (for Sunday)
     *
     * @var array $excludedDays
     */
    protected $excludedDays;

    /**
     * An array representing the time that is the start of the business day, keyed by hour, minute, and second
     *
     * @var array
     */
    protected $startOfBusiness;

    /**
     * An array representing the time that is the end of the business day, keyed by hour, minute, and second
     *
     * @var array
     */
    protected $endOfBusiness;

    /**
     * @param array $startOfBusiness
     * @param array $endOfBusiness
     * @param array $excludedDays default to excluding Saturday and Sunday as business days
     */
    public function __construct(array $startOfBusiness, array $endOfBusiness, array $excludedDays = array(6, 7))
    {
        $this->excludedDays = $excludedDays;

        // make sure the start/end of business times are correctly formatted
        $this->startOfBusiness = $this->formatBusinessTime($startOfBusiness);
        $this->endOfBusiness = $this->formatBusinessTime($endOfBusiness);
    }

    /**
     * Calculate the number of business hours during a given date/time range
     *
     * @param DateTime $start
     * @param DateTime $end
     * @return int
     */
    public function calculateOnHours(DateTime $start, DateTime $end)
    {
        $onSeconds = $this->calculateOnSeconds($start, $end);

        return $onSeconds/self::SECONDS_PER_HOUR;
    }

    /**
     * Calculate the number of non-business hours during a given date/time range
     *
     * @param DateTime $start
     * @param DateTime $end
     * @return int
     */
    public function calculateOffHours(DateTime $start, DateTime $end)
    {
        $totalSeconds = $end->format("U") - $start->format("U");
        $onSeconds = $this->calculateOnSeconds($start, $end);

        return ($totalSeconds - $onSeconds)/self::SECONDS_PER_HOUR;
    }

    /**
     * Figure out how many seconds of business hours occurred during a given date range
     * @param DateTime $start
     * @param DateTime $end
     * @return integer
     */
    protected function calculateOnSeconds(DateTime $start, DateTime $end)
    {
        $seconds = 0;

        // if the start time is on a business day, calculate the seconds until the end of business
        if ($this->isBusinessDay($start)) {
            // Start date is a business day
            if ($this->isInBusinessTime($start)) {
                // Start date is during business day
                $seconds += $this->getEndOfBusinessDay($start)->format("U") - $start->format("U");
            } elseif ($this->isBeforeStartOfBusiness($start)) {
                // Start date is before start of business day
                $seconds += $this->getSecondsInBusinessDay();
            }
        }

        // if the end time is in the middle of a business day, calculate the seconds from the beginning of business
        if ($this->isBusinessDay($end)) {
            // End date is a business day
            if ($this->isInBusinessTime($end)) {
                // End date is during business day
                $seconds += $end->format("U") - $this->getStartOfBusinessDay($end)->format("U");
            } elseif ($this->isAfterEndOfBusiness($end)) {
                // End date is after end of business day
                $seconds += $this->getSecondsInBusinessDay();
            }
        }

        // if the start and end time is on the same day, we may have over-counted above
        if ($start->format("Ymd") == $end->format("Ymd")) {
            // Start and end on same day
            // if this is a business day, we accidentally added an extra whole business day of seconds above
            if ($this->isBusinessDay($start)) {
                // Date is during business day
                $seconds -= $this->getSecondsInBusinessDay();
            }

            return $seconds;
        }

        // now let's add in all the days in between
        for ($i = $start->add(new DateInterval('P1D')); $i->format("U") < $end->format("U"); $i->add(new DateInterval('P1D'))) {
            // if the next day is a business day and not the end day, count all those seconds
            if ($this->isBusinessDay($i) && $i->format("Ymd") != $end->format("Ymd")) {
                // Adding internal business day
                $seconds += $this->getSecondsInBusinessDay();
            }
        }

        return $seconds;
    }

    /**
     * Return a datetime object representing the end of business on the date given
     *
     * @param DateTime $date
     * @return DateTime
     */
    protected function getEndOfBusinessDay($date)
    {
        $endOfDay = new DateTime($date->format("Ymd"));
        $endOfDay->setTime(
            $this->endOfBusiness['hour'],
            $this->endOfBusiness['minute'],
            $this->endOfBusiness['second']
        );
        return $endOfDay;
    }

    /**
     * Return a datetime object representing the start of business on the date given
     *
     * @param DateTime $date
     * @return DateTime
     */
    protected function getStartOfBusinessDay($date)
    {
        $startOfDay = new DateTime($date->format("Ymd"));
        $startOfDay->setTime(
            $this->startOfBusiness['hour'],
            $this->startOfBusiness['minute'],
            $this->startOfBusiness['second']
        );
        return $startOfDay;
    }

    /**
     * Is this time during business hours?
     *
     * @param DateTime $time
     * @return boolean
     */
    protected function isInBusinessTime(DateTime $time)
    {
        if ($time->format("U") <= $this->getEndOfBusinessDay($time)->format("U")
            && $time->format("U") >= $this->getStartOfBusinessDay($time)->format("U")
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is this time before business hours?
     *
     * @param DateTime $time
     * @return boolean
     */
    protected function isBeforeStartOfBusiness(DateTime $time)
    {
        if ($time->format("U") <= $this->getStartOfBusinessDay($time)->format("U")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is this time after business hours?
     *
     * @param DateTime $time
     * @return boolean
     */
    protected function isAfterEndOfBusiness(DateTime $time)
    {
        if ($time->format("U") >= $this->getEndOfBusinessDay($time)->format("U")) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Is the given date a business day?
     *
     * @param DateTime $date
     * @return boolean
     */
    protected function isBusinessDay($date)
    {
        $weekday = $date->format("N");
        if (in_array($weekday, $this->excludedDays) === true) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Calculate the number of seconds in the business day
     *
     * @return int
     */
    protected function getSecondsInBusinessDay()
    {
        $endTime = $this->endOfBusiness['hour'] * self::SECONDS_PER_HOUR +
            $this->endOfBusiness['minute'] * self::SECONDS_PER_MINUTE +
            $this->endOfBusiness['second'];
        $startTime = $this->startOfBusiness['hour'] * self::SECONDS_PER_HOUR +
            $this->startOfBusiness['minute'] * self::SECONDS_PER_MINUTE +
            $this->startOfBusiness['second'];
        return $endTime - $startTime;
    }

    /**
     * Make sure the given array is properly formatted with hour, minute, and second elements
     *
     * @param array $time
     * @return array properly-formatted business time with hour, minute, and second elements
     */
    protected function formatBusinessTime(array $time)
    {
        // set the default as the beginning of the day (midnight)
        $businessTime = array("hour" => 0, "minute" => 0, "second" => 0);

        // set the business time according to the given time
        if (is_int($time['hour']) && $time['hour'] > 0 && $time['hour'] <= 24) {
            $businessTime['hour'] = $time['hour'];
        }
        if (is_int($time['minute']) && $time['minute'] > 0 && $time['minute'] <= 59) {
            $businessTime['minute'] = $time['minute'];
        }
        if (is_int($time['second']) && $time['second'] > 0 && $time['second'] <= 59) {
            $businessTime['second'] = $time['second'];
        }

        return $businessTime;
    }
}
