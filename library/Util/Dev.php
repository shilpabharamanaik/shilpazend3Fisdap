<?php

/**
 *	Developer helpers
 *	@author Maciej Bogucki
 */
class Util_Dev
{
    /**
     *	Adds to Db_ShowSqlResults: for array which have varying columns from row to row
     *	It makes first row contain all column names to be usable by Db_ShowSqlResults
     *	@param array
     *	@param mixed:
     *		if array	set of options
     *		if boolean	option 'returnOnly' (optional) $options=false echoes out or returns formatted html

     */
    public static function showArray($results, $options = false)
    {
        // if boolean $options mean 'returnOnly' option
        if (!is_array($options)) {
            $options['returnOnly'] = (bool) $options;
        }
        
        // inform if not array/object
        if (!is_array($results) || is_object($results)) {
            echo "<h2>Util_Dev::showArray : \$results is not array <h2>";
            var_dump($results);
            return;
        }
        
        // columns
        $columns = array();
        foreach ($results as $row => $fields) {
            if (!is_array($fields)) {
                return self::showOneDimensionalArray($results, $options);
            }
            foreach ($fields as $column => $val) {
                if (!isset($columns[$column])) {
                    $columns[$column] = '';
                }
            }
        }
        
        // apply column to results array
        foreach ($columns as $column => $nothing) {
            if (!isset($results[0][$column])) {
                $results[0][$column] = '';
            }
        }
        
        // run Db_ShowSqlResults
        return self::showResults($results, $options);
    }
    
    public static function printArray($results, $returnOnly = false)
    {
        //echo "<h2>Use Util_Dev::showArray() not printArray()</h2>";
        self::showArray($results, $returnOnly);
    }

    public static function showOneDimensionalArray($results, $options = false)
    {
        // if boolean $options mean 'returnOnly' option
        if (!is_array($options)) {
            $options['returnOnly'] = (bool) $options;
        }
        
        $ret = "<table class='show-border' border=1>";
        
        // headings
        if (isset($options['headings'])) {
            $ret .= "<tr><th>" . key($options['headings']) . "</th><th>" . current($options['headings']) . "</th></tr>\n";
        }
        
        foreach ($results as $column => $val) {
            $ret .= "<tr><td>$column</td><td>$val</td></tr>";
        }
        //echo "<h2>Showign One dimentional array</h2>";
        $ret .= "</table>";
        
        if (!$options['returnOnly']) {
            echo $ret;
        }
        return $ret; //print_r($results, true);
    }
    
    
    public static function Db_ShowSqlResults($results, $options = false)
    {	// was $returnOnly
        self::ShowResults($results, $options);
    }
    
    /**
     *	Displays results of query ran using Util_Db::getSQLResults / $db->fetchAll
     *	@param array $results
     *	@param mixed:
     *		if array	set of options
     *		if boolean	option 'returnOnly' (optional) $options=false echoes out or returns formatted html
     */
    public static function showResults($results, $options = false)
    {
        if (!isset($results[0])) {
            return;
        }
        
        // boolean $options mean 'returnOnly' option
        if (!is_array($options)) {
            $options['returnOnly'] = (bool) $options;
        }
        
        $flNames = array();
        // get column names
        foreach ($results[0] as $fieldName => $val) {
            $flNames[] = $fieldName;
        }
        
        // print columns
        $ret = "<table border=1>";
        //$ret = "<table class='show-border' border=1>";
        $ret .= "<tr class='show-border'>";
        foreach ($flNames as $flName) {
            $ret .= "<th class='show-border'>$flName</th>";
        }
        $ret .= "</tr>";
        
        // print results
        foreach ($results as $row => $data) {
            $ret .= "<tr class='show-border'>\n";
            foreach ($flNames as $flName) {
                $ret .= "\t<td class='show-border'>" . $data[$flName] . "</td>\n";
            }
            $ret .= "</tr>\n";
        }
        
        // return or output?
        $ret .= "</table>";
        if ($options['returnOnly']) {
            return $ret;
        } else {
            echo $ret;
        }
    }

