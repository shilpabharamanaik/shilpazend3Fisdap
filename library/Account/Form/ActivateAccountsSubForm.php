<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * SubForm for activating a group of accounts
 */

/**
 * @package    Account
 */
class Account_Form_ActivateAccountsSubForm extends Fisdap_Form_Base
{
    /**
     * @var array
     */
    protected static $subformDecorators = array(
		'FormErrors',
        'PrepareElements',
		array('ViewScript', array('viewScript' => "forms/activateAccountsSubForm.phtml")),
        array('HtmlTag', array('tag' => 'div', 'class' => 'activate-accounts')),
    );
    
    /**
     * @var \Fisdap\Entity\OrderConfiguration
     */
    public $order_configuration;
	
	/**
	 * @var array containing available serial numbers to email
	 */
	public $serials;
	
	/**
	 * @var array containing student data
	 */
	public $students;
	
	/**
	 * @var int keeps track of the number of records added to form from uploaded csv
	 */
	public $addedCount;
	
	/*
	 * @var bool if we can override the distributed activation codes with data from csv
	 */
	public $overrideDistributed;
	
	/**
	 * @var array to store serials that are incomplete
	 * this is used in $this->isValid() and then in the
	 * viewscript to highligh invalid rows
	 */
	public $invalidSerials = array();
	
	/**
	 * Store the field prefixes, to be used in the view helper
	 */
	public $fieldPrefixes = array();
	
	/**
	 * @var array usernames that have already gone through validation
	 * We need this array to determine if they have any duplicate usernames on the form
	 */
	public static $usernames = array();
    
    public function __construct($orderConfigurationId = null, $students = null, $overrideDistributed = false, $options = null)
    {
		$this->students = $students;
		$this->overrideDistributed = $overrideDistributed;
        $this->order_configuration = \Fisdap\EntityUtils::getEntity('OrderConfiguration', $orderConfigurationId);
        $this->serials = $this->order_configuration->serial_numbers;
		
        parent::__construct($options);
    }
    
