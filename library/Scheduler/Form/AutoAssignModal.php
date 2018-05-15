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
 * This helper will display a modal to set the autoassign settings for a
 * requirement or group of requirements
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_AutoAssignModal extends Fisdap_Form_BaseJQuery
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
        
        $this->addJsFile("/js/library/Scheduler/Form/auto-assign-modal.js");
        $this->addJsFile("/js/library/Scheduler/Form/bulk-req-edit-modal.js");
        $this->addCssFile("/css/library/Scheduler/Form/bulk-req-edit-modal.css");
        
        // auto assign
        $autoAssignForm = new Scheduler_Form_AutoAssignSubForm($this->program->id, $requirement_id);
        $this->addSubForms(array('autoAssignForm' => $autoAssignForm));
        
        // create form elements
        $req_ids = new Zend_Form_Element_Hidden('req_ids');
        
        // Add elements
        $this->addElements(array($req_ids));
        $this->setElementDecorators(self::$hiddenDecorators, array('req_ids'));
        
        // set defaults
        $this->setDefaults(array('req_ids' => $this->reqIdString));
    
        $this->setDecorators(array(
                        'PrepareElements',
                        array('ViewScript', array('viewScript' => "autoAssignRequirementModal.phtml")),
                        'Form',
                        array('DialogContainer', array(
                                'id'          => 'autoAssignRequirementDialog',
                                'jQueryParams' => array(
                                        'tabPosition' => 'top',
                                        'modal' => true,
                                        'autoOpen' => false,
                                        'resizable' => false,
                                        'width' => 815,
                                        'title' => 'Auto-Assign Settings',
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
        // save auto-assign settings for each requirement
        $requirements = $this->requirements;
        foreach ($requirements as $requirement) {
            $autoAssignForm = new Scheduler_Form_AutoAssignSubForm($this->program_id, $requirement->id);
            $autoAssignForm->process($form_data);
        }
        
        return true;
    }
}
