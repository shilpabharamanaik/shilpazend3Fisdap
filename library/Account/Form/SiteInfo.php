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
 * Form for managing basic site information
 */

/**
 * @package    Fisdap
 * @subpackage Account
 */
class Account_Form_SiteInfo extends Fisdap_Form_Base
{
	/**
	 * @var Fisdap\Entity\ProgramLegacy
	 */
	public $program;

	/**
	 * @var array
	 */
	public $associated_programs;

	/**
	 * @var Fisdap\Entity\SiteLegacy
	 */
	public $site;
	
	public $newSite = false;
	
	public $sharing_icon = "";
	
	/**
	 * @var array decorators for individual elements
	 */
	public static $elementDecorators = array(
		'ViewHelper',
		array('Label', array('tag' => 'div', 'class'=>'label-div', 'placement' => 'append')),
		array('Description', array('tag' => 'div', 'class' => 'form-desc', 'placement' => 'append')),
	);
	
	/**
	 * @var array decorators for individual elements
	 */
	public static $leftElementDecorators = array(
		'ViewHelper',
		array('Label', array('tag' => 'div', 'class'=>'label-div', 'placement' => 'prepend')),
		array('Description', array('tag' => 'div', 'class' => 'form-desc', 'placement' => 'append')),
	);

	public function __construct($siteId = null, $options = null)
	{
		$user = \Fisdap\Entity\User::getLoggedInUser();
		$programId = $user->getProgramId();
		$this->program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);

		
		if ($siteId) {
			$this->site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $siteId);
			$sharing_status = $this->program->getSharedStatus($siteId);
			
