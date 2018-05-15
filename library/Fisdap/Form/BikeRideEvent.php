<?php

/**
 *  Setup form for the EMS Memorial Bike Ride Form (Formception!!)
 */

class Fisdap_Form_BikeRideEvent extends Fisdap_Form_Base
{
    public $event;
    
    public function __construct($eventid = null)
    {
        $this->event = \Fisdap\EntityUtils::getEntity("BikeRideEvent", $eventid);
        
        parent::__construct();
    }
    
    /**
     * init method that adds all the elements to the form
     * */
    public function init()
    {
        parent::init();
        
        $this->addJsFile("/js/library/Fisdap/Form/bike-ride-event.js");
                
        $this->setAttrib('id', 'bikeRideEventForm');
        
        //origin
        $origin = new Zend_Form_Element_Text('origin');
        $origin->setLabel('Origin: *')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->addErrorMessage("Please enter an origin for this bike ride event.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //route information
        $route = new Zend_Form_Element_Textarea('route_info');
        $route->setLabel('Route Information:')
            ->setRequired(false)
            ->addFilter('StripTags')
            ->addFilter('HtmlEntities')
            ->setDecorators(self::$gridElementDecorators);
        
        //region
        $region = new Zend_Form_Element_Text('region');
        $region->setLabel('Region: *')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->addErrorMessage("Please enter a region for this bike ride event.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //destination
        $destination = new Zend_Form_Element_Text('destination');
        $destination->setLabel('Destination: *')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->addErrorMessage("Please enter a destination for this bike ride event.")
                  ->setDecorators(self::$gridElementDecorators);
         
        //passcode
        $passcode = new Zend_Form_Element_Text('passcode');
        $passcode->setLabel('Passcode: *')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->addErrorMessage("Please choose a passcode for this event.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //email list
        $email_list = new Zend_Form_Element_TextArea('email_list');
        $email_list->setLabel('Email List:')
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->setAttribs(array("cols" => 40, "rows" => 12))
                  ->setDecorators(self::$gridElementDecorators);
         
        //roles
        $role_options = \Fisdap\Entity\BikeRideRole::getFormOptions();
        
        $roles = new Zend_Form_Element_MultiCheckbox('roles');
        $roles->setRequired(true)
             ->setLabel('Please check all roles to be included: *')
                     ->addErrorMessage("Please check at least one role to include.")
                     ->setMultiOptions($role_options)
                     ->setDecorators(self::$gridElementDecorators);
                     
                     
        //start date
        $start = new Zend_Form_Element_Text('start');
        $start->setLabel('Start Date: *')
                  ->setRequired(true)
                  ->addErrorMessage("Please enter a start date for this bike ride event.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //end date
        $end = new Zend_Form_Element_Text('end');
        $end->setLabel('Ending Date: *')
                  ->setRequired(true)
                  ->addErrorMessage("Please enter an ending date for this bike ride event.")
                  ->setDecorators(self::$gridElementDecorators);
        //housing toggle
        $housingtoggle = new Zend_Form_Element_Checkbox('housingtoggle');
        $housingtoggle->setLabel('Show housing section: ')
                ->setDecorators(self::$gridElementDecorators);
        if ($this->event->id) {
            if (is_null($this->event->housing)) {
                $housingtoggle->setValue('0');
            } else {
                $housingtoggle->setValue('1');
            }
        } else {
            $housingtoggle->setValue('1');
        }
        //housing
        $housing = new Zend_Form_Element_Textarea('housing');
        $housing->setLabel('Housing: ')
                ->setDecorators(self::$gridElementDecorators)
                ->setValue('Hotels will be approximately $125/room/night. Hotel and room block lists will be provided to registered riders and support as soon as possible after registration is completed.');
        
        //transport toggle
        $transporttoggle = new Zend_Form_Element_Checkbox('transporttoggle');
        $transporttoggle->setLabel('Show transporation section: ')
                ->setDecorators(self::$gridElementDecorators);
        
        if ($this->event->id) {
            if (is_null($this->event->transportation)) {
                $transporttoggle->setValue('0');
            } else {
                $transporttoggle->setValue('1');
            }
        } else {
            $transporttoggle->setValue('1');
        }
        
        //transportation
        $transportation = new Zend_Form_Element_Textarea('transportation');
        $transportation->setLabel('Transportation: ')
                ->setDecorators(self::$gridElementDecorators)
                ->setValue('The National EMS Memorial Bike ride is not responsible for transportation to/from start and/or end points: UNLESS OTHERWISE STATED.

The National EMS Memorial Bike Ride is not responsible for parking availability or movement for any individual private vehicles anywhere throughout the course.');
        
        //jersey toggle
        $jerseytoggle = new Zend_Form_Element_Checkbox('jerseytoggle');
        $jerseytoggle->setLabel('Show jersey section: ')
                ->setDecorators(self::$gridElementDecorators);
        
        if ($this->event->id) {
            if (is_null($this->event->jersey)) {
                $jerseytoggle->setValue('0');
            } else {
                $jerseytoggle->setValue('1');
            }
        } else {
            $jerseytoggle->setValue('1');
        }
        //Jersey
        $jerseytext = new Zend_Form_Element_Textarea('jerseytext');
        $jerseytext->setLabel('Jersey Information: ')
                ->setDecorators(self::$gridElementDecorators)
                ->setValue('Jersey Size: Anyone riding 3 or more days will receive a NEMSMBR Official Riding Jersey. Day Riders can purchase jerseys for $75 each.');
        
        //tshirt toggle
        $tshirttoggle = new Zend_Form_Element_Checkbox('tshirttoggle');
        $tshirttoggle->setLabel('Show t-shirt section: ')
                ->setDecorators(self::$gridElementDecorators);
        
        if ($this->event->id) {
            if (is_null($this->event->tshirt)) {
                $tshirttoggle->setValue('0');
            } else {
                $tshirttoggle->setValue('1');
            }
        } else {
            $tshirttoggle->setValue('1');
        }
        //TShirt
        $tshirttext = new Zend_Form_Element_Textarea('tshirttext');
        $tshirttext->setLabel('T-Shirt Information: ')
                ->setDecorators(self::$gridElementDecorators)
                ->setValue('T-shirt Size: All participants (support or rider) receive a NEMSMBR Muddy Angels T-shirt.');

        //liability
        $liability = new Zend_Form_Element_Textarea('liability');
        $liability->setLabel('Liability release: ')
                ->setDecorators(self::$gridElementDecorators)
                ->setValue("National EMS Memorial Bike Ride

Rider Acknowledgments and Release of Liability

I ________________________________________ (PRINT RIDER'S NAME) understand that the EMS Memorial Bike Ride
is both a mentally and physically challenging undertaking. I understand the commitment that is involved, in both
time and finances. I am hereby willfully agreeing to make such commitments. I am also hereby certifying that I am
in a good physical condition with no impairments that may be cause for my not being able to participate in the event.

I understand that I am agreeing to partake in this event on my own free will, and do understand that there is a
certain risk of injury occurring to myself during this event. I fully accept and assume the risk and responsibility
for any possible injuries, losses, costs, and/or damages that might occur, and do hereby agree to hold harmless and
release from any and all liability the National EMS Memorial Bike Ride, its Sponsors, Coordinators, affiliates,
agents, officers and directors, and the owners or lessors of any property and/or premises where the Ride or any
Ride-related activities occur (hereinafter collectively referred to as the Ride).

I further agree and acknowledge that the bicycle I will be using in the Ride is in good working order and that I
am familiar with its controls and operation. I agree to abide by all applicable laws, rules and regulations regarding
the operation of bicycles, including but not limited to observing all posted traffic signs and warnings.

I acknowledge the responsibility to conduct myself at all times, including during riding hours and during after-hours
activities and free time, in a manner befitting the emergency medical services profession and the memory of the EMS
professionals who are being honored by this event. I agree to defend, indemnify and hold harmless the Ride for any
liability arising in any manner from my conduct or activities during riding hours or during any after-hours activities
or free time.

I understand and acknowledge that I am solely responsible to pay any fines, costs or penalties that may be assessed
by any law enforcement agency regarding my violation of any laws, regulations, traffic rules or other applicable law.</p>

I acknowledge that I have had an opportunity to fully read this document prior to signing it, and I understand that
this contains a legal release of any claims I may have against the National EMS Memorial Bike Ride and the other parties
named in this document.


_______________
Rider Signature

_______________
Printed Name

_______________
Date

*If rider is under 18 years of age, the rider's parent or legal guardian must sign for the rider.

_______________
Parent/Guardian Signature

_______________
Printed Parent/Guardian Name");
        
        //notes
        $notes = new Zend_Form_Element_Textarea('notes');
        $notes->setLabel('Additional header notes for event: *')
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->setAttrib('rows', '6')
                  ->setDecorators(self::$gridElementDecorators);
                  
        //Hidden elements to store IDs
        $eventId = new Zend_Form_Element_Hidden('eventId');
        $eventId->setDecorators(self::$hiddenElementDecorators);
            
        //submit
        $submit = new Fisdap_Form_Element_SaveButton('submit');
        $submit->setLabel('Submit Form')
           ->setDecorators(self::$buttonDecorators);
                  
        
        $this->addElements(array($origin, $region, $destination, $passcode, $email_list, $roles, $start, $end, $notes, $eventId, $submit, $housing, $transportation, $jerseytext, $tshirttext, $housingtoggle, $transporttoggle, $jerseytoggle, $tshirttoggle, $liability, $route));
        
        if ($this->event->id) {
            $this->setDefaults(array(
            'eventId' => $this->event->id,
                'region' => $this->event->region,
                'origin' => $this->event->origin,
                'destination' => $this->event->destination,
                'roles' => $this->event->getRoleIds(),
                'start' => $this->event->start_date->format('m/d/Y'),
                'end' => $this->event->end_date->format('m/d/Y'),
                'notes' => $this->event->notes,
                'housing' => $this->event->housing,
                'transportation' => $this->event->transportation,
                'jerseytext' => $this->event->jersey,
                'tshirttext' => $this->event->tshirt,
                'liability' => $this->event->liability,
                'route_info' => $this->event->route_information,
                'passcode' => $this->event->passcode,
                'email_list' => $this->event->email_list,
            ));
        } else {
            $this->setDefaults(array(
                "passcode" => "ridebikes",
            ));
        }
        
        //Set the decorators for the form
        $this->setDecorators(array(
                'FormErrors',
                'PrepareElements',
                array('ViewScript', array('viewScript' => "forms/bikeRideEventForm.phtml")),
                'Form'
        ));
    }
    
    public function process($post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();
            
            //Create entities for a new bike ride event
            if (!$values['eventId']) {
                //Create new event entity
                $event = \Fisdap\EntityUtils::getEntity('BikeRideEvent');
                $event->route_information = $values['route_info'];
                $event->origin = $values['origin'];
                $event->region = $values['region'];
                $event->destination = $values['destination'];
                $event->setRolesIds($values['roles']);
                $event->start_date = new DateTime($values['start']);
                $event->end_date = new DateTime($values['end']);
                $event->notes = $values['notes'];
                $event->passcode = $values['passcode'];
                $event->email_list = $values['email_list'];
                        
                if ($values['housingtoggle'] == 1) {
                    $event->housing = $values['housing'];
                } else {
                    $event->housing = null;
                }
                
                if ($values['transporttoggle'] == 1) {
                    $event->transportation = $values['transportation'];
                } else {
                    $event->transportation = null;
                }
                
                if ($values['jerseytoggle'] == 1) {
                    $event->jersey = $values['jerseytext'];
                } else {
                    $event->jersey = null;
                }
                
                if ($values['tshirttoggle'] == 1) {
                    $event->tshirt = $values['tshirttext'];
                } else {
                    $event->tshirt = null;
                }
                
                $event->liability = $values['liability'];
                            
                //Save the changes and flush
                $event->save();
            }
            //Edit entities for existing bike ride
            else {
                $event = \Fisdap\EntityUtils::getEntity('BikeRideEvent', $values['eventId']);
                $event->route_information = $values['route_info'];
                $event->origin = $values['origin'];
                $event->region = $values['region'];
                $event->destination = $values['destination'];
                $event->setRolesIds($values['roles']);
                $event->start_date = new DateTime($values['start']);
                $event->end_date = new DateTime($values['end']);
                $event->notes = $values['notes'];
                $event->passcode = $values['passcode'];
                $event->email_list = $values['email_list'];
                                
                if ($values['housingtoggle'] == 1) {
                    $event->housing = $values['housing'];
                } else {
                    $event->housing = null;
                }
                
                if ($values['transporttoggle'] == 1) {
                    $event->transportation = $values['transportation'];
                } else {
                    $event->transportation = null;
                }
                
                if ($values['jerseytoggle'] == 1) {
                    $event->jersey = $values['jerseytext'];
                } else {
                    $event->jersey = null;
                }
                
                if ($values['tshirttoggle'] == 1) {
                    $event->tshirt = $values['tshirttext'];
                } else {
                    $event->tshirt = null;
                }

                $event->liability = $values['liability'];
                                
                //Save the changes and flush
                $event->save();
            }
        }
        
        return $event->id;
    }
}
