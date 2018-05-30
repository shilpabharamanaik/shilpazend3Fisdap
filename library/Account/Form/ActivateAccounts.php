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
 * Form for activating accounts
 */
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\User;

/**
 * @package    Account
 */
class Account_Form_ActivateAccounts extends Fisdap_Form_Base
{
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'PrepareElements',
        array('ViewScript', array('viewScript' => "forms/activateAccountsForm.phtml")),
        array('Form', array('class' => 'send-activation-codes-form')),
    );
    
    /**
     * @var \Fisdap\Entity\Order
     */
    public $order;
    
    /**
     * @var int the number of rows affected by csv upload
     */
    public $rowsWithData;
    
    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($orderId = null, $options = null)
    {
        $this->order = \Fisdap\EntityUtils::getEntity('Order', $orderId);
        
        parent::__construct($options);
    }
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();
        
        $this->addCssFile("/css/library/Account/Form/activate-accounts.css");
        $this->addJsFile("/js/library/Account/Form/activate-accounts.js");
        
        //add js file to do cool input masking
        $this->addJsFile("/js/jquery.maskedinput-1.3.js");
        
        $this->setDecorators(self::$_formDecorators);
        
        foreach ($this->order->order_configurations as $config) {
            $subform = new \Account_Form_ActivateAccountsSubForm($config->id);
            $this->addSubForm($subform, "orderConfiguration_" . $config->id);
        }
        
        $emailEveryone = new Zend_Form_Element_Checkbox("emailEveryone");
        $emailEveryone->setLabel("Send email to account holders with their login information.")
                      ->setDecorators(self::$checkboxDecorators)
                      ->setValue(1);
        $this->addElement($emailEveryone);
        
        $orderId = new Zend_Form_Element_Hidden("orderId");
        $orderId->setDecorators(array('ViewHelper'));
        $this->addElement($orderId);

        $saveButton = new Fisdap_Form_Element_SaveButton("saveButton");
        $saveButton->setLabel("Save")
                   ->setDecorators(self::$buttonDecorators);
        $this->addElement($saveButton);
        
        if ($this->order->id) {
            $this->setDefaults(array(
                'orderId' => $this->order->id,
            ));
        }
    }
    
    public function numberOfStudents()
    {
        $numberOfStudents = 0;
        foreach ($this->order->order_configurations->first()->serial_numbers as $sn) {
            $numberOfStudents++;
        }
        return $numberOfStudents;
    }
    
    public function populateFromFile($csv, $overrideDistributed)
    {
        $configCount = 1;
        $addedCount = 0;
        foreach ($this->order->order_configurations as $config) {
            $subform = new \Account_Form_ActivateAccountsSubForm($config->id, $csv->getRows($configCount), $overrideDistributed);
            $this->addSubForm($subform, "orderConfiguration_" . $config->id);
            $configCount++;
            $addedCount += $subform->addedCount;
        }
        $this->rowsWithData = $addedCount;
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
            
            $order = \Fisdap\EntityUtils::getEntity("Order", $values['orderId']);
            $updateCompliance = array();
            
            foreach ($this->getSubForms() as $subform) {
                $subValues = $subform->getValues(true);
                
                foreach ($subform->serials as $serial) {
                    //Check to see if a value is set for first name, if so we know this row was validated
                    if ($subValues["first_" . $serial->id]) {
                        
                        // Create user entity
                        $user = new User();
                        $user->first_name = $subValues["first_" . $serial->id];
                        $user->last_name = $subValues["last_" . $serial->id];
                        $user->email = $subValues["email_" . $serial->id];
                        $user->username = $subValues["username_" . $serial->id];
                        $user->password = $subValues["password_" . $serial->id];
                        
                        $user->license_state = $subValues["licenseState_" . $serial->id];
                        $user->license_number = $subValues["licenseNumber_" . $serial->id];
                        $user->license_expiration_date = $subValues["licenseExpirationDate_" . $serial->id];
                        $user->state_license_number = $subValues["stateLicenseNumber_" . $serial->id];
                        $user->state_license_expiration_date = $subValues["stateLicenseExpirationDate_" . $serial->id];
                        
                        //Create DateTime from the form values
                        if ($subValues['grad_date_' . $serial->id]['year'] && $subValues['grad_date_' . $serial->id]['month']) {
                            $gradDate = new \DateTime($subValues['grad_date_' . $serial->id]['year'] . "-" . $subValues['grad_date_' . $serial->id]['month'] . "-01");
                        } else {
                            $gradDate = new \DateTime("+1 year");
                        }

                        //Create an instructor or entity and attach to user
                        if ($serial->order_configuration->onlyPreceptorTraining()) {
                            //Create instructor entity to attach to user
                            $instructor = new InstructorLegacy();
                            $user->addUserContext("instructor", $instructor, $serial->program->id);
                            
                            $user->save();
                            
                            //Activate the serial number for this student, this needs to be called after the student is created
                            $instructor->activateSerialNumber($serial);
                        } else {
                            // Create student entity to attach to user
                            $student = new StudentLegacy();
                            $user->addUserContext("student", $student, $serial->program->id, $serial->certification_level->id, null, $gradDate);
    
                            // Add student to group if one has been set
                            if ($serial->group->id) {
                                $serial->group->addStudent($student);
                            }
                            
                            $user->save();
                            
                            // Activate the serial number for this student, this needs to be called after the student is created
                            $student->activateSerialNumber($serial);
                            
                            $body = "<p>Welcome to Fisdap! As you get started, please take a moment to visit your <a href='/account/edit/student'>Account</a> page to set your email preferences.</p>";
                            $subject = "Set your email preferences";
                            $user->addDashboardMessage($subject, 1, $body);
                        }
                        
                        
                        
                        //Email account info if requested
                        if ($values['emailEveryone']) {
                            $mail = new \Fisdap_TemplateMailer();
                            $mail->addTo($user->email)
                                 ->setSubject("A new Fisdap account has been created for you")
                                 ->setViewParam('serial', $serial)
                                 ->setViewParam('orderer', $order->user)
                                 ->setViewParam('urlRoot', Util_HandyServerUtils::getCurrentServerRoot())
                                 ->setViewParam('user', $user)
                                 ->setViewParam('password', $subValues["password_" . $serial->id])
                                 ->sendHtmlTemplate('new-account-invitation.phtml');
                        }
                        
                        $user->save();
                        
                        //Auto attach requirements if it's an instructor or they have scheduler
                        if ($user->isInstructor() || $serial->hasScheduler()) {
                            if ($user->getCurrentUserContext()->autoAttachRequirements(false)) {
                                $updateCompliance[] = $user->getCurrentUserContext()->id;
                            }
                        }
                    }
                }
            }
            
            if (count($updateCompliance)) {
                \Fisdap\EntityUtils::getRepository("Requirement")->updateCompliance($updateCompliance);
            }
            
            return true;
        }
    }
}
