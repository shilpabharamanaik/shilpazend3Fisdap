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
 * This produces a modal form for approving a trade, drop or cover request
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_RequestApprovalModal extends Fisdap_Form_BaseJQuery
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
    public function __construct($request_id = null, $state_id= null, $options = null)
    {
        $request = \Fisdap\EntityUtils::getEntity("ShiftRequest", $request_id);
        $this->request = $request;
        $state = \Fisdap\EntityUtils::getEntity("RequestState", $state_id);
        $this->state = $state;
        
        if ($request_id) {
            $this->event = $request->event;
            $conflicts = array();
            $this->data['location'] = $this->event->getLocation();
            $this->data['date'] = $this->event->getDetailViewDate();
            $this->data['description'] = $this->request->owner->user->getName()." wants to ".
                             $this->request->request_type->name. " this shift";
                             
            if ($this->request->request_type->name == 'cover') {
                $this->data['description'] = $this->request->owner->user->getName()." wants ".
                                 $this->request->recipient->user->getName()." to ".
                                 $this->request->request_type->name. " this shift";
            }
            
            if ($this->request->request_type->name == 'cover' || $this->request->request_type->name == 'swap') {
                // add conflict for the recipient, if applicable
                $startdate = $this->event->start_datetime;
                $enddate = $this->event->end_datetime;
                if ($this->request->recipient->hasConflict($startdate, $enddate)) {
                    $conflicts[] = "Note: ".$this->request->recipient->user->getName()." already has a shift scheduled during this time.";
                }
            }

            

            $swap = $this->request->getCurrentSwap();
            if ($swap) {
                $this->data['has_swap'] = true;
                $this->swap_event = $swap->offer->slot->event;
                $this->data['swap_location'] = $this->swap_event->getLocation();
                $this->data['swap_date'] = $this->swap_event->getDetailViewDate();
                $this->data['swap_owner'] = $this->request->recipient->user->getName();
                
                // add conflict for the owner, if applicable
                $swap_startdate = $this->swap_event->start_datetime;
                $swap_enddate = $this->swap_event->end_datetime;
                if ($this->request->owner->hasConflict($swap_startdate, $swap_enddate)) {
                    $conflicts[] = $this->request->owner->user->getName()." already has a shift scheduled during this time.";
                }
            }
            $this->data['conflicts'] = $conflicts;
        }
        
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        $this->addJsFile("/js/library/Scheduler/Form/request-approval-modal.js");
        $this->addCssFile("/css/library/Scheduler/Form/request-response-modal.css");
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        $titleText = ucfirst($this->request->request_type->name) . " approval";
        
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
                        array('ViewScript', array('viewScript' => "requestApprovalModal.phtml")),
                        'Form',
                        array('DialogContainer', array(
                                'id'          => 'requestApprovalDialog',
                                'class'          => 'requestApprovalDialog',
                                'jQueryParams' => array(
                                        'tabPosition' => 'top',
                                        'modal' => true,
                                        'autoOpen' => false,
                                        'resizable' => false,
                                        'width' => 800,
                                        'title' => $titleText,
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
            $user = \Fisdap\Entity\User::getLoggedInUser();
            
            $this->request->set_approved($this->state);
            
            // log the action in event history
            $action_codes = $this->request->getActionCodes();
            $action = \Fisdap\EntityUtils::getEntity("EventAction");
            $action->set_type($action_codes[$this->request->request_type->name][$this->state->name]);
            $action->initiator = $user->getCurrentUserContext();
            $action->recipient = $this->request->owner;
            $this->request->event->addAction($action);
            $this->request->event->save();
            
            if ($this->request->accepted->name == 'accepted' && $this->request->approved->name == 'approved') {
                if (!$this->request->processRequest()) {
                    $this->getElement('request_id')->addError("Sorry. We could not process your request");
                    $this->markAsError();
                    return $this->getMessages();
                } else {
                    return true;
                }
            } else {
                $this->request->save();
                
                // send the mails
                $mail = new \Fisdap_TemplateMailer();
                $mail->addTo($this->request->owner->user->email)
                     ->setSubject(ucfirst($this->request->request_type->name)." request denied")
                     ->setViewParam("request", $this->request)
                     ->sendHtmlTemplate("shift-request-denied-owner.phtml");
                     
                $mail->clearRecipients();
                    
                if ($this->request->request_type->name == 'cover' || $this->request->request_type->name == 'swap') {
                    $mail->addTo($this->request->recipient->user->email)
                     ->sendHtmlTemplate("shift-request-denied-recipient.phtml");
                }
                
                return true;
            }
        }

        return $this->getMessages();
    }
}
