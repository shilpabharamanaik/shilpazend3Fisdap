<?php

class Util_Array
{
    
    /**
     *	Converts multidimensional array to class.
     *	array key names need to be valid as class property names
     *	@param array
     */
    public static function arrayToObject($array)
    {
        $obj = new stdClass();
        
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_array($value)) {
                    $obj->$key = self::arrayToObject($value);
                } else {
                    $obj->$key = $value;
                }
            }
        }
        
        return $obj;
    }
    
    
    /**
     * Recursive function to flatten an array of arrays
     * @param array $array
     */
    public static function flatten($array)
    {
        if (!is_array($array)) {
            // nothing to do if it's not an array
            return array($array);
        }

        $result = array();
        foreach ($array as $value) {
            // explode the sub-array, and add the parts
            $result = array_merge($result, self::flatten($value));
        }

        return $result;
    }
    
    /**
     * Recursive function to count the number of elements in a nested array
     * @param array $array
     */
    public static function countNested($array)
    {
        $return = self::flatten($array, $return);
        return count($return);
    }
    
    /** Reorders array elements order to ease vertical columnar display on the web (tabs: 4,4)
     * Preserves array ids. Look at the 'picture' below to see what it does and understand why
     * blank value is needed for easy 'looping' by function's users.
     * (there is no easy and clean way of doing it through css as of November 2010 that I know of)
     *  From:  1 2 3    To:	1 4 7
     *	       4 5 6		2 5 <blank>
     *	       7			3 6
     * As result function will return array in the following order: 1 4 7 2 5 8 3 6
     * Such ordered array can use standard flow with css.
     * @author Maciej Bogucki
     * @param array $options		 default value
     * 		'empty_cell_value'		 ''				Can be set to some value, by default. Index > 9999 will mean it's dummy empty cell.
     * 		'empty_cell_index_start'				default: 10000
     * 		'skip_empty_cells'		 false			for reflow to work when columns > 2, there may be blank cells. They need to be marked
     * 			(this function will insert them. They can be checked by: checking index value (default: >9999))
     * 			reorder_array_for_vertical_multicolumn_wrap reorderArrayForVerticalMulticolumnWrap
     */
    public static function reorderArrayForVerticalMulticolumnWrap($array, $columns = 2, $options = array())
    {
        Util_Assert::is_true($columns > 0 && is_int($columns));
        Util_Assert::is_array($array);

        // set defaults and read options
        $insert_empty_cells = (isset($options['skip_empty_cells'])) ? (!$options['skip_empty_cells']) : true;
        $empty_cell_value = (isset($options['empty_cell_value'])) ? $options['empty_cell_value'] : ''; //'EMPTY CELL';
        if (isset($options['empty_cell_index_start'])) {
            $empty_cell_id  = $options['empty_cell_index_start'];
            Util_Assert::is_int($empty_cell_index_start);
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
    
    /**
     *	sorts by 2+ dimensional array by 'column'
     *	Example:
     *	array
     *		307 =>
     *		  array
     *			'name' => 'Maciej',
     *			'year' => 1975,
     *		123 =>
     *		  array
     *		    'name' => 'I will be first'
     *	Will return same array in order: 123, 307..
     */
    public static function sortByColumn(&$arr, $column)
    {
        self::$sortByColumn = $column;
        uasort($arr, "self::columnSortCompare");
    }
    public static function columnSortCompare($a, $b)
    {
        return strcasecmp($a[self::$sortByColumn], $b[self::$sortByColumn]);
    }
    protected static $sortByColumn;
    
    // takes a comma-separated list and returns a cleaned up array
    public static function getCleanArray($csl)
    {
        // strip out all extraneous commas and spaces
        $clean_array = array();
        $raw_array = explode(",", $csl);
        foreach ($raw_array as $item) {
            // if this is clean, add it to the array
            $trimmed = trim($item);
            if ($trimmed != "") {
                $clean_array[] = $trimmed;
            }
        }
        return $clean_array;
    }
}
