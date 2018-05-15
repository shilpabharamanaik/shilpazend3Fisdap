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
 * Auto-Assign Notification Form
 */

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_AutoAssignSubForm extends Fisdap_Form_Base
{
    /**
     * @var \Fisdap\Entity\ProgramLegacy
     */
    private $program;
    
    /**
     * @var integer
     */
    public $requirement_id;
    
    /**
     * @var array
     */
    private $default_settings;
    
    /**
     * @var array
     */
    public $account_types;
    
    /**
     * @var array decorators for slider checkboxes
     */
    public static $checkboxDecorators = array(
        'ViewHelper',
        'Errors',
        array('HtmlTag', array('tag' => 'div', 'class' => 'slider')),
        array('Label', array('tag' => 'div', 'openOnly' => true, 'placement' => 'append', 'class' => 'slider-label', 'escape'=>false)),
    );
    
    /**
     * @var array decorators for chosen selects
     */
    public static $chosenDecorators = array(
        'ViewHelper',
        'Errors',
        array('HtmlTag', array('tag' => 'div', 'class' => 'hide-on-auto-assign-off')),
    );
    
    public function __construct($programId = null, $requirement_id = null)
    {
        if ($programId) {
            $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $programId);
        } else {
            $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', \Fisdap\Entity\User::getLoggedInUser()->getProgramId());
        }
        $this->program = $program;
        
        // figure out if this is requirement-specific form, or to use the program defaults
        $this->requirement_id = $requirement_id;
        if ($requirement_id) {
            $this->default_settings = \Fisdap\EntityUtils::getRepository("Requirement")->getAutoAttachmentSettings($this->program->id, $requirement_id);
        } else {
            $this->default_settings = \Fisdap\EntityUtils::getRepository("Requirement")->getAutoAttachmentDefaultsByProgram($this->program->id);
        }
        
        $account_types = \Fisdap\EntityUtils::getRepository('CertificationLevel')->getFormOptions($program->profession->id);
        foreach ($account_types as $id => $opt) {
            $account_types[$id] = $opt . "s";
        }
        $account_types["instructor"] = "Instructors";
        $this->account_types = $account_types;
        
        parent::__construct();
    }


    public function init()
    {
        $this->addJsFile("/js/library/Scheduler/Form/auto-assign-sub-form.js");
        $this->addCssFile("/css/library/Scheduler/Form/auto-assign-sub-form.css");
        
        // auto-assign checkbox
        $autoAssign = new Zend_Form_Element_Checkbox('auto_assign');
        $autoAssign->setAttribs(array("class" => "slider-checkbox"))
                   ->setLabel("Auto-assign requirements to new accounts");
        //$autoAssign->setRegisterInArrayValidator(false);
                   
        // auto-assign checkbox
        $autoAssignAccounts = new Zend_Form_Element_Select('auto_assign_accounts');
        $autoAssignAccounts->setMultiOptions($this->account_types)
                           ->setAttribs(array("class" => "aa-chzn-select",
                                                "data-placeholder" => "Add an account type...",
                                                "style" => "width:375px",
                                                "multiple" => "multiple"));
        $autoAssignAccounts->setRegisterInArrayValidator(false);
        
        $this->addElements(array($autoAssign, $autoAssignAccounts));
        
        // set element decorators
        $this->setElementDecorators(self::$checkboxDecorators, array('auto_assign'));
        $this->setElementDecorators(self::$chosenDecorators, array('auto_assign_accounts'));
        
        //Set the decorators for this form
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "compliance/auto-assign-sub-form.phtml")),
            'Form',
        ));

        // Set up the defaults for the form...
        $autoAssignments = $this->default_settings;
        $auto_assign = false;
        $aa_account_defaults = array();
        if (count($autoAssignments) > 0) {
            foreach ($autoAssignments as $autoAssignment) {
                $auto_assign = true;
                if ($autoAssignment->role->id == 2) {
                    $aa_account_defaults[] = "instructor";
                } else {
                    $aa_account_defaults[] = $autoAssignment->certification_level->id;
                }
            }
        } else {
            $aa_account_defaults = array_keys($this->account_types);
        }
        $this->setDefaults(array(
            'auto_assign' => $auto_assign,
            'auto_assign_accounts' => $aa_account_defaults
        ));
    }
    
    public function process($data)
    {
        $oldAutoAssignments = $this->default_settings;
        // save the new settings if auto-assign is turned on
        if ($data['auto_assign']) {
            $aa_new = ($data['auto_assign_accounts']) ? $data['auto_assign_accounts'] : array();
            $aa_old = array();
            foreach ($oldAutoAssignments as $autoAssignment) {
                if ($autoAssignment->role->id == 2) {
                    $aa_old[] = "instructor";
                } else {
                    $aa_old[] = $autoAssignment->certification_level->id;
                }
            }
            
            // add the new ones
            $aa_add = array_diff($aa_new, $aa_old);
            foreach ($aa_add as $add) {
                $aa = \Fisdap\EntityUtils::getEntity('RequirementAutoAttachment');
                $aa->set_program($this->program->id);
                $aa->set_requirement($this->requirement_id);
                if ($add == "instructor") {
                    $role = 2;
                } else {
                    $role = 1;
                    $aa->set_certification_level($add);
                }
                $aa->set_role($role);
                $aa->save();
            }
            
            // delete the old ones
            $aa_delete = array_diff($aa_old, $aa_new);
            foreach ($aa_delete as $delete) {
                foreach ($oldAutoAssignments as $autoAssignment) {
                    if ($autoAssignment->role->id == 2 && $delete == "instructor") {
                        $autoAssignment->delete();
                    }
                    
                    if ($autoAssignment->certification_level->id == $delete) {
                        $autoAssignment->delete();
                    }
                }
            }
        } else {
            // otherwise, delete ALL the aa attachments
            foreach ($oldAutoAssignments as $autoAssignment) {
                $autoAssignment->delete();
            }
        }
    }
}
