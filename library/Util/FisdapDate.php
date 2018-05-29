<?php
//require_once('Util_Assert.inc');
//require_once('Util_SqlValue.inc');
//require_once('phputil/classes/common_utilities.inc');

/*
 * Holds a calendar date.
 * This holds default SQL dates like '0000-00-00' correctly.
 */
final class Util_FisdapDate implements Util_SqlValue {
	private $day;
	private $month;
    private $mutable;
	private $year;

    /**
     * Return the today.
     * @return FisdapDate Today.
     */
    public static function today() {
        return new self();
    }

    /**
     * Retrieve the date as meaning "no date."
     * @return FisdapDate The date.
     */
    public static function not_set() {
        return self::create_from_ymd_string('0000-00-00');
    }

    /**
     * Create from Unix timestamp.
     * @param int $timestamp The Unix timestamp.
     * @return FisdapDate The date.
     */
    public static function create_from_timestamp($timestamp) {
        Util_Assert::is_int($timestamp);

        return self::create_from_ymd_string(date('Y-m-d', $timestamp));
    }

    /**
     * Create from a year, month, and a day.
     * @param int $year The year.
     * @param int $month The month, 1-12.
     * @param int $day The day, 1-31.
     * @return FisdapDate The date.
	 * @todo needs review
     */
    public static function create_from_ymd($year, $month, $day) {
        Util_Assert::is_int($year);
        Util_Assert::is_int($month);
        Util_Assert::is_int($day);

        return new self($year, $month, $day);
    }

    /**
     * Create from a ymd string.
     * @param string $ymd Y-M-D.
     * @return FisdapDate The date.
	 * @todo needs review
     */
    public static function create_from_ymd_string($ymd) {
        Util_Assert::is_not_empty_trimmed_string($ymd);

        return new self(trim($ymd));
    }

    /**
     * Create from a SQL string.
     * @param string | null $s Y-M-D or null.
     * @return FisdapDate The date.
	 * @todo needs review
     */
    public static function create_from_sql_string($s) {
		Util_Assert::is_true(
			Util_Test::is_null($s) || 
			Util_Test::is_not_empty_trimmed_string($s));

		if (is_null($s)) return self::not_set();
        return new self(trim($s));
    }

    /**
     * Create from script values.
	 * The month is 1-12.
     * @param string $prefix The value prefix, i.e. startdate_.
	 * @param boolean $check_day TRUE if the day field is checked.
     * @return FisdapDate The date.
	 * @todo needs review
     */
//    public static function create_from_script($prefix, $check_day=true) {
//		Util_Assert::is_string($prefix);
//		Util_Assert::is_boolean($check_day);
//
//        // Sometimes different pieces of the date are not needed,
//        // so we allow this.
//        $month_set = false;
//		if (common_utilities::is_scriptvalue($prefix . 'month')) {
//			$month = common_utilities::get_scriptvalue($prefix . 'month'); 
//			if (Util_Test::is_int($month)) {
//				$month_set = true;
//				$month = Util_Convert::to_int($month);
//			}
//		}
//
//		$day_set = false;
//		if ($check_day) {
//			if (common_utilities::is_scriptvalue($prefix . 'day')) {
//				$day = common_utilities::get_scriptvalue($prefix . 'day');
//				if (Util_Test::is_int($day)) {
//					$day_set = true;
//					$day = Util_Convert::to_int($day);
//				}
//			}
//		}
//		else {
//			$day = 1;
//			$day_set = true;
//		}
//		
//		$year_set = false;
//		if (common_utilities::is_scriptvalue($prefix . 'year')) {
//            $year = common_utilities::get_scriptvalue($prefix . 'year');
//			if (Util_Test::is_int($year)) {
//				$year_set = true;
//				$year = Util_Convert::to_int($year);
//			}
//		}
//
//		if ($month_set && $day_set && $year_set) {
//			// We should use the code below, but since our prompts don't handle 
//			// Feb well, we can't.
//			// $date = new self($year, $month, $day);
//			$date = new self($year, $month, 1);
//			$date->set_and_fix_day($day);
//		}
//		else {
//			$date = FisdapDate::not_set();
//		}
//
//		return $date;
//    }

