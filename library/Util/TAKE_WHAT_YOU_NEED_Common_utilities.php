<?php

/*
    TAKE OUT WHAT YOU NEED OUT OF HERE AND PUT IT WITHIN APPROPRIATE
    Util or Model file
    - Change name to zend style
    - Document source
*/

/**
 * Was: common_utilities.inc - Common Utilities
 *
 * Includes:
 *
 * @package CommonInclude
 * @author Warren Jacobson
 */
//
//require_once('phputil/handy_utils.inc');
//require_once('phputil/classes/FisdapErrorHandler.inc');
//
///**
// * Static Utility Methods
// */
//
//class common_utilities {
//
//	public static $unique_suffix = 1;
//
//	/**
//	 * Generate a unique suffix for use on various common elements
//	 *
//	 * @return string
//	 */
//
//	public static function get_unique_suffix() {
//
//		$suf = self::$unique_suffix++;
//
//		return "$suf";
//
//	}
//
//	/**
//	 * Get the name of the script
//	 */
//
//	public static function get_scriptname() {
//
//		$scriptname = $_SERVER["SCRIPT_NAME"];
//
//		return $scriptname;
//
//	}
//
//	/**
//	 * Get the prefix of the script
//	 */
//
//	public static function get_scriptprefix() {
//
//		$scriptprefix = $_SERVER["SCRIPT_NAME"];
//		$scriptprefix = $scriptprefix . '_';
//
//		return $scriptprefix;
//
//	}
//
//	/**
//	 * Get a script's session value
//	 */
//
//	public static function get_scriptvalue($name) {
//
//		require_once "phputil/session_data_functions.php"; // Session variable function library
//
//		$value = get_session_value(self::get_scriptprefix() . $name);
//
//		return $value;
//
//	}
//
//	/**
//	 * Set a script's session value
//	 */
//
//	public static function set_scriptvalue($name,$value) {
//
//		require_once "phputil/session_data_functions.php"; // Session variable function library
//
//		$value = set_session_value(self::get_scriptprefix() . $name,$value);
//
//	}
//
//	/**
//	 * Delete a script's session value
//	 */
//
//	public static function delete_scriptvalue($name) {
//
//		require_once "phputil/session_data_functions.php"; // Session variable function library
//
//		delete_session_value(self::get_scriptprefix() . $name);
//
//	}
//
//	/**
//	 * Determine if a script's session value is set
//	 */
//
//	public static function is_scriptvalue($name) {
//
//		require_once "phputil/session_data_functions.php"; // Session variable function library
//
//		$isset = is_session_value(self::get_scriptprefix() . $name);
//
//		return $isset;
//
//	}
//
//	/**
//	 * Link to a help bubble pop up window
//	 */
//
//	public static function get_bubblelink($section_name,$bubble_name,$linktext) {
//
//		$output = null; // The entire link to the help bubble pop up window
//
//		$fwr = FISDAP_WEB_ROOT; // Absolute path to web root so that this code is portable
//		$onclick_url = $fwr . "displayhelpbubble.html?section=" . $section_name . "&amp;name=" . $bubble_name;
//		$onclick_event = "myWindow = window.open('" . $onclick_url . "','Help','height=400,width=600,resizable=0,scrollbars=0');";
//		$onclick_event .= "myWindow.focus(); return false;";
//
//		$output = '&nbsp;';
//
//		if ($linktext != null) {
//			$output .= '<a href="#" onclick="' . $onclick_event . '" style="font-style: italic;">';
//			$output .= $linktext;
//			$output .= '</a>';
//		}
//		else {
//			$output .= '<a href="#" onclick="' . $onclick_event . '">';
//			$inline_css = "height: 13px; width: 13px; border: none;";
//			$output .= '<img style="' . $inline_css . '" src="' . $fwr . 'images/questionmark.gif" alt="Help">';
//			$output .= '</a>';
//		}
//
//		return $output;
//
//	}
//
//	/**
//	 * Convert a MySQL date into a PHP timestamp
//	 */
//
//	public static function convert_mysqldate($mysqlDate) {
//
//		list($year,$month,$day) = explode('-',$mysqlDate);
//		$phpDate = mktime(12,0,0,$month,$day,$year);
//
//		return $phpDate;
//
//	}
//
//	/**
//     * Determine if an e-mail address is correctly formatted.
//     * @param string|null $email The e-mail address, should be trimmed.
//     * @return boolean TRUE if the address is valid.
//	 */
//	public static function isValidEmailAddress($email) {
//        // This code was taken from webtoolkit.info/php-validate-email.html
//        // This is NOT a complete validator, it misses some characters and
//        // quoted strings...There is a complete validator on google and other
//        // sites but they come with licenses.
//        if (is_null($email)) return false;
//
//        $valid = eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$",
//            $email);
//        return $valid;
//	}
//
//	/**
//	 * Display an Error Message from within a Function / Method
//	 *
//	 * @author Warren Jacobson
//	 * @author Eryn O'Neil
//	 * @author Ian Young
//	 * @deprecated 06/2009 in favor of {@link FisdapLogger}
//	 * @todo after dev5 is up, turn on deprecation warnings
//	 */
//
//	public static function displayFunctionError($msg, $fullbacktrace=null) {
//		$logger = FisdapLogger::get_logger();
//		// First warn that this function is deprecated
//		//$logger->deprecated('displayFunctionError is deprecated, use the FisdapLogger instead.');
//
//		// If we're reporting errors as exceptions, trigger an error here
//		if (FisdapErrorHandler::$errors_as_exceptions) {
//			trigger_error($msg, E_USER_WARNING);
//		}
//
//		// Now log the actual error
//		$logger->log($msg, FisdapLogger::ERR, array('trace_offset' => 1));
//
//	}
//
//	/**
//	 * returns html tag for appropriate progress bar`
//	 */
//
//	public static function show_progressbar($points) {
//
//		$fwr = FISDAP_WEB_ROOT; // Absolute path to web root so that this code is portable
//
//		$points_group = (($points - ($points % 50))/50)+1; // which image to use
//		if ($points <= 0) {
//			$points_group = 0;
//		}
//		if ($points > 1000) {
//			$points_group = 22;
//		}
//		$title_offset = $points_group*7+38;
//		if ($points_group == 0) { $title_offset += 5; }
//
//		if(strpos($_SERVER['HTTP_USER_AGENT'],"MSIE")) {
//			$title_offset -= 6;
//			if ($points_group == 0) { $title_offset += 5; }
//		}
//		$image_url = $fwr . "images/progress" . $points_group . ".png";
//
//		$link_url = $fwr . "testing/prog_pop_summary.html";
//
//		$inline_css = "border: none;";
//
//		$output  = "<div style='float:left;width:12em;text-align:center;margin:5px;'>\n";
//		$output .= "<a href='" . $link_url . "' target='_top' style='text-decoration:none;'>\n";
//		$output .= "<span class='mediumboldtext'>FISDAP Rewards Point Balance</span><br>";
//		$output .= '<img style="' . $inline_css . '" src="' . $image_url . '">';
//		$output .= "<br><span class='smalltext'>Progress towards future FISDAP discounts</span>\n";
//		$output .= "<div class='smalltext' ";
//		$output .= "style='position:relative;left:37px;top:-".$title_offset."px;padding:0px;'>$points pts</div>\n";
//		$output .= "</a>\n";
//		$output .= "</div>\n";
//
//		return $output;
//
//	}
//
//	/**
//	 * Determine if this browser is using IE or not (set via phputils/logger_submit.php)
//	 */
//
//	public static function is_ie() {
//
//		$is_ie = $_SESSION[SESSION_KEY_PREFIX . 'is_ie'];
//
//		return $is_ie;
//
//	}
//
//	/**
//	 * Get the dimension of the client's screen in pixels (set via phputils/logger_submit.php)
//	 */
//
//	public static function get_screenDimension() {
//
//		$screenDimension = $_session[SESSION_KEY_PREFIX . 'screenDimension'];
//
//		return $screenDimension;
//
//	}
//
//	/**
//	 * Recursive function to flatten an array of arrays
//	 * @deprecated USE Util_Array::flatten
//	 */
//	public static function array_flatten($array, $return) {
//		for($x = 0; $x <= count($array); $x++) {
//			if(is_array($array[$x])) {
//				$return = self::array_flatten($array[$x],$return);
//			} else {
//				if($array[$x]) {
//					$return[] = $array[$x];
//				}
//			}
//		}
//		return $return;
//	}
//
//	/**
//	 * returns an array of AccountType objects representing all of the account types this program deals with
//	 */
//	/**
//	 * @todo Move this to model_factory
//	 */
//	public static function program_account_types($prog_id) {
//		//require_once("phputil/classes/AccountType.inc");
//		$connection = &FISDAPDatabaseConnection::get_instance();
//
//		$select = "SELECT A.AccountType_label, A.AccountType_shortdesc,
//			A.AccountType_desc, A.AccountType_id from ProgramAccountTypeData P, AccountTypeTable A";
//		$select .= " WHERE P.Program_id=$prog_id AND P.AccountType_id=A.AccountType_id";
//
//		$result = $connection->query($select);
//
//		$retarr = array();
//		foreach ($result as $res) {
//			$retarr[] = array(
//				'label'=>$res['AccountType_label'],
//				'desc'=>$res['AccountType_desc'],
//				'shortdesc'=>$res['AccountType_shortdesc'],
//				'id'=>$res['AccountType_id']);
//		}
//		return $retarr;
//
//	}
//
//	/**
//	 * returns the label for the given account type
//	 */
//	public static function get_cert_shortdesc($cert_label) {
//		$connection = &FISDAPDatabaseConnection::get_instance();
//
//		$select = "SELECT AccountType_shortdesc FROM AccountTypeTable where AccountType_label='$cert_label'";
//		$result = $connection->query($select);
//		if(count($result)==1) {
//			return $result[0]["AccountType_shortdesc"];
//		} else {
//			return false;
//		}
//	}
//
//	public static function get_student_cert_lvl($student) {
//		$connection = &FISDAPDatabaseConnection::get_instance();
//		$select = "SELECT AccountType from SerialNumbers where Student_id=$student";
//		$result = $connection->query($select);
//		if(count($result)==1) {
//			return $result[0]["AccountType"];
//		} else {
//			return false;
//		}
//	}
//
//	/**
//	 * checks to see if a given string is a valid filename in windows
//	 * borrowed from stackoverflow.com
//	 */
//	public static function is_valid_filename($name) {
//		$valid = preg_match("/^[^\\/?*:;{}\\\\]+$/", $name);
//		return $valid;
//	}
//
//
//	/**
//	 * @deprecated HandySessionUtils::apply_defaults()
//	 */
//	public static function apply_defaults($arglist, $default_list) {
//		require_once("phputil/handy_utils.inc");
//		return HandySessionUtils::apply_defaults($arglist, $default_list);
//	}
//
//	/**
//	 * Given an array of words this function inserts gramatically correct
//	 * delimiters.
//	 *
//	 * I.E. ($prefix and $suffix are null)
//	 * case                              returns
//	 * --------------------------------  --------------------------------
//	 * array("one")                      One Only
//	 * array("one","two")                One and Two
//	 * array("one","two","three")        One, Two, and Three
//	 * array("one","two","three","four") One, Two, Three, and Four
//	 *           .
//	 *           .
//	 *           .
//	 */
//	public function generate_display_list_phrase($prefix, $show, $suffix) {
//
//		$show_phrase = "";
//		$delimiter = "";
//		$show_max = count($show);
//
//		for($i=0; $i < $show_max; $i++) {
//			$show_phrase .= ucfirst($show[$i]);
//			if($show_max > 1) {
//				if ($i == $show_max-2) {
//
//					if ($show_max > 2) {
//						$delimiter = ", and ";
//					} else {
//						$delimiter = " and ";
//					}
//
//				} else {
//
//					if ($i <= $show_max-2) {
//						$delimiter = ", ";
//					} else {
//						$delimiter = " ";
//					}
//
//				}
//			}
//			$show_phrase .= $delimiter;
//		}
//
//		$show_phrase = $show_phrase;
//		$show_phrase .= " ".$suffix;
//
//		if (count($show) == 1) {
//			$show_phrase .= " Only";
//		}
//
//		return $prefix." ".$show_phrase;
//
//	}
//
//
//
//
//
//}
