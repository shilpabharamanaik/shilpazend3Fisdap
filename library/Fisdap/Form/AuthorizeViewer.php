<?php

/**
 *  Form to authorize viewing of National EMS Bike Ride Report
 */

class Fisdap_Form_AuthorizeViewer extends Fisdap_Form_Base
{
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
        
        //pass key
        $key = new Zend_Form_Element_Password('key');
        $key->setLabel('Please enter your Fisdap issued pass code: *')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->addErrorMessage("Please enter a valid pass code.")
                  ->setDecorators(self::$gridElementDecorators);
				  
		//submit
		$submit = new Fisdap_Form_Element_SaveButton('submit');
		$submit->setLabel('Submit Code')
				->setDecorators(self::$buttonDecorators);		  
				  
		$this->addElements(array($key, $submit));
	}
	
	public function process($post)
	{
		if ($this->isValid($post)) {
			$values = $this->getValues();
			
			//Attempt to validate this pass key
			$bikeRides = \Fisdap\EntityUtils::getRepository("BikeRideEvent")->findBy(array("passcode" => $values['key']));
			
			if(count($bikeRides)) {
				return $values['key'];
			} else {
			   return false;
			}
		}
	}
}