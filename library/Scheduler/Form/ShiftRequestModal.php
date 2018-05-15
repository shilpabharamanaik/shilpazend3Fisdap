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
 * This produces a modal form for requesting a trade, drop or cover
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_ShiftRequestModal extends Fisdap_Form_BaseJQuery
{
    /**
     * @var \Fisdap\Entity\SlotAssignment
     */
    public $assignment;
    
    /**
     * @var \Fisdap\Entity\EventLegacy
     */
    public $event;
    
    /**
     * @var array
     */
    public $request_options;
    
    /**
     * @var string
     */
    public $location;
    
    /**
     * @var string
     */
    public $date;
    
    /**
     * @var boolean
     */
    public $pending;
    
    /**
     * @var boolean
     */
    public $past;
    
    /**
         * @var array decorators for individual elements
         */
    public static $termDecorators = array(
        'ViewHelper',
        'Errors',
        array('Label', array('tag' => 'div', 'openOnly' => true, 'placement' => 'prepend', 'class' => 'section-header no-border')),
        array('HtmlTag', array('tag' => 'div', 'class'=>'term-prompts')),
    );

    public static $checkboxDecorators = array(
        'ViewHelper',
        'Errors',
        array('HtmlTag', array('tag' => 'div', 'class'=>'check-terms')),
    );

    /**
     *
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($assignment_id = null, $options = null)
    {
        $assignment = \Fisdap\EntityUtils::getEntity("SlotAssignment", $assignment_id);
        $this->assignment = $assignment;
        if ($assignment_id) {
            $this->event = $assignment->slot->event;
            $this->location = $this->event->getLocation();
            $this->date = $this->event->getDetailViewDate();
            $this->request_options = $assignment->getRequestOptions();
            $this->pending = $assignment->hasPendingRequest();
            $this->past = $this->event->start_datetime->format('U') < date('U') ? true : false;
        } else {
            $this->request_options = array();
        }
        
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        $programId = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
        
        $this->addJsFile("/js/library/Scheduler/Form/shift-request-modal.js");
        //$this->addCssFile("/css/library/Scheduler/Form/shift-request-modal.css");
        $this->addJsFile("/js/jquery.chosen.relative.js");
        $this->addCssFile("/css/jquery.chosen.css");
        
        // create form elements
        $assignment_id = new Zend_Form_Element_Hidden('assignment_id');
        
        $type = new Zend_Form_Element_Radio('type');
        $type->setRegisterInArrayValidator(false);
        
        $recipients = new Zend_Form_Element_Select('recipient');
        $placeholder = "Select a student...";
        if ($this->assignment) {
            $recipient_list = $this->assignment->getRequestRecipients();
        } else {
            $recipient_list = array();
        }
        if (count($recipient_list) < 1) {
            $placeholder = "No students available.";
        }
        
        
        $recipients->setMultiOptions($recipient_list)
            ->setRegisterInArrayValidator(false)
            ->setLabel("Who will be covering this shift?")
            ->clearDecorators()
            ->addDecorator('ViewHelper')
            ->addDecorator('Errors')
            ->addDecorator('Label', array('tag'=>'h3', 'class'=>'section-header no-border'))
            ->addDecorator('HtmlTag', array('tag'=>'div', 'class'=>'recipient', 'id'=>'recipientDiv'))
                        ->setAttribs(array("class" => "chzn-select",
                                                "data-placeholder" => $placeholder,
                                                "style" => "width:300px"));
                        
                        
            
        $siteTypes = \Fisdap\Entity\SiteType::getCapitalizedFormOptions();
        $siteTypeSelect = new Zend_Form_Element_Select('site_type');
        $siteTypeSelect->setMultiOptions($siteTypes)
            ->setRegisterInArrayValidator(false)
            ->setLabel("Any of these types of shifts: ")
                        ->setAttribs(array("class" => "chzn-select",
                                                "data-placeholder" => "Add a shift type...",
                                                "style" => "width:300px",
                                                "multiple" => "multiple",
                                                "tabindex" => count($siteTypes)));
            
        $sites = \Fisdap\EntityUtils::getRepository('SiteLegacy')->getFormOptionsByProgram($programId);
        $siteSelect = new Zend_Form_Element_Select('site');
        $siteSelect->setMultiOptions($sites)
            ->setRegisterInArrayValidator(false)
            ->setLabel("At any of these sites: ")
            ->setAttribs(array("class" => "chzn-select",
                                                "data-placeholder" => "Add a site...",
                                                "style" => "width:475px",
                                                "multiple" => "multiple",
                                                "tabindex" => count($sites)));
        
        $duration = new Zend_Form_Element_Text('duration');
        $duration->setLabel("For this duration")
            ->setAttribs(array("style" => "width:3em"))
            ->addValidator("Float")
            ->addErrorMessage("Please enter the desired shift duration in hours (using only numbers)");
        
        // Add elements
        $this->addElements(array($assignment_id, $type, $recipients, $siteTypeSelect, $siteSelect, $duration));
        $this->setElementDecorators(self::$termDecorators, array('site_type', 'site', 'duration'));
    
        // set defaults
        $this->setDefaults(array(
            'assignment_id' => $this->assignment->id,
                ));
        
        $this->setDecorators(array(
                        'PrepareElements',
                        array('ViewScript', array('viewScript' => "shiftRequestModal.phtml")),
                        'Form',
                        array('DialogContainer', array(
                                'id'          => 'shiftRequestDialog',
                                'class'          => 'shiftRequestDialog',
                                'jQueryParams' => array(
                                        'tabPosition' => 'top',
                                        'modal' => true,
                                        'autoOpen' => false,
                                        'resizable' => false,
                                        'width' => 800,
                                        'title' => 'Request shift change',
                ),
            )),
        ));
    }
    
    /**
     * Validate the form, if valid, send or perform the request, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($form_data)
    {
        if ($this->isValid($form_data)) {
            \Fisdap\EntityUtils::getEntityManager()->getConnection()->exec("SET SESSION wait_timeout = 28800");
            \Zend_Registry::get('db')->query("SET SESSION wait_timeout = 28800");

            $user = \Fisdap\Entity\User::getLoggedInUser();
            $program_id = $user->getProgramId();
            $values = $this->getValues($form_data);
            $request_type = $form_data['type'];
            $email_list = array();
            
            // further validation
            if (!$request_type) {
                $this->getElement('type')->addError("Please choose an action.");
                $this->markAsError();
                return $this->getMessages();
            }
            
            // OK, let's create a request!
            $request = \Fisdap\EntityUtils::getEntity("ShiftRequest");
            $request->assignment = $this->assignment;
            $request->event = $this->event;
            $request->set_request_type($request_type);
            $request->set_owner($this->assignment->user_context);
            $request->set_accepted(1);
            if ($this->request_options[$request_type]['needs_permission'] === 0) {
                $request->set_approved(2);
            } else {
                $request->set_approved(1);
            }
            $request->sent = new DateTime();
            
            // extra stuff for drops
            if ($request_type == 1) {
                // set accepted to 'accepted', since there's no one to accept :)
                $request->set_accepted(4);
                // the request email will go to relevant instructors
                $email_list = $request->getInstructorEmails();
                $mail_template = 'shift-request-instructor.phtml';
            }
            
            // extra stuff for covers and swaps
            if ($request_type == 2 || $request_type == 3) {
                // set recipient, leave NULL if it's going to the calendar
                if (is_numeric($form_data['recipient'])) {
                    $recipient = \Fisdap\EntityUtils::getEntity("UserContext", $form_data['recipient']);
                    $request->set_recipient($recipient);
                    $email_list = array($recipient->user->email);
                    $mail_template = 'shift-request-recipient.phtml';
                    $recipient_name = $recipient->user->getName();
                } else {
                    $recipient_name = "the scheduler";
                }
            }
            
            // extra stuff for swaps
            if ($request_type == 3) {
                $term_types = \Fisdap\Entity\TermType::getFormOptions();
                foreach ($term_types as $type_value => $term_type) {
                    if ($form_data[$term_type]) {
                        $swap_term = \Fisdap\EntityUtils::getEntity("SwapTerm");
                        $swap_term->set_term_type($type_value);
                        $swap_term->set_value($form_data[$term_type]);
                        $request->addSwapTerm($swap_term);
                    }
                }
            }
            $request->save();
            
            // process any requests that are ready for processing
            if ($request->accepted->name == 'accepted' && $request->approved->name == 'approved') {
                if (!$request->processRequest()) {
                    $this->getElement('type')->addError("Sorry, we could not process your request.");
                    $this->markAsError();
                    return $this->getMessages();
                } else {
                    return true;
                }
            } else {
                // send the request emails
                $mail = new \Fisdap_TemplateMailer();
                $mail->addTo($request->owner->user->email)
                     ->setSubject(ucfirst($request->request_type->name)." request sent")
                     ->setViewParam("request", $request)
                     ->setViewParam("recipient_name", $recipient_name)
                     ->setViewParam("url", \Util_HandyServerUtils::getCurrentServerRoot() . "scheduler/requests")
                     ->sendHtmlTemplate("shift-request-owner.phtml");
                     
                $mail->clearRecipients();
    
                $mail->clearSubject()
                     ->setSubject(ucfirst($request->request_type->name)." request");
                foreach ($email_list as $email) {
                    $mail->addTo($email);
                }
                if (count($email_list) > 0) {
                    $mail->sendHtmlTemplate($mail_template);
                }
                
                return true;
            }
        }

        return $this->getMessages();
    }
}
