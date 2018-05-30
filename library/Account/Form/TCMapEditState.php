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
 * Form for editing the transition course map
 */

/**
 * @package    Account
 */
class Account_Form_TCMapEditState extends Fisdap_Form_Base
{
 
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/tCMapEditState.phtml")),
        'Form'
    );
    
    public $state;
    public $usedStatuses;
    public $usedColors;
    
    /**
    * @param $options mixed additional Zend_Form options
    */
    public function __construct($state, $options = null)
    {
        $this->state = $state;
        parent::__construct($options);
    }
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();
        
        $this->setDecorators(self::$_formDecorators);
        
        $status = new Zend_Form_Element_Text("status");
        $status->setRequired(true)
             ->setLabel("Status:");
        $this->addElement($status);
        
        $color = new Zend_Form_Element_Text("color");
        $color->setRequired(true)
             ->setLabel("Color:");
        $this->addElement($color);
        
        $note = new Zend_Form_Element_Textarea("note");
        $note->setRequired(true)
             ->setLabel("Note:");
        $this->addElement($note);
        
        $stateId = new Zend_Form_Element_Hidden("stateId");
        $this->addElement($stateId);
        
        $continue = new \Fisdap_Form_Element_SaveButton("save");
        $this->addElement($continue);
        
        $this->usedColors = \Fisdap\EntityUtils::getRepository("TCMapState")->getUsedColors();
        $standardColors = array('ACA89D', '837E7A', 'FBB03B', 'ADD93D');
        foreach ($this->usedColors as $color) {
            $standardColors[] = $color['color'];
        }
        $this->usedColors = array_unique($standardColors);
        
        
        $this->usedStatuses = \Fisdap\EntityUtils::getRepository("TCMapState")->getUsedStatuses();
        $standardStatuses = array("We're waiting to hear from your state.", "Not approved for transition", "Pending", "Approved");
        foreach ($this->usedStatuses as $status) {
            $standardStatuses[] = $status['status'];
        }
        $this->usedStatuses = array_unique($standardStatuses);
        
                    
        $this->setDefaults(array(
            'status' => $this->state->status,
            'color' => $this->state->color,
            'note' => $this->state->note,
            'stateId' => $this->state->id
        ));
    }
    
    /**
     * Process the submitted POST values and do whatever you need to do
     *
     * @param array $post the POSTed values from the user
     * @return mixed either the values or the form w/errors
     */
    public function process($post)
    {
        if ($this->isValid($post)) {
            $values = $this->getValues();
            $state = \Fisdap\EntityUtils::getEntity('TCMapState', $values['stateId']);
            
            $state->status = nl2br($values['status']);
            $state->color = $values['color'];
            $state->note = $values['note'];
            $state->save();
            
            return $state->id;
        }
        
        return false;
    }
}
