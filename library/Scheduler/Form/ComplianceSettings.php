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
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_ComplianceSettings extends Fisdap_Form_Base
{
    public $isStaff = false;
    
    public $permissions = array();
    
    /**
     * @var \Fisdap\Entity\ProgramLegacy
     */
    private $program;
    
    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($permissions = null, $options = null)
    {
        $this->isStaff = \Fisdap\Entity\User::getLoggedInUser()->isStaff();
        
        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
        $this->program = $program;
        
        $this->permissions = $permissions;
        
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();

        $this->addJsFile("/js/library/Scheduler/Form/compliance-settings.js");
        $this->addCssFile("/css/library/Scheduler/Form/compliance-settings.css");
        $this->addJsFile("/js/jquery.sliderCheckbox.js");
        $this->addCssFile("/css/jquery.sliderCheckbox.css");
        
        // notifications
        $notificationForm = new Scheduler_Form_NotificationSubForm($this->program->id);
        $autoAssignForm = new Scheduler_Form_AutoAssignSubForm($this->program->id);
        $this->addSubForms(array(
            'notificationForm' => $notificationForm,
            'autoAssignForm' => $autoAssignForm
        ));
        
        $save = new Zend_Form_Element_Submit('Save');
        $save->setOptions(array('id' => 'settings-save'));
        
        $this->addElements(array($save));
        
        //Set the decorators for this form
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "compliance/settingsForm.phtml")),
            'Form',
        ));
        $this->setOptions(array('id' => 'settingsForm'));
    }
    
    public function process($data)
    {
        // only process form if both subforms are valid
        $notificationsForm = new Scheduler_Form_NotificationSubForm($this->program->id);
        $autoAssignForm = new Scheduler_Form_AutoAssignSubForm($this->program->id);
        
        if ($notificationsForm->isValid($data) && $autoAssignForm->isValid($data)) {
            $notificationsForm->process($data);
            $autoAssignForm->process($data);
            return true;
        } else {
            $errorMessages['notifications'] = $notificationsForm->getMessages();
            $errorMessages['auto-assign'] = $autoAssignForm->getMessages();
            return $errorMessages;
        }
    }
}