	/** 
	 * Constructor.
     * @param int|string|null The year, a string to be parsed, or null for today.
     * @param int|null The month.
     * @param int|null The day.
	 */
	public function __construct($year=null, $month=null, $day=null) {
		if (is_null($year)) {
			// If no arguments given, it's the current date
			$year = date('Y');
			$month = date('n');
			$day = date('j');
		} else if ($month === null) {
			// If one argument is given, it's a string, parse it
			Util_Assert::is_not_empty_trimmed_string($year);
			$date_string = trim($year);

			// Special check for our user-friendly "not set" string
			if ($date_string == '--/--/----') {
				$date_string = '0000-00-00';
			}

			// Split the pieces of the date by slashes or dashes
			if (strpos($date_string, '/') !== false) {
				$pieces = explode('/', $date_string);
			} else {
				$pieces = explode('-', $date_string);
			}
			Util_Assert::is_true(count($pieces) == 3);

			// Dates can either be international format or US format
			if (strlen($pieces[0]) == 4) {
				$year = $pieces[0];
				$month = $pieces[1];
				$day = $pieces[2];
			} else {
				$month = $pieces[0];
				$day = $pieces[1];
				$year = $pieces[2];
			}
		}

		$this->set_year($year);
		$this->set_month($month);
		$this->set_day($day);
	}

	public function __toString() {
		return 'Util_FisdapDate[' . $this->get_MySQL_date() . ']';
	}

    /**
     * Is this a leap year?
     * @return boolean TRUE if this is a leap year.
     */
    public function is_leap_year() {
        if ($this->year % 4) return false;
        if ($this->year % 100) return true;
        return !($this->year % 400);
    }

	/**
	 * Set a day, fixing it if necessary.
	 * @param int $day The day.
	 * @todo needs review
	 */
	public function set_and_fix_day($day) {
		$max = $this->get_days_in_month();
		$day = max(0, min($day, $max));
		$this->set_day($day);
	}

    /**
     * How many days are in the month?
     * @return int The number of days in the month.
	 * @todo needs review
     */
    public function get_days_in_month() {
        static $days = array(31, -1, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);

        if ($this->month != 2) return $days[$this->month-1];
        if ($this->is_leap_year()) return 29;
        return 28;
    }

	/**
	 * Determine if this object is set.
	 * @return boolean TRUE if the date has been set.
	 */
	public function is_set() {
        return $this->year && $this->month && $this->day;
	}

    /**
     * The internal date has changed.
	 * @todo needs review
     */
    private function date_changed() {
        // If any field is 0, we *assume* a SQL date and no operations can be
        // done on the date.
        $this->mutable = $this->is_set();
    }

    /**
     * Retrieve the date formatted for SQL.
     * @return $string the date formatted for MySQL
	 * @todo needs review
     */
    public function get_MySQL_date() {
		if (!$this->is_set()) return '0000-00-00';
		return $this->get_formatted_date('Y-m-d');
    }

	/**
	 * Retrieve the date formatted for a UI.
	 * @return string The date as Y/M/D.
	 */
	public function get_ui_ymd() {
		if (!$this->is_set()) return '--/--/----';
		return $this->get_formatted_date('m/d/Y');
	}

	/**
	 * @todo document
	 * @todo test
	 */
	public function get_timestamp() {
		return mktime(0, 0, 0, $this->get_month(), $this->get_day(), $this->get_year());
	}

	/**
	 * @todo document
	 * @todo test
	 */
	public function get_formatted_date($format) {
		return date($format, $this->get_timestamp());
	}

	/**
	 * @todo needs review
	 */
	public function get_as_sql_value() {
		return $this->get_MySQL_date();
	}

