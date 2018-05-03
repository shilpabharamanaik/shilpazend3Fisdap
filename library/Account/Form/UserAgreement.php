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

use Fisdap\Api\Users\CurrentUser\CurrentUser;
use Fisdap\Data\User\UserRepository;

/**
 * @package    Account
 */
class Account_Form_UserAgreement extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/userAgreementForm.phtml")),
        array('Form', array('class' => 'study-tools-form')),
    );

    /**
     * @var CurrentUser
     */
    private $currentUser;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var integer
     */
    private $newUserId;

    /**
     * @var string
     */
    public $privacyPolicy;

    /**
     * @param int $userId the id of the user viewing the agreement
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($userId = null, $options = null)
    {
        $this->currentUser = \Zend_Registry::get('container')->make(CurrentUser::class);
        $this->userRepository = \Zend_Registry::get('container')->make(UserRepository::class);
        $this->newUserId = $userId;
        parent::__construct($options);
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->addJsFile("/js/library/Account/Form/user-agreement.js");
        $this->addCssFile("/css/library/Account/Form/user-agreement.css");

        $this->setDecorators(self::$_formDecorators);

        $terms = file_get_contents(APPLICATION_PATH . "/../data/user_agreement.txt");
        $policy = file_get_contents(APPLICATION_PATH . "/../data/privacy_policy.txt");

        $this->privacyPolicy = nl2br($policy);

        $agreement = new Zend_Form_Element_Textarea("agreement");
        $agreement->setValue($terms)
            ->setAttrib("readonly", "readonly")
            ->setAttrib("class", "grid_12");
        $this->addElement($agreement);

        $agreeFlag = new Zend_Form_Element_Checkbox("agreeFlag");
        $agreeFlag->setLabel("Yes, I Agree. I have read and understand the FISDAP Terms and Conditions, and agree to be bound by all of the terms, conditions and policies described therein, including, but not limited to, the following specific consents:")
            ->setUncheckedValue(null)
            ->setRequired(true)
            ->addErrorMessage('You must accept the terms and conditions of the user agreement to continue.');
        $this->addElement($agreeFlag);

        $continue = new \Fisdap_Form_Element_SaveButton("save");
        $continue->setLabel("Continue");
        $this->addElement($continue);

        $this->setElementDecorators(self::$elementDecorators);
    }


    public function process($post)
    {
        if ($this->isValid($post)) {
            $user = $this->currentUser->getWritableUser();
            $values = $this->getValues();

            // when creating a new user, we do this without being logged in yet, so handle that case
            if (!$user) {
                if ($this->newUserId) {
                    $user = \Fisdap\EntityUtils::getEntity('User', $this->newUserId);
                } else {
                    return false;
                }
            }

            $user->accepted_agreement = ($values['agreeFlag'] == "1");
            $this->userRepository->update($user);

            // if we're logged in, reload the current user in the session
            if ($this->currentUser->user()) {
                $this->currentUser->reload();
            }
            return true;
        }
        return false;
    }
}