<?php

/**
* Form to pick a workshop event
*/

class Fisdap_Form_PickWorkshop extends Fisdap_Form_Base
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
            
        //Grab all existing workshops
        $workshops = \Fisdap\EntityUtils::getRepository("Workshop")->findAll();
        $workshopLocs = array();
       
        foreach ($workshops as $item) {
            $workshopLocs[$item->id] = $item->location . " -- " . $item->date->format('m/Y');
        }
           

        //pick a workshop
        $workshop = new Zend_Form_Element_Select('workshops');
        $workshop->setLabel('Please select a workshop: *')
            ->setMultiOptions($workshopLocs);
                 
        //submit
        $submit = new Fisdap_Form_Element_SaveButton('submit');
        $submit->setLabel('Submit')
           ->setDecorators(self::$buttonDecorators);
                   
        $this->addElements(array($workshop, $submit));
        
        //Set the decorators for the form
        $this->setDecorators(array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/pickWorkshopForm.phtml")),
        'Form'
    ));
    }
}
