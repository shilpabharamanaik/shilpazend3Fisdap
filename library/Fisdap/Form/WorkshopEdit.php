<?php

/**
 *  Edit form for Fisdap Workshops
 */

class Fisdap_Form_WorkshopEdit extends Fisdap_Form_Base
{
    public $workshop;
    
    public function __construct($workshopid = null)
    {
        $this->workshop = \Fisdap\EntityUtils::getEntity("Workshop", $workshopid);
        
        parent::__construct();
    }
    
    /**
     * init method that adds all the elements to the form
     * */
    public function init()
    {
        parent::init();
        
        $this->setAttrib('id', 'workshopForm');
    
        $this->addJsFile("/js/library/Fisdap/Form/workshop-edit.js");
        
        //cost
        $cost = new Zend_Form_Element_Text('cost');
        $cost->setLabel('Cost: *')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->addValidator('digits')
                  ->addErrorMessage("Please enter a cost for this workshop (only digits).")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //date
        $date = new Zend_Form_Element_Text('date');
        $date->setLabel('Date: *')
                  ->setRequired(true)
                  ->addErrorMessage("Please enter a starting date for this workshop.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //deadline
        $deadline = new Zend_Form_Element_Text('deadline');
        $deadline->setLabel('Deadline: *')
                  ->setRequired(true)
                  ->addErrorMessage("Please enter a deadline to register for this workshop.")
                  ->setDecorators(self::$gridElementDecorators);
                     
                     
        //duration
        $duration = new Zend_Form_Element_Text('duration');
        $duration->setLabel('Duration: *')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->addErrorMessage("Please enter duration for this workshop.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //location
        $location = new Zend_Form_Element_Text('location');
        $location->setLabel('Location: *')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->addErrorMessage("Please enter a location for this workshop.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //email subject
        $emailSubject = new Zend_Form_Element_Text('emailSubject');
        $emailSubject->setLabel('Email Subject Text: *')
                  ->setRequired(true)
                  ->addFilter('StripTags')
                  ->addFilter('HtmlEntities')
                  ->addErrorMessage("Please enter an email subject for this workshop.")
                  ->setDecorators(self::$gridElementDecorators);
                  
        //email body
        $emailBody = new Zend_Form_Element_Textarea('emailBody');
        $emailBody->setLabel('Email Body Text: *')
                    ->setRequired(true)
                    ->addFilter('StripTags')
                    ->addFilter('HtmlEntities')
                     ->setAttrib('rows', '6')
                    ->addErrorMessage("Please enter an email body for this workhop.")
                     ->setDecorators(self::$gridElementDecorators);
                  
        //Hidden elements to store IDs
        $workshopId = new Zend_Form_Element_Hidden('workshopId');
        $workshopId->setDecorators(self::$hiddenElementDecorators);
            
        //submit
        $submit = new Fisdap_Form_Element_SaveButton('submit');
        $submit->setLabel('Submit Form')
           ->setDecorators(self::$buttonDecorators);
                  
        
        $this->addElements(array($cost, $date, $deadline, $duration, $location,
                                 $emailSubject, $emailBody, $workshopId, $submit));
        
        if ($this->workshop->id) {
            $this->setDefaults(array(
            'workshopId' => $this->workshop->id,
                'cost' => $this->workshop->cost,
                'date' => $this->workshop->date->format('m/d/Y'),
                'deadline' => $this->workshop->deadline->format('m/d/Y'),
                'duration' => $this->workshop->duration,
                'location' => $this->workshop->location,
                'emailSubject' => $this->workshop->email_subject,
                'emailBody' => $this->workshop->email_text,
            ));
        }
        
        //Set the decorators for the form
        $this->setDecorators(array(
                'FormErrors',
                'PrepareElements',
                array('ViewScript', array('viewScript' => "forms/workshopForm.phtml")),
                'Form'
        ));
    }
    
    public function process($post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();
            
            //Create entities for a new workshop
            if (!$values['workshopId']) {
                //Create new workshop entity
                $workshop = \Fisdap\EntityUtils::getEntity('Workshop');
                $workshop->cost = $values['cost'];
                $workshop->date = new DateTime($values['date']);
                $workshop->deadline = new DateTime($values['deadline']);
                $workshop->duration = $values['duration'];
                $workshop->location = $values['location'];
                $workshop->email_subject = $values['emailSubject'];
                $workshop->email_text = $values['emailBody'];
                
                //Save the changes and flush
                $workshop->save();
            }
            //Edit entities for existing workshop
            else {
                $workshop = \Fisdap\EntityUtils::getEntity('Workshop', $values['workshopId']);
                $workshop->cost = $values['cost'];
                $workshop->date = new DateTime($values['date']);
                $workshop->deadline = new DateTime($values['deadline']);
                $workshop->duration = $values['duration'];
                $workshop->location = $values['location'];
                $workshop->email_subject = $values['emailSubject'];
                $workshop->email_text = $values['emailBody'];
                
                //Save the changes and flush
                $workshop->save();
            }
        }
        
        return $workshop->id;
    }
}
