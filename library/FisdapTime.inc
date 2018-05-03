<?php
require_once('Assert.inc');
require_once('SqlValue.inc');
//require_once('phputil/exceptions/Fisdap_Exception_InvalidArgumentException.inc');

/**
 * Holds a time.
 * For legacy purposes, seconds are NOT used except when noted in the factory
 * methods.
 */
final class FisdapTime implements SqlValue {
	private $hours;
	private $minutes;
    private $seconds = 0;

    private $use_seconds = false;

    /**
     * Return the current time.
     * @return FisdapTime The current time.
     */
    public static function now() {
        return FisdapTime::create_from_timestamp(time());
    }

    /**
     * Retrieve the time as a time meaning "no time."
     * @return FisdapTime The time.
     */
    public static function not_set() {
        return FisdapTime::create_from_hms_string('00:00:00');
    }

    /**
     * Create from Unix timestamp.
     * @param int $timestamp The Unix timestamp.
     * @return FisdapTime The time.
     */
    public static function create_from_timestamp($timestamp) {
        Assert::is_int($timestamp);

        return FisdapTime::create_from_hms_string(date('H:i:s', $timestamp));
    }

    /**
     * Create from integers.
     * Seconds are ignored.
     * @param int $hour The hour 0-23.
     * @param int $minute The minute 0-59.
     * @return FisdapTime The time.
     */
    public static function create_from_hm($hour, $minute) {
        Assert::is_int($hour);
        Assert::is_int($minute);

        return new FisdapTime($hour, $minute);
    }

    /**
     * Create from a string.
     * Seconds are ignored.
     * @param string $time {H}H:{M}M format.
     * @return FisdapTime The time.
     */
    public static function create_from_hm_string($time) {
        Assert::is_not_empty_trimmed_string($time);

        $pieces = split(':', trim($time));

        Assert::is_true(count($pieces) == 2);

        return new FisdapTime($time);
    }

    /**
     * Create from integers.
     * @param int $hour The hour 0-23.
     * @param int $minute The minute 0-59.
     * @param int $second The second 0-59.
     * @return FisdapTime The time.
     */
    public static function create_from_hms($hour, $minute, $second) {
        Assert::is_int($hour);
        Assert::is_int($minute);
        Assert::is_int($second);

        $time = new FisdapTime($hour, $minute);
        $time->set_seconds($second);

        return $time;
    }

    /**
     * Create from a string.
     * @param int|string|null $time The hour as '{H}H:{M}M' or '{H}H:{M}M{:{S}S}'.
     * @return FisdapTime The time.
     */
    public static function create_from_hms_string($time) {
        Assert::is_not_empty_trimmed_string($time);

        $pieces = split(':', trim($time));
        $n = count($pieces);

        Assert::is_true(($n > 0) && ($n < 4));

        if ($n < 2) {
            $pieces[] = 0;
        }

        if ($n < 3) {
            $pieces[] = 0;
        }

        return self::create_from_hms($pieces[0], $pieces[1], $pieces[2]);
    }

    /**
     * Create from military time.
     * Seconds are ignored.
     * @param int|string $time The time as [int M or MM or HMM or HHMM] or '{H}HMM'.
     * @return FisdapTime The time.
     */
    public static function create_from_military_time($time) {
        if (is_int($time)) {
            $time = (string) $time;
        }

        Assert::is_not_empty_trimmed_string($time);

        $time = trim($time);
        $pieces = split(':', trim($time));

        Assert::is_true(count($pieces) == 1);

        return new FisdapTime($time);
    }

	/** 
     * Constructor.
     * @todo I would really like to support seconds here too when a single 
     * string is passed in, but I am unable to tell whether this will break
     * existing code without plenty of testing.
	 * @todo don't bork on DST
     * This implementation ignores seconds.
     * @param int|string|null $hour The hour as [int M or MM or HMM or HHMM] or '{H}HMM' or 
     * 'H{H}:M{M}' or NULL for now.
     * @param int|null The minute.
     */
	public function __construct($hour=null, $minute=null) {
		if (is_null($hour)) {
			$this->set_hours(date('G'));
			$this->set_minutes(date('i'));
		}
		elseif (is_null($minute)) {
            if (is_int($hour)) {
                $hour = (string) $hour;
            }

            Assert::is_not_empty_trimmed_string($hour);

            $hour = trim($hour);
			if (strstr($hour, ':')) {
				$pieces = split(':', $hour);

                if (count($pieces) == 2) {
                    $this->set_hours($pieces[0]); 
                    $this->set_minutes($pieces[1]);
                }
                else {
                    throw new Fisdap_Exception_InvalidArgumentException(
                        "Invalid time[$hour]");
                }
			}
			else {
                $hour = str_pad($hour, 4, '0', STR_PAD_LEFT);

                Assert::is_true(strlen($hour) == 4);

				$this->set_hours(substr($hour, 0, 2));
				$this->set_minutes(substr($hour, 2, 2));
			}
		}
		else {
		    $this->set_hours($hour);
		    $this->set_minutes($minute);
		}

	}

    public function set_use_seconds($use) {
		if ($use == false) {
			$this->set_seconds(0);
		}
        $this->use_seconds = $use;
    }

	public function get_use_seconds() {
		return $this->use_seconds;
	}

	/**
	 * Determine if this object is set.
	 * @return boolean TRUE if the time is NOT 0-0-0.
	 */
	public function is_set() {
		$b = $this->hours || $this->minutes;
	    if ($this->use_seconds) {
			$b = $b || $this->seconds;
		}

		return $b;
	}

