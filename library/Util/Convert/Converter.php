<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_Converter implements Util_Convert_iDatabaseConverter
{
	

	/**
	 * @var Zend_Db_Adapter_Abstract
	 */
	protected $db;
	
	/**
	 * @var Doctrine\ORM\EntityManager
	 */
	protected $em;
	
	public function __construct()
	{
		$this->db = Zend_Registry::get('db');
		
		$this->em = \Fisdap\EntityUtils::getEntityManager();
	}
	
	public function convert($data){}
}
