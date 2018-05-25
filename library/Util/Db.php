<?php

class Util_Db
{
	protected static $dbInstance;

	protected static $dbLoggingOn = false;
	
	// schema from zend registry or set by user
	protected static $schema;

	protected static $existingTables = array();

	// current db connection = db or dbadmin
	protected static $currentDbConn = 'db';
	
	protected static $entitiesInfo;
	
	public static function mapEntitiesToTables()
	{
		$schema = self::getTableInformationSchema();
		\Util_Dev::Dump($schema);
	}
	
	/**
	 *	PERFORMANCE WARNING: users getEntitiesList which SCANS ALL ENTITY FILES
	 */
	public static function getEntityNameByTableName($tableName)
	{
		$entInfo = self::getEntitiesList();
		
		return isset($entInfo[$tableName]) ? $entInfo[$tableName]['entityName'] : false;
	}

	/**
	 *	Get names of entities which were not considered by self::getEntitiesList()
	 *		as table mapping entities. 
	 *	PERFORMANCE WARNING: users getEntitiesList which SCANS ALL ENTITY FILES
	 *
	 *	@return array list of filenames of entities with indexes matching self::$entitiesInfo
	 *		so other entities info could be obtained from there.
	 */
	public static function getExcludedEntityFilenames()
	{
		$entInfo = self::getEntitiesList();
		
		
		foreach ($entInfo as $tableName => $vals) {
			if (is_int($tableName)) {
				$ret[$tableName] = (empty($vals['entityName'])) ?
					'(no entity name), Filename: ' . $vals['filename'] : $vals['entityName'];
			}
		}
		return $ret;
	}
	
	
	/**
	 *	Returns mapping of database table to entities
	 *	
	 *	PERFORMANCE WARNING: this actually scans all the entity files.
	 *	Meant to be admin only method.
	 *
	 *	@todo Support default table naming.
	 *		Currently this method requires table name to be set via @Table
	 */
	public static function getEntitiesList()
	{
		if (is_null(self::$entitiesInfo)) {
			$entityFiles = \Util_File::directoryToArray('../library/Fisdap/Entity/');
			
			$file = current($entityFiles);
			
			$patterns = array('isEntity' => '/\@Entity/',
							  'entityName' => '/^class /',
							  'tableName' => '/@Table/'
							  );	//'entityName2' => '/class .* extends/'
			
			foreach ($entityFiles as $id => $path) {
				$res = \Util_File::findInFile($path, $patterns);
				
				// table name, also: $index will be table name or $id (if no table)
				if (is_array($res['tableName'])) {
					$tableString = explode('"', $res['tableName'][0]);
					$index = $tableString[1];
					self::$entitiesInfo[$index]['tableName'] = $tableString[1];
				} else {
					$index = $id;
					self::$entitiesInfo[$index]['tableName'] = '';
				}
				
				
				$filename = substr(strrchr($path, '/'), 1); //substr($path, strrchr($path, '/'));
				self::$entitiesInfo[$index]['filename'] = $filename;
				
				self::$entitiesInfo[$index]['isEntity'] = is_array($res['isEntity']); // ?	implode('<br/>', $res['isEntity']) : '';
	
				if (is_array($res['entityName'])) {
					$entString = explode(' ', $res['entityName'][0]);
					self::$entitiesInfo[$index]['entityName'] = $entString[1];
				} else {
					self::$entitiesInfo[$index]['entityName'] = '';
				}
				
				//if (is_array($res['tableName'])) {
				//	$tableString = explode('"', $res['tableName'][0]);
				//	self::$entitiesInfo[$index]['tableName'] = $tableString[1];
				//} else {
				//	self::$entitiesInfo[$index]['tableName'] = '';
				//}
				//
				//self::$entitiesInfo[$index]['tableName'] = is_array($res['tableName']) ?  implode('<br/>', $res['tableName']) : $res['tableName'];
				
				//self::$entitiesInfo[$index]['entityName'] = is_array($res['entityName']) ?  implode('<br/>', $res['entityName']) : '';
			}
			
			//\Util_Dev::showArray(self::$entitiesInfo);
			//\Util_Dev::Dump(array(self::$entitiesInfo, $entityFiles, $file, $res));
		}
		
		return self::$entitiesInfo;
	
		/* Unit test with:
			for all results:	if ($result: isEntity) -> load entity by name -> get table name -> match it
		*/
	}
	
	
	/**
	 * METHODS ABOVE USE DOCTRINE
	 *
	 * 		#####  D I V I D E R  #####
	 *
	 * METHODS BELOW ARE NOT DOCTRINE-DEPENDENT
	 */

	
	/**
	 *	Takes database results and puts them inside $treeColumns
	 *	@param array $results
	 *	@param array $treeColumns
	 *	@param boolean $takeLastLevelOutOfArray (carefull: if set to true it means
	 *		that if there are multiple results for the $treeColumns specified,
	 *		only last one will be return (it will overwrite previous results))
	 *	@return array $restructuredResults
	 *	Example: Let's say query result row has columns: 'program_id', 'product_type'
	 *		You can run organizeDbResults($results, array('program_id', 'product_type'))
	 *			to have results nested inside subarray(s) of values of: 'program_id', 'product_type'
	 */
	public static function organizeDbResults($results, $treeColumns, $takeLastLevelOutOfArray = false)
	{
		$res = array();
		
		if(is_array($results)) {
			$link = &$res;
			
			foreach ($results as $rowId => $row) {
				// where to put it within results array
				foreach ($treeColumns as $column) {
					var_dump(array($column, $row[$column], $row)); exit;
					if (!isset($link[$row[$column]])) {
						$link[$row[$column]] = array();
					}
					$link = &$link[$row[$column]];
				}
				
				// how to put it there (as is or out of array)
				if ($takeLastLevelOutOfArray) {
					$link = $row;
				} else {
					$link[$rowId] = $row;
				}
				$link = &$res;
			}
		}
		return $res;
	}
	