			if($sharing_status != 0){
				if($sharing_status == 1 || $sharing_status == 2){
					// not in network (could be pending)
					$this->sharing_icon = '<img id="sharing_icon" src="/images/icons/not-in-network.png">';
				}
				else if ($sharing_status == 3){
					// in network - not admin
					$this->sharing_icon = '<img id="sharing_icon" src="/images/icons/sharing.png">';
				}
				else {
					// admin
					$this->sharing_icon = '<img id="sharing_icon" src="/images/icons/sharing-admin.png">';
				}
			}
			
			
		} else {
			$this->site = \Fisdap\EntityUtils::getEntity("SiteLegacy");
			$this->newSite = true;
		}
		

		parent::__construct($options);
	}

	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
		parent::init();
		
		$this->addJsFile("/js/library/Account/Form/site-sub-forms/site-info.js");
		$this->addCssFile("/css/library/Account/Form/site-sub-forms/site-info.css");
		$this->addJsFile("/js/jquery.sliderCheckbox.js");
		$this->addCssFile("/css/jquery.sliderCheckbox.css");
		
		$user = \Fisdap\Entity\User::getLoggedInUser();

		$name = new Zend_Form_Element_Text('name');
		$name->setLabel('Name:')
			 ->setRequired(true)
			 ->setDescription('(required)')
			 ->setAttrib('size', 51)
			 ->setAttrib("class","fancy-input")
			 ->addErrorMessage("Please enter a site name.");

		$abbrev = new Zend_Form_Element_Text('abbrev');
		$abbrev->setLabel('Abbreviation:')
			 ->setRequired(true)
			 ->setDescription('(required)')
			 ->setAttrib('size', 8)
			 ->setAttrib('maxlength', 8)
			 ->setAttrib("class","fancy-input")
			 ->addErrorMessage("Please enter an abbreviation.");
			 
		$type = new Zend_Form_Element_Hidden('type');
		$type->setRequired(true)
			->addErrorMessage("Please select a site type.");

		$address = new Zend_Form_Element_Text('address');
		$address->setLabel('Address:')
			 ->setAttrib('size', 51)
			 ->setAttrib("class","fancy-input")
			 ->setRequired(false);

		$city = new Zend_Form_Element_Text('city');
		$city->setLabel('City:')
			 ->setRequired(true)
			 ->setDescription('(required)')
			 ->setAttrib("class","fancy-input")
			 ->addErrorMessage("Please enter a city.");

		if (!$this->site->id) {
			$country = $this->program->country;
		} else {
			$country = $this->site->country;
		}
		$state = new Fisdap_Form_Element_States('state');
		$state->setLabel('State:')
			  ->setCountry($country)
			  ->setAttrib('style', 'width: 142px')
			  ->setAttrib("class","fancy-input")
			  ->addErrorMessage('Please choose a state.');
		if (count($state->getMultiOptions()) > 0) {
			$state->setRequired(true)
				  ->setDescription('(required)')
				  ->addValidator('NotEmpty', true, array('string'));
		}

		// NOTE: since zip code validation is based on country, additional validation logic
		// can be found in Account_Form_SiteInfo::isValid()
		$zip = new Zend_Form_Element_Text('zip');
		$zip->setLabel('Zip:')
			->setAttrib("class", "fancy-input")
			->setAttrib('size', 8)
			->addErrorMessage('Please enter a valid zip code.');

		$contactName = new Zend_Form_Element_Text('contactName');
		$contactName->setLabel('Name:')
			 ->setAttrib('size', 51)
			 ->setAttrib("class","fancy-input");

		$contactTitle = new Zend_Form_Element_Text('contactTitle');
		$contactTitle->setLabel('Title:')
			->setAttrib('size', 39)
			->setAttrib("class","fancy-input");
			
		$trigger_phone_masking = ($this->program->country == "USA") ? "add-masking" : "";
		
		$contactPhone = new Zend_Form_Element_Text('contactPhone');
		$contactPhone->setLabel('Phone:')
			 ->setAttrib('size', 18)
			 ->setAttrib("class","fancy-input " . $trigger_phone_masking);

		$contactFax = new Zend_Form_Element_Text('contactFax');
		$contactFax->setLabel('Fax:')
			 ->setAttrib('size', 18)
			 ->setAttrib("class","fancy-input " . $trigger_phone_masking);

		$contactEmail = new Fisdap_Form_Element_Email('contactEmail');
		$contactEmail->setLabel('Email:');
		$contactEmail->addDecorator('Label', array('escape'=>false))
			->setAttrib('size', 38)
			->addErrorMessage("Please enter a valid email address.")
			->setAttribs(array("class" => "fancy-input", "autocomplete" => "off"));

		$active = new Zend_Form_Element_Checkbox('active');
		
		
		$site_id = new Zend_Form_Element_Hidden('site_id');

		$this->addElements(array(
			$name,
			$abbrev,
			$type,
			$address,
			$city,
			$state,
			$zip,
			$contactName,
			$contactTitle,
			$contactPhone,
			$contactFax,
			$contactEmail,
			$active,
			$site_id
		));

		$this->setElementDecorators(self::$elementDecorators,
									array('name', 'address',
										  'city', 'state',
										  'contactName',
										  'contactPhone', 'contactFax', 'active'),
									true);
		$this->setElementDecorators(self::$leftElementDecorators,
									array('abbrev', 'zip', 'contactTitle', 'contactEmail'),
									true);
		$active->removeDecorator("Label");
		
		$this->setDecorators(array(
			'PrepareElements',
			array('ViewScript', array('viewScript' => "forms/site-sub-forms/site-info.phtml")),
			'Form'
		));
		$this->setAttrib('id', 'siteInfoForm');

		if ($this->site->id) {
			$this->setDefaults(array(
				"site_id" => $this->site->id,
				"name" => $this->site->name,
				"abbrev" => $this->site->abbreviation,
				"address" => $this->site->address,
				"city" => $this->site->city,
				"state" => $this->site->state,
				"zip" => $this->site->zipcode,
				"contactName" => $this->site->contact_name,
				"contactTitle" => $this->site->contact_title,
				"contactPhone" => $this->site->phone,
				"contactFax" => $this->site->fax,
				"contactEmail" => $this->site->contact_email,
				"type" => $this->site->type,
				"active" => $this->program->isActiveSite($this->site->id)
			));
			
			$active->setAttrib("data-siteId", $this->site->id);
			
		} else {
			$this->setDefaults(array(
				"state" => $this->program->state,
				"active" => true
			));
		}
	}

	public function isValid($data)
	{
		//Check to see if we have a region from the given country code, if so, add some postal code validation
		if (!$this->site->id) {
			$country = $this->program->country;
		} else {
			$country = $this->site->country;
		}
		
		$locale = new \Zend_Locale(\Zend_Locale::getLocaleToTerritory(substr($country, 0, 2)));
		$zip = $this->getElement("zip");
		$zip->clearValidators();

		if ($locale->getRegion()) {
			//Zend sometimes freaks out about postal codes for strange countries
			try {
				$zip->addValidator(new Zend_Validate_PostCode($locale));
			} catch (\Zend_Validate_Exception $e) {
				$zip->clearValidators();
			}
		}

		return parent::isValid($data);
	}

	public function process($data){

		if ($this->isValid($data)) {
			$program = \Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData()->program;
			
			// update our current site
			$this->site->name = $data['name'];
			$this->site->abbreviation = $data['abbrev'];
			$this->site->address = $data['address'];
			$this->site->city = $data['city'];
			$this->site->state = $data['state'];
			$this->site->zipcode = $data['zip'];
			$this->site->contact_name = $data['contactName'];
			$this->site->contact_title = $data['contactTitle'];
			$this->site->contact_email = $data['contactEmail'];
			$this->site->phone = $data['contactPhone'];
			$this->site->fax = $data['contactFax'];
			
			// if this is a new site, add it to the program
			if (!$this->site->id) {
				$this->site->owner_program = $program;
				$this->site->country = $program->country;
				$this->site->type = $data['type'];
			}
			$this->site->save();
			
			if (!$this->program->hasSite($this->site)) {
				$program->addSite($this->site);
			}
			
			// set active field
			$program->toggleSite($this->site, $data['active']);
			
			return $this->site->id;
		
		} else {
			return $this->getMessages();
		}

	}

}