    /**
     * Retrieve the date as a string.
     * @return $string The date as 'Y-m-d'
	 * @todo needs review
	 * @deprecated This is poorly named and of dubious necessity
     
	public function get_date() {
		$logger = FisdapLogger::get_logger();
		$logger->deprecated("Don't use this function");
		return $this->year . '-' . $this->month . '-' . $this->day;
	}
	 */

    /**
     * Retrieve the year.
     * @return int The year.
     */
	public function get_year() {
		return $this->year;
	}

    /**
     * Retrieve the month.
     * @return int The month 1-12.
     */
	public function get_month() {
		return $this->month;
	}

    /**
     * Retrieve the day.
     * @return int The day 1-31.
     */
	public function get_day() {
		return $this->day;
	}

	private	function set_year($year) {
        Util_Assert::is_int($year);

        $year = Util_Convert::to_int($year);

        Util_Assert::is_true(($year == 0) ||
            ($year == date('Y', mktime(0, 0, 0, 1, 1, $year))));

        $this->year = $year;
        $this->date_changed();
	}

	/**
	 * Should be called after {@link set_year()}.
	 */
	private function set_month($month) {
        Util_Assert::is_int($month);

        $month = Util_Convert::to_int($month);

        Util_Assert::is_true(($month == 0) ||
            ($month == date('n', mktime(0, 0, 0, $month, 1, $this->get_year()))));

        $this->month = $month;
        $this->date_changed();
	}

	/**
	 * Should be called after {@link set_month()}.
	 */
	private function set_day($day) {
        Util_Assert::is_int($day);

        $day = Util_Convert::to_int($day);

        Util_Assert::is_true(($day == 0) ||
            ($day == date('j', mktime(0, 0, 0, $this->get_month(), $day, $this->get_year()))));

        $this->day = $day;
        $this->date_changed();
	}

	/**
	 * Change the date by a given offset of years, months, or days.
	 * Offsets may be positive or negative.
	 *
	 * @param int $y The number of years to change by
	 * @param int $m The number of months to change by
	 * @param int $d The number of days to change by
	 * @return Util_FisdapDate
	 * */
	public function change_date($y, $m, $d) {
        Util_Assert::is_int($y);
        Util_Assert::is_int($m);
        Util_Assert::is_int($d);

        if (!$this->mutable) return;

		$stamp = mktime(0, 0, 0,
			$this->get_month() + $m,
			$this->get_day() + $d,
			$this->get_year() + $y);

		$this->set_year(date("Y", $stamp));
		$this->set_month(date("m", $stamp));
		$this->set_day(date("d", $stamp));
		
		return $this;
	}

	/**
	 * Change the date by the given number of years.
	 * @param int $offset The number of years to change by.
	 * @return Util_FisdapDate
	 * @todo I think weird things are happening with large numbers here - 
	 * needs testing and debugging
	 * */
	public function change_year($offset) {
        Util_Assert::is_int($offset);
		$this->change_date($offset, 0, 0);
		return $this;
	}

	/**
	 * Change the date by the given number of months.
	 * @param int $offset The number of months to change by.
	 * @return Util_FisdapDate
	 * */
	public function change_month($offset) {
        Util_Assert::is_int($offset);
		$this->change_date(0, $offset, 0);
		return $this;
	}

	/**
	 * Change the date by the given number of days.
	 * @param int $offset The number of days to change by.
	 * @return Util_FisdapDate
	 * */
	public function change_day($offset) {
        Util_Assert::is_int($offset);
		$this->change_date(0, 0, $offset);
		return $this;
	}

    /**
     * Compare this date with another.
     * @param FisdapDate $date The other date.
     * @return int Like a normal compare method.
     */
	public function compare($date) {
        Util_Assert::is_a($date, 'FisdapDate');

		$diff = $this->get_year() - $date->get_year();
		if ($diff != 0) return $diff;

		$diff = $this->get_month() - $date->get_month();
		if ($diff != 0) return $diff;

		return $this->get_day() - $date->get_day();
	}