    public function init()
    {
		$studentIndex = 0;
		$distributedCodes;
		$addedCount = 0;

		foreach ($this->serials as $i => $sn) {
			$fields = array();
			
            $first = new Zend_Form_Element_Text("first_" . $sn->id);
            $first->setLabel('First Name #' . ($i+1))
                  ->setDecorators(array("ViewHelper"));
			$this->addElement($first);
			$fields['first'] = $first;
			$this->fieldPrefixes['first_'] = "First Name";
            
            $last = new Zend_Form_Element_Text("last_" . $sn->id);
            $last->setLabel('Last Name #' . ($i+1))
                 ->setDecorators(array("ViewHelper"));
			$this->addElement($last);
			$fields['last'] = $last;
			$this->fieldPrefixes['last_'] = "Last Name";
	
            $usernameValidator = new \Zend_Validate_Db_NoRecordExists(array('table' => 'fisdap2_users', 'field' => 'username', 'adapter' => \Zend_Registry::get('db')));
            $usernameValidator->setMessage("The username '%value%' already exists. Please choose another.");
			
			$regexValidator = new \Zend_Validate_Regex(array('pattern' => '/^[a-zA-Z0-9]+$/'));
			$regexValidator->setMessage("Please enter a username that only contains letters and numbers and is at least 3 characters long.");
            
            $username = new Zend_Form_Element_Text('username_' . $sn->id);
            $username->setLabel('Username #' . ($i+1))
                     ->setDecorators(array("ViewHelper"))
				     ->addValidator($regexValidator)
                     ->addValidator("StringLength", false, array('min' => 3))
                     ->addValidator($usernameValidator);
			$this->addElement($username);
			$fields['username'] = $username;
			$this->fieldPrefixes['username_'] = "Username";
            
            $password = new Zend_Form_Element_Text('password_' . $sn->id);
            $password->setLabel('Password #' . ($i+1))
                     ->setDecorators(array("ViewHelper"))
                     ->addValidator("StringLength", false, array('min' => 5))
					 ->addValidator("Alnum");
			$this->addElement($password);
			$fields['password'] = $password;
			$this->fieldPrefixes['password_'] = "Password";
            
            $email = new Fisdap_Form_Element_Email('email_' . $sn->id);
            $email->setLabel('Email #' . ($i+1))
                  ->setDecorators(array("ViewHelper"))
				  ->addErrorMessage("Please choose a valid email address.");
			$this->addElement($email);
			$fields['email'] = $email;
			$this->fieldPrefixes['email_'] = "Email";
            
			//Don't ask for graduation status if we're activating instructors or EMS providers
			if (!$this->order_configuration->onlyTransitionCourse() && !$this->order_configuration->onlyPreceptorTraining()) {
				$gradDate = new Fisdap_Form_Element_GraduationDate('grad_date_' . $sn->id);
				$gradDate->setLabel("Graduating")
					 ->setYearRange(date("Y"), date("Y") + 5)
                     ->setDecorators(array("ViewHelper"))
                     ->setValue($sn->graduation_date);
				$this->addElement($gradDate);
				$fields['gradDate'] = $gradDate;
				$this->fieldPrefixes['grad_date_'] = "Graduating";
			}
			
			//Add specific fields if these accounts are EMS providers
			if ($this->order_configuration->onlyTransitionCourse()) {
				$licenseNumber = new Zend_Form_Element_Text("licenseNumber_" . $sn->id);
				$licenseNumber->setLabel("NREMT License #" . ($i+1))
							  ->setDecorators(array("ViewHelper"))
							  ->addErrorMessage('Please enter your license number.');
				$this->addElement($licenseNumber);
				$fields['licenseNumber'] = $licenseNumber;
				$this->fieldPrefixes['licenseNumber_'] = "NREMT License";
				
				$licenseExpirationDate = new Zend_Form_Element_Text("licenseExpirationDate_" . $sn->id);
				$licenseExpirationDate->setLabel("License Expiration #" . ($i+1))
							 ->setDecorators(array("ViewHelper"))
							 ->addErrorMessage('Please enter your license expiration.');
				$this->addElement($licenseExpirationDate);
				$fields['licenseExpirationDate'] = $licenseExpirationDate;
				$this->fieldPrefixes['licenseExpirationDate_'] = "License Expiration";
				
				$licenseState = new Fisdap_Form_Element_States("licenseState_" . $sn->id);
				$licenseState->setLabel("License State #" . ($i+1))
							 ->setDecorators(array("ViewHelper"))
							 ->setAttrib("class", "license-state")
							 ->useFullNames()
							 ->addErrorMessage('Please choose your licensing state.');
				$this->addElement($licenseState);
				$fields['licenseState'] = $licenseState;
				$this->fieldPrefixes['licenseState_'] = "License State";
				
				$stateLicenseNumber = new Zend_Form_Element_Text("stateLicenseNumber_" . $sn->id);
				$stateLicenseNumber->setLabel("State License #" . ($i+1))
							  ->setDecorators(array("ViewHelper"))
							  ->addErrorMessage('Please enter your state license number.');
				$this->addElement($stateLicenseNumber);
				$fields['stateLicenseNumber'] = $stateLicenseNumber;
				$this->fieldPrefixes['stateLicenseNumber_'] = "State License";
				
				$stateLicenseExpirationDate = new Zend_Form_Element_Text("stateLicenseExpirationDate_" . $sn->id);
				$stateLicenseExpirationDate->setLabel("State License Expiration #" . ($i+1))
							 ->setDecorators(array("ViewHelper"))
							 ->addErrorMessage('Please enter your state license expiration.');
				$this->addElement($stateLicenseExpirationDate);
				$fields['stateLicenseExpirationDate'] = $stateLicenseExpirationDate;
				$this->fieldPrefixes['stateLicenseExpirationDate_'] = "State License Expiration";
				
				$this->addJsOnLoad('
					$("#licenseExpirationDate_' . $sn->id . '").mask("99/99/9999");
					$("#stateLicenseExpirationDate_' . $sn->id . '").mask("99/99/9999");
				');
			}
			
			//$fields = array(
			//	"first" => $first,
			//	"last" => $last,
			//	"username" => $username,
			//	"password" => $password,
			//	"email" => $email,
			//	"gradDate" => $gradDate,
			//);
			
			if($this->students){
				if(!$sn->isActive()){
					// it's distributed
					if($sn->distribution_email){
						// can we override it?
						if($this->overrideDistributed){
							if($this->students[$studentIndex]){
								$this->setDefaultsFromFile($sn->id, $this->students[$studentIndex], $fields, $this->order_configuration);
								$addedCount++;
							}
							// only continue through file if we've added this student to the form
							$studentIndex++;
						}
					}
					// not a distributed code, just set the defaults
					else {
						if($this->students[$studentIndex]){
							$this->setDefaultsFromFile($sn->id, $this->students[$studentIndex], $fields, $this->order_configuration);
							$addedCount++;
							// only continue through file if we've added this student to the form
						}
						$studentIndex++;

					}
				}
			}
		}
        
		
		$this->addedCount = $addedCount;

		
		$orderConfigurationId = new Zend_Form_Element_Hidden("orderConfigurationId");
		$orderConfigurationId->setValue($this->order_configuration->id)
							 ->setAttrib("class", "orderConfigurationId")
							 ->setDecorators(array('ViewHelper'));
		$this->addElement($orderConfigurationId);
		
        $this->setDecorators(self::$subformDecorators);		
    }
	
	public function setDefaultsFromFile($snId, $rowData, $fields, $config)
	{
		
		// set the default values
		// if this row doesn't have a particular value, give it the missing value calss
		(strlen($rowData[0]) > 0) ? $defaults["first_" . $snId] = $rowData[0]: $fields["first"]->setAttrib("class", "missingValue");
		(strlen($rowData[1]) > 0) ? $defaults["last_" . $snId] = $rowData[1]: $fields["last"]->setAttrib("class", "missingValue");
		(strlen($rowData[2]) > 0) ? $defaults["username_" . $snId] = $rowData[2]: $fields["username"]->setAttrib("class", "missingValue");
		(strlen($rowData[3]) > 0) ? $defaults["password_" . $snId] = $rowData[3]: $fields["password"]->setAttrib("class", "missingValue");
		(strlen($rowData[4]) > 0) ? $defaults["email_" . $snId] = $rowData[4]: $fields["email"]->setAttrib("class", "missingValue");
		
		if (!$config->onlyTransitionCourse() && !$config->onlyPreceptorTraining()) {

			if(strlen($rowData[5]) > 0){
				$defaults["grad_date_" . $snId]['month'] = $rowData[5];
			}
			
			if(!$defaults["grad_date_" . $snId]['month']){
				$classArray = array("missingValue");
				$fields['gradDate']->yearAttribs["class"] = $classArray;
			}
			if(strlen($rowData[6]) > 0){
				$defaults["grad_date_" . $snId]['year'] = substr($rowData[6], 0, 4);
			}
			
			if(!$defaults["grad_date_" . $snId]['year']){
				$classArray = array("missingValue");
				$fields['gradDate']->yearAttribs["class"] = $classArray;
			}
		}
		
		if ($config->onlyTransitionCourse()) {
			(strlen($rowData[5]) > 0) ? $defaults["licenseNumber_" . $snId] = $rowData[5]: $fields["licenseNumber"]->setAttrib("class", "missingValue");

			
			if($this->addMissingValueClass($rowData[6])){
				$fields["licenseExpirationDate"]->setAttrib("class", "missingValue");
			}
			else {
				$defaults["licenseExpirationDate_" . $snId] = date("m/d/Y", strtotime($rowData[6]));
			}
			
			
			if($this->addMissingValueClass($rowData[9])){
				$fields["stateLicenseExpirationDate"]->setAttrib("class", "missingValue");
			}
			else {
				$defaults["stateLicenseExpirationDate_" . $snId] = date("m/d/Y", strtotime($rowData[9]));
			}

			
			
			(strlen($rowData[7]) > 0) ? $defaults["licenseState_" . $snId] = $rowData[7]: $fields["licenseState"]->setAttrib("class", "missingValue license-state");
			(strlen($rowData[8]) > 0) ? $defaults["stateLicenseNumber_" . $snId] = $rowData[8]: $fields["stateLicenseNumber"]->setAttrib("class", "missingValue");
		}
		
		if($defaults){
			$this->setDefaults($defaults);
		}
	}
	
	private function addMissingValueClass($rowDataVal){
		$addMissingValueClass = false;
			
		if(strlen(ltrim($rowDataVal)) > 0){
			$displayDate = date("m/d/Y", strtotime($rowData[9]));
			if(strpos($displayDate,'1969') !== false){
				// it has an issue
				$addMissingValueClass = true;
			}
		}
		else {
			$addMissingValueClass = true;
		}
		
		return $addMissingValueClass;
	}
	
	/**
	 * Determine if a row in the activation table has an errors
	 * @param integer $snId the ID of the serial number row
	 * @return boolean does this row have errors
	 */
	public function hasErrors($snId)
	{
		return in_array($snId, $this->invalidSerials);
	}
	
	public function isValid($post)
	{
		$isValid = parent::isValid($post);
		
		foreach ($this->serials as $i => $sn) {
			$first = $this->getElement("first_" . $sn->id);
			$last = $this->getElement("last_" . $sn->id);
			$username = $this->getElement("username_" . $sn->id);
			$password = $this->getElement("password_" . $sn->id);
			$email = $this->getElement("email_" . $sn->id);
			$gradDate = $this->getElement("grad_date_" . $sn->id);
			
			if ($gradDate) {
				$gradDateValue = $gradDate->getValue();
				$gradMonth = $gradDateValue['month'];
				$gradYear = $gradDateValue['year'];
			}
			
			$licenseNumber = $this->getElement("licenseNumber_" . $sn->id);
			$stateLicenseNumber = $this->getElement("stateLicenseNumber_" . $sn->id);
			$licenseExpirationDate = $this->getElement("licenseExpirationDate_" . $sn->id);
			$stateLicenseExpirationDate = $this->getElement("stateLicenseExpirationDate_" . $sn->id);
			$licenseState = $this->getElement("licenseState_" . $sn->id);
			
			//Add a validator for checking against the other names on the form
			$usernameValidator = new Fisdap_Validate_NotInArray(self::$usernames);
			$usernameValidator->setMessage("The username '%value%' already exists. Please choose another.");
			$username->addValidator($usernameValidator);
			
			//Now that we've added the validator, recheck validation on this one form element
			if (!$username->isValid($username->getValue())) {
				$isValid = false;
			}
			
			//Add username to the list of usernames on this form
			if ($username->getValue()) {
				self::$usernames[] = $username->getValue();				
			}
			
			$fieldsOr = $first->getValue() || $last->getValue() || $username->getValue() || $password->getValue() || $email->getValue();
			$fieldsAnd = $first->getValue() && $last->getValue() && $username->getValue() && $password->getValue() && $email->getValue();
			
			if ($licenseNumber && $licenseState && $stateLicenseNumber && $licenseExpirationDate && $stateLicenseExpirationDate) {
				$fieldsOr = $fieldsOr || $licenseNumber->getValue() || $licenseState->getValue() || $stateLicenseNumber->getValue() || $licenseExpirationDate->getValue() || $stateLicenseExpirationDate->getValue();
				$fieldsAnd = $fieldsAnd && $licenseNumber->getValue() && $licenseState->getValue() && $stateLicenseNumber->getValue() && $licenseExpirationDate->getValue() && $stateLicenseExpirationDate->getValue();
			}
			
			if ($gradDate) {
				$fieldsAnd = $fieldsAnd && $gradMonth && $gradYear;
			}
			
			//If any element in a row has been filled out, and the rest of the row ISN'T, mark the row as having failed validation
			if ($fieldsOr){
				if (!$fieldsAnd) {
					//Give them a gentle reminder to fill in the entire row
					$this->addErrorMessage("Please fill out Row #" . ($i + 1) . " completely.");						
					
					$this->invalidSerials[] = $sn->id;
					$isValid = false;
				}
			}
			
			//If any of this serial's elements have errors, mark the row as having failed individual validation
			if ((count($first->getMessages()) ||
				 count($last->getMessages()) ||
				 count($username->getMessages()) ||
				 count($password->getMessages()) ||
				 count($email->getMessages()))
				&& !in_array($sn->id, $this->invalidSerials)) {
				$this->invalidSerials[] = $sn->id;
			}
		}
		
		return $isValid;
	}
}