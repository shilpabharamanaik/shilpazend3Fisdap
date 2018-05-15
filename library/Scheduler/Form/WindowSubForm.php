<?php
/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                           *
*        Copyright (C) 1996-2011.  This is an unpublished work of           *
*                         Headwaters Software, Inc.                         *
*                            ALL RIGHTS RESERVED                            *
*        This program is a trade secret of Headwaters Software, Inc. and    *
*        it is not to be copied, distributed, reproduced, published, or     *
*        adapted without prior authorization of Headwaters Software, Inc.   *
*                                                                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * /


/**
 * Form for creating/editing windows!
 * @package Scheduler
 * @author hammer:)
 */
class Scheduler_Form_WindowSubForm extends Fisdap_Form_Base
{
    
    /**
     * @var \Fisdap\Entity\User
     */
    public $user;
    
    /*
     * @var int
     */
    public $id;
    
    /*
     * @var array offests (start/end)
     */
    public $offsets;
    
    /*
     * @var \Fisdap\Entity\Window
     */
    public $window;
    
    /*
     * @var boolean for displaying the appropriate offset types
     */
    public $show_start_static;
    
    public $show_start_interval;
    public $show_start_prevMonth;
    public $date_of_shift_creation_start;
    public $show_end_static;
    public $show_end_interval;
    public $show_end_prevMonth;
    public $date_of_shift_creation_end;
    public $show_1_constraint;
    public $show_2_constraint;
    public $hide_add_category;
    public $new_class;
    public $default_window;
    public $shift_type;

    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($new_window = null, $window_id = null, $default_window = false, $shift_type = null, $options = null)
    {
        $this->offsets = array("start", "end");
        $this->user = \Fisdap\Entity\User::getLoggedInUser();
        $this->window = \Fisdap\EntityUtils::getEntity('Window', $window_id);
        $this->id = ($new_window) ?  "new_window_" . $new_window : $window_id;
        $this->default_window = $default_window;
        $this->shift_type = $shift_type;
        
        // we'll want to identify which windows are 'new' and which are 'exisiting', we'll use this class to help
        $this->new_class = ($new_window) ? "new_window" : "existing_window";
        
        parent::__construct($options);
    }
    
    /**
     * init method that adds all the elements to the form
     */
    public function init()
    {
        parent::init();
        
        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $this->user->getProgramId());

        if ($this->shift_type) {
            $saved_default_window = $program->program_settings->{'default_' . $this->shift_type . '_window'};
        }
        $logger = Zend_Registry::isRegistered('logger') ? Zend_Registry::get('logger') : null;
        $today = new DateTime();
        
        // this will be the ID used for form elements
        $id = $this->id;
        $logger->debug($this->window->offset_type_start->id);
        if ($this->window) {
            // collect our defaults from the given window
            $event = $this->window->slot->event;
            $defaults = array();
            $defaults['active'] = $this->window->active;

            if ($this->window->offset_type_start->id == 4) {
                $today = $today = new DateTime();
                $defaults['offset_type_start'] = 1;
                $defaults['offset_value_start'] = array($today->format("m/d/Y"));
            } else {
                $defaults['offset_type_start'] = $this->window->offset_type_start->id;
                $defaults['offset_value_start'] = $this->window->offset_value_start;
            }

            $defaults['offset_type_end'] = $this->window->offset_type_end->id;
            $defaults['offset_value_end'] = $this->window->offset_value_end;
            $defaults['cert_constraints'] = array();
            $defaults['group_constraints'] = array();
            
            foreach ($this->window->constraints as $constraint) {
                $constraint_type = ($constraint->constraint_type->id != 1) ? "cert_constraints" : "group_constraints";
                foreach ($constraint->values as $val) {
                    $defaults[$constraint_type][] = $val->value;
                }
            }
        } elseif ($this->default_window) {
            $defaults['offset_type_start'] = 4;
            $defaults['offset_type_end'] = 2;
            $defaults['offset_value_end'] = array(1, "week");
        } elseif ($saved_default_window) {
            // get our defaults from the program's default window for this type
            $defaults = array();
            $defaults['active'] = 1;
            $defaults['offset_type_start'] = $saved_default_window->offset_type_start->id;
            $defaults['offset_type_end'] = $saved_default_window->offset_type_end->id;
            $defaults['offset_value_start'] = $saved_default_window->offset_value_start;
            $defaults['offset_value_end'] = $saved_default_window->offset_value_end;
            $defaults['cert_constraints'] = array();
            $defaults['group_constraints'] = array();

            if ($saved_default_window->offset_type_start->id == 4) {
                // set it to today's date
                $today = new DateTime();
                $defaults['offset_type_start'] = 1;
                $defaults['offset_value_start'] = array($today->format("m/d/Y"));
            }
        }
        
        // get the constraint type options
        $constraint_type_options = $this->getConstraintTypeOptions();
        
