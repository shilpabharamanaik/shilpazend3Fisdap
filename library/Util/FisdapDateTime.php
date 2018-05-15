<?php
//require_once('Util_Assert.inc');
//require_once('phputil/classes/common_utilities.inc');
//require_once('Util_FisdapDate.inc');
//require_once('Util_FisdapTime.inc');
//require_once('SqlValue.inc');
//require_once('phputil/exceptions/Fisdap_Exception_InvalidArgumentException.inc');
//require_once('phputil/exceptions/Fisdap_Exception_RuntimeException.inc');

/**
 * Keeps a date and a time object.
 */
final class Util_FisdapDateTime implements Util_SqlValue
{
    private $date;
    private $time;

    /**
     * Return the current date/time.
     * @return FisdapDateTime The current date/time.
     */
    public static function now()
    {
        return new self();
    }

    /**
     * Retrieve the date as a date meaning "no date."
     * @return Util_FisdapDate The date/time.
     */
    public static function not_set()
    {
        return new self(
            Util_FisdapDate::not_set(),
            Util_FisdapTime::not_set()
        );
    }

    /**
     * Create from Unix timestamp.
     * @param int $timestamp The Unix timestamp.
     * @return Util_FisdapDateTime The date/time.
     */
    public static function create_from_timestamp($timestamp)
    {
        Util_Assert::is_int($timestamp);

        return new self(
            Util_FisdapDate::create_from_timestamp($timestamp),
            Util_FisdapTime::create_from_timestamp($timestamp)
        );
    }

    /**
     * Retrieve the date as the end of day.
     * If the date is not, the time is set to 0:0:0.
     * @param Util_FisdapDate $date The date.
     * @return Util_FisdapDateTime The date/time.
     */
    public static function end_of_day($date)
    {
        Util_Assert::is_a($date, 'Util_FisdapDate');

        if ($date->is_set()) {
            $time = '23:59:59';
        } else {
            $time = '0:0:0';
        }

        return new self($date, $time);
    }

    /**
     * Create from a SQL string.
     * @param string | null $ymd Y-M-D H:M:S or null.
     * @return Util_FisdapDateTime The date/time.
     */
    public static function create_from_sql_string($s)
    {
        Util_Assert::is_true(
            Util_Test::is_null($s) ||
            Util_Test::is_not_empty_trimmed_string($s)
        );

        if (is_null($s)) {
            return self::not_set();
        }
        return new self(trim($s));
    }

    /**
     * Constructor.
     * @param string|Util_FisdapDate|null $date The date or 'Y-M-D [H:M:S]'.
     * @param string|Util_FisdapTime|null The time.
     */
    public function __construct($date=null, $time=null)
    {
        // Date.
        if ($date instanceof Util_FisdapDate) {
            $this->date = $date;
        } elseif (is_null($date)) {
            $this->date = new Util_FisdapDate();
        } elseif (is_null($time)) {
            Util_Assert::is_not_empty_trimmed_string($date);

            $pieces = explode(' ', trim($date));
            $n = count($pieces);

            $this->date = new Util_FisdapDate($pieces[0]);

            if ($n > 1) {
                $time = $pieces[1];
            }

            if ($n > 2) {
                throw new Fisdap_Exception_InvalidArgument(
                    "Invalid date/time[$date]"
                );
            }
        } else {
            $this->date = new Util_FisdapDate($date);
        }

        // Time.
        if ($time instanceof Util_FisdapTime) {
            $this->time = $time;
        } elseif (is_null($time)) {
            $this->time = Util_FisdapTime::now();
        } else {
            // Until the constructor handles seconds too.
            $pieces = explode(':', $time);
            if (count($pieces) == 3) {
                $this->time = Util_FisdapTime::create_from_hms_string($time);
            } else {
                $this->time = new Util_FisdapTime($time);
            }
        }
    }

    /**
     * Retrieve the Unix timestamp.
     * @param int $timestamp The Unix timestamp.
     * @return int The time stamp.
     */
    public function get_timestamp()
    {
        return mktime(
            $this->time->get_hours(),
            $this->time->get_minutes(),
            $this->time->get_seconds(),
            $this->date->get_month(),
            $this->date->get_day(),
            $this->date->get_year()
        );
    }

