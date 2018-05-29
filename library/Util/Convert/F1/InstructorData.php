<?php

class Util_Convert_F1_InstructorData extends Util_Convert_LiveConverterBase implements Util_Convert_LiveConverterTableInterface
{
	protected static $fields = array(
		'Instructor_id' => array(),
		);
	
	protected function init()
	{
		$this->generateFieldOptions(self::$fields, self::$defaultFieldOptions);
		//$this->loadEntities();
		
	}
	
	public function loadEntities($dataId)
	{
		
	}
	
	public function changeField($change)
	{
			
	}
	
	public function getFields()
	{
		return self::$fields;
	}
}

?>