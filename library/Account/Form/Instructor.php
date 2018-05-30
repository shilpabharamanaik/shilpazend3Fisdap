<?php

use Fisdap\Api\Users\CurrentUser\CurrentUser;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\User;

/**
 * Form for editing a Fisdap Instructor account
 *
 * @package    Account
 */
class Account_Form_Instructor extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for display groups
     */
    protected $_displayGroupDecorators = array(
        array('Description', array('tag' => 'div', 'class' => 'form-group-title section-header no-border')),
        'FormElements',
        array('HtmlTag', array('tag' => 'div', 'class' => 'form-group')),
    );

    /**
     * @var array the decorators for display groups
     */
    protected $_displayGroupDecoratorsNoTitle = array(
        'FormElements',
        array('HtmlTag', array('tag' => 'div', 'class' => 'form-group')),
    );

    /**
     * @var \Fisdap\Entity\InstructorLegacy
     */
    public $instructor;

    /**
     * @var \Fisdap\Entity\SerialNumberLegacy
     */
    public $serial;

    /**
     * @var \Fisdap\Entity\ProgramLegacy
     */
    public $program;

    /**
     * @var boolean
     */
    public $isSecure = false;

    /**
     * @var boolean is the current user editting him/herself
     */
    public $isSelf = true;

    /**
     * @var boolean can this user set permissions for him/herself
     */
    public $canChangePermissions = false;

    /**
     * @var bool
     */
    public $isLtiUser = false;

    /**
     * @var CurrentUser
     */
    private $currentUser;


    /**
     * @param integer $instructorId the id of the instructor to edit
     * @param integer $serialId
     * @param integer $programId
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($instructorId = null, $serialId = null, $programId = null, $options = null)
    {
        $this->instructor = \Fisdap\EntityUtils::getEntity('InstructorLegacy', $instructorId);

        if (!is_null($instructorId)) {
            $this->isLtiUser = $this->isLtiUser();
        }

        $this->serial = \Fisdap\EntityUtils::getEntity('SerialNumberLegacy', $serialId);
        $this->program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $programId);

        $this->currentUser = Zend_Registry::get('container')->make(CurrentUser::class);
        $user = $this->currentUser->user();

        if ($user) {
            //There's no need to force a user to enter their password to create a new instructor
            $this->isSecure = $user->isSecure() || !$this->instructor->id;

            //Determine if the user is editting their own account, Staff Access will trump this
            $this->isSelf = ($user->getCurrentRoleData()->id == $instructorId);
            if ($user->isStaff()) {
                $this->isSelf = true;
            }
            $this->canChangePermissions = $user->hasPermission("Edit Instructor Accounts");
        }

        parent::__construct($options);
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setAttrib('id', 'instructorForm');

        //add js file to do cool input masking
        $this->addJsFile("/js/jquery.maskedinput-1.3.js");

        //add js file to do awesome live validation
        $this->addJsFile("/js/library/Account/Form/instructor.js");
        $this->addCssFile("/css/library/Account/Form/instructor.css");

        $this->addJsOnLoad(
            '$("#homePhone").mask("999-999-9999");
			 $("#workPhone").mask("999-999-9999? x99999");
			 $("#cellPhone").mask("999-999-9999");
			 $("#contactPhone").mask("999-999-9999");
			'
        );


        //first name
        if ($this->isLtiUser) {
            $first = new Zend_Form_Element_Hidden('firstName');
        } else {
            $first = new Zend_Form_Element_Text('firstName');
            $first->setLabel('First Name')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('HtmlEntities')
                ->addErrorMessage("Please enter a first name.")
                ->setDecorators(self::$gridElementDecorators);
        }


        //last name
        if ($this->isLtiUser) {
            $last = new Zend_Form_Element_Hidden('lastName');
        } else {
            $last = new Zend_Form_Element_Text('lastName');
            $last->setLabel('Last Name')
                ->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('HtmlEntities')
                ->addErrorMessage("Please enter a last name.")
                ->setDecorators(self::$gridElementDecorators);
        }


        //email
        if ($this->isLtiUser) {
            $email = new Zend_Form_Element_Hidden('email');
        } else {
            $email = new Fisdap_Form_Element_Email('email');
            $email->setRequired(true)
                ->addErrorMessage('Please enter a valid email address.')
                ->setDecorators(self::$gridElementDecorators);
        }


        //home phone
        $homePhone = new Zend_Form_Element_Text('homePhone');
        $homePhone->setLabel('Home')
            ->setDescription("(optional)")
            ->setDecorators(self::$gridElementDecorators);

        //work phone
        $workPhone = new Zend_Form_Element_Text('workPhone');
        $workPhone->setLabel('Work')
            ->setDescription("(optional)")
            ->setDecorators(self::$gridElementDecorators);

        //cell phone
        $cellPhone = new Zend_Form_Element_Text('cellPhone');
        $cellPhone->setLabel('Cell')
            ->setDescription("(optional)")
            ->setDecorators(self::$gridElementDecorators);

        //Email settings
        $mailingList = new Zend_Form_Element_Checkbox('mailingList');
        $mailingList->setLabel('Fisdap news and events')
            ->setDecorators(self::$checkboxDecorators);


        $automatedEmails = new Zend_Form_Element_Checkbox('automatedEmails');
        $automatedEmails->setLabel('Student Events')
            ->setValue(1)
            ->setDecorators(self::$checkboxDecorators);

        $labEmails = new Zend_Form_Element_Checkbox('labEmails');
        $labEmails->setLabel('Lab Shifts')
            ->setValue(1)
            ->setDecorators(self::$checkboxDecorators);

        $clinicalEmails = new Zend_Form_Element_Checkbox('clinicalEmails');
        $clinicalEmails->setLabel('Clinical Shifts')
            ->setValue(1)
            ->setDecorators(self::$checkboxDecorators);

        $fieldEmails = new Zend_Form_Element_Checkbox('fieldEmails');
        $fieldEmails->setLabel('Field Shifts')
            ->setValue(1)
            ->setDecorators(self::$checkboxDecorators);

        //Password stuff
        $currentPassword = new Zend_Form_Element_Password('currentPassword');
        $currentPassword->setLabel('Current Password')
            ->setAttrib("autocomplete", "off")
            ->addValidator(new \Fisdap_Validate_AuthenticatePassword())
            ->setDecorators(self::$elementDecorators);

        //Change the password label for instructors
        if ($this->isSelf) {
            $currentPassword->setLabel('Your Password');
        }

        if (!$this->isLtiUser) {
            $newPassword = new Zend_Form_Element_Password('newPassword');
            $newPassword->setLabel('New Password')
                ->setDescription("(5+ characters)")
                ->setAttrib("autocomplete", "off")
                ->addValidator("StringLength", false, array('min' => 5))
                ->setDecorators(self::$elementDecorators);

            $confirmPassword = new Zend_Form_Element_Password('confirmPassword');
            $confirmPassword->setLabel('Confirm New Password')
                ->setAttrib("autocomplete", "off")
                ->addValidator('identical', false, array('token' => 'newPassword', 'messages' => array('notSame' => 'New passwords do not match.')))
                ->setDecorators(self::$elementDecorators);

            //Make password fields required if it's a new instructor
            if (!$this->instructor->id) {
                $newPassword->setRequired();
                $confirmPassword->setRequired();
                $newPassword->setDescription('(5+ characters, required)');
                $confirmPassword->setDescription('(required)');
            }
        }


        //Create a validator for username to check for unique names
        $usernameValidator = new \Zend_Validate_Db_NoRecordExists(array('table' => 'fisdap2_users', 'field' => 'username', 'adapter' => \Zend_Registry::get('db')));
        if ($this->instructor->username) {
            $usernameValidator->setExclude("username != '" . $this->instructor->username . "'");
        }
        $usernameValidator->setMessage('That username already exists. Please choose another.');

        $regexValidator = new \Zend_Validate_Regex(array('pattern' => '/^[a-zA-Z0-9]+$/'));
        $regexValidator->setMessage("Please enter a username that only contains letters and numbers and is at least 3 characters long.");

        $username = new Zend_Form_Element_Text('newUsername');
        $username->setLabel('Username')
            ->setRequired(true)
            ->setDescription('(required)')
            ->setAttrib("autocomplete", "off")
            ->addValidator($regexValidator)
            ->addValidator("StringLength", false, array('min' => 3))
            ->addValidator($usernameValidator)
            ->setDecorators(self::$elementDecorators);

        //SubRoles
        $subRoles = new Zend_Form_Element_MultiCheckbox("subRoles");
        $subRoles->setMultiOptions(\Fisdap\Entity\PermissionSubRole::getFormOptions(false, false))
            ->setDecorators(self::$elementDecorators)
            ->removeDecorator("LabelDescription")
            ->removeDecorator("break");

        //Permissions
        $programPermissions = new Zend_Form_Element_MultiCheckbox('programPermissions');
        $programPermissions->setMultiOptions(\Fisdap\Entity\Permission::getFormOptions(false, false, "name", 1))
            ->setAttrib("helper", "multiCheckboxList")
            ->setAttrib("numColumns", 2)
            ->setAttrib("class", "permission-checkbox")
            ->setSeparator("")
            ->setDecorators(self::$elementDecorators)
            ->removeDecorator("LabelDescription")
            ->removeDecorator("break");

        $skillsTrackerPermissions = new Zend_Form_Element_MultiCheckbox('skillsTrackerPermissions');
        $skillsTrackerPermissions->setMultiOptions(\Fisdap\Entity\Permission::getFormOptions(false, false, "name", 2))
            ->setAttrib("helper", "multiCheckboxList")
            ->setAttrib("numColumns", 2)
            ->setAttrib("class", "permission-checkbox")
            ->setSeparator("")
            ->setDecorators(self::$elementDecorators)
            ->removeDecorator("LabelDescription")
            ->removeDecorator("break");

        $schedulePermissions = new Zend_Form_Element_MultiCheckbox('schedulePermissions');
        $schedulePermissions->setMultiOptions(\Fisdap\Entity\Permission::getFormOptions(false, false, "name", 3))
            ->setAttrib("helper", "multiCheckboxList")
            ->setAttrib("numColumns", 2)
            ->setAttrib("class", "permission-checkbox")
            ->setSeparator("")
            ->setDecorators(self::$elementDecorators)
            ->removeDecorator("LabelDescription")
            ->removeDecorator("break");


        $reportsPermissions = new Zend_Form_Element_MultiCheckbox('reportsPermissions');
        $reportsPermissions->setMultiOptions(\Fisdap\Entity\Permission::getFormOptions(false, false, "name", 4))
            ->setAttrib("helper", "multiCheckboxList")
            ->setAttrib("numColumns", 2)
            ->setAttrib("class", "permission-checkbox")
            ->setSeparator("")
            ->setDecorators(self::$elementDecorators)
            ->removeDecorator("LabelDescription")
            ->removeDecorator("break");

        //Email new instructor login info
        $emailNewAccount = new Zend_Form_Element_Checkbox("emailNewAccount");
        $emailNewAccount->setLabel("Send email to instructor with his/her login information.")
            ->setDecorators(self::$checkboxDecorators);

        //Hidden elements to store IDs
        $instructorId = new Zend_Form_Element_Hidden('instructorId');
        $instructorId->setDecorators(self::$hiddenElementDecorators);

        $snId = new Zend_Form_Element_Hidden('snId');
        $snId->setDecorators(self::$hiddenElementDecorators);

        $programId = new Zend_Form_Element_Hidden('programId');
        $programId->setDecorators(self::$hiddenElementDecorators);

        //save button
        $submitButton = new Fisdap_Form_Element_SaveButton('save');
        $submitButton->setDecorators(self::$buttonDecorators);

        //Add elements that aren't in a display group
        $this->addElements(array(
            $submitButton,
            $instructorId,
            $subRoles,
            $programPermissions,
            $skillsTrackerPermissions,
            $schedulePermissions,
            $reportsPermissions,
            $snId,
            $labEmails,
            $clinicalEmails,
            $fieldEmails,
            $automatedEmails,
            $programId,
        ));

        //Add optional email to new account option if we're creating a new account for someone else and it's not a brand new program
        if (!$this->instructor->id && !$this->serial->id && !$this->program->id) {
            $this->addElement($emailNewAccount);
        }

        //Add email mailing list if they're editting themselves
        if ($this->isSelf) {
            $this->addElement($mailingList);
        }

        $this->addDisplayGroup(
            array($first, $last, $email),
            'general',
            array('description' => 'Contact Info', 'decorators' => $this->_displayGroupDecoratorsNoTitle)
        );

        if ($this->isLtiUser) {
            $this->getDisplayGroup('general')->setDecorators([
                'FormElements',
                new Zend_Form_Decorator_HtmlTag(['tag' => 'div', 'style'=> 'display: none;'])
            ]);
        }

        $this->addDisplayGroup(
            array($workPhone, $cellPhone, $homePhone),
            'phoneNumbers',
            array('description' => 'Phone', 'decorators' => $this->_displayGroupDecorators)
        );

        //Change the contents of the login display group depending on whether we're creating a new instructor,
        //and if the logged in user is 'secure'
        if (!$this->instructor->id) {
            $passwordElements = array($username, $newPassword, $confirmPassword);
        } elseif ($this->isSecure) {
            $passwordElements = array($newPassword, $confirmPassword);
        } else {
            $passwordElements = array($currentPassword, $newPassword, $confirmPassword);
        }

        if (!$this->isLtiUser) {
            $this->addDisplayGroup(
                $passwordElements,
                'passwords',
                array('description' => 'Login Info', 'decorators' => array(
                    array('Description', array('tag' => 'div', 'class' => 'form-group-title section-header no-border')),
                    'FormElements',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'form-group')),
                ))
            );
        }


        //Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/editInstructorForm.phtml")),
            'Form'
        ));

        if ($this->instructor->id) {
            $permissionBits = $this->instructor->getAllPermissionBits();

            $this->setDefaults(array(
                'firstName' => $this->instructor->user->first_name,
                'lastName' => $this->instructor->user->last_name,
                'email' => $this->instructor->user->email,
                'homePhone' => $this->instructor->user->home_phone,
                'workPhone' => $this->instructor->user->work_phone,
                'cellPhone' => $this->instructor->user->cell_phone,
                'instructorId' => $this->instructor->id,
                'labEmails' => $this->instructor->receive_lab_late_data_emails,
                'clinicalEmails' => $this->instructor->receive_clinical_late_data_emails,
                'fieldEmails' => $this->instructor->receive_field_late_data_emails,
                'automatedEmails' => $this->instructor->email_event_flag,
                'mailingList' => $this->instructor->onMailingList(),
                'programPermissions' => $permissionBits,
                'skillsTrackerPermissions' => $permissionBits,
                'schedulePermissions' => $permissionBits,
                'reportsPermissions' => $permissionBits,
                'subRoles' => $this->instructor->getPermissionSubRoleIds(),
            ));
        } elseif ($this->serial->id) {
            //If we don't have an instructor but do have a serial, default all the email checkboxes to off
            $this->setDefaults(array(
                "automatedEmails" => 0,
                "labEmails" => 0,
                "clinicalEmails" => 0,
                "fieldEmails" => 0,
            ));
        }

        if ($this->serial->id) {
            $this->setDefault("snId", $this->serial->id);
        }

        if ($this->program->id) {
            $this->setDefault("programId", $this->program->id);
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

            //Create entities for a new instructor account
            if (!$values['instructorId']) {

                // Create new user entity
                $user = new User();
                $user->first_name = $values['firstName'];
                $user->last_name = $values['lastName'];
                $user->email = $values['email'];
                $user->username = $values['newUsername'];
                $user->password = $values['newPassword'];
                $user->home_phone = $values['homePhone'];
                $user->cell_phone = $values['cellPhone'];
                $user->work_phone = $values['workPhone'];

                //Get serial number entity for activation stuff
                $serial = \Fisdap\EntityUtils::getEntity('SerialNumberLegacy', $values['snId']);

                //Figure out the program ID by using the serial number or the logged in user, or the programId given to the form
                if ($serial->id) {
                    $programId = $serial->program->id;
                } elseif (User::getLoggedInUser()->id) {
                    $programId = User::getLoggedInUser()->getProgramId();
                } elseif ($values['programId']) {
                    $programId = $values['programId'];
                }

                // Create instructor entity to attach to user
                $instructor = new InstructorLegacy();
                $user_context = $user->addUserContext("instructor", $instructor, $programId);
                $user->setCurrentUserContext($user_context);

                //Save the changes and flush
                $user->save();

                //Activate the serial if we have one, we need to call this after the user is saved or else the instructor_id field will not be set
                if ($serial->id) {
                    //Activate the serial number for this instructor
                    $instructor->activateSerialNumber($serial);
                }
                $instructor->save();

                // Create a myFisdap message if an account is being set up for another instructor
                if (!$this->isSelf) {
                    $body = "<p>Welcome to Fisdap! As you get started, please take a moment to visit your <a href='/account/edit/instructor'>Account</a> page to set your email preferences.</p>";
                    $subject = "Set your email preferences";
                    $user->addDashboardMessage($subject, 1, $body);
                }

                //Save email settings if editing yourself
                if ($this->isSelf) {
                    $values['mailingList'] ? $instructor->addToMailingList() : $instructor->removeFromMailingList();
                }

                //Permissions
                if ($this->canChangePermissions) {
                    $programPermissions = $values['programPermissions'] ? array_sum($values['programPermissions']) : 0;
                    $skillsTrackerPermissions = $values['skillsTrackerPermissions'] ? array_sum($values['skillsTrackerPermissions']) : 0;
                    $schedulePermissions = $values['schedulePermissions'] ? array_sum($values['schedulePermissions']) : 0;
                    $reportsPermissions = $values['reportsPermissions'] ? array_sum($values['reportsPermissions']) : 0;
                    $instructor->permissions = $programPermissions + $skillsTrackerPermissions + $schedulePermissions + $reportsPermissions;
                    $instructor->setPermissionSubRoles($values['subRoles']);
                }

                //Email the instructor if we've chosen to
                if ($values['emailNewAccount']) {
                    $mail = new \Fisdap_TemplateMailer();
                    $mail->addTo($user->email)
                        ->setSubject("A new Fisdap account has been created for you")
                        ->setViewParam('orderer', User::getLoggedInUser())
                        ->setViewParam('urlRoot', Util_HandyServerUtils::getCurrentServerRoot())
                        ->setViewParam('user', $user)
                        ->setViewParam('password', $values['newPassword'])
                        ->sendHtmlTemplate('new-account-invitation.phtml');
                }

                $instructor->receive_clinical_late_data_emails = $values['clinicalEmails'];
                $instructor->receive_lab_late_data_emails = $values['labEmails'];
                $instructor->receive_field_late_data_emails = $values['fieldEmails'];
                $instructor->email_event_flag = $values['automatedEmails'];

                //Assign new requirements
                $instructor->user_context->autoAttachRequirements();

                $instructor->save();


                // ok, now that we've saved the instructor we can create a record in permissions history
                if ($this->canChangePermissions) {
                    $permissionsHistoryRecord = \Fisdap\EntityUtils::getEntity('PermissionHistoryLegacy');
                    $permissionsHistoryRecord->entry_time = new DateTime();
                    $permissionsHistoryRecord->changed_instructor = $instructor;
                    $permissionsHistoryRecord->changer = User::getLoggedInUser()->getCurrentRoleData();
                    $permissionsHistoryRecord->permissions = $instructor->permissions;
                    $permissionsHistoryRecord->save();
                }

                return $user->id;
            }

            //Save properties for existing instructor entity
            $instructor = \Fisdap\EntityUtils::getEntity('InstructorLegacy', $values['instructorId']);

            $instructor->user->first_name = $values['firstName'];
            $instructor->user->last_name = $values['lastName'];
            $instructor->user->email = $values['email'];
            $instructor->user->home_phone = $values['homePhone'];
            $instructor->user->cell_phone = $values['cellPhone'];
            $instructor->user->work_phone = $values['workPhone'];

            if (array_key_exists('newPassword', $values) && $values['newPassword']) {
                $instructor->user->password = $values['newPassword'];
            }

            //Save email settings if editting yourself
            if ($this->isSelf) {
                $values['mailingList'] ? $instructor->addToMailingList() : $instructor->removeFromMailingList();
            }

            //Permissions
            if ($this->canChangePermissions) {
                // save the old value so we can compare later
                $oldPermissions = $instructor->permissions;
                // wrapping permissions arrays in check for is_array to avoid warnings
                $programPermissions = $skillsTrackerPermissions = $schedulePermissions = $reportsPermissions = 0;
                if (is_array($values['programPermissions'])) {
                    $programPermissions = array_sum($values['programPermissions']);
                }
                if (is_array($values['skillsTrackerPermissions'])) {
                    $skillsTrackerPermissions = array_sum($values['skillsTrackerPermissions']);
                }
                if (is_array($values['schedulePermissions'])) {
                    $schedulePermissions = array_sum($values['schedulePermissions']);
                }

                if (is_array($values['reportsPermissions'])) {
                    $reportsPermissions = array_sum($values['reportsPermissions']);
                }

                $instructor->permissions = $programPermissions + $skillsTrackerPermissions + $schedulePermissions + $reportsPermissions;
                $instructor->setPermissionSubRoles($values['subRoles']);
            }

            $instructor->receive_clinical_late_data_emails = $values['clinicalEmails'];
            $instructor->receive_lab_late_data_emails = $values['labEmails'];
            $instructor->receive_field_late_data_emails = $values['fieldEmails'];
            $instructor->email_event_flag = $values['automatedEmails'];

            $instructor->save();


            // ok, now that we've saved the instructor we can create a record in permissions history
            if ($this->canChangePermissions) {
                // only create a record if the permissions have actually changed
                if ($oldPermissions != $instructor->permissions) {
                    $permissionsHistoryRecord = \Fisdap\EntityUtils::getEntity('PermissionHistoryLegacy');
                    $permissionsHistoryRecord->entry_time = new DateTime();
                    $permissionsHistoryRecord->changed_instructor = $instructor;
                    $permissionsHistoryRecord->changer = $this->currentUser->getWritableUser()->getCurrentRoleData();
                    $permissionsHistoryRecord->permissions = $instructor->permissions;
                    $permissionsHistoryRecord->save();
                }
            }

            return $instructor->user->id;
        }
        return false;
    }


    private function isLtiUser()
    {
        if (! isset($this->instructor)) {
            return false;
        }
        
        $username = $this->instructor->getUserContext()->getUser()->getUsername();
        $ltiUserId = $this->instructor->getUserContext()->getUser()->getLtiUserId();
        $psgUserId = $this->instructor->getUserContext()->getUser()->getPsgUserId();

        return $username === $ltiUserId || $username === $psgUserId;
    }
}
