<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * Preceptor Signoff Form
 */
use Fisdap\Api\Shifts\Attachments\Entities\ShiftAttachment;
use Fisdap\Entity\Verification;

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_VerificationSubForm extends Fisdap_Form_Base
{
    /**
     * @var \Fisdap\Entity\Verification
     */
    public $verification;
    
    /**
     * @var \Fisdap\Entity\Run
     */
    public $run;
    
    /**
     * @var \Fisdap\Entity\Patient
     */
    public $patient;
    
    /**
     * @var \Fisdap\Entity\ShiftLegacy
     */
    public $shift;

    /**
     * @var \Fisdap\Entity\ProgramSettings
     */
    public $programSettings;

    /**
     * @var array
     */
    public $attachmentTableConfig;

    /**
     * @var array
     */
    public $attachmentTableOptions;

    /**
     * @var \Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway
     */
    public $shiftAttachmentsGateway;

    /**
     * @var array
     */
    public $attachments;

    /**
     * @var boolean
     */
    public $shiftAttachmentsRemaining;

    /**
     * @var \Fisdap\Attachments\Repository\AttachmentsRepository
     */
    private $attachmentsRepository;

    /**
     * @var Fisdap\Api\Shifts\Attachments\Entities\ShiftAttachment
     */
    public $shiftAttachment;

    /**
     * @param null $verificationId
     * @param null $runId
     * @param null $shiftId
     * @param null $shiftAttachmentsGateway
     * @param null $options
     *
     * @throws Zend_Exception
     */
    public function __construct($verificationId = null, $runId = null, $shiftId = null, $shiftAttachmentsGateway = null, $options = null)
    {
        $this->verification = \Fisdap\EntityUtils::getEntity('Verification', $verificationId);
        $this->run = \Fisdap\EntityUtils::getEntity('Run', $runId);
        
        if ($shiftId) {
            $this->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
        } else {
            $this->shift = $this->run->shift;
        }
        
        if ($runId) {
            $this->patient = \Fisdap\EntityUtils::getRepository('Patient')->findOneByRun($runId);
        }

        $this->programSettings = \Fisdap\Entity\ProgramLegacy::getCurrentProgram()->program_settings;
        $this->shiftAttachmentsGateway = $shiftAttachmentsGateway;

        if ($this->shiftAttachmentsGateway) {
            $this->shiftAttachmentsRemaining = ($shiftAttachmentsGateway->getRemainingAllottedCount($this->shift->student->user_context->id) > 0);
            try {
                $this->attachments = $this->shiftAttachmentsGateway->get($this->shift->id);
            } catch (\GuzzleHttp\Exception\ServerException $e) {
                $this->attachments = array();
            }
        } else {
            $this->attachments = array();
        }

        $this->attachmentsRepository = Zend_Registry::get('container')->make('Fisdap\Attachments\Repository\AttachmentsRepository');

        parent::__construct($options);
    }


    public function init()
    {
        $this->addJsFile("/js/library/SkillsTracker/Form/verification-sub-form.js");

        //Form ELements for password tab
        $username = new Zend_Form_Element_Text('username');
        $username->setLabel("Educator's username");
        $username->autocomplete = 'off';
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel("Educator's password");
    
        //Form elements for signature tab
        $signatureName = new Zend_Form_Element_Text('signatureName');
        $signatureName->setLabel('Type first and last name');
        $signatureElement = new Fisdap_Form_Element_Signature('signature');
        
        //Form elements for email tab
        $emailCheckbox = new Zend_Form_Element_Checkbox('emailCheckbox');
        $preceptorName = $this->patient->preceptor->first_name . " " . $this->patient->preceptor->last_name;
        $emailCheckbox->setLabel("Email this form to $preceptorName to be verified.");
        $emailCheckbox->setDecorators(self::$checkboxDecorators);
        $preceptorId = new Zend_Form_Element_Hidden('preceptorId');
        $preceptorId->setValue($this->patient->preceptor->id);

        //Form elements for attachments tab
        if ($this->programSettings->allow_educator_signoff_attachment) {
            $attachmentService = new \Fisdap\Service\AttachmentService();
            $attachmentRows = $this->attachments ? $attachmentService->getCheckboxRows($this->attachments, $this->shift->type) : array();
            $this->attachmentTableConfig = array();
            $this->attachmentTableOptions = array(
                'rows' => $attachmentRows,
                'fieldName' => 'selectedAttachmentId'
            );

            if ($this->verification->type->id == 4 && $this->verification->verified) {
                if (!empty($this->shiftAttachmentsGateway)) {
                    $this->shiftAttachment = $this->shiftAttachmentsGateway->getOne($this->shift->id, $this->verification->getShiftAttachment()->getId());
                }
            }
        }
        
        $lockCheckbox = new Zend_Form_Element_Checkbox('lock');
        if ($this->run) {
            $lockCheckbox->setLabel('Lock patient care/narrative documentation for this patient.');
        } else {
            $lockCheckbox->setLabel('Lock patient care/narrative documentation for this shift.');
        }
        $lockCheckbox->setDecorators(self::$checkboxDecorators);
        
        $verificationType = new Zend_Form_Element_Hidden('verificationType');
        $verificationType->setDecorators(array("ViewHelper"));
        $verificationId = new Zend_Form_Element_Hidden('verificationId');
        $verificationId->setDecorators(array("ViewHelper"));
        if ($this->run) {
            $runId = new Zend_Form_Element_Hidden('subRunId');
            $runId->setValue($this->run->id)
                  ->setDecorators(array("ViewHelper"));
        }
        if ($this->shift) {
            $shiftId = new Zend_Form_Element_Hidden('subShiftId');
            $shiftId->setValue($this->shift->id)
                  ->setDecorators(array("ViewHelper"));
        }
        
        $unlock = new Zend_Form_Element_Button('unlockButton');
        $unlock->setLabel('Unverify')
               ->setDecorators(array("ViewHelper"));
        
        $signoff = new Zend_Form_Element_Button('signoffButton');
        $signoff->setLabel('Sign off')
                ->setDecorators(array("ViewHelper"));

        $selectedAttachmentId = new Zend_Form_Element_Hidden('selectedAttachmentId');

        $this->addElements(array($username, $password, $signatureName, $signatureElement, $emailCheckbox, $verificationType, $verificationId, $lockCheckbox, $unlock, $signoff, $runId, $shiftId, $preceptorId, $selectedAttachmentId));
        
        if ($this->verification->id) {
            $this->setDefaults(array(
                'verificationType' => $this->verification->type->name,
                'verificationId' => $this->verification->id,
            ));
        } else {
            // Figure out what the first name of the available signoff types is
            // Default to "Password" (should be the first in the list).
            $defaultValue = "Password";
            
            if ($this->run) {
                $programSettings = $this->run->student->program->program_settings;
            } else {
                $programSettings = $this->shift->student->program->program_settings;
            }
            
            if ($programSettings->allow_educator_signoff_login) {
                $defaultValue = "Password";
            } elseif ($programSettings->allow_educator_signoff_signature) {
                $defaultValue = "Signature";
            } elseif ($programSettings->allow_educator_signoff_email) {
                $defaultValue = "Email";
            } elseif ($programSettings->allow_educator_signoff_attachment) {
                $defaultValue = "Attachment";
            // If none match, the form shouldn't show up anyways, so don't
            } else {
                $defaultValue = '';
            }
            
            $this->setDefaults(array(
                'verificationType' => $defaultValue,
            ));
        }
        
        $this->setDecorators(array(
            array('ViewScript', array('viewScript' => "verificationSubForm.phtml")),
        ));
    }
    
    public function process($values)
    {
        if ($values['verificationId']) {
            /** @var Verification $verification */
            $verification = \Fisdap\EntityUtils::getEntity('Verification', $values['verificationId']);
        } else {
            /** @var Verification $verification */
            $verification = \Fisdap\EntityUtils::getEntity('Verification');
        }
        
        $verification->type = \Fisdap\EntityUtils::getEntityByName("VerificationType", $values['verificationType'])->id;
        
        $valid = true;
        $errors = array();
        
        switch ($verification->type->name) {
            case 'Password':
                //Validation
                $user = \Fisdap\Entity\User::getByUsername($values['username']);
                $contactName = \Fisdap\Entity\ProgramLegacy::getCurrentProgram()->getProgramContactName();
                if (!$user->id) {
                    $valid = false;
                    $errors['username'] = "I don't recognize that username.  Please try again or contact " . $contactName . ".";
                    break;
                } elseif ($user->getCurrentRoleData()->program->id != \Fisdap\Entity\ProgramLegacy::getCurrentProgram()->id) {
                    $valid = false;
                    $errors['username'] = "This username does not belong to the student's program. Please try a different username.";
                    break;
                } elseif (!\Fisdap\Entity\User::authenticate_password($values['username'], $values['password'])) {
                    $valid = false;
                    $errors['password'] = "I don't recognize that password.  Please try again or contact " . $contactName . ".";
                    break;
                } elseif (!$user->isInstructor()) {
                    $valid = false;
                    $errors['username'] = "This username does not belong to an educator.";
                    break;
                }
                $verification->verified_by = $user->id;
                $verification->verified = true;
                break;
            
            case 'Signature':
                //Validation
                if (!$values['signatureName']) {
                    $valid = false;
                    $errors['signatureName'] = "Please type your name.";
                }
                
                if (!$values['signature']) {
                    $valid = false;
                    $errors['signature'] = "Please draw your signature in the space provided.  You can use a touch-screen device or your computer's mouse or track pad.";
                }
    
                if (!$valid) {
                    break;
                }

                $signature = \Fisdap\EntityUtils::getEntity('Signature');
                $signature->signature_string = $values['signature'];
                $signature->name = $values['signatureName'];

                $verification->signature = $signature;
                $verification->verified = true;
                break;

            case 'Attachment':
                //Validation
                if (!$values['selectedAttachmentId']) {
                    $valid = false;
                    $errors['selectedAttachmentId'] = "Please select an attachment.";
                } else {
                    /** @var ShiftAttachment $attachment */
                    $attachment = $this->attachmentsRepository->setAttachmentEntityClassName('Fisdap\Api\Shifts\Attachments\Entities\ShiftAttachment')->getOneById($values['selectedAttachmentId']);
                    $verification->setShiftAttachment($attachment);
                    $verification->verified = true;
                    $valid = true;
                }
                break;
        }

        // Check to be sure this run has all the necessary info to be locked
        if ($values['lock']) {
            // if there is a patient, this is a patient, so make sure the patient is complete
            if ($this->patient->id > 0) {
                if (!$this->run->isValid()) {
                    foreach ($this->run->getInvalidPatients() as $patient) {
                        $valid = false;
                        $errors['lock'] = "This patient is missing some required information that must be entered before it can be locked. (Missing: " . implode(', ', $patient->getInvalidFields()) . ")";
                    }
                }
            } elseif (!$this->shift->isValid()) {
                // otherwise we care about invalid shifts
                $valid = false;
                $invalidPatients = $this->shift->getInvalidPatients();
                $errorMsg  = "The following patient" . (count($invalidPatients) > 1 ? "s are" : " is") . " missing some required information that must be entered before this shift can be locked: ";
                $errorMsg .= "<ul>";
                foreach ($invalidPatients as $patient) {
                    $errorMsg .= "<li><a href='/skills-tracker/patients/index/runId/" . $patient->run->id . "'>" . $patient->getSummaryLine() . "</a> (Missing: " . implode(', ', $patient->getInvalidFields()) . ")</li>";
                }
                $errorMsg .= "</ul>";
                $errors['lock'] = $errorMsg;
            }
        }
        
        if ($valid) {
            // if the submission includes a shift ID but no run ID, then it is only a shift-level signoff
            // and hence we save a shift value
            if ($values['subShiftId'] && !$values['subRunId']) {
                $verification->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['subShiftId']);
                $verification->save();
            } // otherwise we assume it is a run-level signoff and a run value (no shift value) will be saved by the parent form
            
            return $verification;
        } else {
            return $errors;
        }
    }
}
