<?php

class Util_String {

	/**
	 * Pluralize a word.
	 * If the singular form ends in a 'y', the plural will end in 'ies'.
	 * @param string $singular The word or phrase in singular form (no trailing 's').
	 * @param int $n The number the word pertains to.
	 * @return The singular (n=1) or plural form (n != 1, an 's' is added).
	 * @deprecated should use Doctrine's Inflector class instead
	 */
	public static function pluralize($singular, $n, $plural = NULL) {
		Util_Assert::is_string($singular);
		Util_Assert::is_int($n);

		if ($n != 1) {
			// if a plural form is specified
			if ($plural) {
				$singular = $plural;
			}
			elseif (substr($singular, -1) == 'y') {
				$singular = substr($singular, 0, -1) . 'ies';
			}
			elseif (substr($singular, -1) == 'Y') {
				$singular = substr($singular, 0, -1) . 'IES';
			}
			else {
				$singular .= 's';
			}
		}

		return $singular;
	}

	/**
	 *	Converts from_this toThis
	 *	@param string input string
	 *	@return string converted string
	 */
	public static function camelizeNotFirst( $word )
	{
		if (empty ($word)) {
			return '';
		}
		$ret = self::camelize($word);
		$ret[0] = strtolower($ret[0]);
		return $ret;

		//$t = explode('_', $word);
		//return empty($t[1]) ? $t[0] : $t[0] . ucfirst($t[1]);
	}

	/**
	 * Converts from_this_format ToThisFormat
	 */
	public static function camelize( $word )
	{
		return str_replace(' ', '', ucwords(preg_replace('/[^A-Z^a-z^0-9]+/', ' ', $word)));
	}

	/**
	 *	Converts from_this$type%^ofFormat -. 'To This Type Of Format'
	 */
	public static function spaceWords ( $string )
	{
		return str_replace('  ', ' ', ucwords(preg_replace('/[^A-Z^a-z^0-9]+/', ' ', $string)));
	}

	// testing SpacingThisOut too
	public static function spaceAllWords ( $string )
	{
		return preg_replace('/^(.*[^\s])([A-Za-z])(.*)$/', '${1} ${2}${3}', $string);
	}

    /**
     * takes a list of comma-separated values and adds an "and" between the last two, with optional oxford comma
     * @param string $string
     * @param boolean $oxford whether or not to include the oxford comma, defaults to false
     *
     * @return string converted string
     */
    public static function addAndToList( $string, $oxford = false )
    {
        $search = ',';
        $replace = $oxford ? ', and' : " and";

        // reverses the string so the last comma is first, split it into two pieces at the first
        // comma, glue it back together with the reversed replacement string, then reverse the whole
        return strrev(implode(strrev($replace), explode($search, strrev($string), 2)));
    }

	/**
	 *	Converts special text entities (like "curly" quotes) to plain text
	 *	@param string input string
	 *	@return string converted string
	 */
	public static function convertSpecialCharacters( $string )
	{
		// Replace special entities with standard text ones
		$search = array(
				'&ldquo;', // 1. Left Double Quotation Mark “
				'&rdquo;', // 2. Right Double Quotation Mark ”
				'&lsquo;', // 3. Left Single Quotation Mark ‘
				'&rsquo;', // 4. Right Single Quotation Mark ’
				'&#039;',  // 5. Normal Single Quotation Mark '
				'&amp;',   // 6. Ampersand &
				'&quot;',  // 7. Normal Double Qoute
				'&lt;',    // 8. Less Than <
				'&gt;',    // 9. Greater Than >
				'&nbsp;',  // 10. space
				'&ndash;', // 11. n-dash 
				'&mdash;'  // 12. m-dash 
			       );

		$replace = array(
				'"', // 1
				'"', // 2
				"'", // 3
				"'", // 4
				"'", // 5
				"&", // 6
				'"', // 7
				"<", // 8
				">", // 9
				" ", // 10
				"-", // 11
				"-", // 12
				);

		// Fix the String
		$fixed_string = htmlentities($string, ENT_QUOTES, 'UTF-8');
		$fixed_string = str_replace($search, $replace, $fixed_string);
		return $fixed_string;	
	}

	/**
	 * detects whether a string is a valid date
	 *
	 * @param $date_string
	 *
	 * @return bool
	 */
	public static function isValidDate($date_string) {
		$valid = true;
		
		$date_array = explode("/", $date_string);
		
		// if there are not three components to the date, it's not valid
		if (count($date_array) != 3) {
			$valid = false;
		}
		
		// if the year portion is not 4 digits, it's not valid
		if (strlen($date_array[2]) != 4) {
			$valid = false;
		}
		
		// if this is not a valid date when put together, it's not valid
		if (!checkdate($date_array[0], $date_array[1], $date_array[2])) {
			$valid = false;
		}
		
		return $valid;
	}

}