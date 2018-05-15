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
class Account_Form_Preceptors extends Fisdap_Form_Base
{
    public $site;
    public $preceptors;
    public $user;
    public $program;
    public $site_type;
    public $preceptor_modal;
    public $merge_preceptors_modal;
    
    /**
     * @param SiteLegacy $site the current site
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($site, $options = null)
    {
        $this->site = $site;
        $this->site_type = $this->site->type;
        $this->user = \Fisdap\Entity\User::getLoggedInUser();
        $this->program = $this->user->getProgram();
        
        $this->preceptor_modal = new Account_Form_Modal_PreceptorModal($site);
        $this->merge_preceptors_modal = new Account_Form_Modal_MergePreceptorsModal(null, $site->id);
        
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        $this->addJsFile("/js/library/Account/Form/site-sub-forms/preceptors.js");
        $this->addCssFile("/css/library/Account/Form/site-sub-forms/preceptors.css");
        
        $search = new Zend_Form_Element_Text("search_preceptors");
        $search->setAttribs(array("class" => "fancy-input search-accordion hide-when-no-accordion", "title" => "Type a preceptor name to search..."));
        $this->addElement($search);
        
        $repo = \Fisdap\EntityUtils::getRepository("PreceptorLegacy");
        $this->preceptors = $repo->getAccordionData($this->user->getProgramId(), $this->site->id);
        ksort($this->preceptors);
        
        if ($this->preceptors) {
            foreach ($this->preceptors as $preceptor) {
                $id = $preceptor['id'];
                
                $select_checkbox = new Zend_Form_Element_Checkbox('preceptor_select_checkbox_' . $id);
                $select_checkbox->setAttribs(array("class" => "merge-preceptor-checkbox", "data-preceptorid" => $id, "data-preceptorname" => $preceptor['name']));
                $this->addElement($select_checkbox);
                    
                // create a checkbox for the active/inactive slider
                $active_checkbox = new Zend_Form_Element_Checkbox('preceptor_active_checkbox_' . $id);
                $active_checkbox->setAttribs(array("class" => "slider-checkbox", "data-preceptorid" => $id));
                $this->addElement($active_checkbox);
                
                $this->setDefaults(array('preceptor_active_checkbox_' . $id => $preceptor['active']));
            }
        }
        
        // Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'forms/site-sub-forms/preceptors.phtml')),
            'Form'
        ));
    }
    
    public function process($data)
    {
        return true;
    } // end process()
}
