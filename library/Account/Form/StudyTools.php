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
 * Form for processing an activation code
 */

/**
 * @package    Account
 */
class Account_Form_StudyTools extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/studyToolsForm.phtml")),
        array('Form', array('class' => 'study-tools-form')),
    );
    
    /**
     * @var array decorators for products
     */
    public static $gridDecorators = array(
        'ViewHelper',
        array('Label', array('class' => 'grid_1')),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'email')),
    );
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(self::$_formDecorators);
        
        $this->setAction('/account/new/search-study-tools');

        $email = new Zend_Form_Element_Text("email");
        $email->setRequired(true)
             ->setLabel("Email:")
             ->setAttrib('size', '40');
        $this->addElement($email);
        
        $continue = new \Fisdap_Form_Element_SaveButton("save");
        $continue->setLabel("Continue");
        $this->addElement($continue);
        
        $this->setElementDecorators(self::$basicElementDecorators);
    }
    
    public function process()
    {
    }
}
