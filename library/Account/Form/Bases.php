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
 * @author     Hammer :)
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_Bases extends Fisdap_Form_Base
{
    public $site;
    public $bases;
    public $show_merge;
    public $user;
    public $new_button;
    public $base_department;
    public $site_type;
    public $base_modal;
    public $merge_bases_modal;
    public $program;
    
    /**
     * @param SiteLegacy $site the currrent site
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($site, $options = null)
    {
        $this->site = $site;
        $this->site_type = $this->site->type;
        $this->user = \Fisdap\Entity\User::getLoggedInUser();
        $this->program = $this->user->getProgram();
        $site_admin = $this->program->isAdmin($this->site->id);
        $shares_site = $this->program->sharesSite($this->site->id);
        $this->show_merge = ($shares_site && !$site_admin) ? false : true;
        
        $this->base_department = ($this->site_type == "clinical") ? "department" : "base";
        $this->new_button = array("class" => "add-base-" . $this->site->type,
                                  "modal_launch_id" => "new-" . $this->base_department . "-trigger");
        
        $this->base_modal = new Account_Form_Modal_BaseModal($site);
        $this->merge_bases_modal = new Account_Form_Modal_MergeBasesModal(null, $site->id);
        
        parent::__construct($options);
    }
    
    
    public function init()
    {
        parent::init();
        
        $this->addJsFile("/js/library/Account/Form/site-sub-forms/bases.js");
        $this->addCssFile("/css/library/Account/Form/site-sub-forms/bases.css");
        
        $search = new Zend_Form_Element_Text("search_bases");
        $search->setAttribs(array("class" => "fancy-input search-accordion hide-when-no-accordion", "data-basedept" => $this->base_department, "title" => "Type a " . $this->base_department . " name to search..."));
        $this->addElement($search);
        
        $base_repo = \Fisdap\EntityUtils::getRepository("BaseLegacy");
        $network_program_ids = $this->program->getNetworkPrograms($this->site);
        
        $bases = $base_repo->getAccordionData($this->site->id, $this->program->id, $network_program_ids);
        $this->bases = $bases['results']['current_program'];
        $this->includeClinicalDepartments($bases);
        $this->includeSharedBases($bases['results']);
        
        ksort($this->bases);
        
        if ($this->bases) {
            foreach ($this->bases as $base) {
                $id = $base['id'];
                $base_id_attribute = ($base["alternate_data_attribute"]) ? $base["alternate_data_attribute"] : $id;
                
                // create a checkbox for selecting for merging - only if this IS NOT a standard/default department
                if (!$base['is_default'] || !$this->show_merge) {
                    $select_checkbox = new Zend_Form_Element_Checkbox('select_checkbox_' . $id);
                    $select_checkbox->setAttribs(array("class" => "merge-base-checkbox", "data-baseid" => $base_id_attribute, "data-basename" => $base['name']));
                    $this->addElement($select_checkbox);
                }
                
                // create a checkbox for the active/inactive slider
                $active_checkbox = new Zend_Form_Element_Checkbox('active_checkbox_' . $id);
                $active_checkbox->setAttribs(array("class" => "slider-checkbox", "data-baseid" => $base_id_attribute, "new_association" => $base['from_other_program']));
                $this->addElement($active_checkbox);
                
                $this->setDefaults(array('active_checkbox_' . $id => $base['active']));
            }
        }
        
        // Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'forms/site-sub-forms/bases.phtml')),
            'Form'
        ));
    }
    
    public function includeClinicalDepartments($bases)
    {
        $base_repo = \Fisdap\EntityUtils::getRepository("BaseLegacy");
        
        if ($this->site_type == "clinical" && $bases['defaults_not_found']) {
            foreach ($bases['defaults_not_found'] as $abbrev => $default_depart) {
                $dom_id = $abbrev . "_new_default";
                $key = $base_repo->getBaseOrderKey(0, $default_depart, $dom_id);
                $this->bases[$key] = array("active" => false,"id" => $dom_id,"name" => $default_depart,"is_default" => true,"alternate_data_attribute" => $abbrev,
                                            "address" => null,"city" => null,"state" => null,"zip" => null);
            }
        }
    }
    
    
    public function includeSharedBases($bases)
    {
        $base_repo = \Fisdap\EntityUtils::getRepository("BaseLegacy");
        
        if ($bases['other_programs']) {
            foreach ($bases['other_programs'] as $key => $base_data) {
                $this->bases[$key] = $base_data;
                $this->bases[$key]['from_other_program'] = true;
            }
        }
    }
    
    public function process($data)
    {
        return true;
    } // end process()
}