	/**
	 *	Another handy function for handling db results
	 *	We often get results such as: array(0=> array('shift' => shiftObject), ...)
	 *	Running below method $res=extractDBResults($results, 'shift', 'id')
	 *		will get us array((int)shift_id => shiftObject, ...)
	 */
	public static function extractDBResults($results, $column, $id='id')
	{
		foreach ($results as $i => $arr) {
			foreach ($arr as $row => $val) {
				$idVal = (is_array($val)) ? $val[$id] : $val->$id;
				$res[$idVal]=$val;
			}
		}
		return $res;
	}
	
	/**
	 * Somewhat fringe-case method:
	 *   Reorganizes query results not by rows but by fields.
	 * Each field will be array of field values from all rows
	 * @param array $results
	 * @return array $res Reorganized results
	 */
	public static function organizeByField($results)
	{
		$res = array();
		
		if (is_array($results)) {
			foreach ($results as $id => $row) {
				foreach ($row as $field => $value) {
					$res[$field][$id] = $value;
				}
			}
		}
		
		return $res;
	}
	
	//public static function _organizeResults(&$results)
	
	/**
	 *	Checks if mysql table exists, caches results in self::$existingTables
	 *	@param string $tableName
	 *	@param string $schema (optional)
	 */
	public static function tableExists($tableName, $schema = null)
	{
		$schema = (($schema) ? $schema : self::getDbSchema());
		
		if (!isset(self::$existingTables[$schema][$tableName])) {
		
			$schemaString = " AND table_schema = '" . $schema . "'";
			
			$sql = "SELECT count(*) count FROM information_schema.tables"
				. " WHERE table_name = '$tableName' $schemaString;";
			
			$results =  self::fetchQuery($sql);

			self::$existingTables[$schema][$tableName] = (bool)$results['count'];
		}
		
		return self::$existingTables[$schema][$tableName];
	}
	
	public static function getTableInformationSchema($schema = null)
	{
		$schema = (($schema) ? $schema : self::getDbSchema());
		
		$schemaString = " AND table_schema = '" . $schema . "'";
		
		$sql = "SELECT * FROM information_schema.tables"
			. " WHERE 1 $schemaString;";
		
		$return = self::getDBInstance()->fetchAll($sql);
		//$return = self::getDBInstance()->query($sql)->fetchAll();
		//\Util_Dev::Dump($return);
		return $return;
	}
	
	
	
	/**
	 *	Get Db Schema, retrieve default from config if none set
	 */
	public static function getDbSchema()
	{
		if (!self::$schema) {
			$config = \Zend_Registry::get('db')->getConfig();
			self::$schema = $config['dbname'];
		}
		return self::$schema;
	}


