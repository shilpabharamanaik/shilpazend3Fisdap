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
 * Form for creating and editing a program
 */
use Fisdap\Members\Commerce\Events\CustomerWasUpdated;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * @package    Account
 */
class Account_Form_Program extends Fisdap_Form_Base
{
	/**
	 * @var array the decorators for the form
	 */
	protected static $_formDecorators = array(
		'FormErrors',
		'PrepareElements',
		array('ViewScript', array('viewScript' => "forms/newProgramForm.phtml")),
		array('Form'),
	);

    /**
     * @var \Fisdap\Entity\User
     */
    public $user;

	/**
	 * @var \Fisdap\Entity\ProgramLegacy
	 */
	public $program;

	/**
	 * @var boolean
	 */
	public $includeEmsInfo;

	/**
	 * @var integer
	 */
	public $profession;

	/**
	 * @var string
	 */
	public $professionName;

	/**
	 * @var string
	 */
	public $certifications;

	/**
	 * @param int $programId the id of the program
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($programId = null, $includeEmsInfo = false, $professionId = null, $options = null)
	{
        if ($username = Zend_Auth::getInstance()->getIdentity()) {
            $this->user = \Fisdap\Entity\User::getByUsername($username);
        }

		$this->program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $programId);

		//If we have a program already, just get the info from that
		if ($this->program->id) {
			$this->includeEmsInfo = $this->program->isTrainingProgram();
			$this->profession = $this->program->profession->id;
			$this->professionName = $this->program->profession->name;
		} else {
			$this->includeEmsInfo = $includeEmsInfo;

			//Default profession to EMS if none is chosen
			$this->profession = $professionId > 0 ? $professionId : 1;
			$this->professionName = \Fisdap\EntityUtils::getEntity("Profession", $this->profession)->name;
		}


		//Create a string listing all the associations for the given certification level
		$certs = \Fisdap\EntityUtils::getRepository("CertificationLevel")->findByProfession($this->profession);

		if (count($certs) > 0) {
			foreach($certs as $cert) {
				$this->certifications[] = $cert->description;
			}
			$this->certifications = implode(", ", $this->certifications);
		}


		parent::__construct($options);
	}

	/**
	 * init method that adds all the elements to the form
	 */
	public function init()
	{
        parent::init();

		$this->setAttrib('id', 'programForm');

		//add js file to do cool input masking
        $this->addJsFile("/js/jquery.maskedinput-1.3.js");

		//add js file to do awesome live validation, etc
		$this->addJsFile("/js/library/Account/Form/program.js");
		$this->addCssFile("/css/library/Account/Form/program.css");

		$this->addJsOnLoad('$("#phone").mask("999-999-9999? x99999");');

        //Name
        $name = new \Zend_Form_Element_Text("name");
        $name->setLabel("Organization name:")
             ->setRequired(true)
			 ->addErrorMessage("Please enter an organization name.");
        $this->addElement($name);

			//disabled program ID
		$programId = new Zend_Form_Element_Text("programId");
		$programId->setLabel("Program Id:")
             ->setAttrib('readonly', 'true');
		$this->addElement($programId);
		
        //Abbreviation
        $abbreviation = new \Zend_Form_Element_Text("abbreviation");
        $abbreviation->setLabel("Abbreviation:")
                     ->setRequired(true)
					 ->addErrorMessage("Please enter an abbreviation.");

        $this->addElement($abbreviation);

		//Contact Info
        $addressLineOne = new Zend_Form_Element_Text("addressLineOne");
        $addressLineOne->setLabel("Address")
                       ->setRequired(true)
        			   ->addErrorMessage("Please enter a street address.");
		$this->addElement($addressLineOne);

        $addressLineTwo = new Zend_Form_Element_Text("addressLineTwo");
        $addressLineTwo->setLabel("(line 2)");
        $this->addElement($addressLineTwo);

        $addressLineThree = new Zend_Form_Element_Text("addressLineThree");
        $addressLineThree->setLabel("(line 3)");
        $this->addElement($addressLineThree);

        $city = new Zend_Form_Element_Text("city");
        $city->setLabel("City:")
             ->setRequired(true)
			 ->addErrorMessage("Please enter a city.");
        $this->addElement($city);

        $country = new Fisdap_Form_Element_Countries("country");
        $country->setLabel("Country:")
                ->setRequired(true)
                ->addErrorMessage("Please choose a country.");
        $this->addElement($country);

        $state = new Fisdap_Form_Element_States("state");
        $state->setLabel("State/Provence:")
              ->addValidator(new \Fisdap_Validate_States("country"));
        $this->addElement($state);

		if ($this->program->id) {
			$state->setCountry($this->program->country);
		} else {
			$state->setCountry($country->getValue());
		}

		//Please note, additional validators are added to this field in the Account_Form_Program::isValid()
        $zip = new Zend_Form_Element_Text("zip");
        $zip->setLabel("Postal Code:")
            ->setRequired(true)
			->addErrorMessage("Please enter a valid zip code.");
        $this->addElement($zip);

        $timezone = new Zend_Form_Element_Select("timezone");
        $timezone->setLabel("Time zone:")
                 ->setMultiOptions(\Fisdap\Entity\Timezone::getFormOptions());
        $this->addElement($timezone);

        $phone = new Zend_Form_Element_Text("phone");
        $phone->setLabel("Phone:")
              ->setRequired(true)
			  ->addErrorMessage("Please enter a phone number.");
        $this->addElement($phone);

		//Billing Info
        $billingElements = array();

        $addressLineOne = new Zend_Form_Element_Text("billingAddressLineOne");
        $addressLineOne->setLabel("Address")
                       ->setRequired(true)
        			   ->addErrorMessage("Please enter a billing street address.");
        $billingElements[] = $addressLineOne;

        $addressLineTwo = new Zend_Form_Element_Text("billingAddressLineTwo");
        $addressLineTwo->setLabel("(line 2)");
        $billingElements[] = $addressLineTwo;

        $addressLineThree = new Zend_Form_Element_Text("billingAddressLineThree");
        $addressLineThree->setLabel("(line 3)");
        $billingElements[] = $addressLineThree;

        $city = new Zend_Form_Element_Text("billingCity");
        $city->setLabel("City:")
             ->setRequired(true)
			 ->addErrorMessage("Please enter a billing city.");
        $billingElements[] = $city;

        $billingCountry = new Fisdap_Form_Element_Countries("billingCountry");
        $billingCountry->setLabel("Country:")
                ->setRequired(true)
				->setStateElementName("billingState")
                ->addErrorMessage("Please choose a billing country.");
        $billingElements[] = $billingCountry;

        $billingState = new Fisdap_Form_Element_States("billingState");
        $billingState->setLabel("State/Provence:")
              ->addValidator(new \Fisdap_Validate_States("billingCountry"));
        $billingElements[] = $billingState;

		if ($this->program->id) {
			$billingState->setCountry($this->program->billing_country);
		} else {
			$billingState->setCountry($billingCountry->getValue());
		}

		//Please note, additional validators are added to this field in the Account_Form_Program::isValid()
        $zip = new Zend_Form_Element_Text("billingZip");
        $zip->setLabel("Postal Code:")
            ->setRequired(true)
			->addErrorMessage("Please enter a valid billing zip code.");
        $billingElements[] = $zip;

        $phone = new Zend_Form_Element_Text("billingPhone");
        $phone->setLabel("Phone:")
              ->setRequired(true)
			  ->addErrorMessage("Please enter a billing phone number.");
        $billingElements[] = $phone;

		$billingContact = new Zend_Form_Element_Text("billingContact");
        $billingContact->setLabel("Billing Contact:")
              ->setRequired(true)
			  ->addErrorMessage("Please enter a billing contact.");
        $billingElements[] = $billingContact;

		$billingEmail = new Zend_Form_Element_Text("billingEmail");
        $billingEmail->setLabel("Email:")
              ->setRequired(true)
			  ->addErrorMessage("Please enter a billing email address.");
        $billingElements[] = $billingEmail;

        //Only add billing elements if we're editing a program
        if ($this->program->id) {
            $this->addElements($billingElements);
        }

        // staff only tool to set ordering permissions
        if ($this->user && $this->user->isStaff()) {
            $orderPermissions = new Zend_Form_Element_Radio("orderPermissions");
            $orderPermissions->setMultiOptions(\Fisdap\Entity\OrderPermission::getFormOptions(false, false));
            $orderPermissions->setLabel("Can this program order accounts?");
            $orderPermissions->setRegisterInArrayValidator(false);
            $this->addElement($orderPermissions);
        }

		$professionId = new Zend_Form_Element_Hidden("professionId");
		$this->addElement($professionId);

		$save = new \Fisdap_Form_Element_SaveButton("save");
		$this->addElement($save);

		$primaryContact = new \Zend_Form_Element_Select("primaryContact");
		$primaryContact->setLabel("Primary Contact:");
		$instructors = \Fisdap\EntityUtils::getRepository('User')->getAllInstructorsByProgram($this->program->id);
		foreach($instructors as $instructor) {
			$primaryContact->addMultiOption($instructor['id'], $instructor['first_name'] . " " . $instructor['last_name']);
		}
		$this->addElement($primaryContact);

		//EMS specific settings
		$studentsPerYear = new \Zend_Form_Element_Text("studentsPerYear");
		$studentsPerYear->setLabel("Estimated # of students/year");

		$accredited = new Fisdap_Form_Element_jQueryUIButtonset("accredited");
		$accredited->setLabel("CAAHEP accredited?")
				   ->setOptions(array(1 => "Yes", 0 => "No"));

        //Record the school's CoAEMSP identifier
        $coaemspProgramId = new \Zend_Form_Element_Text('coaemspProgramId');
        $coaemspProgramId->setLabel('CoAEMSP Program ID number (600xxx)')
                         ->addValidator(new Zend_Validate_Digits(), true)
                         ->addValidator(new Zend_Validate_StringLength(array('min' => 6, 'max' => 6)))
                         ->addErrorMessage('Please enter a 6 digit CoAEMSP Program ID');

        //Record the initial accreditation year for this program
        $years = array(0 => 'Year');
        foreach(range(date("Y"), 1994) as $year) {
            $years[$year] = $year;
        }
        $yearAccredited = new \Zend_Form_Element_Select('yearAccredited');
        $yearAccredited->setLabel('Initial Accreditation Year<br />(full CAAHEP approval)')
            ->setMultiOptions($years);

		//how did you hear about Fisdap?
		$referral = new Zend_Form_Element_Select('referral');
		$referral->setLabel('How did you hear about Fisdap? ')
				   ->setMultiOptions(array(
						0 => '--Select One--',
						"Blog" => "Blog or Forum",
						"Event" => "Event",
						"Friend" => "Friend or Colleague",
						"Search" => "Search Engine",
						"Social" => "Social Media",
						"Other" => "Other"));
				   
		//referral description
		$description = new Zend_Form_Element_Textarea('description');
		$description->setLabel('Please specify: ')
			->setAttrib('rows', '3')
			->setAttrib('cols', '34');		   

		$trainingElements = array($studentsPerYear);

        //Only include these form elements for EMS programs
		if ($this->profession == 1) {
			$trainingElements[] = $accredited;
			$trainingElements[] = $coaemspProgramId;
			$trainingElements[] = $yearAccredited;
		}
		
		$referArray = array($referral, $description);

		if ($this->includeEmsInfo) {
			$this->addDisplayGroup(
				$trainingElements,
				'emsInfo',
				array('description' => 'Optional Info', 'decorators' => array(
					array('Description', array('tag' => 'div', 'class' => 'form-group-title section-header no-border')),
					'FormElements',
					array('HtmlTag', array('tag' => 'div', 'class' => 'form-group')),
				))
			);
		}
		
		$this->addDisplayGroup(
				$referArray,
				'referralGroup',
				array('description' => 'Optional Info', 'decorators' => array(
					array('Description', array('tag' => 'div', 'class' => 'form-group-title section-header no-border')),
					'FormElements',
					array('HtmlTag', array('tag' => 'div', 'class' => 'form-group')),
				))
			);


        $this->setElementDecorators(self::$gridElementDecorators, array('orderPermissions'), false);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('professionId', 'save'), true);
        $this->setElementDecorators(self::$longLabelGridElementDecorators, array('accredited', 'studentsPerYear', 'referral', 'coaemspProgramId', 'yearAccredited'), true);
        $this->setElementDecorators(self::$elementDecorators, array('description'), true);
		$this->setDecorators(self::$_formDecorators);

