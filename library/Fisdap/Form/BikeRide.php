<?php

/**
* Form for the EMS Memorial Bike Ride
*/

class Fisdap_Form_BikeRide extends Fisdap_Form_Base
{
    public $event;

    public function __construct($eventid)
    {
        $this->event = \Fisdap\EntityUtils::getEntity("BikeRideEvent", $eventid);
    
        parent::__construct();
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();
    
        $this->addJsFile("/js/library/Fisdap/Form/bike-ride.js");
    
        //add js file to do cool input masking
        $this->addJsFile("/js/jquery.maskedinput-1.3.js");

        $this->setAttrib('id', 'bikeRideForm');
    
        $this->addJsOnLoad(
    '$("#homePhone").mask("999-999-9999");
	 $("#workPhone").mask("999-999-9999? x99999");
	 $("#cellPhone").mask("999-999-9999");
	 $("#emPhone").mask("999-999-9999");
	 '
    
    );
    
        //first name
        $first = new Zend_Form_Element_Text('firstName');
        $first->setLabel('First Name*')
          ->setRequired(true)
          ->addFilter('StripTags')
          ->addFilter('HtmlEntities')
          ->addErrorMessage("Please enter a first name.")
          ->setDecorators(self::$gridElementDecorators);
    
        //last name
        $last = new Zend_Form_Element_Text('lastName');
        $last->setLabel('Last Name*')
          ->setRequired(true)
          ->addFilter('StripTags')
          ->addFilter('HtmlEntities')
          ->addErrorMessage("Please enter a last name.")
          ->setDecorators(self::$gridElementDecorators);
          
        //home phone
        $homePhone = new Zend_Form_Element_Text('homePhone');
        $homePhone->setLabel('Home Phone')
              ->setDecorators(self::$gridElementDecorators);
    
        //work phone
        $workPhone = new Zend_Form_Element_Text('workPhone');
        $workPhone->setLabel('Work Phone')
              ->setDecorators(self::$gridElementDecorators);
    
        //cell phone
        $cellPhone = new Zend_Form_Element_Text('cellPhone');
        $cellPhone->setLabel('Cell Phone')
              ->setDecorators(self::$gridElementDecorators);
              
        //address1
        $address1 = new Zend_Form_Element_Text('address1');
        $address1->setLabel('Address 1*')
             ->setRequired(true)
             ->addErrorMessage("Please enter a street address.")
             ->setDecorators(self::$gridElementDecorators);
    
        //address2
        $address2 = new Zend_Form_Element_Text('address2');
        $address2->setLabel('Address 2')
             ->setDecorators(self::$gridElementDecorators);
    
        //city
        $city = new Zend_Form_Element_Text('city');
        $city->setLabel('City*')
         ->setRequired(true)
         ->addErrorMessage("Please enter a city name.")
         ->setDecorators(self::$gridElementDecorators);
    
        //state
        $state = new Fisdap_Form_Element_States('state');
        $state->setLabel('State*')
           ->addValidator('NotEmpty', false, array('string'))
           ->useFullNames()
           ->setRequired(true)
           ->addErrorMessage('Please choose a state.')
           ->setDecorators(self::$gridElementDecorators);
    
        //zip
        $zip = new Zend_Form_Element_Text('zip');
        $zip->setLabel('Zip*')
        ->addValidator('Digits', true)
        ->addValidator('LessThan', true, array('max' => '99999'))
        ->setRequired(true)
        ->addErrorMessage('Please choose a valid zip code.')
        ->setDecorators(self::$gridElementDecorators);
        
        //email
        $email = new Fisdap_Form_Element_Email('email');
        $email->setLabel('Email*')
          ->setRequired(true)
          ->addErrorMessage('Please enter a valid email address.')
          ->setDecorators(self::$gridElementDecorators);
          
        //emergency contact name
        $emName = new Zend_Form_Element_Text('emName');
        $emName->setLabel('Name*')
          ->setRequired(true)
          ->addFilter('StripTags')
          ->addFilter('HtmlEntities')
          ->addErrorMessage("Please enter an emergency contact's name.")
          ->setDecorators(self::$gridElementDecorators);
          
        //emergency contact relationship
        $emRel = new Zend_Form_Element_Text('emRel');
        $emRel->setLabel('Relationship*')
          ->setRequired(true)
          ->addFilter('StripTags')
          ->addFilter('HtmlEntities')
          ->addErrorMessage("Please enter your relationship to this contact.")
          ->setDecorators(self::$gridElementDecorators);
          
        //emergency contact phone
        $emPhone = new Zend_Form_Element_Text('emPhone');
        $emPhone->setLabel('Phone*')
            ->setRequired(true)
            ->addErrorMessage("Please enter your emergency contact's phone number.")
            ->setDecorators(self::$gridElementDecorators);
                
        //role
        $role= new Zend_Form_Element_Radio('role');
        $role->setRequired(true)
         ->setLabel('What will your primary role be as participant? *')
         ->addErrorMessage("Please check at least one role.");
         
        $roles = $this->event->getRoleIds();
        foreach ($roles as $role_id) {
            $role_entity = \Fisdap\EntityUtils::getEntity("BikeRideRole", $role_id);
            $role_array[$role_entity->id] = $role_entity->getSummary();
        }
        
        $role->setMultiOptions($role_array);
        
        
        //days
        $days = new Zend_Form_Element_MultiCheckbox('days');
        $days->setRequired(true)
         ->setLabel('Please check all days that apply: *')
         ->addErrorMessage("Please check at least one day.");
         
        $start_compare = strtotime($this->event->start_date->format('Y-m-d'));
        $end_compare = strtotime($this->event->end_date->format('Y-m-d'));
        $start_temp = clone $this->event->start_date;
         
        for (;$start_compare <= $end_compare;) {
            $temp = $start_temp->format('m/d (D)');
            $days_array[$temp] =  $temp;
            $start_temp->add(new DateInterval('P1D'));
            $start_compare = strtotime($start_temp->format('Y-m-d'));
        }
    
        $days->setMultiOptions($days_array);
    
        // estimated guest count
        $guests = new Zend_Form_Element_Select('guests');
        $guests->setRequired(false)
        ->setLabel('Estimated number of guests:')
        ->setMultiOptions(array(0 => 0, 1 => 1, 2 => 2, 3 => 3, 4 => 4));
    
    
        //jerseysize
        $jerseysize = new Zend_Form_Element_Select('jerseysize');
        $jerseysize->setLabel('Please only indicate a size jersey if you are riding for 3 or more days OR are riding and intend to buy a jersey')
         ->setMultiOptions(array(
                   "None" => '--Select One--',
                   "None" => 'No Jersey Requested',
                   "Men's Extra Small" => "Men's Extra Small",
                   "Men's Small" => "Men's Small",
                   "Men's Medium" => "Men's Medium",
                   "Men's Large" => "Men's Large",
                   "Men's XL"=> "Men's XL",
                   "Men's XXL" => "Men's XXL",
                   "Men's XXXL" => "Men's XXXL",
                   "Women's Extra Small" => "Women's Extra Small",
                   "Women's Small" => "Women's Small",
                   "Women's Medium" => "Women's Medium",
                   "Women's Large" => "Women's Large",
                   "Women's XL" => "Women's XL",
                   "Women's XXL" => "Women's XXL"));
               
        //tshirtsize
        $tshirtsize = new Zend_Form_Element_Select('tshirtsize');
        $tshirtsize->setLabel('Please indicate your tshirt size: *')
               ->setMultiOptions(array(
                                       0 => '--Select One--',
                                       "Small" => "Small",
                                       "Medium" => "Medium",
                                       "Large" => "Large",
                                       "XL" => "XL",
                                       "XXL" => "XXL",
                                       "XXXL" => "XXXL"))
                ->addErrorMessage("Please choose a tshirt.");
               
        //specialneeds
        $special = new Zend_Form_Element_Textarea('special');
        $special->setLabel('Please indicate any special needs below: ')
            ->setAttrib('rows', '6');
    
        //why
        $why = new Zend_Form_Element_Textarea('why');
        $why->setLabel('For a partner? To support the community? For Fun?')
            ->setAttrib('rows', '6');
            
        //comments
        $comments = new Zend_Form_Element_Textarea('comments');
        $comments->setLabel('Know a good rest stop? A potential sponsor? Details on how you can help? Let us know!')
             ->setAttrib('rows', '6');
            
        //liability
        $liability = new Zend_Form_Element_Checkbox('liability');
        $liability->setLabel('Please check to indicate you agree with the following statements.')
              ->setRequired(true)
              ->setUncheckedValue(null)
              ->addErrorMessage("Please sign the Liability release.");
    
        //submit
        $submit = new Fisdap_Form_Element_SaveButton('submit');
        $submit->setLabel('Submit Form')
           ->setDecorators(self::$buttonDecorators);
           
        //event id
        $eventId = new Zend_Form_Element_Hidden('eventId');
        $eventId->setDecorators(self::$hiddenElementDecorators);
        $eventId->setValue($this->event->id);
    
        $this->addElements(array($first,$last,$homePhone,$workPhone,$cellPhone,$address1,
                             $address2,$city,$state,$zip,$email,$emName,$emPhone,$emRel
                             ,$role,$days,$jerseysize,$tshirtsize,$special,$why,$comments,
                             $liability,$submit,$eventId, $guests));
            
        //Set the decorators for the form
        $this->setDecorators(array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/bikeRideForm.phtml")),
        'Form'
    ));
    }

    public function process($post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();

            //Create new bikerider entity
            $biker = \Fisdap\EntityUtils::getEntity('BikeRiderData');
            $biker->first_name = $values['firstName'];
            $biker->last_name = $values['lastName'];
            $biker->address1 = $values['address1'];
            $biker->address2 = $values['address2'];
            $biker->city = $values['city'];
            $biker->state = $values['state'];
            $biker->zipcode = $values['zip'];
            $biker->email = $values['email'];
            $biker->home_phone = $values['homePhone'];
            $biker->cell_phone = $values['cellPhone'];
            $biker->work_phone = $values['workPhone'];
            $biker->emergency_contact = $values['emName'];
            $biker->emergency_relation = $values['emRel'];
            $biker->emergency_phone = $values['emPhone'];
            $biker->role = $values['role'];
            $biker->days = implode(", ", $values['days']);
            $biker->jersey_size = $values['jerseysize'];
            $biker->shirt_size = $values['tshirtsize'];
            $biker->special_needs = $values['special'];
            
            $biker->liability = $values['liability'];
            $biker->event = $values['eventId'];
            $biker->estimate_guest_count = $values['guests'];
            
            //
            $biker->why_ride = "";
            $biker->suggestions = "";
            
            //Save the changes and flush
            $biker->save();
            
            $mailToUser = new \Fisdap_TemplateMailer();
            $mailToUser->addTo($biker->email)
                 ->setSubject("EMS Bike Ride:  Registration Received")
                 ->setViewParam('date', $biker->event->start_date->format('Y'))
                 ->sendHtmlTemplate('new-bike-ride.phtml');
                 
            $mailToOrganizer = new \Fisdap_TemplateMailer();
            
            //Check to see if the event has custom emails to send to
            if ($biker->event->email_list) {
                $emails = explode(",", str_replace(";", ",", $biker->event->email_list));
            } else {
                $emails = array("registration@muddyangels.org");
            }
            
            $mailToOrganizer->addTo($emails)
                 ->setSubject("EMS Bike Ride:  Registration Received")
                 ->setViewParam('item', $biker)
                 ->sendHtmlTemplate('new-ride-to-org.phtml');
        }
        return $biker->id;
    }
}
