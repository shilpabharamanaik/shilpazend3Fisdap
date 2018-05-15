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
 * This helper will display a modal to set the notifications settings for a
 * requirement or group of requirements
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_NotificationsModal extends Fisdap_Form_BaseJQuery
{
    
    /**
     * @var array
     */
    public $requirements;
    
    /**
     * @var integer
     */
    public $program_id;
    
    /**
     * @var string
     */
    public $title;
    
    /**
     * @var string
     */
    public $reqIdString;
    
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
    public function __construct($requirement_ids = array())
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $this->program_id = $user->getProgramId();
        foreach ($requirement_ids as $requirement_id) {
            $requirements[] = \Fisdap\EntityUtils::getEntity("Requirement", $requirement_id);
        }
        $this->requirements = $requirements;
        $this->reqIdString = implode(',', $requirement_ids);

        parent::__construct();
    }
    
    public function init()
    {
        parent::init();
    
        $user = \Fisdap\Entity\User::getLoggedInUser();
        if (count($this->requirements) == 1) {
            $requirement = $this->requirements[0];
            $requirement_id = $requirement->id;
            $this->title = $requirement->name;
        } else {
            $requirement_id = null;
            $this->title = count($this->requirements) . " Requirements Selected";
        }
        
        $this->addJsFile("/js/library/Scheduler/Form/notifications-modal.js");
        $this->addJsFile("/js/library/Scheduler/Form/bulk-req-edit-modal.js");
        $this->addCssFile("/css/library/Scheduler/Form/bulk-req-edit-modal.css");
        
        // notifications
        $notificationsForm = new Scheduler_Form_NotificationSubForm($this->program->id, $requirement_id);
        $this->addSubForms(array('notificationsForm' => $notificationsForm));
        
        // create form elements
        $req_ids = new Zend_Form_Element_Hidden('req_ids');
        
        // Add elements
        $this->addElements(array($req_ids));
        $this->setElementDecorators(self::$hiddenDecorators, array('req_ids'));
        
        // set defaults
        $this->setDefaults(array('req_ids' => $this->reqIdString));
    
        $this->setDecorators(array(
                        'PrepareElements',
                        array('ViewScript', array('viewScript' => "notificationsRequirementModal.phtml")),
                        'Form',
                        array('DialogContainer', array(
                                'id'          => 'notificationsRequirementDialog',
                                'jQueryParams' => array(
                                        'tabPosition' => 'top',
                                        'modal' => true,
                                        'autoOpen' => false,
                                        'resizable' => false,
                                        'width' => 815,
                                        'title' => 'Notifications Settings',
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
        // see if this is for one or many requirements
        if (count($this->requirements) == 1) {
            $base_requirement = $this->requirements[0];
            $base_requirement_id = $base_requirement->id;
            $bulk_edit = false;
        } else {
            $base_requirement_id = null;
            $bulk_edit = true;
        }
        
        // check validation and process if valid
        $notificationsForm = new Scheduler_Form_NotificationSubForm($this->program->id, $base_requirement_id);
        if ($notificationsForm->isValid($form_data)) {
            // save notifications settings for each requirement
            $requirements = $this->requirements;
            foreach ($requirements as $requirement) {
                $notificationsForm = new Scheduler_Form_NotificationSubForm($this->program->id, $requirement->id);
                $notificationsForm->process($form_data, $bulk_edit);
            }
            return true;
        } else {
            return $notificationsForm->getMessages();
        }
    }
}