    /**
     * Determine if this object is set.
     * If the date is not set we ignore the time.
     * @return boolean TRUE if the date/time has been set.
     */
    public function is_set()
    {
        return $this->date->is_set();
    }

    public function __call($function, $params)
    {
        // Try to pass the call to one of the subobjects
        if (is_callable(array($this->date, $function))) {
            return call_user_func_array(array($this->date, $function), $params);
        }

        if (is_callable(array($this->time, $function))) {
            return call_user_func_array(array($this->time, $function), $params);
        }

        throw new Fisdap_Exception_Runtime(
            "Invalid method call: $function(" . implode(', ', $params) . ')'
        );
    }

    /**
     * Retrieve the underlying date.
     * @return Util_FisdapDate The date.
     */
    public function get_date()
    {
        return $this->date;
    }

    /**
     * Retrieve the underlying time.
     * @return Util_FisdapDate The time.
     */
    public function get_time()
    {
        return $this->time;
    }

    /**
     * Retrieve the date / time formatted for SQL.
     * @return $string The date / time as 'Y:m:d H:i:s'
     */
    public function get_MySQL_date_time()
    {
        return $this->date->get_MySQL_date() . ' ' . $this->time->get_MySQL_time();
    }

    public function get_as_sql_value()
    {
        return $this->get_MySQL_date_time();
    }

    public function __toString()
    {
        return 'Util_FisdapDateTime[' . $this->get_MySQL_date_time() . ']';
    }

    /**
     * Need to override this because if we change the time by enough,
     * date will be affected.
     * @see Util_FisdapTime::change_time()
     */
    public function change_time($h, $m)
    {
        Util_Assert::is_int($h);
        Util_Assert::is_int($m);

        $stamp = mktime(
            $this->time->get_hours() + $h,
            $this->time->get_minutes() + $m,
            0,
            $this->date->get_month(),
            $this->date->get_day(),
            $this->date->get_year()
        );
        // Get the offset from 0 - is this going to work?
        //0, 0, 0);
        $month = date("m", $stamp) - $this->date->get_month();
        $year = date("Y", $stamp) - $this->date->get_year();
        $day = date("d", $stamp) - $this->date->get_day();
        $this->time->change_time($h, $m);
        $this->date->change_date($year, $month, $day);
    }

    /**
     * @see Util_FisdapDate::change_date()
     */
    public function change_date($y, $m, $d)
    {
        $this->date->change_date($y, $m, $d);
    }

    /**
     * @see Util_FisdapDate::change_day()
     */
    public function change_day($offset)
    {
        $this->date->change_day($offset);
    }

    /**
     * @see Util_FisdapDate::change_month()
     */
    public function change_month($offset)
    {
        $this->date->change_month($offset);
    }

    /**
     * @see Util_FisdapDate::change_year()
     */
    public function change_year($offset)
    {
        $this->date->change_year($offset);
    }

    /**
     * Retrieve the date/time formatted for a UI.
     * @return string The date/time as Y/M/D H:M:S.
     */
    public function get_ui_ymd_hms()
    {
        return $this->date->get_ui_ymd() . ' ' . $this->time->get_ui_hms();
    }

    /**
     * @see Util_FisdapTime::change_minutes()
     */
    public function change_minutes($offset)
    {
        Util_Assert::is_int($offset);
        $this->change_time(0, $offset, 0);
    }

    /**
     * Compare this date / time with another.
     * @param Util_FisdapDateTime $datetime The other date / time.
     * @return int Like a normal compare method.
     */
    public function compare($datetime)
    {
        Util_Assert::is_a($datetime, 'Util_FisdapDateTime');

        $diff = $this->date->compare($datetime->date);
        if ($diff != 0) {
            return $diff;
        }

        return $this->time->compare($datetime->time);
    }

    /**
     * A comparator.
     * @param Util_FisdapDateTime|string|null $a The first date /time.
     * @param Util_FisdapDateTime|string|null $b Another date /time.
     * @return int Like a normal compare method.
     */
    public static function datetime_comparator($a, $b)
    {
        $a = Util_Convert::to_a($a, 'Util_FisdapDateTime');
        $b = Util_Convert::to_a($b, 'Util_FisdapDateTime');

        return $a->compare($b);
    }
}
