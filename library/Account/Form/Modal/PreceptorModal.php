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
class Account_Form_Modal_PreceptorModal extends Fisdap_Form_Base
{
    public $site;

    public $user;

    public $preceptor;

    /**
     * @param SiteLegacy $site the currrent site
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($site, $preceptor_id = null, $options = null)
    {
        $this->site = $site;
        $this->user = \Fisdap\Entity\User::getLoggedInUser();

        if($preceptor_id){
            $this->preceptor = \Fisdap\EntityUtils::getEntity("PreceptorLegacy", $preceptor_id);
        }

        parent::__construct($options);
    }


    public function init()
    {
        parent::init();

        // To limit the number of external files, modal specific javascript and css are located in:
        //		/js/library/Account/Form/site-sub-forms/preceptors.js
        //		/css/library/Account/Form/site-sub-forms/preceptors.css

        $name_regex = '/^[^#!$@%&*()+={}:;<>?"]+$/';

        $first_name = new Zend_Form_Element_Text('preceptor_first');
        $first_name->setLabel('First Name: <span class="required-field">(required)</span>')
            ->setRequired(true)
            ->addDecorator('Label', array('escape'=>false))
            ->addValidator('regex', false, array($name_regex))
            ->addValidator('stringLength', false, array(1, 32))
            ->setAttribs(array("class" => "fancy-input", "autocomplete" => "off"))
            ->addErrorMessage("Please provide a valid first name. Names must be smaller than 32 characters and cannot contain special characters.<br />");

        $last_name = new Zend_Form_Element_Text('preceptor_last');
        $last_name->setLabel('Last Name: <span class="required-field">(required)</span>')
            ->setRequired(true)
            ->addDecorator('Label', array('escape'=>false))
            ->setAttribs(array("class" => "fancy-input", "autocomplete" => "off"))
            ->addValidator('regex', false, array($name_regex))
            ->addValidator('stringLength', false, array(1, 32))
            ->addErrorMessage("Please provide a valid last name. Names must be smaller than 32 characters and cannot contain special characters.");

        $trigger_phone_masking = ($this->user->getProgram()->country == "USA") ? "add-masking" : "";

        $work_phone = new Zend_Form_Element_Text('preceptor_work');
        $work_phone->setLabel('Work Phone:');
        $work_phone->setAttribs(array("class" => "fancy-input " . $trigger_phone_masking, "autocomplete" => "off", "maxlength" => "14"));

        $home_phone = new Zend_Form_Element_Text('preceptor_home');
        $home_phone->setLabel('Home Phone:');
        $home_phone->setAttribs(array("class" => "fancy-input preceptor_short_input " . $trigger_phone_masking, "autocomplete" => "off", "maxlength" => "14"));

        $pager = new Zend_Form_Element_Text('preceptor_pager');
        $pager->setLabel('Pager:');
        $pager->setAttribs(array("class" => "fancy-input preceptor_short_input " . $trigger_phone_masking, "autocomplete" => "off", "maxlength" => "14"));

        $email = new Fisdap_Form_Element_Email('preceptor_email');
        $email->addErrorMessage("Please enter a valid email address.");
        $email->setAttribs(array("class" => "fancy-input", "autocomplete" => "off"));

        $this->addElements(array($first_name, $last_name, $work_phone, $home_phone, $pager, $email));

        if($this->preceptor){
            $preceptor_id = new Zend_Form_Element_Hidden("preceptor_id");
            $preceptor_id->setValue($this->preceptor->id);
            $this->addElement($preceptor_id);
            $this->setDefaults(array(
                    'preceptor_id' => $this->preceptor->id,
                    'preceptor_first' => $this->preceptor->first_name,
                    'preceptor_last' => $this->preceptor->last_name,
                    'preceptor_work' => $this->preceptor->work_phone,
                    'preceptor_home' => $this->preceptor->home_phone,
                    'preceptor_pager' => $this->preceptor->pager,
                    'preceptor_email' => $this->preceptor->email
                )   );
        }

        // Set the decorators for the form
        $this->setDecorators(array(
                'FormErrors','PrepareElements',array('ViewScript', array('viewScript' => 'forms/site-sub-forms/modals/preceptor-modal.phtml')),'Form'
            ));

    }

    public function process($post)
    {
        if ($this->isValid($post)) {

            $program = \Fisdap\Entity\User::getLoggedInUser()->getProgram();

            $preceptor = ($this->preceptor) ? $this->preceptor : new \Fisdap\Entity\PreceptorLegacy;

            $preceptor->first_name = $post['preceptor_first'];
            $preceptor->last_name = $post['preceptor_last'];
            $preceptor->work_phone = preg_replace('[\D]', '', $post['preceptor_work']);
            $preceptor->home_phone = preg_replace('[\D]', '', $post['preceptor_home']);
            $preceptor->pager = preg_replace('[\D]', '', $post['preceptor_pager']);
            $preceptor->email = $post['preceptor_email'];
            $preceptor->site = $this->site;
            $preceptor->save();


            if(!$this->preceptor) {
                $program->addPreceptor($preceptor, true);
            }

            return array("success" => true, "new_preceptor_id" => $preceptor->id);

        }
        else {
            return $this->getMessages();
        }

    } // end process()
}
