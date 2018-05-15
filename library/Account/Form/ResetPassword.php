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
 * Form for resetting a password
 */

/**
 * @package    Account
 */
class Account_Form_ResetPassword extends Fisdap_Form_Base
{
    
    
    /**
     * @var \Fisdap\Entity\User
     */
    public $user;
    
    /**
     * @var \Fisdap\Entity\PasswordReset
     */
    public $passwordReset;
    
    
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/resetPasswordForm.phtml")),
        array('Form', array('class' => 'reset-password-form')),
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
     * @param int $userId the id of the user to edit
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($userId = null, $passwordResetId = null, $options = null)
    {
        $this->user = \Fisdap\EntityUtils::getEntity('User', $userId);
        $this->passwordReset = \Fisdap\EntityUtils::getEntity('PasswordReset', $passwordResetId);
        parent::__construct($options);
    }
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setDecorators(self::$_formDecorators);

        $new = new Zend_Form_Element_Password("newPassword");
        $new->setRequired(true)
             ->setLabel("New Password:");
        $this->addElement($new);
        
        $new2 = new Zend_Form_Element_Password("new2");
        $new2->setRequired(true)
             ->setLabel("Confirm Password:");
        $this->addElement($new2);
        
        $continue = new \Fisdap_Form_Element_SaveButton("save");
        $continue->setLabel("Submit");
        $this->addElement($continue);
        
        $this->setElementDecorators(self::$elementDecorators);
        $this->setElementDecorators(self::$gridDecorators, array('new'), true);
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
            
            // save this user's password
            $this->user->password = $values['newPassword'];
            $this->user->save();
            
            // then remove the PasswordReset entity
            $this->passwordReset->delete();
            
            return true;
        } else {
            return false;
        }
    }
}