	/**
	 *	Set connection type for further db function calls
	 *	Usage: self::setDbConn('dbadmin');
	 *	@param string $dbConnName
	 */
	public static function setDbConn($dbConnName)
	{
		self::$currentDbConn = $dbConnName;
	}
	/**
	 *	Gets database instance from registry
	 *	@param connection_name if specified changes connections for ongoing calls
	 *		Valid connections are: db, dbadmin
	 */
	public static function getDb($conn = null)
	{

		//if (!is_null($conn))
		if(is_null($conn)) {
			$conn = self::$currentDbConn;
		}
		// remember connection for later calls (disabled) use self:setDbConn instead
		// else  {self::$currentDbConn = $conn;}
		
		if (is_null(self::$dbInstance[$conn])) {
			self::$dbInstance[$conn] = Zend_Registry::get($conn); // db or dbadmin
		}
		return self::$dbInstance[$conn];
	}
	
	public static function getDBInstance($conn = null)
	{
		return self::getDb($conn);
	}
	
	/**
	 *	Return database results by passing mysql query
	 *	@param string $param	Mysql Query
	 *	@returns array array of database rows
	 */
	public static function getSqlResults($query)
	{
		$result = self::getDBInstance()->fetchAll($query);
		
		if (self::$dbLoggingOn) {
			$count = count($result);
			$logger = Zend_Registry::get('logger');
			$logger->debug('Results: ' . $count . ' Query: ' . $query);
			$logger->debug(print_r($result, true));
		}
		return $result;
	}
	
	/**
	 *	Fetches sql query (shortcut for lazy people:)
	 */
	public static function fetchQuery($sql)
	{
		$db = self::getDBInstance();
		return $db->query($sql)->fetch();
	}
	
	
	/**
	 *	Enables or disables logging of db queries to logger
	 *	@param boolean $loggingOnOff
	 */
	public static function dbLoggingOn ($loggingOnOff=true)
	{
		self::$dbLoggingOn = $loggingOnOff;
	}
	
	/**
	 *	Return database result (ONE ROW) by passing mysql query
	 *	@param string $param	Mysql Query
	 *	@returns array values of one row of values or empty array
	 *	Notes: No results returns empty array. Such result:
	 *	 - can be easiy tested for being empty by simply: if ($results) ...
	 *	 - can be iterated for results if so desired (result is still array)
	 */
	public static function getSqlResult( $query )
	{
		$db = self::getDBInstance();

		$result = $db->fetchAll($query);
		if (isset($result[0])) {
			return $result[0];
		} else {
			return array();
		}
	}
	
	
	
	


	/**
	 * METHODS ABOVE Are good in Fisdap 2.0
	 * 
	 * 		#####  D I V I D E R  #####
	 *
	 * METHODS BELOW USE Fisdap 1.0 Models
	 */

	
	/**
	 *	SECTION: METHODS BELOW IMPLEMENT 'DELETED, NOT ACTUALLY DELETED' FUNCTIONALITY
	 */
	/*
	const DNAD_FIELD_NAME = 'DeletedTime';
	const DNAD_NOT_DELETED_CONDITION = 'DeletedTime IS NULL';
	const DNAD_NOT_DELETED_VALUE = 'NULL';
	*/
	const DNAD_FIELD_NAME = 'DeletedTimeValue';
	const DNAD_NOT_DELETED_CONDITION = "`DeletedTimeValue` = '99990000'";	//99991231235959
	const DNAD_NOT_DELETED_VALUE = '99990000';
	
	/* NOT IMPLEMENTED: other deleted values - all of these values are considered 'deleted'
	protected static $otherDeletedValues = array(
	);*/
	const DELETED_DB_FIELD_NAME = 'DateTimeStampDeleted';

	
	/**
	 *	Note: Usually you should use:	Util_Db::getDNADSelectWhereCondition
	 *	(Unless you're calling from models and use $DNADInUse param)
	 *	
	 *	Returns 'Not Deleted Row' Clause WITHOUT CHECKING Model's setting
	 *	
	 *	@param array or string $tables - table or array of tables
	 *	@param boolean if false return empty string (DNAD inactive) Hint: USE INSIDE OF MODELS
	 */
	public static function getDNADNotDeletedRowCondition ( $tableAliases = '', $DNADInUse = true)
	{
		return ($DNADInUse) ?
			' AND ' . self::applySqlConditionToMultipleTables(self::DNAD_NOT_DELETED_CONDITION, $tableAliases) : '';
	}
	
	public static function applySqlConditionToMultipleTables( $condition, $tables = '', $andOr = 'AND')
	{
		$ret = '';
		
		// if it needs dotted format
		if (!empty($tables) && !is_array($tables)) {
			$tables = array ($tables);
		}
		
		if (is_array($tables)) {
			foreach ($tables as $tableName) {
				if ($ret) {
					$ret .= " $andOr ";
				}
				$ret .= '`' . $tableName . '`.' . $condition;
			}
		} else {
			$ret = $condition;
		}
		return $ret;
	}
	