        if ($this->program->id) {
            $this->setDefaults(array(
                "name" => $this->program->name,
				"programId" => $this->program->id,
                "abbreviation" => $this->program->abbreviation,
                "addressLineOne" => $this->program->address,
                "addressLineTwo" => $this->program->address2,
                "addressLineThree" => $this->program->address3,
                "city" => $this->program->city,
                "country" => $this->program->country,
                "state" => $this->program->state,
                "zip" => $this->program->zip,
                "timezone" => $this->program->program_settings->timezone->id,
                "phone" => $this->program->phone,
				"studentsPerYear" => $this->program->class_size,
				"accredited" => $this->program->accredited,
				"programId" => $this->program->id,
				"professionId" => $this->program->profession->id,
				"primaryContact" => $this->program->program_contact,
				"billingAddressLineOne" => $this->program->billing_address,
                "billingAddressLineTwo" => $this->program->billing_address2,
                "billingAddressLineThree" => $this->program->billing_address3,
                "billingCity" => $this->program->billing_city,
                "billingCountry" => $this->program->billing_country,
                "billingState" => $this->program->billing_state,
                "billingZip" => $this->program->billing_zip,
                "billingPhone" => $this->program->billing_phone,
				"billingEmail" => $this->program->billing_email,
				"billingContact" => $this->program->billing_contact,
                "orderPermissions" => $this->program->order_permission->id,
                "coaemspProgramId" => $this->program->coaemsp_program_id,
                "yearAccredited" => $this->program->year_accredited,
            ));
        } else {
            $this->setDefaults(array(
                "country" => "USA",
				"billingCountry" => "USA",
                "timezone" => 2,
				"professionId" => $this->profession,
            ));
        }
    }

	public function isValid($post)
	{
		//Check to see if we have a region from the given country code, if so, add some postal code validation
		$locale = new \Zend_Locale(\Zend_Locale::getLocaleToTerritory(substr($post['country'], 0, 2)));
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

		return parent::isValid($post);
	}

    /**
     * @param $data
     * @param Dispatcher $dispatcher
     * @return bool|Mixed
     * @throws Exception
     */
    public function process($data, Dispatcher $dispatcher)
    {
		//Stupid hack to get the right options into the state select before processing
		$this->getElement("state")->setCountry($data['country']);

		if ($this->isValid($data)) {
			$values = $this->getValues();

			if ($values['programId']) {
				$program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $values['programId']);
			} else {
				$program = \Fisdap\EntityUtils::getEntity("ProgramLegacy");
			}

			$program->name = $values['name'];
			$program->abbreviation = $values['abbreviation'];
			$program->address = $values['addressLineOne'];
			$program->address2 = $values['addressLineTwo'];
			$program->address3 = $values['addressLineThree'];
			$program->city = $values['city'];
			$program->country = $values['country'];
			$program->state = $values['state'];
			$program->zip = $values['zip'];
			$program->program_settings->timezone = $values['timezone'];
			$program->phone = $values['phone'];
			$program->class_size = $values['studentsPerYear'];
			$program->accredited = $values['accredited'];
			$program->profession = $values['professionId'];
            $program->year_accredited = $values['yearAccredited'] ? $values['yearAccredited'] : null;
            $program->coaemsp_program_id = $values['coaemspProgramId'] ? $values['coaemspProgramId'] : null;

            if ($this->user && $this->user->isStaff()) {
                $program->set_order_permission($values['orderPermissions']);
            }

			//Only set the primary contact and billing info if the program already exists
			if ($program->id) {
				$program->program_contact = $values['primaryContact'];
				$program->billing_address = $values["billingAddressLineOne"];
                $program->billing_address2 = $values["billingAddressLineTwo"];
                $program->billing_address3 = $values["billingAddressLineThree"];
                $program->billing_city = $values["billingCity" ];
				$program->billing_country = $values["billingCountry"];
				$program->billing_state = $values["billingState"];
                $program->billing_zip = $values["billingZip"];
                $program->billing_phone = $values["billingPhone"];
				$program->billing_email = $values["billingEmail"];
				$program->billing_contact = $values["billingContact"];
			} else {
				//Only set the referral for a new program
				$program->referral = $values['referral'];
				$program->ref_description = $values['description'];

                // Set default order permission to "can order"
                $program->set_order_permission(1);
			}

            $program->save();

			//Generate the product code ID for a new program only
			//this has to happen after the program has been saved because we need the new program's ID
			//We also need to copy the program address into the billing address for new programs
			if (!$values['programId']) {
				$program->generateProductCodeId();
				$program->populateBillingAddress();
				
				$program->save();
			}
			
            if ($program->customer_name) {
                $dispatcher->fire(new CustomerWasUpdated($program->id, $program->customer_name, $program->customer_id));
            }

			return $program->id;
		}

		return false;
    }
}