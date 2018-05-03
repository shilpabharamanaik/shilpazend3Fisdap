<?php
use Fisdap\Data\CertificationLevel\CertificationLevelRepository;
use Fisdap\Entity\SerialNumberLegacy;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\User;
use Fisdap\Entity\CertificationLevel;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\UserContext;
use Fisdap\EntityUtils;


/**
 * Form for editing a Fisdap Student account
 *
 * @package    Account
 */
class Account_Form_Student extends Fisdap_Form_Base
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
     * @var StudentLegacy
     */
    public $student;

    /**
     * @var int the new user id
     */
    public $userId;

    /**
     * @var \Fisdap\Entity\SerialNumberLegacy
     */
    public $serial;

    /**
     * @var boolean
     */
    public $instructorView = false;

    /**
     * @var boolean
     */
    public $staffView = false;

    /**
     * @var boolean
     */
    public $isSecure = false;

    /**
     * @var bool
     */
    public $isLtiUser = false;


    /**
     * @param int $studentId the id of the student to edit
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($studentId = null, $serialId = null, $options = null)
    {
        $this->student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $studentId);

        if (!is_null($studentId)) {
            $this->isLtiUser = $this->isLtiUser();
        }

        if ($serialId) {
            $this->serial = \Fisdap\EntityUtils::getEntity('SerialNumberLegacy', $serialId);
        } else if ($this->student) {
            $this->serial = $this->student->getSerialNumber();
        } else {
            $this->serial = \Fisdap\EntityUtils::getEntity("SerialNumberLegacy");
        }

        $user = \Fisdap\Entity\User::getLoggedInUser();
        if ($user) {
            $this->instructorView = $user->isInstructor();
            $this->staffView = $user->isStaff();
            $this->isSecure = $user->isSecure();
        }

        parent::__construct($options);
    }

    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();

        $this->setAttrib('id', 'studentForm');

        //add js file to do cool input masking
        $this->addJsFile("/js/jquery.maskedinput-1.3.js");

        //add js file to do awesome live validation
        $this->addJsFile("/js/library/Fisdap/Form/student.js");
        $this->addCssFile("/css/library/Fisdap/Form/student.css");

        $this->addJsOnLoad(
            '$("#homePhone").mask("999-999-9999");
			 $("#workPhone").mask("999-999-9999? x99999");
			 $("#cellPhone").mask("999-999-9999");
			 $("#contactPhone").mask("999-999-9999");
			 $("#stateLicenseExpirationDate").mask("99/99/9999");
			 $("#licenseExpirationDate").mask("99/99/9999");
			');


        //first name
        if ($this->isLtiUser) {
            $first = new Zend_Form_Element_Hidden('firstName');
        } else {
            $first = new Zend_Form_Element_Text('firstName');
            $first->setLabel('First Name')
                ->setRequired(true)
                ->setDescription('(required)')
                ->addFilter('StripTags')
                ->addFilter('HtmlEntities')
                ->addErrorMessage("Please enter a first name.");
        }

        //last name
        if ($this->isLtiUser) {
            $last = new Zend_Form_Element_Hidden('lastName');
        } else {
            $last = new Zend_Form_Element_Text('lastName');
            $last->setLabel('Last Name')
                ->setRequired(true)
                ->setDescription('(required)')
                ->addFilter('StripTags')
                ->addFilter('HtmlEntities')
                ->addErrorMessage("Please enter a last name.");
        }

        //email
        if ($this->isLtiUser) {
            $email = new Zend_Form_Element_Hidden('email');
        } else {
            $email = new Fisdap_Form_Element_Email('email');
            $email->setDescription('(required)')
                ->setRequired(true)
                ->addErrorMessage('Please enter a valid email address.');
        }

        //home phone
        $homePhone = new Zend_Form_Element_Text('homePhone');
        $homePhone->setLabel('Home');

        //work phone
        $workPhone = new Zend_Form_Element_Text('workPhone');
        $workPhone->setLabel('Work');

        //cell phone
        $cellPhone = new Zend_Form_Element_Text('cellPhone');
        $cellPhone->setLabel('Cell');

        //address1
        $address = new Zend_Form_Element_Text('address');
        $address->setLabel('Street');

        //city
        $city = new Zend_Form_Element_Text('city');
        $city->setLabel('City');

        //Country
        $country = new Fisdap_Form_Element_Countries("country");
        $country->setLabel("Country")
            ->setRequired(true)
            ->addErrorMessage("Please choose a country.");

        //state
        $state = new Fisdap_Form_Element_States('state');
        $state->setLabel('State')
            ->addValidator('NotEmpty', false, array('string'))
            ->addErrorMessage('Please choose a state.');

        //state
        $state = new Fisdap_Form_Element_States('state');
        $state->setLabel('State')
            ->addValidator('NotEmpty', false, array('string'))
            ->addErrorMessage('Please choose a state.');
        if ($this->student) {
            $selCountry = $this->student->country;
        } else {
            $selCountry = $this->serial->order->country;
        }
        $state->setCountry($selCountry);

        //zip
        $zip = new Zend_Form_Element_Text('zip');
        $zip->setLabel('Zip')
            ->addValidator('Digits', true)
            ->addValidator('LessThan', true, array('max' => '99999'))
            ->addErrorMessage('Please enter a valid zip code.');

        //contact name
        $contactName = new Zend_Form_Element_Text('contactName');
        $contactName->setLabel('Name')
            ->addFilter('StripTags')
            ->addFilter('HtmlEntities');

        //contact phone
        $contactPhone = new Zend_Form_Element_Text('contactPhone');
        $contactPhone->setLabel('Phone');

        //contact relationship
        $contactRelationship = new Zend_Form_Element_Text('contactRelationship');
        $contactRelationship->setLabel('Relationship')
            ->addFilter('StripTags')
            ->addFilter('HtmlEntities');

        //Personal stuff
        $gender = new Zend_Form_Element_Radio('gender');
        $gender->setLabel('Gender')
            ->setMultiOptions(\Fisdap\Entity\Gender::getFormOptions())
            ->setValue(1)
            ->setSeparator(' ');

        $ethnicity = new Zend_Form_Element_Select('ethnicity');
        $ethnicity->setLabel('Ethnicity')
            ->setMultiOptions(\Fisdap\Entity\Ethnicity::getFormOptions())
            ->setValue(6);

        $birthDate = new Fisdap_Form_Element_Date('birthDate');
        $birthDate->setLabel('Birth Date');

        //Mailing list stuff
        $mailingList = new Zend_Form_Element_Checkbox('mailingList');
        $mailingList->setLabel('Email me about new Fisdap features, tools and events.')
            ->setDecorators(self::$checkboxDecorators);

        if (!$this->isLtiUser) {
            //Password stuff
            $currentPassword = new Zend_Form_Element_Password('currentPassword');
            $currentPassword->setLabel('Current Password')
                ->setAttrib("autocomplete", "off")
                ->addValidator(new \Fisdap_Validate_AuthenticatePassword());

            //Change the password label for instructors
            if ($this->instructorView) {
                $currentPassword->setLabel('Your Password');
            }

            $newPassword = new Zend_Form_Element_Password('newPassword');
            $newPassword->setLabel('New Password')
                ->setAttrib("autocomplete", "off")
                ->addValidator("StringLength", false, array('min' => 5));

            $confirmPassword = new Zend_Form_Element_Password('confirmPassword');
            $confirmPassword->setLabel('Confirm New Password')
                ->setAttrib("autocomplete", "off")
                ->addValidator('identical', false, array('token' => 'newPassword', 'messages' => array('notSame' => 'New passwords do not match.')));


            //Make password fields required if it's a new student
            if (!$this->student->id) {
                $newPassword->setRequired();
                $confirmPassword->setRequired();
                $newPassword->setDescription('(required)');
                $confirmPassword->setDescription('(required)');
            }
        }


        //Transition Course Fields
        if ($this->serial && $this->serial->hasTransitionCourse()) {
            //Since it's a transition course account, we need to require certain fields
            $address->setRequired(true)->setDescription("(required)")->addErrorMessage("Please enter a street address.");
            $city->setRequired(true)->setDescription("(required)")->addErrorMessage("Please enter a city.");
            $zip->setRequired(true)->setDescription("(required)");

            //Only require state if they hav options to choose from
            if (count($state->getMultiOptions())) {
                $state->setRequired(true)->setDescription("(required)");
            }


            $licenseNumber = new Zend_Form_Element_Text("licenseNumber");
            $licenseNumber->setLabel("NREMT License Number:")
                ->setRequired(true)
                ->setDescription("(required)")
                ->addErrorMessage('Please enter your NREMT license number.');
            $this->addElement($licenseNumber);

            $dateValidator = new \Zend_Validate_Date(array("format" => "MM/dd/yyyy"));

            $licenseExpirationDate = new Zend_Form_Element_Text("licenseExpirationDate");
            $licenseExpirationDate->setLabel("NREMT License Expiration:")
                ->setRequired(true)
                ->addValidator($dateValidator)
                ->addErrormessage('Please enter your NREMT license expiration.')
                ->setDescription("(required) format: MM/DD/YYYY");
            $this->addElement($licenseExpirationDate);

            $licenseState = new Fisdap_Form_Element_States("licenseState");
            $licenseState->setLabel("Licensing State")
                ->setRequired(true)
                ->useFullNames()
                ->setDescription("(required)")
                ->addErrorMessage('Please choose your licensing state.');
            $this->addElement($licenseState);

            $stateLicenseNumber = new Zend_Form_Element_Text("stateLicenseNumber");
            $stateLicenseNumber->setLabel("State License Number:")
                ->setRequired(true)
                ->setDescription("(required)")
                ->addErrorMessage('Please enter your state license number.');
            $this->addElement($stateLicenseNumber);

            $stateLicenseExpirationDate = new Zend_Form_Element_Text("stateLicenseExpirationDate");
            $stateLicenseExpirationDate->setLabel("State License Expiration:")
                ->setRequired(true)
                ->addValidator($dateValidator)
                ->addErrormessage('Please enter your state license expiration.')
                ->setDescription("(required) format: MM/DD/YYYY");
            $this->addElement($stateLicenseExpirationDate);
        }

        //Graduation stuff
        $gradDate = new Fisdap_Form_Element_GraduationDate('gradDate');
        $gradDate->setYearRange(date("Y") - 5, date("Y") + 5)
            ->setDescription('(required)');

        $futureDateOnly = false;

        //If we have a student and their graduation date is in the past, add it to the available options and change the validator
        if ($this->student->id && ($gradYear = $this->student->getGraduationDate()->format("Y")) < date("Y")) {
            $gradDate->addYearOption($gradYear);
        }

        if (!$this->student->id) {
            $gradDate->setLabel("What is your anticipated graduation date?");
            $futureDateOnly = true;

        }
        $gradDate->addValidator(new \Fisdap_Validate_GraduationDate(true, $futureDateOnly));

        // If there is already a graduation_date on the serial number entity, then this form field is not necessary
        // so don't do any validation on it
        if ($this->serial->graduation_date) {
            $gradDate->clearValidators();
        }

        $emtGradDate = new Fisdap_Form_Element_GraduationDate('emtGradDate');
        $emtGradDate->setLabel('When did you finish EMT school?')
            ->setYearRange(1970, date("Y"))
            ->addValidator(new \Fisdap_Validate_GraduationDate(false, false));

        $emtCertDate = new Fisdap_Form_Element_GraduationDate('emtCertDate');
        $emtCertDate->setLabel('When did you obtain your EMT certification?')
            ->setYearRange(1970, date("Y"))
            ->addValidator(new \Fisdap_Validate_GraduationDate(false, false));

        $emtGradFlag = new Zend_Form_Element_Checkbox('emtGradFlag');
        $emtGradFlag->setLabel("I have not attended or did not finish EMT school.")
            ->setDecorators(self::$checkboxDecorators);

        $emtCertFlag = new Zend_Form_Element_Checkbox('emtCertFlag');
        $emtCertFlag->setLabel("I have not obtained my EMT certification.")
            ->setDecorators(self::$checkboxDecorators);

        $graduationStatus = new Zend_Form_Element_Select('graduationStatus');
        $graduationStatus->setLabel('Status')
            ->setMultiOptions(\Fisdap\Entity\GraduationStatus::getFormOptions(false, false));

        $goodData = new Zend_Form_Element_Radio('goodData');
        $goodData->setLabel('Good Data')
            ->setSeparator(' ')
            ->setMultiOptions(array(1 => "Yes", 0 => "No", -1 => "Not Set"));

        if ($user = \Fisdap\Entity\User::getLoggedInUser()) {
            $professionId = $user->getCurrentProgram()->profession->id;
        } else {
            $professionId = 1;
        }
        $certLevel = new Zend_Form_Element_Select('certLevel');
        $certLevel->setLabel('Certification')
            ->setMultiOptions(\Fisdap\Entity\CertificationLevel::getFormOptions(false, false, "description", $professionId));

        //Staff only options
        $usernameValidator = new \Zend_Validate_Db_NoRecordExists(array('table' => 'fisdap2_users', 'field' => 'username', 'adapter' => \Zend_Registry::get('db')));
        if ($this->student->username) {
            $usernameValidator->setExclude("username != '" . $this->student->username . "'");
        }
        $usernameValidator->setMessage('That username already exists. Please choose another.');

        $regexValidator = new \Zend_Validate_Regex(array('pattern' => '/^[a-zA-Z0-9]+$/'));
        $regexValidator->setMessage("Please enter a username that only contains letters and numbers and is at least 3 characters long.");

        $username = new Zend_Form_Element_Text('newUsername');
        $username->setLabel('Username')
            ->setRequired(true)
            ->setDescription('(required)')
            ->addValidator($regexValidator)
            ->addValidator("StringLength", false, array('min' => 3))
            ->addValidator($usernameValidator);

        $studentId = new Zend_Form_Element_Hidden('studentId');
        $studentId->setDecorators(self::$hiddenElementDecorators);

        $snId = new Zend_Form_Element_Hidden('snId');
        $snId->setDecorators(self::$hiddenElementDecorators);

        //save button
        $submitButton = new Fisdap_Form_Element_SaveButton('save');
        $submitButton->setLabel("Save & Continue");
        if ($this->student->id) {
            $submitButton->setLabel("Save");
        }
        $submitButton->setDecorators(self::$buttonDecorators);

        //Add elements that aren't in a display group
        $this->addElements(array(
            $submitButton,
            $studentId,
            $emtGradDate,
            $emtCertDate,
            $gender,
            $ethnicity,
            $birthDate,
            $emtCertFlag,
            $emtGradFlag,
            $snId,
        ));

        //Add elements for new accounts only
        if (!$this->student->id) {
            $this->addElement($username);

            if (!$this->serial->hasTransitionCourse()) {
                $this->addElement($gradDate);
            }
        }


        //Add mailing list if they're a student
        if (!$this->instructorView) {
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
            array($homePhone, $workPhone, $cellPhone),
            'phoneNumbers',
            array('description' => 'Phone', 'decorators' => $this->_displayGroupDecorators)
        );

        $this->addDisplayGroup(
            array($address, $city, $country, $state, $zip),
            'contactAddress',
            array('description' => 'Address', 'decorators' => $this->_displayGroupDecorators)
        );

        $this->addDisplayGroup(
            array($contactName, $contactPhone, $contactRelationship),
            'contact',
            array('description' => 'Emergency Contact', 'decorators' => $this->_displayGroupDecoratorsNoTitle)
        );


        if (!$this->isLtiUser) {

            if ($this->isSecure && $this->instructorView) {
                $passwordElements = array($newPassword, $confirmPassword);
            } else {
                $passwordElements = array($currentPassword, $newPassword, $confirmPassword);
            }

            $this->addDisplayGroup(
                $passwordElements,
                'passwords',
                array('description' => 'Password', 'decorators' => array(
                    array('Description', array('tag' => 'div', 'class' => 'form-group-title section-header no-border')),
                    'FormElements',
                    array('HtmlTag', array('tag' => 'div', 'class' => 'form-group password-section')),
                ))
            );
        }


        if ($this->staffView) {
            $gradElements = array($gradDate, $graduationStatus, $goodData, $certLevel);
        } else {
            $gradElements = array($gradDate, $graduationStatus, $goodData);
        }

        if ($this->instructorView) {
            $this->addDisplayGroup(
                $gradElements,
                'graduation',
                array('description' => 'Graduation', 'decorators' => $this->_displayGroupDecorators)
            );
        }

        //Switch out the viewscript depending on whether this is a new student or an existing one
        if ($this->student->id) {
            $viewscript = "forms/editStudentForm.phtml";
            $this->setElementDecorators(self::$gridElementDecorators, array('save', 'studentId', 'snId', 'mailingList'), false);
        } else {
            $viewscript = "forms/newStudentForm.phtml";
            $this->setElementDecorators(self::$elementDecorators, array('save', 'studentId', 'snId', 'mailingList', 'emtCertFlag', 'emtGradFlag'), false);
        }

        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => $viewscript)),
            'Form'
        ));

        //Editing an existing student
        if ($this->student->id) {
            $this->setDefaults(array(
                'firstName' => $this->student->user->first_name,
                'lastName' => $this->student->user->last_name,
                'email' => $this->student->email,
                'homePhone' => $this->student->home_phone,
                'workPhone' => $this->student->work_phone,
                'cellPhone' => $this->student->cell_phone,
                'address' => $this->student->address,
                'city' => $this->student->city,
                'country' => $this->student->country,
                'state' => $this->student->state,
                'zip' => $this->student->zip,
                'contactName' => $this->student->contact_name,
                'contactPhone' => $this->student->contact_phone,
                'contactRelationship' => $this->student->contact_relation,
                'gradDate' => $this->student->getGraduationDate(),
                'graduationStatus' => $this->student->graduation_status->id,
                'goodData' => (is_null($this->student->good_data) ? -1 : (int)$this->student->good_data),
                'certLevel' => $this->student->getCertification()->id,
                'newUsername' => $this->student->user->username,
                'studentId' => $this->student->id,
                'mailingList' => $this->student->onMailingList(),
                'licenseNumber' => $this->student->user->license_number,
                'stateLicenseNumber' => $this->student->user->state_license_number,
                'licenseState' => $this->student->user->license_state,
            ));

            //We have to check if these dates exist before calling methods associated with them
            if ($this->student->user->license_expiration_date instanceof \DateTime) {
                $this->setDefault("licenseExpirationDate", $this->student->user->license_expiration_date->format("m/d/Y"));
            }

            if ($this->student->user->state_license_expiration_date instanceof \DateTime) {
                $this->setDefault("stateLicenseExpirationDate", $this->student->user->state_license_expiration_date->format("m/d/Y"));
            }
        } else {
            //Creating a new account
            if ($this->serial->order->individual_purchase) {
                $order = $this->serial->order;
                $config = $order->order_configurations->first();

                $date = strtotime($config->graduation_date->date);

                $this->setDefaults(array(
                    'firstName' => $order->getFirstName(),
                    'lastName' => $order->getLastName(),
                    'email' => $order->email,
                    'homePhone' => $order->phone,
                    'address' => $order->address1,
                    'city' => $order->city,
                    'country' => $order->country,
                    'state' => $order->state,
                    'zip' => $order->zip,
                    'gradDate[month]' => date("m", $date),
                    'gradDate[year]' => date("Y", $date),
                    'certLevel' => $this->serial->certification_level->id,
                ));
            }

            if ($this->serial->id) {
                $this->setDefaults(array(
                    'snId' => $this->serial->id,
                ));
            }
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
            $genderRepository = \Fisdap\EntityUtils::getRepository("Gender");
            $ethnicityRepository = \Fisdap\EntityUtils::getRepository("Ethnicity");
            $values = $this->getValues();

            // Create entities for a new student account
            if (!$values['studentId']) {

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
                $user->address = $values['address'];
                $user->city = $values['city'];
                $user->country = $values['country'];
                $user->state = $values['state'];
                $user->zip = $values['zip'];
                $user->contact_name = $values['contactName'];
                $user->contact_phone = $values['contactPhone'];
                $user->contact_relation = $values['contactRelationship'];
                $user->setGender($genderRepository->getOneById($values['gender']));
                $user->setEthnicity($ethnicityRepository->getOneById($values['ethnicity']));

                //Transition Course stuff
                $user->license_number = $values['licenseNumber'];
                $user->license_expiration_date = $values['licenseExpirationDate'];
                $user->state_license_number = $values['stateLicenseNumber'];
                $user->license_state = $values['licenseState'];
                $user->state_license_expiration_date = $values['stateLicenseExpirationDate'];

                // if the value is 1, they have not graduated
                if ($values['emtGradFlag']) {
                    $user->emt_grad = 0;
                } else {
                    $user->emt_grad = 1;
                    if ($values['emtGradDate']['month'] && $values['emtGradDate']['year']) {
                        $user->emt_grad_date = new \DateTime($values['emtGradDate']['year'] . "-" . $values['emtGradDate']['month'] . "-01");
                    }
                }

                // if the value is 1, they do not have a certification
                if ($values['emtCertFlag']) {
                    $user->emt_cert = 0;
                } else {
                    $user->emt_cert = 1;
                    if ($values['emtCertDate']['month'] && $values['emtCertDate']['year']) {
                        $user->emt_cert_date = new \DateTime($values['emtCertDate']['year'] . "-" . $values['emtCertDate']['month'] . "-01");
                    }
                }

                if ($values['birthDate']['year'] && $values['birthDate']['month'] && $values['birthDate']['day']) {
                    $user->birth_date = new \DateTime($values['birthDate']['year'] . "-" . $values['birthDate']['month'] . "-" . $values['birthDate']['day']);
                }

                // Get serial number entity for activation stuff
                $serial = \Fisdap\EntityUtils::getEntity('SerialNumberLegacy', $values['snId']);

                // Create DateTime from the form values
                try {
                    $gradDate = $serial->graduation_date ? $serial->graduation_date : new \DateTime($values['gradDate']['year'] . "-" . $values['gradDate']['month'] . "-01");
                } catch (Exception $e) {
                    $gradDate = new \DateTime("+1 year");
                }

                // Create student entity to attach to user
                $student = new StudentLegacy();
                $user->addUserContext("student", $student, $serial->program->id, $serial->certification_level->id, null, $gradDate);

                // Add student to group if one has been set
                if ($serial->group->id) {
                    $serial->group->addStudent($student);
                }

                // Save the changes and flush
                $user->save();

                // Activate the serial number for this student, this needs to be called after the student is created
                $student->activateSerialNumber($serial);
                $student->save();

                // Auto assign requirements if the student has scheduler
                if ($serial->hasScheduler()) {
                    $user->getCurrentUserContext()->autoAttachRequirements();
                }

                // Add student to mailing list
                $values['mailingList'] ? $student->addToMailingList() : $student->removeFromMailingList();
                $student->save();

                $this->userId = $user->id;
                return true;
            }

            // Save properties for existing student entity
            $student = \Fisdap\EntityUtils::getEntity('StudentLegacy', $values['studentId']);
            $serial = $student->getSerialNumber();


          	//set certification_level for the student's serial number

            if(!is_null($values['certLevel'])){
              $serial->set_certification_level($values['certLevel']);

              $serial->save();
            }

            $student->user->first_name = $values['firstName'];
            $student->user->last_name = $values['lastName'];
            $student->user->email = $values['email'];
            $student->user->home_phone = $values['homePhone'];
            $student->user->cell_phone = $values['cellPhone'];
            $student->user->work_phone = $values['workPhone'];
            $student->user->address = $values['address'];
            $student->user->city = $values['city'];
            $student->user->country = $values['country'];
            $student->user->state = $values['state'];
            $student->user->zip = $values['zip'];
            $student->user->contact_name = $values['contactName'];
            $student->user->contact_phone = $values['contactPhone'];
            $student->user->contact_relation = $values['contactRelationship'];

            //Transition Course stuff

            if ($serial->hasTransitionCourse()) {
                $student->user->license_number = $values['licenseNumber'];
                $student->user->license_state = $values['licenseState'];
                $student->user->license_expiration_date = $values['licenseExpirationDate'];
                $student->user->state_license_number = $values['stateLicenseNumber'];
                $student->user->state_license_expiration_date = $values['stateLicenseExpirationDate'];

                //Update the user fields in moodle
                $moodleAPI = new \Util_MoodleAPI("transition_course");
                $moodleAPI->updateMoodleUser($student->user);
            }


            //student only fields
            if (!$this->instructorView && $values['studentId']) {
                $values['mailingList'] ? $student->addToMailingList() : $student->removeFromMailingList();
            }

            //Instructor only fields
            if ($this->instructorView && $values['studentId']) {
                $student->setGraduationDate(new \DateTime($values['gradDate']['year'] . "-" . $values['gradDate']['month'] . "-01"));
                $student->graduation_status = $values['graduationStatus'];

                switch ($values['goodData']) {
                    case 1:
                        $student->good_data = true;
                        break;
                    case 0:
                        $student->good_data = false;
                        break;
                    case -1:
                        $student->good_data = NULL;
                        break;
                }
            }

            //Staff only fields
            if ($this->staffView && $values['studentId']) {
                $student->setCertification($values['certLevel']);
            }

            if (array_key_exists('newPassword', $values) && $values['newPassword']) {
                $student->user->password = $values['newPassword'];
            }

            $student->save();


            return true;
        }
        return false;
    }


    private function isLtiUser()
    {
        if (!$this->student) {
            return false;
        }
        $username = $this->student->getUserContext()->getUser()->getUsername();
        $ltiUserId = $this->student->getUserContext()->getUser()->getLtiUserId();
        $psgUserId = $this->student->getUserContext()->getUser()->getPsgUserId();

        return $username === $ltiUserId || $username === $psgUserId;
    }
}
