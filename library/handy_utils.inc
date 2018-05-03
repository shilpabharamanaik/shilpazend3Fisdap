<?php
require_once('Assert.inc');
require_once('string_utils.php');
require_once('binarySearch.inc');
//require_once('Zend/Config/Ini.php');

/*----------------------------------------------------------------------*
 |                                                                      |
 |     Copyright (C) 1996-2010.  This is an unpublished work of         |
 |                      Headwaters Software, Inc.                       |
 |                         ALL RIGHTS RESERVED                          |
 |     This program is a trade secret of Headwaters Software, Inc.      |
 |     and it is not to be copied, distributed, reproduced, published,  |
 |     or adapted without prior authorization                           |
 |     of Headwaters Software, Inc.                                     |
 |                                                                      |
*----------------------------------------------------------------------*/

/**
 * Useful generic utils
 *
 * This file contains useful utility methods for PHP that are not terribly
 * FISDAP-specific or tied to one particular page.
 *
 * Everything should be encapsulated as a method of an appropriately named class
 * (i.e. Handy____Utils) to keep our namespace nice and clean.
 *
 * @author Ian Young
 */

/**
 * Utils for manipulating arrays
 * @author Ian Young
 */
class HandyArrayUtils {
	/**
	 * Recursively take all the values and put them in a flag array.
	 * Each element is recursively processed in the order it is iterator over.
	 * Keys are ignored.
	 * @param array $array The array to flatten.
	 * @return array The flattened array.
	 */
	public static function flatten_values($array) {
		Assert::is_array($array);

		$values = array();
		foreach ($array as $key=>$value) {
			if (is_array($value)) {
				$values = array_merge($values, self::flatten_values($value));
			}
			else {
				$values[] = $value;
			}
		}

		return $values;
	}


	/**
	 * Get the last element of $array.
	 * @param array $array
	 * @return mixed the last element of the array
	 */
	public static function array_peek($array) {
		$element = end($array);
		reset($array);
		return $element;
	}

	/**
	 * Pushes (nondestructively) $element onto the $array, removing any old
	 * instances so that $element only occurs once.
	 * @param array $array
	 * @param mixed $element
	 * @return array an array with $element included
	 */
	public static function array_push_unique($array, $element) {
		if (($search = array_search($element, $array)) !== false) {
			unset($array[$search]);
		}
		$array[] = $element;
		return $array;
	}

