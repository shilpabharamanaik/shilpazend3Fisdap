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
 * This produces a modal form for merging precetpors
 */

/**
 * @author Hammer :D
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_Modal_MergePreceptorsModal extends Fisdap_Form_BaseJQuery
{
    public $options;
    public $site;
    
    public static $radioButtonDecorators = array('ViewHelper','Errors',
        array('HtmlTag', array('tag' => 'div', 'class'=>'preceptor-input')),
        array('Label', array('tag' => 'h3', 'openOnly' => true, 'placement' => 'prepend', 'class' => 'section-header no-border')),
    );
    public static $hiddenDecorators = array('ViewHelper',array('HtmlTag', array('tag' => 'div', 'class' => 'hidden')),);
    
    
    public function __construct($preceptor_options = null, $site_id = null, $options = null)
    {
        /*
        $this->options = array();
        if($preceptors_options){
            foreach($preceptors_options as $id => $name){
                $preceptor = \Fisdap\EntityUtils::getEntity("PreceptorLegacy", $id);

                if($preceptor->work_phone){$phone = $preceptor->work_phone;}
                else if($preceptor->home_phone){$phone = $preceptor->home_phone;}
                else if($preceptor->pager){$phone = $preceptor->pager;}

                if($preceptor->email){
                    $email = ($phone) ? ", " : "";
                    $email .= $preceptor->email;
                }

                $this->options[$id] = $name . ": " . $phone . $email;
            }
        }*/
        
        $this->options = $preceptor_options;
        
        $this->site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);
        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        $label = "Choose one preceptor to merge the other";
        $label .= (count($this->options) > 2) ? "s" : "";
        $label .= " into, and only that preceptor will remain in the list.";
        
        $preceptors = new Zend_Form_Element_Radio('target_preceptor');
        if (is_array($this->options)) {
            $preceptors->setMultiOptions($this->options)
                  ->setLabel($label)
                  ->setRequired(true)
                  ->addErrorMessage("Please choose one preceptor to represent all the others.")
                  ->setRegisterInArrayValidator(false);
        }
        
        $preceptor_options = new Zend_Form_Element_Hidden('preceptor_options');
        $site_id = new Zend_Form_Element_Hidden('site_id');
        
        $this->addElements(array($preceptors, $preceptor_options, $site_id));
        $this->setElementDecorators(self::$radioButtonDecorators, array('target_preceptor'));
        $this->setElementDecorators(self::$hiddenDecorators, array('preceptor_options', 'site_id'));
        
        // set defaults
        if (is_array($this->options)) {
            $this->setDefaults(array(
                'preceptor_options' => implode(', ', array_keys($this->options)),
                'site_id' => $this->site->id,
            ));
        }
        
        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "forms/site-sub-forms/modals/merge-preceptors-modal.phtml")),
            'Form',
            array('DialogContainer', array(
                'id'          	=> 'mergePreceptorsDialog',
                'class'         => 'mergePreceptorsDialog',
                'jQueryParams' 	=> array(
                    'tabPosition' 	=> 'top',
                    'modal' 	=> true,
                    'autoOpen' 	=> false,
                    'resizable' 	=> false,
                    'width' 	=> 550,
                    'title'	 	=> "Merge preceptors",
                )
            )),
        ));
    }
    
    /**
     * @param array $data the POSTed data
     * @return mixed either boolean true, or an array of error messages
     */
    public function process($data)
    {
        if ($data['target_preceptor'] == "") {
            return "Please select a preceptor.";
        } else {
            $user = \Fisdap\Entity\User::getLoggedInUser();
            $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $user->getProgramId());
            
            $site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $data['site']);
            
            $otherPreceptors = explode(",", $data['preceptor_options']);
            
            // for all other preceptors
            foreach ($otherPreceptors as $preceptor_id) {
                if ($preceptor_id != $data['target_preceptor']) {
                    $preceptor = \Fisdap\EntityUtils::getEntity("PreceptorLegacy", $preceptor_id);
                    
                    // all runs associated with this preceptor
                    $patients = $preceptor->getPatientsByPreceptor($preceptor_id);
                    
                    // for each of those runs, set the preceptor to the new preceptor
                    foreach ($patients as $patient) {
                        $patient->set_preceptor($data['target_preceptor']);
                        $patient->save();
                    }
                    
                    // now handle events with this preceptor
                    $preceptor->setEventPreceptors($data['target_preceptor'], $preceptor_id);
                    
                    // finally delete this preceptor
                    $preceptor->delete();
                }
            }
            
            return $data['target_preceptor'];
        }
    }
}
