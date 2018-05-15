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
 * Form for creating/editing a Fisdap User account
 */
use Fisdap\Entity\User;

/**
 * @package    Account
 */
class Account_Form_User extends Fisdap_Form_Base
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var boolean
     */
    public $canEditPassword = false;

    /**
     *
     */
    public function __construct($userId = null)
    {
        $this->user = \Fisdap\EntityUtils::getEntity('User', $userId);

        parent::__construct();
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setAttrib('id', 'userForm');

        //add js file to do awesome live validation
        $this->addJsFile("/js/library/Account/Form/user.js");
        $this->addCssFile("/css/library/Account/Form/user.css");

        //first name
        $first = new Zend_Form_Element_Text('firstName');
        $first->setLabel('First Name:')
            ->setRequired(true)
            ->setDescription('(required)')
            ->addFilter('StripTags')
            ->addFilter('HtmlEntities')
            ->addErrorMessage("Please enter a first name.")
            ->setAttribs(array("class" => "modal-input fancy-input"));

        //last name
        $last = new Zend_Form_Element_Text('lastName');
        $last->setLabel('Last Name:')
            ->setRequired(true)
            ->setDescription('(required)')
            ->addFilter('StripTags')
            ->addFilter('HtmlEntities')
            ->addErrorMessage("Please enter a last name.")
            ->setAttribs(array("class" => "modal-input fancy-input"));

        //email
        $email = new Fisdap_Form_Element_Email('email');
        $email->setLabel('Email:')
            ->setDescription('(required)')
            ->setRequired(true)
            ->addErrorMessage('Please enter a valid email address.')
            ->setAttribs(array("class" => "modal-input fancy-input"));

        //demographic stuff
        $gender = new Zend_Form_Element_Radio('gender');
        $gender->setLabel('Gender:')
            ->setMultiOptions(\Fisdap\Entity\Gender::getFormOptions())
            ->setValue('')
            ->setSeparator(' ');

        $ethnicity = new Zend_Form_Element_Select('ethnicity');
        $ethnicity->setLabel('Ethnicity:')
            ->setMultiOptions(\Fisdap\Entity\Ethnicity::getFormOptions())
            ->setValue(6)
            ->setAttribs(array("class" => "chzn-select",
                "data-placeholder" => "Choose an ethnicity",
                "style" => "width:250px;"));
        ;

        $birthDate = new Zend_Form_Element_Text('birth_date');
        $birthDate->setLabel('Birth Date:')
            ->setAttrib("class", "selectDate");

        $userId = new Zend_Form_Element_Hidden('userId');
        $userId->setDecorators(self::$hiddenElementDecorators);

        //save button
        $submitButton = new Fisdap_Form_Element_SaveButton('save');
        $submitButton->setLabel("Save & Continue");
        if ($this->student->id) {
            $submitButton->setLabel("Save");
        }
        $submitButton->setDecorators(self::$buttonDecorators);

        //Add elements
        $this->addElements(array(
            $first,
            $last,
            $email,
            $submitButton,
            $gender,
            $ethnicity,
            $birthDate,
            $userId
        ));

        $this->setElementDecorators(self::$standardFormInputDecorators, array("save"), false);

        $this->setDecorators($this->getStandardFormDecorators("forms/userForm.phtml"));

        //Editing an existing user
        if ($this->user->id) {
            $this->setDefaults(array(
                'firstName' => $this->user->first_name,
                'lastName' => $this->user->last_name,
                'email' => $this->user->email,
                'gender' => $this->user->getGender(),
                'ethnicity' => $this->user->getEthnicity()->id,
                'birth_date' => $this->user->birth_date->format("m/d/Y"),
                'userId' => $this->user->id,
            ));
        } else {
            // creating a ghost user
            $session = $_SESSION;
            $this->setDefaults(array(
                'firstName' => $session->first_name,
                'lastName' => $session->last_name,
                'email' => $session->email
            ));
        }
    }

    /**
     * Overwriting the isValid method to add some dependency validation
     *
     * @param array $values
     * @return boolean
     */
    public function isValid($values)
    {
        if (array_key_exists('currentPassword', $values) && $values['currentPassword']) {
            $this->newPassword->setRequired(true)->addErrorMessage("Please enter a new password.");
        }

        if (array_key_exists('newPassword', $values) && $values['newPassword']) {
            //These two lines affectively add the same validator, but we need both to overwrite the validation msg
            $this->confirmPassword->addValidator('NotEmpty', false, array('messages' => array('isEmpty' => 'Please confirm your new password.')));
            $this->confirmPassword->setRequired(true);
        }

        return parent::isValid($values);
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

            // Create entities for a new student account
            if (!$values['userId']) {

                // Create new user entity
                $user = new User();
                $user->first_name = $values['firstName'];
                $user->last_name = $values['lastName'];
                $user->email = $values['email'];
                $user->gender = $values['gender'];
                $user->ethnicity = $values['ethnicity'];

                // Save the changes and flush
                $user->save();

                return true;
            }

            // Save properties for existing user entity
            $user = \Fisdap\EntityUtils::getEntity('User', $values['userId']);
            $user->first_name = $values['firstName'];
            $user->last_name = $values['lastName'];
            $user->email = $values['email'];

            $user->save();

            return true;
        }

        return false;
    }
}