        // give both certs and groups, we'll hide the right one based on some default parameters
        if ($event) {
            // figure out if the current user is an admin
            $is_admin = false;
            
            if ($event->program->id == $program->id) {
                $is_admin = true;
            } else {
                // check out the site and find out if this user is an admin
                if ($program->isAdmin($event->site->id)) {
                    $is_admin = true;
                }
            }
            
            $cert_constraint_value_options = array();
            $profession_certs = \Fisdap\Entity\CertificationLevel::getAllByProfession($program->profession->id);
            foreach ($profession_certs as $cert) {
                if ($is_admin) {
                    $add_cert = true;
                } else {
                    $add_cert = ($event->cert_levels & $cert->bit_value) ? true : false;
                }
                
                if ($add_cert) {
                    $cert_constraint_value_options[$cert->id] = $cert->description;
                }
            }
        } else {
            $cert_constraint_value_options = \Fisdap\Entity\CertificationLevel::getFormOptions(false, true, "description");
        }
        
        // default values for cert levels
        if ($defaults['cert_constraints']) {
            $default_cert_values = $defaults['cert_constraints'];
        } else {
            // new windows
            $default_cert_values = array();
            foreach ($cert_constraint_value_options as $val => $label) {
                $default_cert_values[] = $val;
            }
        }
        
        // get the groups
        $group_constraint_value_options = \Fisdap\EntityUtils::getRepository('ClassSectionLegacy')->getFormOptions($this->user->getProgramId(), true);
        
        // default values for groups
        if ($defaults['group_constraints']) {
            $default_group_values = $defaults['group_constraints'];
        }
        
        // create the constraint values multiselect
        // do this twice since each window could have up to two constraints
        for ($i = 1; $i < 3; $i++) {
            
            // create constraint type select box
            $constraint_type_select = $this->createChosen('constraint_type_' . $i . '_' . $id, "", "140px", " ", $constraint_type_options, false);
            $constraint_type_select->setAttribs(array("class" => "call-chosen remove-search constraint-type-options"));
            
            $constraint_type_select_default_val = ($i == 1) ? "2" : "1";
            $constraint_type_select->setValue($constraint_type_select_default_val);
            $constraint_type_select->setRegisterInArrayValidator(false);
            
            // provide both certs/groups to make saving easy later
            $cert_constraint_values_select = $this->createChosen('cert_constraint_values_' . $i . '_' . $id, "", "280px", "All certification levels...", $cert_constraint_value_options, true);
            $cert_constraint_values_select->setAttribs(array("class" => "call-chosen"));
            $cert_constraint_values_select->setRegisterInArrayValidator(false);
            
            if ($i == 1) {
                $cert_constraint_values_select->setValue($default_cert_values);
            }
            
            $group_constraint_values_select = $this->createChosen('group_constraint_values_' . $i . '_' . $id, "", "280px", "All student groups...", $group_constraint_value_options, true);
            $group_constraint_values_select->setAttribs(array("class" => "call-chosen"));
            $group_constraint_values_select->setRegisterInArrayValidator(false);
            if ($i != 1) {
                $group_constraint_values_select->setValue($default_group_values);
            }
            
            $this->{'show_' . $i . '_constraint'} = ($i == 1) ? "" : "style='display:none;'";
            if ($i == 2 && $defaults['group_constraints']) {
                $this->{'show_' . $i . '_constraint'} = "";
                $this->hide_add_category = true;
            }
            
            $this->addElements(array($constraint_type_select, $cert_constraint_values_select, $group_constraint_values_select));
        }
        
        // create the "active" checkbox
        $window_active = new Zend_Form_Element_Checkbox("window_active_" . $id);
        $window_active->setValue(1);
        
        if (isset($defaults['active'])) {
            $window_active->setValue($defaults['active']);
        }
        
        
        // now things will get a little more interesting...
        
        // deal will offset types, create hidden elements to keep track of these ids
        // 1 is static, 2 is interval, 3 is previous month
        
