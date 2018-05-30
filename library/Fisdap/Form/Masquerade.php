<?php
class Fisdap_Form_Masquerade extends Zend_Form
{
    protected $elementDecorators = array(
        'ViewHelper',
        'Label',
        array(array('prompt' => 'HtmlTag'), array('tag' => 'div', 'class' => 'login_prompts')),
        array(array('descBreak' => 'HtmlTag'), array('tag' => 'br', 'openOnly'=> true, 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
    );
    
    protected $loginButtonDecorators;
    
    protected $secureDecorators = array(
        'ViewHelper',
        array('Label', array('placement' => 'APPEND')),
        array(array('prompt' => 'HtmlTag'), array('tag'=>'div', 'class'=>'checkbox')),//'form-prompt'
        array(array('secureDiv' =>'HtmlTag'), array('tag' => 'div', 'class' => 'form_field secure', 'id' => 'my_secure_box_ff')),
        array(array('secureBreak' => 'HtmlTag'), array('tag' => 'br', 'openOnly'=> true, 'placement' => Zend_Form_Decorator_Abstract::APPEND)),
    );
    
    public static $formJQueryElements = array(
        array('UiWidgetElement', array('tag' => '')), // it necessary to include for jquery elements
        array('Label', array('class' => 'form-label dark-gray')),
        array('HtmlTag', array('tag'=>'div', 'class'=>'form-prompt')),
    );
    
    public function init()
    {
        //initialize form
        $this->setMethod('post');
        
        //username
        /*
        $username = new Zend_Form_Element_Text('username');
        $username->setLabel('Username:')
            ->setOptions(array('size' => '20'))
            ->setRequired(true)
            ->addValidator('Alnum')
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        */
            
        $username = new ZendX_JQuery_Form_Element_AutoComplete('username');
        $username->setLabel("Username")
               ->setAttrib('title', 'Type username')
               ->setAttrib('size', '20')
               ->addFilter('StringTrim')
               ->addFilter('PregReplace', array('match' => '/\s+/', 'replace' => ' '))
               ->addFilter('Alnum', array('allowwhitespace' => true))
               ->addFilter('HtmlEntities')
               ->setDecorators(self::$formJQueryElements);
        
        $username->setJQueryParam('source', '/ajax/usernamesearch');
        $username->setJQueryParam('focus', new Zend_Json_Expr('function( event, ui ) { $("#username").val(ui.item.value); return false; }'));
      
            
        // I'm secure
        $ImSecure = new Zend_Form_Element_Checkbox('ImSecure');
        $ImSecure->setLabel("I'm secure. Don't ask for my password when I make changes.")
            ->setAttrib('class', 'secure')
            ->setDecorators($this->secureDecorators);
                
        if ($_COOKIE['secureLogin'] == 1) {
            $ImSecure->setChecked(true);
        }
        
        //submit
        $submit = new Fisdap_Form_Element_Submit('submit');
        $submit->setLabel('Log In')
            ->setAttrib('class', 'green-button medium');
        
        //attach elements to form
        $this->addElements(array(
            $username, $ImSecure, $submit
        ));
        
        //Set some decorators to add a description
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
        
        $this->setElementDecorators($this->elementDecorators, array('username', 'submit', 'ImSecure'), false); //, 'ImSecure'
    }
}