	/**
	 *	Extracts Schema, Table and table Alias from string
	 *	@param $string
	 *	@return array of strings: (Table, Alias, Schema)
	 *	Usage:
	 *		list($table, $alias, $schema) = Util_Db::getSchemaTableAliasFromString ($tableString);
	 */
	public static function getSchemaTableAliasFromString( $tableString )
	{
		// get table name
		$parts = explode(' ', trim($tableString));
		
		// alias
		if (!empty($parts[1])) {
			$alias = $parts[1];
		} else {
			$alias = '';
		}
		
		// schema and table
		$schTb = explode('.', $parts[0]);
		if (!empty($schTb[1])) {
			$table = $schTb[1];
			$schema = str_replace('`', '', $schTb[0]);
		} else {
			$table = $parts[0];
			$schema = '';
		}
		
		$table = str_replace('`', '', $table);
		
		return array($table, $alias, $schema);
	}
	
	/**
	 *	Complement to getSchemaTableAliasFromString
	 *	Usage:
	 *		$queryString = Util_Db::constructSQLFromSchemaTableAlias ($table, $alias, $schema);
	 */
	public static function constructSQLFromSchemaTableAlias ( $table, $alias, $schema = '' )
	{
		if (!empty($schema)) {
			$schema = "`$schema`.";
		}
		
		if (!empty($alias)) {
			$alias = ' ' . $alias;
		}
		
		return $schema . '`' . $table . '`' . $alias;
	}
	
	/**
	 *	Currently returns 'DeletedTimeValue'
	 */
	public static function getDNADFieldName ( $table = '')
	{
		$tablePrefix = (empty($table)) ? '' : '.' . $table;
		return $tablePrefix . self::DNAD_FIELD_NAME;
	}
	
	
	/**
	 *	Attmpts to add DNAD statement to the where clause of given query
	 *	@param string $query
	 *	@todo Needs consider words that are quoted or not.
	 *	Hint how to do it: first scan whole string and record in array each position's 'quoted' value: true/false
	 *	This simple version catches first occurrence of words that could be keywords, but could also be quoted
	 */
	/* COULD BE REALLY HANDY FUNCTION IF USING MANUAL QUERIES, NOT WORRYING ABOUT IT NOW
	public static function addExcludeDeletedToQuery( $userQuery )
	{
		//split statement in three parts:
		//	- before WHERE
		//	- WHERE statements
		//	- AFTER where
		
		$checkAfterWhere = array(
			'limit', 'group by', 'order by'
		);
		$qry = strtolower($userQuery);
		$strLen = strlen($userQuery);
		// --- END OF SETUP ---
		
		
		// find position of 'where'
		$whereBegPos = strrpos($qry, 'where');
		$whereWordEndPos = ($whereBegPos) ? $whereBegPos + 6 : $whereBegPos;
		
		// find position of first keyword 'after where' (even if there was no where)
		$firstAfterWherePos = strlen($qry);
		foreach ($checkAfterWhere as $keyword) {
			$aftWherePos = strpos($qry, $keyword, $whereBegPos);
			if ($aftWherePos && $aftWherePos < $firstAfterWherePos) {
				$firstAfterWherePos = $aftWherePos;
			}
		}
		
		// set 'where' position to imaginary one when there is no where keyword
		if (!$whereBegPos) {
			$whereBegPos = $whereWordEndPos = $aftWherePos;
		} else {
			$whereWordEndPos = $whereBegPos + 6;	
		}
		
		$query['beforeWhere'] = substr($userQuery, 0, $whereBegPos);
		$query['wherePart'] = substr($userQuery, $whereWordEndPos, $firstAfterWherePos - $whereWordEndPos);
		$query['afterWhere'] = substr($userQuery, $firstAfterWherePos);
		$query['keywords'] = implode('; ', $keywords);
		$query['tests'] = $t;
		*/

		/*
		if ($wherePos) {
			/* find first statement after 'where':
				- unquoted
				- space followed by unquoted word
			

			$strLen = strlen($qry);
			
			$pos = $wherePos;
			do {
				$pos++;
			} while ($singleQuoted || $doubleQuoted)
			
		} else {
			// list of sql keywords which are AFTER 'where' keywor
		}*/
	//	return $query;
	//}
	
	
}

?>