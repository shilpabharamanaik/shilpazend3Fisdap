<?php

/**
* Form for Workshop Event Registration
*/

class Fisdap_Form_Events extends Fisdap_Form_Base
{
	
	public $workshops;
				
	public $wsLocations = array();
	public $wsIds = array();
				
	public function __construct()
	{	
		parent::__construct();
	}

	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();
		
	//add js file to do cool input masking
	$this->addJsFile("/js/jquery.maskedinput-1.3.js");	
		
	$this->addJsOnLoad(
	'$("#phone").mask("999-999-9999");
	');
	
	$workshops = \Fisdap\EntityUtils::getEntityManager()->createQuery("SELECT w FROM \Fisdap\Entity\Workshop w WHERE w.date >= '" . date_create("now")->format("Y-m-d"). "'")->getResult();
				
	foreach ($workshops as $workshop)
	{
		$wsLocations[] = $workshop->location . " -- " . $workshop->date->format('m/d/Y');
	}			
		
	//workshop locations	
	$locations = new Zend_Form_Element_Select('locations');
	$locations->setLabel('Please select your workshop location: *')
			->setRequired(true)
			->addErrorMessage("Please select a workshop location.")
			->setMultiOptions($wsLocations)
			->setDecorators(self::$gridElementDecorators);
			
	//first name
	$first = new Zend_Form_Element_Text('firstName');
	$first->setLabel('First Name: *')
		  ->setRequired(true)
		  ->addFilter('StripTags')
		  ->addFilter('HtmlEntities')
		  ->addErrorMessage("Please enter a first name.")
		  ->setDecorators(self::$gridElementDecorators);
	
	//last name
	$last = new Zend_Form_Element_Text('lastName');
	$last->setLabel('Last Name: *')
		  ->setRequired(true)
		  ->addFilter('StripTags')
		  ->addFilter('HtmlEntities')
		  ->addErrorMessage("Please enter a last name.")
		  ->setDecorators(self::$gridElementDecorators);
		  
	//user name
	$user = new Zend_Form_Element_Text('userName');
	$user->setLabel('Fisdap User Name (If Applicable):')
		  ->addFilter('StripTags')
		  ->addFilter('HtmlEntities')
		  ->setDecorators(self::$gridElementDecorators);	  

	// phone
	$phone = new Zend_Form_Element_Text('phone');
	$phone->setLabel('Phone: *')
			->setRequired(true)
			->addErrorMessage("Please enter a phone number.")
			->setDecorators(self::$gridElementDecorators);
			
	//email
	$email = new Fisdap_Form_Element_Email('email');
	$email->setLabel('Email: *')
		  ->setRequired(true)
		  ->addErrorMessage('Please enter a valid email address.')
		  ->setDecorators(self::$gridElementDecorators);
		  
	//last name
	$organization = new Zend_Form_Element_Text('organization');
	$organization->setLabel('Organization: *')
		  ->setRequired(true)
		  ->addFilter('StripTags')
		  ->addFilter('HtmlEntities')
		  ->addErrorMessage("Please enter your organization.")
		  ->setDecorators(self::$gridElementDecorators);
		  
	//certification level
	
	//get our cert levels
	$levels = \Fisdap\EntityUtils::getEntityManager()
			->createQuery("SELECT w FROM \Fisdap\Entity\CertificationLevel w WHERE w.id IN (1,3,5,6)")
			->getResult();
	
	$certNames = array();		
			
	foreach ($levels as $level)
		$certNames[$level->id] = $level->description;		
													
	$cert_levels = new Zend_Form_Element_Select('certLevels');
	$cert_levels->setLabel('Certification Level: *')
			->setRequired(true)
			->addErrorMessage("Please select a certification level.")
			->setMultiOptions($certNames)
			->setDecorators(self::$gridElementDecorators);												
		
		
	//certification number
	$cert_number = new Zend_Form_Element_Text('certNumber');
	$cert_number->setLabel('Certification #:')
		  ->addFilter('StripTags')
		  ->addFilter('HtmlEntities')
		  ->setDecorators(self::$gridElementDecorators);
	
	//certification taught
	$cert_taught = new Zend_Form_Element_Select('certTaught');
	$cert_taught->setLabel('Primary Certification Level Taught: *')
			->setRequired(true)
			->addErrorMessage("Please select a primary certification level taught.")
			->setMultiOptions($certNames)
			->setDecorators(self::$gridElementDecorators);
	
	//address1
	$address1 = new Zend_Form_Element_Text('address1');
	$address1->setLabel('Address (line 1):')
			 ->setDecorators(self::$gridElementDecorators);
	
	//address2
	$address2 = new Zend_Form_Element_Text('address2');
	$address2->setLabel('Address (line 2):')
			 ->setDecorators(self::$gridElementDecorators);
	
	//city
	$city = new Zend_Form_Element_Text('city');
	$city->setLabel('City:')
		 ->setDecorators(self::$gridElementDecorators);
	
	//state
	$state = new Fisdap_Form_Element_States('state');
	$state->setLabel('State:')
		   ->addValidator('NotEmpty', false, array('string'))
		   ->useFullNames()
		   ->setDecorators(self::$gridElementDecorators);
	
	//zip
	$zip = new Zend_Form_Element_Text('zip');
	$zip->setLabel('Zip:')
		->addValidator('Digits', true)
		->addValidator('LessThan', true, array('max' => '99999'))
		->setDecorators(self::$gridElementDecorators);
		
	//submit
	$submit = new Fisdap_Form_Element_SaveButton('submit');
	$submit->setLabel('Continue')
		   ->setDecorators(self::$buttonDecorators);	
		
	$this->addElements(array($locations,$first,$last,$user,$phone,$email,$organization,$cert_number,
							 $cert_levels,$cert_taught,$address1,$address2,
							 $city,$state,$zip,$submit));
	
	//Set the decorators for the form
	$this->setDecorators(array(
		'FormErrors',
		'PrepareElements',
		array('ViewScript', array('viewScript' => "forms/eventsForm.phtml")),
		'Form'
	));
	}
	