	/**
	 * Works like PHP's in_array(), but will search recursively if it encounters
	 * arrays.
	 *
	 * @param mixed $needle The item to search for
	 * @param array $haystack The array to be searched
	 * @param boolean $strict If true, matching will happen with strict typing (===).
	 * @return boolean true if <var>$needle</var> occurs in <var>$haystack</var>.
	 */
	public static function in_array_recursive($needle, $haystack, $strict=false) {
		foreach ($haystack as $straw) {
			// if it's an array, descend and search recursively
			if ((is_array($straw) && HandyArrayUtils::in_array_recursive($needle,$straw))
					// otherwise check for a match depending on strictness
					|| ($needle === $straw)
					|| (!$strict && $needle == $straw)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * inarray() that is case sensitive.
	 * Both parameters must be able to be converted to strings.
	 * @param mixed $needle The item to search for.
	 * @param array $haystack The array to search.
	 * @return boolean TRUE if the needle was found in the haystack.
	 */
	public static function in_array_ci($needle, $haystack) {
		$needle_lc = strtolower((string) $needle);

		foreach ($haystack as $straw) {
			if (strtolower((string) $straw) == $needle_lc) return true;
		}

		return false;
	}

	/**
	 * Perform a binary search on a sorted list.
	 * The items in the list should be the same type as the search item.
	 * @param mixed $needle The item to search for.
	 * @param array $haystack The list to search.
	 * @return integer The index in the list of the item, or less than 0 if not
	 * found.
	 * @todo Merge {@link BinarySearch()} function into this
	 * @author Brian Peterson
	 * @depricated Don't use this. Use php's built in sort, for the love of god.
	 */
	public static function binary_search($needle, $haystack) {
		return BinarySearch($needle, $haystack);
	}

	/**
	 * Removes all values given values from array (unsets whole key=>value pairs)
	 * @param array Input array
	 * @param mixed Value to remove
	 */
	public static function remove_value_from_array(&$array, $val) {
		Assert::is_array($array);

		foreach ($array as $key => $value) {
			if ($value === $val) unset($array[$key]);
		}
	}

	/** Reorders array's element's order to ease vertical columnar display on the web (tabs: 4,4)
	 * Preserves array ids. Look at the 'picture' below to see what it does and understand why
	 * blank value is needed for easy 'looping' by function's users.
	 * (there is no easy and clean way of doing it through css as of November 2010 that I know of)
	 *  From:  1 2 3    To:	1 4 7
	 *	       4 5 6		2 5 <blank>
	 *	       7			3 6
	 * As result function will return array in the following order: 1 4 7 2 5 8 3 6
	 * Such ordered array can use standard flow with css.
	 * $author Maciej Bogucki
	 * $param array $options		 default value
	 * 		'empty_cell_value'		 ''				Can be set to some value, by default. Index > 9999 will mean it's dummy empty cell.
	 * 		'empty_cell_index_start'				default: 10000
	 * 		'skip_empty_cells'		 false			for reflow to work when columns > 2, there may be blank cells. They need to be marked
	 * 			(this function will insert them. They can be checked by: checking index value (default: >9999))
	 */

	public static function reorder_array_for_vertical_multicolumn_wrap ($array, $columns = 2, $options = array()) {
		Assert::is_true($columns > 0 && is_int($columns));
		Assert::is_array($array);

		// set defaults and read options
		$insert_empty_cells = (isset($options['skip_empty_cells'])) ? (!$options['skip_empty_cells']) : true;
		$empty_cell_value = (isset($options['empty_cell_value'])) ? $options['empty_cell_value'] : ''; //'EMPTY CELL';
		if (isset($options['empty_cell_index_start'])) {
			$empty_cell_id  = $options['empty_cell_index_start'];
			Assert::is_int($empty_cell_index_start);
		} else {
			$empty_cell_id = 10000;
		}


		$count = count($array);
		$rows = ceil($count / $columns);
		if ($count == 0) {
			return array();
		}

		// record positions of current indexes of array" pos starts at 0 ($pos is 0 and $row is 0, both could be changed to starting value)
		foreach ($array as $id => $value) {
			$pos[] = $id;
		}

		for ($row=0; $row<$rows; $row++) {
			for ($column=0; $column<$columns; $column++) {
				$curpos = $column * $rows + $row;
				if (isset($array[$pos[$curpos]])) {
					$ret[$pos[$curpos]] = $array[$pos[$curpos]];
				} else {	// empty cell
					if ($insert_empty_cells) {
						$ret[$empty_cell_id++] = $empty_cell_value;
					}
				}
			}
		}

		return $ret;
	}
}

/**
 * Utilities for dealing with server stuff.
 */
class HandyServerUtils {
	const LOCAL_HOST = '127.0.0.1';

	/**
	 * Determine the type of environment we are running in.
	 * @return string 'development', 'testing', 'unit_testing', or 'production'
	 */
	public static function get_environment() {
		$hostname = self::get_hostname();
		switch ($hostname) {
			case (preg_match('/fisdapoffice|hs\.internal/', $hostname) ? $hostname : !$hostname):
			case self::LOCAL_HOST:
				if (self::is_unit_testing()) {
					return 'unit_testing';
				}
				// If we're not in someone's home directory, we're staging
				if (!self::is_command_line() &&!preg_match('/^\/home\/\w+\//', $_SERVER['SCRIPT_FILENAME'])) {
					return 'testing';
				}
				return 'development';
			default:
				return 'production';
		}
	}

	/**
	 * Determine the valid environments we can run in.
	 * @return array The valid environment names.
	 */
	public static function get_all_environments() {
		$hostname = self::get_hostname();
		switch ($hostname) {
			case (preg_match('/fisdapoffice|hs\.internal/', $hostname) ? $hostname : !$hostname):
			case self::LOCAL_HOST:
				return array('development', 'unit_testing', 'testing');
			default:
				return array('production', 'testing');
		}
	}

	/**
	 * Retrieve the host name.
	 * @return string The lower case host name, or self::LOCAL_HOST if none.
	 */
	public static function get_hostname() {
		static $keys = array('HOSTNAME', 'HTTP_HOST');

		$name = self::LOCAL_HOST;
		foreach ($keys as $key) {
			if (!isset($_SERVER[$key])) continue;

			$s = trim($_SERVER[$key]);
			if ($s == '') continue;

			$name = $s;
			break;
		}

		return strtolower($name);
	}

	/**
	 * Determine if we are running from the command line or a web page.
	 * @return boolean TRUE if we are running from the command line.
	 */
	public static function is_command_line() {
		$command_line = (php_sapi_name() == 'cli');
		return $command_line;
	}

	/**
	 * Determine if we are unit testing.
	 * @return boolean TRUE if we are unit testing.
	 */
	public static function is_unit_testing() {
		return self::is_command_line() && str_ends_with($_SERVER['PHP_SELF'], 'phpunit');
	}

	/**
	 * Get the configuration options for this server.
	 *
	 * Will read from the config file for the section determined by {@link 
	 * get_environment()}.
	 * @param string $config_file The config file name, no path.
	 * @param boolean $allow_changes TRUE if the config should be mutable.
	 * @param string | null $env The environment.
	 * @return Zend_Config
	 * @todo We should be storing this in Zend_Registry or something instead of
	 * calling this function every time we need the config.
	 */
	public static function get_config($allow_changes = false, $env = null) {
		if (is_null($env)) {
			$env = self::get_environment();
		}

		$config = new Zend_Config_Ini('fisdap_config.ini', $env, array('allowModifications' => $allow_changes));

		return $config;
	}
}

class HandySessionUtils {

	/**
	 * Apply defaults to a list of parameters
	 *
	 * Accepts an array of variable _references_ and a matching array of default values.
	 * If any of the arguments is typed-equal to null, replaces it with the corresponding entry
	 * from default_list.
	 *
	 * Useful for allowing parameters to be passed as null explicitly to obtain default behavior.
	 *
	 * @author Ian Young
	 */
	public static function apply_defaults($arglist, $default_list) {

		foreach ($default_list as $index => $val) {
			if ($arglist[$index] === null) {
				$arglist[$index] = $val;
			}
		}
	}

}

/**
 * Utils for doing things at the level of bits and bytes
 * @author Ian Young
 */
class HandyBitUtils {

	/**
	 * Reduce the length of a hash
	 *
	 * Lets you shrink a hash value to a more manageable size while
	 * retaining as much of the uniqueness as we can.
	 * @param string $hash the hash to be shrunk
	 * @param int $target_length the length in characters to shrink it to
	 * @return string the new, shortened hash
	 */
	public static function hash_reduce($hash, $target_length) {
		if (strlen($hash) <= $target_length) {
			return $hash;
		} else {
			// Split the hash into two sections, reduce the second one recursively
			$hash1 = substr($hash, 0, $target_length);
			$hash2 = self::hash_reduce(substr($hash, $target_length), $target_length);
			// Now combine the two sections with XOR and return it as a hex string
			$newhash = hexdec($hash1) ^ hexdec($hash2);
			$newhash = dechex($newhash);
			return $newhash;
		}
	}

}

/**
 * @todo phputil/string_utils.inc
 */
class HandyStringUtils {

	/**
	 * Convert anything to a string without getting an error
	 * @param mixed
	 * @return string
	 */
	public static function anythingToString($param) {
		if (is_object($param)) {
			return get_class($param);
		} else {
			return (string)$param;
		}
	}

	/**
	 * Sanitize user input for the DB. Nothing's perfect, but this helps.
	 * @param string
	 * @return string
	 */
	public static function sanitizeStringForDatabase($param) {
		if ($param != null) {
			Assert::is_string($param);
			return strval(((isset($GLOBALS["___mysqli_ston"]) && is_object($GLOBALS["___mysqli_ston"])) ? mysqli_real_escape_string($GLOBALS["___mysqli_ston"], trim(stripslashes($param))) : ((trigger_error("[MySQLConverterToo] Fix the mysql_escape_string() call! This code does not work.", E_USER_ERROR)) ? "" : "")));
		}
		else return "";
	}

	/**
	 * Increments numerical value at the end of a string containing number
	 * Accepts any string including one without numbers or just numbers. Ex 'Joe' => 'Joe1', '123' => '124'
	 * Preserves leading 0-s in the strings  Ex: 'Joe001' => 'Joe002',  'Joe009' => 'Joe010'
	 * Optionally takes second argument with amount by which to increment
	 * @return string Input string with incremented
	 */
	public static function increment_string($strnum, $incby=1) {
		Assert::is_int($incby);

		// matches string that has at least one non numeric string followed by at least one numeric character
		//preg_match('/(^.*[^[0-9])([0-9]+$)/', $strnum, $matches);

		// matches any string splitting all last digits from anything at the beginning. Accepts just number or just string
		preg_match('/(^.*[^[0-9]|^)([0-9]*$)/', $strnum, $matches);
		$str = $matches[1];
		$num = $matches[2];
		// echo " $str - $num\n";
		$numlen = strlen($num);

		$num = str_pad($num+$incby, $numlen, '0', STR_PAD_LEFT);
		return $str.$num;
	}


	public static function split_string_and_number($strnum) {
		preg_match('/(^.*[^[0-9]|^)([0-9]*$)/', $strnum, $matches);
		return (array($matches[1], $matches[2]));
		// $str = $matches[1];	$num = $matches[2];
	}


}
