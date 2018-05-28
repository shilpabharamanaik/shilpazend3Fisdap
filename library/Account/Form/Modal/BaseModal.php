<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * @author     Hammer :)
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_Modal_BaseModal extends Fisdap_Form_Base
{
	public $site;
	
	public $user;
	
	public $base_department;
	
	public $site_type;
	
	public $base;

    /**
     * @var bool Are we creating a new base in a sharing network where the logged in user is a site admin
     */
    public $newSharedBase;

    /**
     * @var bool Is this a default clinical department?
     */
    public $isDefault;
	
	/**
	 * @param SiteLegacy $site the current site
	 * @param $options mixed additional Zend_Form options
	 */
	public function __construct($site, $base_id = null, $options = null)
	{
		$this->site = $site;
		$this->site_type = $this->site->type;
		$this->user = \Fisdap\Entity\User::getLoggedInUser();
		$this->base_department = ($this->site_type == "clinical") ? "department" : "base";
		
		if($base_id){
			$this->base = \Fisdap\EntityUtils::getEntity("BaseLegacy", $base_id);
            $this->newSharedBase = false;

            // see if this is a default department
            $this->isDefault = \Fisdap\EntityUtils::getRepository("BaseLegacy")->isDefault($this->base->name);

		} else {
            $program = $this->user->getCurrentProgram();
            $site_admin = $program->isAdmin($this->site->id);
            $shares_site = $program->sharesSite($this->site->id);
            $programCount = count($program->getNetworkPrograms($this->site));
            $this->newSharedBase = $site_admin && $shares_site && ($programCount > 1);
            $this->isDefault = false;
        }
		
		parent::__construct($options);
	}
	
	
	public function init()
	{
		parent::init();

		// To limit the number of external files, modal specific javascript and css are located in:
		//		/js/library/Account/Form/site-sub-forms/bases.js
		//		/css/library/Account/Form/site-sub-forms/bases.css
		if (!$this->isDefault) {
            $name = new Zend_Form_Element_Text('base_name');
            $name->setLabel('Name:')
                ->setRequired(true)
                ->setAttribs(array("class" => "fancy-input"))
                ->addValidator('regex', false, array('/^[^#!$%&*()+={}:;<>?"]+$/'))
                ->addValidator('stringLength', false, array(1, 64))
                ->addErrorMessage("Please provide a name for your " . $this->base_department . ". Names must be less than 64 characters long and cannot contain special characters.")
                ->addDecorator('Label', array('escape' => false));

            // if we're working with a department, there is additional validation
            if ($this->site_type == "clinical") {
                $name->addValidator(new Zend_Validate_Callback(array($this, 'notDefault')));
            }

            $this->addElement($name);
        }

        //Add switch for determining if new bases/depts should be activated for other shared programs
        $activateOthers = new Zend_Form_Element_Hidden("activate_bases");
        $activateOthers->setValue(1)
            ->setDecorators(self::$hiddenElementDecorators);
        $this->addElement($activateOthers);

		// fields for address
        $address = new Zend_Form_Element_Text("base_address");
        $address->setLabel('Address:')
            ->setAttribs(array("class" => "fancy-input"));
        $this->addElement($address);

        $city = new Zend_Form_Element_Text("base_city");
        $city->setLabel("City:")
            ->setAttribs(array("class" => "fancy-input"));
        $this->addElement($city);

        $state = new Fisdap_Form_Element_States("base_state");
        $state->setCountry($this->site->country);
        $state->setLabel("State:")
            ->setAttribs(array("class" => "chzn-select", "data-placeholder" => "Select state/provence", "style" => "width:180px", "tabindex" => "4", "multiple" => false));
        $this->addElement($state);

        // NOTE: since zip code validation is based on country, additional validation logic
        // can be found in Account_Form_Modal_BaseModal::isValid()
        $zip = new Zend_Form_Element_Text("base_zip");
        $zip->setLabel("Zip:")
            ->setAttribs(array("class" => "fancy-input zip-code"))
            ->addErrorMessage('Please enter a valid zip code.');
        $this->addElement($zip);
		
		if($this->base){
			$base_id = new Zend_Form_Element_Hidden("base_id");
			$base_id->setValue($this->base->id);
			$this->addElement($base_id);
			$this->setDefaults(array('base_id' => $this->base->id));
            if (!$this->isDefault) {
                $this->setDefaults(array('base_name' => $this->base->name));
            }
		}

        $this->setDefaults(array('base_address' => ($this->base) ? $this->base->address : $this->site->address,
            'base_city' => ($this->base) ? $this->base->city : $this->site->city,
            'base_state' => ($this->base) ? $this->base->state : $this->site->state,
            'base_zip' => ($this->base) ? $this->base->zip : $this->site->zipcode
        ));
		
		// Set the decorators for the form
		$this->setDecorators(array(
			'FormErrors','PrepareElements',array('ViewScript', array('viewScript' => 'forms/site-sub-forms/modals/base-modal.phtml')),'Form'
		));
		
	}
	
	public function isValid($post)
	{
        //Check to see if we have a region from the given country code, if so, add some postal code validation
        $locale = new \Zend_Locale(\Zend_Locale::getLocaleToTerritory(substr($this->site->country, 0, 2)));
        $zip = $this->getElement("base_zip");
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
	
	public function process($post)
	{
		if ($this->isValid($post)) {
			
			$program = \Fisdap\Entity\User::getLoggedInUser()->getProgram();
			$base = ($this->base) ? $this->base : new \Fisdap\Entity\BaseLegacy;

            // only update the base name is this is not a default department
            if (!$this->isDefault) {
                $base->name = $post['base_name'];
            }
			$base->site = $this->site;
			$base->address = $post['base_address'];
			$base->city = $post['base_city'];
			$base->state = $post['base_state'];
			$base->zip = $post['base_zip'];
			$base->save();
			
			if(!$this->base) {
				$site_admin = $program->isAdmin($this->site->id);
				$shares_site = $program->sharesSite($this->site->id);
				$add_association_for = ($site_admin && $shares_site) ? $program->getNetworkPrograms($this->site) : array($program->id);
				
				foreach($add_association_for as $pro_id){
					$pro = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $pro_id);
                    //If we're adding a base for this program, make it active
                    if ($pro->id == $program->id) {
                        $active = true;
                    } else {
                        //If we're adding a base for other programs, use what they selected
                        $active = $post['activate_bases'] ? 1 : 0;
                    }
					$pro->addBase($base, $active);
				}
				
			}
			
			return array("success" => true, "new_base_id" => $base->id);
			
		}
		else {
			return $this->getMessages();
		}
		
		return false;
	} // end process()
	
	public function notDefault($value) {
		//Get performed by value
		$name = $this->getElement('base_name');
		$base_repo = \Fisdap\EntityUtils::getRepository("BaseLegacy");
		if ($base_repo->isDefault($name->getValue())) {
			$name->setErrorMessages(array("The name you entered already exists as a standard clinical department. Please use the standard department or choose a different name."));
			return false;
		}
		return true;
	}
}
