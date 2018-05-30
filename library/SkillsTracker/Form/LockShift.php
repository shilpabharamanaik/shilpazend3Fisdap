<?php
use Fisdap\Api\Users\CurrentUser\CurrentUser;

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
 * This produces a modal form for editing shifts
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_LockShift extends SkillsTracker_Form_Modal
{

    /**
     * @var \Fisdap\Entity\ShiftLegacy
     */
    public $shift;

    /**
     * @var string
     */
    public $redirectUrl;

    /**
     * @var string url to the Patient Care section
     */
    public $patientCareUrl;

    /**
     * @var CurrentUser
     */
    private $currentUser;

    /**
     * @param int $shiftId the id of the shift to edit
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($shiftId = null, $redirectUrl = null, $patientCareUrl = "/skills-tracker/patients/index/runId/", $options = null)
    {
        $this->shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);
        $this->redirectUrl = $redirectUrl;
        $this->patientCareUrl = $patientCareUrl;
        $this->currentUser = \Zend_Registry::get('container')->make(CurrentUser::class);


        parent::__construct($options);
    }

    public function init()
    {
        parent::init();

        $this->addCssFile('/css/library/SkillsTracker/Form/lock-shift.css');
        $this->addJsFile('/js/library/SkillsTracker/Form/lock-shift.js');

        $lock = new Zend_Form_Element_Radio('lock');
        $lock->setMultiOptions(array(0 => "Unlocked", 1 => "Locked"));

        $sendEmail = new Zend_Form_Element_Checkbox('sendEmail');
        if ($this->shift) {
            $sendEmail->setLabel("Notify " . $this->shift->student->user->getName() . " that you have unlocked this shift.");
        }

        $emailText = new Zend_Form_Element_Textarea('emailText');
        $emailText->setAttrib('class', 'fancy-input');
        if ($this->shift) {
            $emailText->setAttrib("placeholder", "Enter your message to ".$this->shift->student->user->first_name."...");
        }

        $unlockAllRuns = new Zend_Form_Element_Checkbox('unlockAllRuns');
        $unlockAllRuns->setLabel("unlock all");

        $lockshift_shiftId = new Zend_Form_Element_Hidden('lockshift_shiftId');
        $currentLockedStatus = new Zend_Form_Element_Hidden('currentLockedStatus');
        $valid = new Zend_Form_Element_Hidden('validShift');

        $this->addElements(array($lock, $sendEmail, $emailText, $unlockAllRuns, $lockshift_shiftId, $currentLockedStatus, $valid));

        $this->setElementDecorators(self::$hiddenElementDecorators);
        $this->setElementDecorators(self::$checkboxDecorators, array('lock', 'sendEmail', 'unlockAllRuns'), true);
        $this->setElementDecorators(self::$hiddenElementDecorators, array('lockshift_shiftId', 'currentLockedStatus', 'validShift'), true);




        if (count($this->shift->runs) > 0) {
            foreach ($this->shift->runs as $run) {
                $hidden = new Zend_Form_Element_Hidden('run_lock_switch_' . $run->id);
                $hidden->setDecorators(self::$hiddenElementDecorators);
                $hidden->setValue($run->locked ? 1 : 0);
                $hidden->class = "run_lock_switch";
                $this->addElement($hidden);
            }
        }

        $this->redirectUrl = "/skills-tracker/shifts/studentId/" . $this->shift->student->id;

        // set up the modal
        if ($this->shift->id) {
            $title = ($this->shift->locked) ? "Unlock Shift" : "Lock Shift";
        } else {
            $title = 'Lock/Unlock Shift';
        }
        $this->setDecorators(array(
                    'PrepareElements',
                    array('ViewScript', array('viewScript' => "lockShiftForm.phtml")),
                    'Form',
                    array('DialogContainer', array(
                            'id'          => 'lockShiftDialog',
                            'class'		=> 'lockShiftDialog',
                            'style'       => 'width: 600px; height: 700px;',
                            'jQueryParams' => array(
                                'tabPosition' => 'top',
                                'modal' => true,
                                'autoOpen' => false,
                                'resizable' => false,
                                'width' => 600,
                                'maxHeight' => 700,
                                'title' => $title
                            )
                    ))
        ));

        $this->setDefaults(array(
                    'lock' => $this->shift->locked,
                    'currentLockedStatus' => $this->shift->locked,
                    'lockshift_shiftId' => $this->shift->id,
                    ));

        if ($this->shift->id && !$this->shift->locked) {
            $this->setDefault('validShift', $this->shift->isValid() ? 1 : 0);
        } else {
            $this->setDefault('validShift', 1);
        }
    }

    /**
     * Validate the form, if valid, save the shift, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($data)
    {
        if ($this->isValid($data)) {
            $values = $this->getValues($data);

            $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $values['lockshift_shiftId']);

            // go through and lock/unlock all the patients per the form
            foreach ($shift->runs as $run) {
                $lock = $values['run_lock_switch_' . $run->id];

                if ($lock == 1) {
                    $run->locked = 1;
                    $run->save();
                } elseif ($lock == 0 && $values['currentLockedStatus'] == 1) {
                    $run->locked = 0;
                    if ($run->verification) {
                        $run->verification = null;
                    }
                    $run->save();
                }
            }

            // toggle the lock status of the shift
            $shift->lockShift(!$values['currentLockedStatus']);

            // if we are unlocking a shift, delete any associated verification and signoff
            if (!$values['currentLockedStatus'] == 0 && $shift->student->program->program_settings->allow_signoff_on_shift == true) {
                if ($shift->signoff) {
                    $shift->signoff->delete();
                }
                if ($shift->verification) {
                    $shift->verification->delete();
                }
                $shift->signoff = null;
                $shift->verification = null;
                $shift->save();
            }

            // send an unlock notification email to the student, if that box is checked
            if (!$values['currentLockedStatus'] == 0 && $values['sendEmail']) {
                $emailBody = $this->currentUser->user()->getFirstName() . " " . $this->currentUser->user()->getLastName() . " has unlocked your shift and has left the following comment: " . $values['emailText'] . "\n";
                $emailBody .= 'Go to: https://members.fisdap.net/skills-tracker/shifts/my-shift/shiftId/'.$shift->id.' to view the shift' . "\n";
                $mail = new Fisdap_TemplateMailer();
                $mail->addTo($shift->student->user->email)
                    ->setSubject("Shift " . $shift->id . " on " . $shift->start_datetime->format('Y-m-d') . " Unlocked")
                    ->setBodyText($emailBody . $mail->getDefaultSignature())
                    ->send();
            }

            $shift->save();
            return true;
        }

        return $this->getMessages();
    }
}
