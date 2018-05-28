<?php

class Util_Convert_F1_StudentData extends Util_Convert_LiveConverterBase implements Util_Convert_LiveConverterTableInterface
{
	protected static $fields = array(
		'Student_id' => array(
			),
		'FirstName' => array(
			'target' => array(
				'entity' => 'User',
				'field' => 'first_name'
			 )),
		'LastName' => array(
			'target' => array(
				'entity' => 'User',
				'field' => 'last_name'
			 )),
		'Program' => array(),
		'Program_id' => array(),
		'Mentor_id' => array(),
		'UserName' => array(
			'target' => array(
				'entity' => 'User',
				'field' => 'username'
			 )),
		'Box_Number' => array(),
		'Address' => array(),
		'City' => array(),
		'State' => array(),
		'ZipCode' => array(),
		'HomePhone' => array(),
		'WorkPhone' => array(),
		'EmailAddress' => array(
			'target' => array(
				'entity' => 'User',
				'field' => 'email'
			 )),
		'Pager' => array(),
		'BirthDate' => array(),
		'Gender' => array(),
		'Ethnicity' => array(),
		'Class_Year' => array(),
		'EMT_Year' => array(),
		'ClassMonth' => array(),
		'ResearchConsent' => array(
			'target' => array(
				'entity' => 'link_Student',
				'field' => 'research_consent',
				'translate' => array(
					'yes' => 1,
					'no' => 0,
				)),
			),
		'GoodDataFlag' => array(
			'target' => array(
				'entity' => 'link_Student',
				'field' => 'good_data_flag'
			)),
		'ClassAssigned' => array(),
		'MaxFieldShifts' => array(),
		'MaxClinicShifts' => array(),
		'MaxLabShifts' => array(),
		'CellPhone' => array(),
		'ContactPhone' => array(),
		'ContactName' => array(),
		'ContactRelation' => array(),
		'TestingExpDate' => array(),
		'DefaultGoalSet_id' => array(),
	);
	
	protected static $defaultFieldOptions = array(
		'target' => array(
			'User' => array(
				  'entity' => 'User',
			)
		)
	);

	
	/**
	 *	todo: Allow multiple targets for one change row
	 */
	public function changeField($change)
	{
		\Util_Debug::vard('ChFl: '.$change['id'].'-'.$change['data_id']);
		// can change + message if not
		if (!$this->canChangeField($change)) {
			\Util_Debug::vard('ChFl_NOT-OK: '.$change['id'].'-'.$change['data_id']);
			return;
		}
		
		// target set up for field ?
		$target = self::$fields[$change['field']]['target'];
		if (!isset($target)) {
			\Util_Debug::vard('DataId: '.$change['data_id'].' '.$change['field'].' changeField TargetNotSet');
			return;
		}
		
		$this->messages[] = array('event'=>'changingField', 'message' => 'Field: '.$change['field']);
		$targetField = $target['field'];
		// todo: value conversion
		
		// set new value to target entity/field
		$this->f2Entities[$target['entity']]->$targetField = $change['new_value'];
		
		$this->changesMade = true;
	}
	

	
	public function dbGetUsername($studentId)
	{
		$sql = "SELECT UserName from StudentData where Student_id ='$studentId'";
		
		//$st = new Zend_Db_Statement_Mysqli($this->db, $sql);
		$st = $this->db->query($sql);
		return $st->fetchColumn(0);
	}
	
	/**
	 *	Loads entities: User and setup links to sub-entities which need to be
	 *		targets for field updates
	 */
	public function loadEntities($dataId)
	{
		$this->dbLoadStatus = self::DB_NOT_FOUND;
		
		$this->f2Entities = array();
		
		// using old tables to find userid (in case username was changed)
		$username = $this->dbGetUsername($dataId);
		
		if(!$username) {
			$this->messages[] = array('event' => 'loadEntities failed',
				'message' => 'Username not loaded for '.$dataId);
			\Util_Debug::vard('DataId: '.$dataId.' No username');
			return false;
		}
	
		$userId = Util_Convert_F1_UserAuthData::dbGetUserIdByUsername($username);
		if(!$userId) {
			$this->messages[] = array('event' => 'loadEntities failed',
				'message' => 'UserId not loaded for '.$dataId);
			\Util_Debug::vard('DataId: '.$dataId.' No username');
			return false;
		}
		
		//throw new Exception("Username: " . $username . "UserId: ". $userId);	var_dump($username); echo "<h2>Username: " . $username . "UserId: ". $userId. "</h2>";
		
		// Entity: User
		if (!$this->loadEntity('User', $userId)) {
			$this->messages[] = array('event' => 'loadEntities failed',
				'message' => 'User Entity not loaded for '.$dataId);
			\Util_Debug::vard('DataId: '.$dataId.' No User entity');
			return false;
		}

		$this->messages[] = array('event' => 'loadEntities loaded!',
			'message' => 'User Entity loaded for '.$dataId);
		\Util_Debug::vard('DataId: '.$dataId.' OK DB LOADED!!!');
		
		// Student Roles:
		$roles = $this->f2Entities['User']->getUserContexts('student');
		//$roles = $this->f2Entities['User']->getUserContexts('instructor');
		
		$hasStudentRecord = (isset($roles[0]));
		
		//echo "<h2>DataID:" . $userId . "Is Student: $studentPresent </h2>";
		//// will freeze everything:  $userDump = \Doctrine\Common\Util\Debug::dump($this->f2Entities['User']);
		//echo "<pre>";

		if ($hasStudentRecord) {
			$this->f2Entities['link_Student'] = $roles[0]->getRoleData();
		}		
		//$this->f2Entities['link_Student'] = \Fisdap\EntityUtils::getEntity($name, $id);
		//var_dump($roles[0]->getRoleData());
		$this->dbLoadStatus = self::DB_LOADED;
		//\Util_Dev::Dump($this->f2Entities);
		//exit;
		
		// load entity
		//if (!$this->loadEntity('User', $this->dataId)) {

		//$this->f2Entities['User'] = \Fisdap\EntityUtils::getEntity('User');
	
	}
	
	
	
	/**
	 *	Array of 'To' entities which 'From' fields would refer to
	 */
	protected $entities = array();

	protected function init()
	{
		$this->generateFieldOptions(self::$fields, self::$defaultFieldOptions);
		//$this->loadEntities();
		
	}
	
	public function getFields()
	{
		return self::$fields;
	}
}
?>