    /**
     *	Gets / outputs all tables for schema
     *	@param string (optional) schema
     *	@param boolean $options=false (optional)
     */
    public static function Db_ShowAllTableDefinitions($schema = null, $options = false)
    {
        // boolean $options mean 'returnOnly' option
        if (!is_array($options)) {
            $options['returnOnly'] = (bool) $options;
        }
        
        $ret = '';
        
        // schema part of query
        $schemaPart = (is_null($schema)) ? '' : ' from `' . $schema . '`';

        // get all tables from schema
        $tables = Util_Db::getSQLResults('show tables' . $schemaPart . ';');
        
        // get definitions for all tables
        foreach ($tables as $i => $tableinfo) {
            list($id, $tableName) = each($tableinfo);
            $ret .= '<br/><h2>' . $tableName . "</h2>";
            $tableSchema = Util_Db::getSQLResults("desc $tableName;");
            $ret .= self::DB_ShowSqlResults($tableSchema, true);
        }
        
        // return or output?
        if ($options['returnOnly']) {
            return $ret;
        } else {
            echo $ret;
        }
    }
    
    
    /**
     *	Just vardump/print_r with title, <pre> and Doctrine safe
     *	@param mixed $var
     *	@param string $title
     *	@param mixed $varDump, print_r is default
     */
    public static function Dump($var, $title='', $varDump=false)
    {
        if ($title) {
            echo "<h2>$title</h2>";
        }
        echo "<pre>";
        
        // Doctrine safe toArray, now recursive
        $var = self::convertToArray($var);
        
        //old not-recursive toArray:
        //if (method_exists($var, 'toArray')) { $var = $var->toArray(); }
        
        if ($varDump) {
            var_dump($var);
        } else {
            print_r($var);
        }
        echo "</pre>";
    }
    
    /**
     *	Uses toArray recursively on all arrays.
     *	It does not attempt to travers objects.
     */
    public static function convertToArray($var)
    {
        // object with toArray method
        if (is_object($var) && method_exists($var, 'toArray')) {
            return $var->toArray();
        }
        
        // arrays or objects without toArray
        if (is_array($var)) {
            foreach ($var as $i => $vals) {
                $var[$i] = self::convertToArray($vals);
            }
        } elseif (is_object($var)) {
            foreach ($var as $i => $vals) {
                $var->$i = self::convertToArray($vals);
            }
        }
        return $var;
    }
    
    
    
    
    // NOT FINISHED.. New version of convertToArray
    // trying to prevent endless looping of self referenced objects
    // pointer array used to avoid endless referencing in loops
    protected static $pointers = array();
    protected static $pointersCount = array();
    
    public static function convertToArray2($var)
    {
        self::$pointers = array();
        self::$pointersCount = 0;
        
        $ret = self::convertToArrayLoop($var);
        
        //$t = \Fisdap\EntityUtils::getEntity('User', 1);
        //self::$pointers[] = $t;
        //$test = in_array($t, self::$pointers) ? "IN Array" : "NOT IN ARRAY";

        $count = count(self::$pointers);
        if ($count) {
            echo "<h2>$test  Pointers count: </h2>" . $count;
            
            //var_dump(self::$pointers);
            var_dump($ret);
            die;
        }
        return $ret;
    }
    
    /**
     *	Uses toArray recursively on all arrays.
     *	It does not attempt to travers objects.
     */
    protected static function convertToArrayLoop($var)
    {
        // object with toArray method
        if (is_object($var) && method_exists($var, 'toArray')) {
            return $var->toArray();
        }
        
        // arrays or objects without toArray
        if (is_array($var)) {
            foreach ($var as $i => $vals) {
                $var[$i] = self::convertToArray($vals);
            }
        } elseif (is_object($var)) {
            if (self::$pointersCount > 100) {
                return array("Pointers count exceeded", $var);
            }
            if (!in_array($var, self::$pointers)) {
                self::$pointers[] = $var;
                self::$pointersCount++;
                
                //trying to prevent looping through changing object
                $props = array();
                foreach ($var as $i => $vals) {
                    $props[] = $i;
                }
                foreach ($props as $prop) {
                    $var->$prop = self::convertToArray($var->$prop);
                }
            }
        }
        return $var;
    }
    
    
    
    
    
    
    
    
    
    // DEBUG
    public static function showMessages($object)
    {
        $messages = $object->getMessages();
        
        \Util_Dev::printArray($messages);
    }
    
    /**
     *	Gets viewable bits from bitfield value
     *	@return string 0 and 1's represending $bitField
     */
    public function showBitfieldBits($bitField, $bits = 16)
    {
        $max = pow(2, $bits);
        $ret = '';
        $check = 0;
        for ($i=1; $i<$max; $i*=2) {
            //echo "Bits: $bitField $i $char\n";
            $char = ($bitField & $i) ? '1' : '0';
            //if ($char) { $check+=$i; }
            //echo "Bits: $bitField $i $char Check: $check\n";
            $ret = $char . $ret;
        }

        return $ret;
    }
}
