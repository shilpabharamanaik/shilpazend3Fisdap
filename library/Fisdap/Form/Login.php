<?php
class Fisdap_Form_Login extends Zend_Form
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
    
    public function init()
    {
        $forgotLoginLink = '<a href="/account/help/forgot">Forgot your login?</a>';

        $this->loginButtonDecorators = array(
            'ViewHelper',
            array(array('forgotPasswordText' => 'AddText'), array('text' => $forgotLoginLink, 'class' => 'grid_8', 'placement' => 'PREPEND')),	//'tag' => '',
            array('HtmlTag', array('tag' => 'div', 'class' => 'button_container green-buttons small')),
        );

        //initialize form
        $this->setMethod('post');
        
        //username
        $username = new Zend_Form_Element_Text('username');
        $username->setLabel('Username:')
            ->setOptions(array('size' => '20'))
            ->setRequired(true)
            ->addFilter('HtmlEntities')
            ->addFilter('StringTrim');
        
        //password
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('Password:')
            ->setOptions(array('size' => '20'))
            ->setRequired(true)
            //->addFilter('HtmlEntities')
            //->addFilter('StringTrim');
        ;
            
        // I'm secure
        $ImSecure = new Zend_Form_Element_Checkbox('ImSecure');
        $ImSecure->setLabel("I'm secure. Don't ask for my password when I make changes.")
            ->setAttrib('class', 'secure')
            ->setDecorators($this->secureDecorators);
                
        if (isset($_COOKIE['secureLogin'])) {
            if ($_COOKIE['secureLogin'] == 1) {
                $ImSecure->setChecked(true);
            }
        }
        
        //submit
        $submit = new Fisdap_Form_Element_Submit('submit');
        $submit->setLabel('Log in');
            
        
        //attach elements to form
        $this->addElements(array(
            $username, $password, $ImSecure, $submit
        ));
        
        //Set some decorators to add a description
        $this->setDecorators(array(
            'FormElements',
            array('HtmlTag', array('tag' => 'dl', 'class' => 'zend_form')),
            array('Description', array('placement' => 'prepend')),
            'Form'
        ));
        
        $this->setElementDecorators($this->elementDecorators, array('submit', 'ImSecure'), false); //, 'ImSecure'
        $this->setElementDecorators($this->loginButtonDecorators, array('submit'), true);
    }
}