    /**
     * A comparator.
     * @param FisdapDate|string|null $a The first date.
     * @param FisdapDate|string|null $b The second date.
     * @return int Like a normal compare method.
     */
	public static function date_comparator($a, $b) {
		if ($a === '') {
			$a = self::not_set();
		}
		if ($b === '') {
			$b = self::not_set();
		}
        $a = Util_Convert::to_a($a, 'FisdapDate');
        $b = Util_Convert::to_a($b, 'FisdapDate');

		return $a->compare($b);
	}

	/**
	 * Retrieve the short month names.
	 * @return array The month names.
	 * @todo needs review
	 */
	public static function get_short_month_names() {
		static $names = array(
			'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
			'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
		return $names;
	}

	/**
	 * Retrieve a month name.
	 * @param int $month The month.
	 * @param int $offset The month for January.
	 * @return string The month name.
	 * @todo needs review
	 */
	public static function get_short_month_name($month, $offset=1) {
		$names = self::get_short_month_names();

		Util_Assert::is_int($month);
		Util_Assert::is_int($offset);

		$i = $month - $offset;
		Util_Assert::is_true($i >= 0);
		Util_Assert::is_true($i < 12);

		return $names[$month-$offset];
	}

	/**
	 * Retrieve the long month names.
	 * @return array The month names.
	 * @todo needs review
	 */
	public static function get_long_month_names() {
		static $names = array(
			'January', 'February', 'March', 'April', 'May', 'June',
			'July', 'August', 'September', 'October', 'November', 'December');
		return $names;
	}
	/**
	 * Retrieve a long month name.
	 * @param int $month The month.
	 * @param int $offset The month for January.
	 * @return string The month name.
	 * @todo needs review
	 */
	public static function get_long_month_name($month, $offset=1) {
		$names = self::get_long_month_names();

		Util_Assert::is_int($month);
		Util_Assert::is_int($offset);

		$i = $month - $offset;
		Util_Assert::is_true($i >= 0);
		Util_Assert::is_true($i < 12);

		return $names[$month-$offset];
	}
	
	/**
	 * Retrieve an array to populate a Zend_Form_Element
	 *
	 * @return array
	 */
	public static function get_month_prompt_names($includeNa = true, $start = 1, $end = 12)
	{
		$return = array();
		
		if ($includeNa) {
			$return = array("0" => "Month");		
		}
		
		$months = self::get_short_month_names();
		
		foreach ($months as $id=>$month) {
			//Skip this value if it's not in our month range
			if ($id+1 < $start || $id+1 > $end) {
				continue;
			}
			
			$value = $id+1;
			if ($value < 10) {
				$value = "0" . $value;
			}
			$return[$value] = $month;
		}
		return $return;
	}
	
	/**
	 * Retrieve an array to populate a Zend_Form_Element
	 *
	 * @return array
	 */
	public static function get_year_prompt_names($includeNa = true)
	{
		$years = array();
		$today = new \DateTime();
		$year = $today->format('Y');
		
		if ($includeNa) {
			$years[0] = "Year";
		}
		
		for ($i = ($year-10); $i > ($year-100); $i--) {
			$years[$i] = $i;
		}
		
		return $years;
	}
	
	/**
	 * Retrieve an array to populate a Zend_Form_Element
	 */
	public static function get_day_prompt_names($includeNa = true)
	{
		$days = array();
		
		if ($includeNa) {
			$days[0] = "Day";
		}
		
		for($i=1; $i<=31; $i++) {
			$days[$i] = $i;
		}
		
		return $days;
	}
	
	/**
	 * creating between two date
	 * @param string since
	 * @param string until
	 * @param string step
	 * @param string date format
	 * @return array
	 * @author Ali OYGUR <alioygur@gmail.com>
	 */
	public static function get_dates_in_range($first, $last, $step = '+1 day', $format = 'm/d/Y') { 
		$dates = array();
		$current = strtotime($first);
		$last = strtotime($last);

		while($current <= $last) { 
		    $dates[] = date($format, $current);
		    $current = strtotime($step, $current);
		}

		return $dates;
	}
}
?>
