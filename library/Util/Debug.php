<?php

/**
 *	Methods starting with Ht: Html output
 *	@author Maciej Bogucki
 */
class Util_Debug
{
	protected static $vards = array();
	const MODE_AUTO = 0;	// does print_r on non scalar values
	const MODE_TEXT = 1;
	const MODE_PRINT_R = 2;
	const MODE_VAR_DUMP = 3;
	
	/**
	 *	Collects debug info from anywhere
	 *	@param mixed $value
	 *	@param string $title - label value
	 *	@param string vardump mode: A-Auto, P-PrintR, V-Vardump
	 *	
	 *	Usage:
	 *	  1. through your scripts: Util_Dev:vard($whatever I want to output)
	 *	  2. get output: echo Util_Dev::getVarDumps
	 */
	public static function vard($value, $title = '', $vardumpMode = 'A')
	{
		$modes = array(
			'A' => MODE_AUTO,
			'P' => MODE_PRINT_R,
			'V' => MODE_VAR_DUMP,
			'T' => MODE_TEXT,
		);
		$mode = (isset($modes[$vardumpMode])) ? $modes[$vardumpMode] : MODE_AUTO;
		
		$p = self::$vards[] = new stdClass();
		$p->value = $value;
		$p->title = $title;
		$p->mode = $mode;
	}
	
	public static function getVarDumps()
	{
		$ret = '<center><table><tr><th>Memo</th><th>Message</th></tr>';
		foreach (self::$vards as $v) {
			$mode = $v->mode;
			if ($mode == MODE_AUTO) {
				$mode = (is_scalar($v->value)) ? MODE_TEXT : MODE_PRINT_R;
			}
			if ($mode=MODE_TEXT) {
				$val = $v->value;
			} else if ($mode == MODE_PRINT_R) {
				$val = print_r($v->value);
			} else if ($mode == MODE_VAR_DUMP) {
				$val = var_export($v->value, true);
			}
			$ret .= '<tr><td>' . $v->title . '</td><td>' . $val . "</td></tr>\n";
		}
		$ret .= '</table></center>';
		return $ret;
	}
	
	/**
	 *	Misc small output methods
	 */
	public static function HtPrArrHead( $headItems, $addTableStart = true)
	{
		if ($addTableStart) {
			echo "\n<table>";
		}
		echo "<tr>";
		foreach ($headItems as $item) {
			echo "\t<th>$item</th>\n";
		}
		echo "</tr>\n";
	}
	

	public static function HtPrArr( $items, $addTable = true)
	{
		echo "<tr>";
		foreach ($items as $item) {
			echo "\t<td>$item</td>\n";
		}
		echo "</tr>\n";
	}
	
	
	public static function HtPr( $var )
	{
		echo "<pre>\n";
		print_r($var);
	}
	
}

?>