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
 * This produces a modal form for merging bases
 */

/**
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_Modal_MergeBasesModal extends Fisdap_Form_BaseJQuery
{
    /**
     * @var array the array of bases to be merged
     */
    public $options;
    
    /**
     * @var \Fisdap\Entity\SiteLegacy
     */
    public $site;
    
    /**
    * @var array decorators for the radio buttons
    */
    public static $radioButtonDecorators = array(
        'ViewHelper',
        'Errors',
        array('HtmlTag', array('tag' => 'div', 'class'=>'base-input')),
        array('Label', array('tag' => 'h3', 'openOnly' => true, 'placement' => 'prepend', 'class' => 'section-header no-border')),
    );

    /**
    * @var array decorators for hidden elements
    */
    public static $hiddenDecorators = array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div', 'class' => 'hidden')),
    );

    public function __construct($base_options = null, $site_id = null, $options = null)
    {
        $this->options = $base_options;
        $this->site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        $title = ($this->site->type != 'clinical') ? "Merge bases" : "Merge departments";
        $obj = ($this->site->type != 'clinical') ? "base" : "department";
        
        $label = "Choose one " . $obj . " to merge the other";
        $label .= (count($this->options) > 2) ? "s" : "";
        $label .= " into, and only that " . $obj . " will remain in the list.";
        
        $bases = new Zend_Form_Element_Radio('target_base');
        if (is_array($this->options)) {
            $bases->setMultiOptions($this->options)
                  ->setLabel($label)
                  ->setRequired(true)
                  ->addErrorMessage("Please choose one $obj to represent all the others.")
                  ->setRegisterInArrayValidator(false);
        }
        
        $base_options = new Zend_Form_Element_Hidden('base_options');
        $site_id = new Zend_Form_Element_Hidden('site_id');
        
        $this->addElements(array($bases, $base_options, $site_id));
        $this->setElementDecorators(self::$radioButtonDecorators, array('target_base'));
        $this->setElementDecorators(self::$hiddenDecorators, array('base_options', 'site_id'));
        
        // set defaults
        if (is_array($this->options)) {
            $this->setDefaults(array(
                'base_options' => implode(', ', array_keys($this->options)),
                'site_id' => $this->site->id,
            ));
        }
        
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/site-sub-forms/modals/merge-bases-modal.phtml")),
            'Form',
            array('DialogContainer', array(
                'id'          	=> 'mergeBasesDialog',
                'class'         => 'mergeBasesDialog',
                'jQueryParams' 	=> array(
                    'tabPosition' 	=> 'top',
                    'modal' 	=> true,
                    'autoOpen' 	=> false,
                    'resizable' 	=> false,
                    'width' 	=> 550,
                    'title'	 	=> $title,
                )
            )),
        ));
    }
    
    /**
     * Validate the form, if valid, merge the bases, if not, return the error msgs
     *
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($form_data)
    {
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $programId = $user->getProgramId();
        $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);

        if ($this->isValid($form_data)) {

            // first get the base we will merge into
            $baseToKeep = \Fisdap\EntityUtils::getEntity("BaseLegacy", $form_data['target_base']);
            $chosenBases = explode(', ', $form_data['base_options']);
            $baseRepo = \Fisdap\EntityUtils::getRepository('BaseLegacy');
    
            foreach ($chosenBases as $merge_base_id) {
                // we only want to merge the OTHER bases
                if ($merge_base_id != $baseToKeep->id) {
                    $baseRepo->mergeBases($baseToKeep->id, $merge_base_id);
                }
            }
            
            return $baseToKeep->id;
        }
            
        return $this->getMessages();
    }
}