	public function process($post)
	{
						
	$workshops = \Fisdap\EntityUtils::getEntityManager()->createQuery("SELECT w FROM \Fisdap\Entity\Workshop w WHERE w.date >= '" . date_create("now")->format("Y-m-d"). "'")->getResult();			
				
	foreach ($workshops as $workshop)
	{
		$wsIds[] = $workshop->id;
	}		

	if ($this->isValid($post)) {
		$values = $this->getValues();
				
		//Create new attendee entity
		$attendee = \Fisdap\EntityUtils::getEntity('Attendee');
		$attendee->first_name = $values['firstName'];
		$attendee->last_name = $values['lastName'];
		$attendee->address1 = $values['address1'];
		$attendee->address2 = $values['address2'];
		$attendee->city = $values['city'];
		$attendee->state = $values['state'];
		$attendee->zipcode = $values['zip'];
		$attendee->email = $values['email'];
		$attendee->user_name = $values['userName'];
		$attendee->cert_lvl = $values['certLevels'];
		$attendee->cert_lvl_taught = $values['certTaught'];
		$attendee->cert_num = $values['certNumber'];
		$attendee->organization = $values['organization'];
		$attendee->phone = $values['phone'];
		$attendee->workshop = $wsIds[$values['locations']];
		
		//Save the changes and flush
		$attendee->save();			 
				 
		$mailToAttendee = new \Fisdap_TemplateMailer();
		$mailToAttendee->addTo($attendee->email)
			 ->setSubject($attendee->workshop->email_subject)
			 ->setViewParam('workshop', $attendee->workshop)
			 ->sendHtmlTemplate('new-workshop-registration.phtml');

				
		}
	return $attendee->id;
	}
		
		
		
}