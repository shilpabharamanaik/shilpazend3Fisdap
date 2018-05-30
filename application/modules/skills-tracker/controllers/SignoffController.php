<?php

/**
 *
 * @package    Fisdap
 * @subpackage Controllers
 */
class SkillsTracker_SignoffController extends Fisdap_Controller_SkillsTracker_Private
{
    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
        $patientId = $this->_getParam('patientId');
        $runId = $this->_getParam('runId');
        
        $this->view->pageTitle = "Preceptor Sign Off";
        $this->view->form = new SkillsTracker_Form_Signoff();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            var_dump($this->view->form->process($request->getPost()));
        }
    }

    public function testSignatureAction()
    {
        $form = new Zend_Form();

        $signatureElement = new Fisdap_Form_Element_Signature('signature');

        $ur = \Fisdap\Entity\User::getLoggedInUser();
        
        if (count($_POST) > 0) {
            $signatureElement->signature = $this->_getParam('signature');

            $signature = \Fisdap\EntityUtils::getEntity('Signature');

            $signature->signature_string = $this->_getParam('signature');

            $signature->user = $ur;

            $signature->save();
        }

        $form->addElement($signatureElement);

        $this->view->form = $form;
    }
    
    public function signoffLandingAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity()) {
            $user = $ur = \Fisdap\Entity\User::getLoggedInUser();
        } else {
            $user = null;
        }
        
        switch ($this->_getParam('source')) {
            case 'password':
                break;
            case 'signature':
                // Save down the signature

                /** @todo THIS IS A BUG.  $signatureElement has not been defined. */
                $signatureElement->signature = $this->_getParam('signature');
                
                $signature = \Fisdap\EntityUtils::getEntity('Signature');
                $signature->signature_string = $this->_getParam('signature');
                $signature->user = $user;

                $signature->save();
                break;
            case 'email':
                // Send out a new email
                $email = \Fisdap\EntityUtils::getEntity('SignoffEmail');
                $email->user = $user;
                $email->has_signed_off = false;
                $email->sendEmail();
                $email->save();
                break;
        }
        
        $this->redirect($this->_getParam('redirect'));
    }
    
    /**
     * This action should be used to sign off on an email based signoff request.
     */
    public function emailBasedAction()
    {
        $signoffEmail = $this->em->getRepository('Fisdap\Entity\SignoffEmail')->findOneBy(array('email_key' => $this->_getParam('key')));
        
        // Determine what the message should be...
        // We have 4 or so states.
        // State 1 - No signoffEmail was found.
        if ($signoffEmail == null) {
            $message = "I'm sorry, we could not find a matching shift for the provided code.  Please contact customer support.";
        // State 2 - Email has expired
        } elseif ($signoffEmail->expire_time != null && $signoffEmail->expire_time->format('U') <= time()) {
            $message = "I'm sorry, the deadline to fill out the form for this shift has passed.";
        // State 3 - Shift has already been signed off on
        } elseif ($signoffEmail->has_signed_off) {
            $message = "We're sorry.  It looks like someone already signed off on this shift on " . $signoffEmail->signoff_time->format('F jS, Y \a\t Hi') . ".";
        } else {
            $signoffEmail->has_signed_off = true;
            $signoffEmail->signoff_time = new \DateTime();
            $signoffEmail->save();
            
            $message = "Thank you, you have successfully signed off on this shift.";
        }
        
        $this->view->message = $message;
    }
    
    public function viewSignatureAction()
    {
        $user = $ur = \Fisdap\Entity\User::getLoggedInUser();
        $sigs = \Fisdap\EntityUtils::getRepository('Signature')->findByUser($user->id);

        $this->view->signatures = $sigs;
    }
    
    public function signOffTestAction()
    {
    }
    
    public function shiftSignoffAction()
    {
        $program_settings = \Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData()->program->program_settings;
        if (!$program_settings->allow_signoff_on_shift) {
            $this->displayError("Your program doesn't allow shift sign off.");
            return;
        }

        $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $this->_getParam('shiftId', null));

        //Set the page title and sub links
        $titleText = "My Shift";
        if ($this->user->getCurrentRoleName() == 'instructor') {
            $titleText = $shift->student->user->first_name . " " . $shift->student->user->last_name . "'s shift";
        }
        $this->view->pageTitle = $titleText;
        //Set link text for the shift list bread crumb
        $this->view->shiftListLinkText = "&lt;&lt; Back to " . (($this->user->getCurrentRoleName() == 'instructor') ? $shift->student->user->first_name . "'s" : 'your') .  " shift list";


        $shift_summary_display_helper = new Fisdap_View_Helper_ShiftSummaryDisplayHelper();
        $summary_options = array("show_icon" => true);
        $this->view->shiftInfo = $shift_summary_display_helper->shiftSummaryDisplayHelper(null, null, $shift, $summary_options);

        $shiftAttachmentsGateway = $this->container->make('Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway');
        $this->view->signoffForm = new SkillsTracker_Form_Signoff($shift->signoff->id, null, $shift->id, $shiftAttachmentsGateway);
    }
    
    public function validateShiftSignoffAction()
    {
        $params = $this->getAllParams();
        $form = new SkillsTracker_Form_Signoff($params['signoffId'], null, $params['shiftId']);
        $this->_helper->json($form->process($params));
    }
    
    public function unverifyAction()
    {
        $shiftId = $this->_getParam('shiftId');
        
        if ($shiftId) {
            $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $shiftId);

            $shiftAttachment = $shift->verification->getShiftAttachment();

            if ($shiftAttachment) {
                $shiftAttachment->removeVerification($shift->verification);
                $shift->verification->setShiftAttachment(null);
            }

            $shift->verification->delete();
            $shift->verification = null;
            
            // We're not going to delete the $shift->signoff, because we need the signoff to retain summary/plan data
            // The signoff entity contains all info about the signoff (

            $shift->save();
        }
        
        $this->_helper->json(true);
    }
}