	private function set_hours($hour) {
        Assert::is_int($hour);
        $hour = Convert::to_int($hour);
        Assert::is_true(($hour >= 0) && ($hour < 24));

        $this->hours = $hour;
	}

	private function set_minutes($minute) {
        Assert::is_int($minute);
        $minute = Convert::to_int($minute);

        Assert::is_true(($minute >= 0) && ($minute <= 99));

        $this->minutes = $minute;
	}

	private function set_seconds($second) {
        Assert::is_int($second);
        $second = Convert::to_int($second);
        Assert::is_true(($second >= 0) && ($second < 60));

        $this->seconds = $second;
        $this->use_seconds = true;
	}

    /**
     * Retrieve the seconds.
     * @return int The seconds 0-59, or 0 if seconds are not being used.
     */
	public function get_seconds() {
		return $this->seconds;
	}

    /**
     * Retrieve the hours.
     * @return int The hours 0-23.
     */
	public function get_hours() {
		return $this->hours;
	}

    /**
     * Retrieve the minutes.
     * @return int The minutes 0-59.
     */
	public function get_minutes() {
		return $this->minutes;
	}

    private static function get_2digits($n) {
        if ($n < 10) {
            return "0$n";
        }

        return (string) $n;
    }

	/**
	 * Retrieve the time formatted for a UI.
	 * @return string The time as H:M:S.
	 */
	public function get_ui_hms() {
        return self::get_2digits($this->hours) . ':' .
            self::get_2digits($this->minutes) . ':' .
            self::get_2digits($this->seconds); 
	}

	/**
	 * Retrieve the time formatted for a UI.
	 * @return string The time as H:M.
	 */
	public function get_ui_hm() {
        return self::get_2digits($this->hours) . ':' .
            self::get_2digits($this->minutes); 
	}

    /**
     * Retrieve the military time.
     * @return string HHMM.
     */
    public function get_military_time() {
        return self::get_2digits($this->hours) . 
            self::get_2digits($this->minutes);
    }

    /**
     * Retrieve the time as a string.
     * @return string {H}HMM{SS}.
     */
	public function get_time() {
        return $this->hours . self::get_2digits($this->minutes) .
            $this->get_seconds_string(false);
	}

    private function get_seconds_string($include_colon) {
        if ($this->use_seconds) {
            $s = self::get_2digits($this->seconds);
            if ($include_colon) {
                $s = ':' . $s;
            }

            return $s;
        }

        return '';
    }

    /**
     * Retrieve the time as a string.
     * @return string {H}H:MM{:SS} AM/PM.
     */
	public function get_12hour_time() {
        $min_sec = self::get_2digits($this->minutes) . $this->get_seconds_string(true);

		if ($this->hours == 12) {
			return "12:$min_sec PM";
		}
        
        if ($this->hours > 11) {
			return ($this->hours - 12) . ":$min_sec PM";
		}
        
        if ($this->hours == 0) {
			return "12:$min_sec AM";
		}

        return $this->hours . ":$min_sec AM";
	}

    /**
     * Retrieve the time as a string.
     * @return string HHMM{SS}.
     */
	public function get_24hour_time() {
        return self::get_2digits($this->hours) .
            self::get_2digits($this->minutes) .
            $this->get_seconds_string(false);
	}

	/**
	 * Retrieve the time formated for MySQL.
	 * @return string The time as H:i:s
	 */
	public function get_MySQL_time() {
        return self::get_2digits($this->hours) . ':' .
            self::get_2digits($this->minutes) . 
            $this->get_seconds_string(true);
	}

	public function get_as_sql_value() {
		return $this->get_MySQL_time();
	}

	public function __toString() {
		return 'FisdapTime[' . $this->get_MySQL_time() . ']';
	}

    /**
     * Compare this time with another.
     * @param FisdapTime $time The other time.
     * @return int Like a normal compare method.
     */
	public function compare($time) {
        Assert::is_a($time, 'FisdapTime');

		$hours = $this->get_hours() - $time->get_hours();
        if ($hours != 0) {
            return $hours;
        }

		$minutes = $this->get_minutes() - $time->get_minutes();
        if (!$this->use_seconds || ($minutes != 0)) {
            return $minutes;
        }

		return $this->get_seconds() - $time->get_seconds();
	}

	/**
	 * Change the time by a given offset of hours and minutes.
	 * Offsets may be positive or negative.
	 * @param int $h The number of hours to change by
	 * @param int $m The number of minutes to change by
	 * */
	public function change_time($h, $m) {
        Assert::is_int($h);
        Assert::is_int($m);

		$stamp = mktime(
			$this->get_hours() + $h,
			$this->get_minutes() + $m);
		$this->set_hours(date('G', $stamp));
		$this->set_minutes(date('i', $stamp));
	}

	/**
	 * Change the time by the given number of hours.
	 * @param int $offset The number of years to change by. May be positive or negative.
	 */
	public function change_hours($offset) {
        Assert::is_int($offset);
		$this->change_time($offset, 0);
	}

	/**
	 * Change the time by the given number of minutes.
	 * @param int $offset The number of minutes to change by. May be positive or negative.
	 */
	public function change_minutes($offset) {
        Assert::is_int($offset);
		$this->change_time(0, $offset);
	}

    /**
     * A comparator.
     * @param FisdapTime|string|null $a The first time.
     * @param FisdapTime|string|null $b Another time.
     * @return int Like a normal compare method.
     */
	public static function time_comparator($a, $b) {
        $a = Convert::to_a($a, 'FisdapTime');
        $b = Convert::to_a($b, 'FisdapTime');

		return $a->compare($b);
	}
}
?>
