<?php

class Util_Convert_F1_UserAuthData extends Util_Convert_LiveConverterBase implements Util_Convert_LiveConverterTableInterface
{
	protected static $fields = array(
		'Instructor_id' => array(),
		);
	
	/**
	 *	Usage:
	 *		Util_Convert_F1_UserAuthData::dbGetUserIdByUsername($username)
	 */
	public static function dbGetUserIdByUsername($username)
	{
		$db = Util_Db::getDBInstance();
		
		$sql = "SELECT idx from UserAuthData where email ='$username'";
		
		$st = $db->query($sql);
		//$res = $st->fetchColumn(0);
		//throw new Exception("Username: '" . $username . "' UserId: ". print_r($res, true));
		return $st->fetchColumn(0);
	}

	
	protected function init()
	{
		$this->generateFieldOptions(self::$fields, self::$defaultFieldOptions);
		//$this->loadEntities();
		
	}
	
	public function changeField($change)
	{
			
	}
	
	public function getFields()
	{
		return self::$fields;
	}
	
	public function loadEntities($dataId)
	{
		
	}
}

?>