<?php

class Mobile_Form_Login extends Fisdap_Form_Base
{
    public static $checkboxDecorators = array(
		'ViewHelper',
		array('Label', array('placement' => 'APPEND')),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'form-prompt'))
	);    
    
    public function init()
    {
		parent::init();
		
        $this->addElement('text', 'username', array(
            'label' => "Username:",
            'decorators' => parent::$elementDecorators,
        ));
        
        $this->addElement('password', 'password', array(
            'label' => "Password:",
            'decorators' => self::$elementDecorators,
        ));
        
        $this->addElement('checkbox', 'rememberMe', array(
            'label' => "Remember Me",
            'decorators' => self::$checkboxDecorators
        ));
        
        $this->addElement('submit', 'submit', array(
            'label' => 'Sign in',
            'decorators' => self::$buttonDecorators,
        ));
        
        $this->setDecorators(array(
            'Description',
            'FormElements',
            'Form',
        ));
    }
}
