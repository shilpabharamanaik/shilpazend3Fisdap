<?php
class Util_MoodleAPI {
	/**
	 * @var Zend_Http_Client_Adapter_Curl
	 */
	protected $adapter;

	/**
	 * @var Zend_Http_Client
	 */
	protected $client;

	/**
	 * @var array of moodle connection params
	 */
	protected $moodleParams;

	/**
	 * @var string the context of which moodle we're using
	 */
	protected $context;

	public function __construct($context = "secure_testing")
	{
		$this->moodleParams = \Zend_Registry::get('config')->moodle->$context->params->toArray();

		$this->context = $context;
		$this->adapter = new Zend_Http_Client_Adapter_Curl();
		$this->resetClient();
	}

	private function resetClient()
	{
		$this->client = new Zend_Http_Client("https://" . $this->moodleParams['host'] . "/webservice/rest/server.php");
		$this->client->setAdapter($this->adapter);
		$this->client->setMethod("POST");
		$this->adapter->setConfig(array(
			"curloptions" => array(
				CURLOPT_VERBOSE => 1,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_RETURNTRANSFER => 1,
		)));
	}

	public function createMoodleUser($user)
	{
		if (!$user->id) {
			throw new \Exception("Can't process moodle user, user ID not found.");
		}

        $moodleUser = new stdClass();
        $moodleUser->username = strtolower($user->username);
        $moodleUser->password = "N0tcached!";
        $moodleUser->firstname = $user->first_name;
        $moodleUser->lastname = $user->last_name;
        $moodleUser->email = $user->email;
        $moodleUser->auth = "fisdap";
		
		//Create dummy fields if they're empty
		if (!$user->license_number) {
			$user->license_number = "12345";
		}
		if (!$user->license_state) {
			$user->license_state = "Minnesota";
		}
		if (!$user->state_license_number) {
			$user->state_license_number = "12345";
		}
		if (!$user->license_expiration_date) {
			$user->license_expiration_date = new \DateTime();
		}
		if (!$user->state_license_expiration_date) {
			$user->state_license_expiration_date = new \DateTime();
		}
		
		$moodleUser->customfields[] = array("type"=>"licensenum", "value" => $user->license_number);
		$moodleUser->customfields[] = array("type"=>"licensestate", "value" => $user->license_state);
		$moodleUser->customfields[] = array("type"=>"statelicensenum", "value" => $user->state_license_number);
		$moodleUser->customfields[] = array("type"=>"licenseexp", "value" => $user->license_expiration_date->format("m/d/Y"));
		$moodleUser->customfields[] = array("type"=>"statelicenseexp", "value" => $user->state_license_expiration_date->format("m/d/Y"));


		$params = array(
            "users" => array($moodleUser)
		);

		return $this->apiCall($params, "core_user_create_users");
	}

	public function updateMoodleUser($user)
	{
		if (!$user->id) {
			throw new \Exception("Can't process moodle user, user ID not found.");
		}

        $moodleUser = new stdClass();
        $moodleUser->id = $this->getMoodleUserId($user);
        $moodleUser->firstname = $user->first_name;
        $moodleUser->lastname = $user->last_name;
        $moodleUser->email = $user->email;
		$moodleUser->customfields[] = array("type"=>"licensenum", "value" => $user->license_number);
		$moodleUser->customfields[] = array("type"=>"licensestate", "value" => $user->license_state);
		$moodleUser->customfields[] = array("type"=>"statelicensenum", "value" => $user->state_license_number);
		$moodleUser->customfields[] = array("type"=>"licenseexp", "value" => $user->license_expiration_date->format("m/d/Y"));
		$moodleUser->customfields[] = array("type"=>"statelicenseexp", "value" => $user->state_license_expiration_date->format("m/d/Y"));


		$params = array(
            "users" => array($moodleUser)
		);

		return $this->apiCall($params, "core_user_update_users");
	}
	
    public function addGroupMember($user, $groupId)
    {
		$moodleUserId = $this->getMoodleUserId($user);

		$group = new stdClass();
		$group->groupid = $groupId;
		$group->userid = $moodleUserId;

		$params = array("members" => array($group));

		return $this->apiCall($params, "core_group_add_group_members");
    }

	public function enrollCourse($user, $courseId)
	{
		$moodleUserId = $this->getMoodleUserId($user);
		
		//If no moodle user exists, create one
		if (!$moodleUserId) {
			$result = $this->createMoodleUser($user);
			$moodleUserId = $result[0]['id'];
		}

		$enrollment = new stdClass();
		$enrollment->roleid = 5;
		$enrollment->userid = $moodleUserId;
		$enrollment->courseid = $courseId;

		$params = array("enrolments" => array($enrollment));

		return $this->apiCall($params, "enrol_manual_enrol_users");
	}

	public function createGroup($name, $course_id)
	{
		$group = new stdClass();
		$group->courseid = $course_id;
		$group->name = $name;
		$group->description = $name;

		$params = array("groups" => array($group));

		return $this->apiCall($params, "core_group_create_groups");
	}

	public function createGroups($groups)
	{
		return $this->apiCall(array("groups" => $groups), "core_group_create_groups");
	}

	public function createGrouping($name, $course_id)
	{
		$grouping = new stdClass();
		$grouping->courseid = $course_id;
		$grouping->name = $name;
		$grouping->description = $name;

		$params = array("groupings" => array($grouping));

		return $this->apiCall($params, "core_group_create_groupings");
	}

	public function createGroupings($groupings)
	{
		return $this->apiCall(array("groupings" => $groupings), "core_group_create_groupings");
	}

	public function assignGroupings($assignments)
	{
		return $this->apiCall(array("assignments" => $assignments), "core_group_assign_grouping");
	}

	public function apiCall($params = array(), $functionName)
	{
		$this->resetClient();
		$this->client->setParameterPost("wstoken", $this->moodleParams['token']);
		$this->client->setParameterPost("wsfunction", $functionName);
		$this->client->setParameterPost("moodlewsrestformat", "json");

		foreach ($params as $key => $value) {
			$this->client->setParameterPost($key, $value);
		}

		$result = json_decode($this->client->request()->getBody(), true);

		if (is_array($result) && array_key_exists("exception", $result)) {
			throw new \Exception($result['exception'] . ": " . $result['message'] . " - " . $result['debuginfo']);
		}

		return $result;
	}

	private function getMoodleUserId(\Fisdap\Entity\User $user)
	{
		if (!$user->id) {
			throw new \Exception("Can't process moodle add group member, user ID not found.");
		}

		$moodleUserId = array_pop(\Fisdap\MoodleUtils::getMoodleUserIds(array($user->username), $this->context));

		//if (!$moodleUserId) {
		//	throw new \Exception("Can't find moodle user id (returned $moodleUserId) for username: " . $user->username . " and context: " . $this->context);
		//}

		return $moodleUserId;
	}
}
