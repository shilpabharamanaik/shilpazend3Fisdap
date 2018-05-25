<?php

class Util_File
{
	
	
	/**
	 * From: http://snippets.dzone.com/posts/show/155
	 */
	function directoryToArray($directory, $recursive=false) {
		$array_items = array();
		if ($handle = opendir($directory)) {
			while (false !== ($file = readdir($handle))) {
				if ($file != "." && $file != "..") {
					if (is_dir($directory. "/" . $file)) {
						if($recursive) {
							$array_items = array_merge($array_items, directoryToArray($directory. "/" . $file, $recursive));
						}
						$file = $directory . "/" . $file;
						$array_items[] = preg_replace("/\/\//si", "/", $file);
					} else {
						$file = $directory . "/" . $file;
						$array_items[] = preg_replace("/\/\//si", "/", $file);
					}
				}
			}
			closedir($handle);
		}
		return $array_items;
	}
	
	
	/**
	 *	Searches file and returns lines that match pattern
	 *	@param string $file path to a file
	 *	@param mixed (string or array - one or multiple) regular expression pattern(s) to match for
	 *		when multiple patterns are used, results will be returned in array. Indexes of results
	 *		will match indexes or patterns. Ex:
	 *			$pattern = array('one' => '/one pattern/', 'two' => 'second pattern')
	 *			results will be found in array ('one' => array or results, 'two' => array of results)
	 *	@return mixed	===false if file could not be opened
	 *					array of results (empty array for no results)
	 */
	function findInFile($file, $pattern)
	{
		$fh = fopen($file, 'r');
		// file could not be opened
		if (!$fh) {
			return false;
		}
		
		$ret = array();
		
		while (!feof($fh)) {
			$line = fgets($fh, 4096);
			if (is_array($pattern)) {
				foreach ($pattern as $patternId => $onePattern) {
					if (preg_match($onePattern, $line)) {
						$ret[$patternId][] = $line;
					}
				}
			} else {
				if (preg_match($pattern, $line)) {
					$ret[] = $line;
				}
			}
		}
		
		fclose($fh);
		
		return $ret;
	}
}