        foreach ($this->offsets as $time) {
            $offset_type = new Zend_Form_Element_Hidden('offset_type_' . $time . '_' . $id);
            $offset_type_val = ($time == "start") ? "1" : "2";

            if ($this->default_window) {
                $offset_type_val = ($time == "start") ? "4" : "2";
            }

            if (isset($defaults['offset_type_' . $time])) {
                $offset_type_val = $defaults['offset_type_' . $time];
            }

            
            $offset_type->setValue($offset_type_val);
            
            // set up each of the offset types
            $offset_value_static = new Zend_Form_Element_Text('offset_value_' . $time . '_static_' . $id);
            
            if ($defaults['offset_type_' . $time] == 1) {
                $val_date = new DateTime($defaults['offset_value_' . $time][0]);
                $offset_value_static_value = $val_date->format("m/d/Y");
            } else {
                $offset_value_static_value =  $today->format("m/d/Y");
            }
            
            $offset_value_static->setValue($offset_value_static_value);
            $offset_value_static->setAttribs(array("class" => "selectDate fancy-input"));
            
            $offset_value_interval = new Zend_Form_Element_Text('offset_value_' . $time . '_interval_' . $id);
            $offset_value_interval_value = ($defaults['offset_type_' . $time] == 2) ? $defaults['offset_value_' . $time][0] : 1;
            $offset_value_interval->setValue($offset_value_interval_value);
            $offset_value_interval->setAttribs(array("class" => "extra-small-input fancy-input interval_frequency"));
            
            $offset_value_interval_type = $this->createChosen('offset_value_' . $time . '_interval_type_' . $id, "", "85px", " ", array("day" => "day", "week" => "week", "month" => "month"), false);
            $offset_value_interval_type_value = ($defaults['offset_type_' . $time] == 2) ? $defaults['offset_value_' . $time][1] : "week";
            $offset_value_interval_type->setValue($offset_value_interval_type_value);
            $offset_value_interval_type->setRegisterInArrayValidator(false);
            $offset_value_interval_type->setAttribs(array("class" => "call-chosen remove-search"));

            $prev_month_options = $this->getPrevMonthOptions();
            $prev_month_chosen_width = ($this->default_window) ? "85px" : "85px";
            $offset_value_prevMonth = $this->createChosen('offset_value_' . $time . '_prevMonth_' . $id, "", $prev_month_chosen_width, " ", $prev_month_options, false);
            $offset_value_prevMonth->setRegisterInArrayValidator(false);
            $offset_value_prevMonth_value = ($defaults['offset_type_' . $time] == 3) ? $defaults['offset_value_' . $time][0] : "15";
            $offset_value_prevMonth->setValue($offset_value_prevMonth_value);
            $offset_value_prevMonth->setAttribs(array("class" => "call-chosen remove-search"));
            
            $this->{'show_' . $time . '_static'} = ($time == "start") ? "" : "style='display:none;'";
            $this->{'show_' . $time . '_interval'} = ($time == "start") ? "style='display:none;'" : true;
            $this->{'show_' . $time . '_prevMonth'} = "style='display:none;'";
            
            if ($defaults['offset_type_' . $time] == 1) {
                $this->{'show_' . $time . '_static'} = "";
                $this->{'show_' . $time . '_interval'} = "style='display:none;'";
                $this->{'show_' . $time . '_prevMonth'} = "style='display:none;'";
            } elseif ($defaults['offset_type_' . $time] == 2) {
                $this->{'show_' . $time . '_static'} = "style='display:none;'";
                $this->{'show_' . $time . '_interval'} = "";
                $this->{'show_' . $time . '_prevMonth'} = "style='display:none;'";
            } elseif ($defaults['offset_type_' . $time] == 3) {
                $this->{'show_' . $time . '_static'} = "style='display:none;'";
                $this->{'show_' . $time . '_interval'} = "style='display:none;'";
                $this->{'show_' . $time . '_prevMonth'} = "";
            } elseif ($defaults['offset_type_' . $time] == 4) {
                $this->{'show_' . $time . '_static'} = "style='display:none;'";
                $this->{'show_' . $time . '_interval'} = "style='display:none;'";
                $this->{'show_' . $time . '_prevMonth'} = "style='display:none;'";
                $this->{'date_of_shift_creation_' . $time} = true;
            }
            
            $this->addElements(array($offset_type, $offset_value_static, $offset_value_interval, $offset_value_interval_type, $offset_value_prevMonth));
        }
        
        // Add elements
        $this->addElements(array(
            $window_active
        ));
        
        //Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/windows-sub-form.phtml"))
        ));
    }
    
    private function getPrevMonthOptions()
    {
        $prev_month_options = array();
        for ($i = 1; $i < 32; $i++) {
            $prev_month_options[$i] = $i;
            if ($i == 1 || $i == 21 || $i == 31) {
                $prev_month_options[$i] .= "st";
            } elseif ($i == 2 || $i == 22) {
                $prev_month_options[$i] .= "nd";
            } elseif ($i == 3 || $i == 23) {
                $prev_month_options[$i] .= "rd";
            } else {
                $prev_month_options[$i] .= "th";
            }
        }
        return $prev_month_options;
    }
    
    private function getConstraintTypeOptions()
    {
        $constraint_type_entities = \Fisdap\EntityUtils::getRepository('ConstraintType')->getAll();
        $constraint_type_options = array();
        foreach ($constraint_type_entities as $ct) {
            $constraint_type_options[$ct->id] = $ct->description;
        }
        
        return $constraint_type_options;
    }

    private function createChosen($elementName, $label, $width, $placeholderText, $options, $multi = "multiple")
    {
        $chosen = new Zend_Form_Element_Select($elementName);
        $chosen->setMultiOptions($options)
             ->setLabel($label)
             ->setAttribs(array("class" => "chzn-select",
                                           "data-placeholder" => $placeholderText,
                                           "style" => "width:" . $width,
                                           "multiple" => $multi,
                                           "tabindex" => count($options)));
        return $chosen;
    }
    
    /**
     * Process the submitted POST values and do whatever you need to do
     *
     * @param array $post the POSTed values from the user
     * @return mixed either the values or the form w/errors
     */
    public function process($post)
    {
    }
}
