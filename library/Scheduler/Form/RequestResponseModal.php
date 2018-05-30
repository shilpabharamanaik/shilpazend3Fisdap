<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2013.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

/**
 * This produces a modal form for responding to a trade, drop or cover request
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_RequestResponseModal extends Fisdap_Form_BaseJQuery
{
    /**
     * @var \Fisdap\Entity\ShiftRequest
     */
    public $request;
    
    /**
     * @var \Fisdap\Entity\RequestState
     */
    public $state;
    
    /**
     * @var \Fisdap\Entity\EventLegacy
     */
    public $event;

    /**
     * @var array
     * All the stuff we need to know to spit out the modal
     */
    public $data;
    
    /**
     * @var \Fisdap\Entity\EventLegacy
     */
    public $swap_event;
    
    /**
         * @var array decorators for hidden elements
         */
    public static $hiddenDecorators = array(
                'ViewHelper',
                array('HtmlTag', array('tag' => 'div', 'class' => 'hidden')),
        );

    /**
     *
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($request_id = null, $state_id= null)
    {
        $request = \Fisdap\EntityUtils::getEntity("ShiftRequest", $request_id);
        $this->request = $request;
        $state = \Fisdap\EntityUtils::getEntity("RequestState", $state_id);
        $this->state = $state;
        
        if ($request_id) {
            $this->event = $request->event;
            $this->data['location'] = $this->event->getLocation();
            $this->data['date'] = $this->event->getDetailViewDate();
        }
        
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        $this->addJsFile("/js/library/Scheduler/Form/request-response-modal.js");
        $this->addCssFile("/css/library/Scheduler/Form/request-response-modal.css");
        $this->addJsFile("/js/jquery.chosen.relative.js");
        $this->addCssFile("/css/jquery.chosen.css");
        
        $user_context = \Fisdap\Entity\User::getLoggedInUser()->getCurrentUserContext();
        $titleText = ucfirst($this->state->action)." ".$this->request->request_type->name;
        $offer_needs_permission = false;
        
        if ($this->request) {
            $this->data['description'] = "You have chosen to ".$this->state->action." ".
                        $this->request->owner->user->getName()."'s ".$this->request->request_type->name ." request:";
        }
        
        // if this is a cover
        if ($this->request->request_type->name == 'cover') {
            $titleText .= " request";
        }
        
        // if this is a swap
        if ($this->request->request_type->name == 'swap') {
            $swap = $this->request->getCurrentSwap();
            
            // coming in to the owner of a swap request
            if ($user_context == $this->request->owner) {
                $this->swap_event = $this->event;
                $this->data['swap_location'] = $this->data['location'];
                $this->data['swap_date'] = $this->data['date'];
                $this->event = $swap->offer->slot->event;
                $this->data['location'] = $this->event->getLocation();
                $this->data['date'] = $this->event->getDetailViewDate();
                $this->data['has_swap'] = true;
                $this->data['description'] = "You have chosen to ".$this->state->action." ".$this->request->recipient->user->getName()."'s swap offer:";
                $titleText .= " offer";
                $offer_needs_permission = ($swap->offer->getRequestCode('switch_needs_permission') & 4) ? true : false;
            } else {
                // Here's where we make an offer!
                if ($this->state->name == 'unset') {
                    $this->data['description'] = "You have chosen to make an offer on ".$this->request->owner->user->getName()."'s swap request:";
                    $titleText = "Make a swap offer";
                    $this->data['make_offer'] = true;
                    $terms = $this->request->getTermsDescription();
                    if ($terms) {
                        $this->data['has_terms'] = true;
                        $this->data['terms'] = $terms;
                        $this->data['owner'] = $this->request->owner->user->getName();
                    }
                    
                    $assignments = new Zend_Form_Element_Select('assignment');
                    $assignment_list = $this->request->getAssignmentOptions();
                    if (count($assignment_list) > 0) {
                        $assignments->setMultiOptions($assignment_list);
                    } else {
                        $assignments->setMultiOptions(array('No shifts available'))
                            ->setAttrib('disabled', array(0));
                    }
                    $assignments->setRegisterInArrayValidator(false)
                        ->setLabel("Which shift would you like to offer?")
                        ->clearDecorators()
                        ->addDecorator('ViewHelper')
                        ->addDecorator('Errors')
                        ->addDecorator('Label', array('tag'=>'h3', 'class'=>'section-header no-border'))
                        ->addDecorator('HtmlTag', array('tag'=>'div', 'class'=>'assignment', 'id'=>'assignmentDiv'))
                                    ->setAttribs(array("class" => "chzn-select",
                                                        "data-placeholder" => "Select a shift...",
                                                        "style" => "width:80%"));
                    $this->addElements(array($assignments));
                } else {
                    $titleText .= " request";
                }
            }
        }
        
        // if the user is accepting a shift or if the user is offering to swap
        if ($this->state->name == 'accepted' || $this->state->name == 'unset') {
            $startdate = $this->event->start_datetime;
            $enddate = $this->event->end_datetime;
            $this->data['has_conflict'] = $user_context->hasConflict($startdate, $enddate);
        }
        
        // does this request require instructor permission?
        if ($this->state->name == 'accepted' &&
            ($this->request->approved->name != 'approved' || $offer_needs_permission)) {
            $this->data['needs_permission'] = true;
        }
        
        // create form elements
        $request_id = new Zend_Form_Element_Hidden('request_id');
        $state_id = new Zend_Form_Element_Hidden('state_id');
        $title = new Zend_Form_Element_Hidden('title');
        
        // Add elements
        $this->addElements(array($request_id, $state_id, $title));
    
        // set defaults
        $this->setDefaults(array(
            'request_id' => $this->request->id,
            'state_id' => $this->state->id,
            'title' => $titleText,
                ));
        $this->setElementDecorators(self::$hiddenDecorators, array('request_id', 'state_id', 'title'));
        
        $this->setDecorators(array(
                        'PrepareElements',
                        array('ViewScript', array('viewScript' => "requestResponseModal.phtml")),
                        'Form',
                        array('DialogContainer', array(
                                'id'          => 'requestResponseDialog',
                                'class'          => 'requestResponseDialog',
                                'jQueryParams' => array(
                                        'tabPosition' => 'top',
                                        'modal' => true,
                                        'autoOpen' => false,
                                        'resizable' => false,
                                        'width' => 800,
                                        'title' => $title,
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
            $user_context = \Fisdap\Entity\User::getLoggedInUser()->getCurrentUserContext();
            $mail = new \Fisdap_TemplateMailer();
            $mail->setViewParam("request", $this->request)
                 ->setViewParam("url", \Util_HandyServerUtils::getCurrentServerRoot() . "scheduler/requests");
            
            // if this is a swap offer, we may need to set the swap state
            if ($this->request->request_type->name == 'swap') {
                $swap = $this->request->getCurrentSwap();
                
                // responding to an offer
                if ($user_context == $this->request->owner) {
                    $swap->set_accepted($this->state);
                    
                    $mail->setSubject("Swap offer ".$this->state->name)
                         ->addTo($this->request->recipient->user->email)
                         ->setViewParam("swap", $swap);
                    $mail_template = 'shift-offer-'.$this->state->name.'.phtml';
                    
                    // if the offer has been accepted, the request itself has been accepted
                    if ($this->state->name == 'accepted') {
                        $this->request->set_accepted($this->state);
                        
                        // if the original shift DOESN'T need permission, but the offer DOES
                        // set the request to need permission
                        if ($this->request->approved->name == 'approved' && ($swap->offer->getRequestCode('switch_needs_permission') & 4)) {
                            // unset the approval
                            $this->request->set_approved(1);
                        }
                    }
                } elseif ($user_context == $this->request->recipient) {
                    // if the recipient is declining the request, decline the request
                    if ($this->state->name == 'declined') {
                        $this->request->set_accepted($this->state);
                        $mail->setSubject("Swap request declined")
                             ->addTo($this->request->owner->user->email);
                        $mail_template = 'shift-request-'.$this->state->name.'.phtml';
                    } else {
                        // make an offer!
                        $swap = \Fisdap\EntityUtils::getEntity("Swap");
                        $swap->set_offer($form_data['assignment']);
                        $swap->set_accepted(1);
                        $swap->sent = new DateTime();
                        $this->request->addSwap($swap);
                        
                        $mail->setSubject("Shift swap offer")
                             ->addTo($this->request->owner->user->email)
                             ->setViewParam("swap", $swap);
                        $mail_template = 'shift-offer.phtml';
                    }
                }
            } else {
                // otherwise, just set the accepted state on the request
                $this->request->set_accepted($this->state);
                
                $mail->setSubject(ucfirst($this->request->request_type->name)." request ".$this->state->name);
                $mail->addTo($this->request->owner->user->email);
                $mail_template = 'shift-request-'.$this->state->name.'.phtml';
            }
            
            if ($this->request->accepted->name == 'accepted' && $this->request->approved->name == 'approved') {
                if (!$this->request->processRequest()) {
                    $this->getElement('request_id')->addError("Sorry, we could not process your request.");
                    $this->markAsError();
                    return $this->getMessages();
                } else {
                    return true;
                }
            } else {
                $mail->sendHtmlTemplate($mail_template);
                
                // if this request is has been accepted but is NOT ready for processing, that means it needs to be sent to
                // the instructors
                if ($this->request->accepted->name == 'accepted') {
                    $inst_email_list = $this->request->getInstructorEmails();
                    $inst_mail = new \Fisdap_TemplateMailer();
                    $inst_mail->setSubject(ucfirst($this->request->request_type->name)." request")
                          ->setViewParam("request", $this->request)
                          ->setViewParam("url", \Util_HandyServerUtils::getCurrentServerRoot() . "scheduler/requests");
                    foreach ($inst_email_list as $email) {
                        $inst_mail->addTo($email);
                    }
                    $inst_mail->sendHtmlTemplate('shift-request-instructor.phtml');
                }
                $this->request->save();
                
                return true;
            }
        }

        return $this->getMessages();
    }
}
