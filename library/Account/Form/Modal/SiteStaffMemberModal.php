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
 * @author     khanson
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_Modal_SiteStaffMemberModal extends Fisdap_Form_Base
{
    /**
     * @var int the site this staff member is/will be associated with
     */
    public $site_id;

    /**
     * @var \Fisdap\Entity\User this logged in user
     */
    public $user;

    /**
     * @var \Fisdap\Entity\SiteStaffMember the staff member we're editing (if applicable)
     */
    public $staff_member;

    /**
     * @param int $site_id the id of the current site
     * @param int | null $staff_member_id the id of the staff member
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($site_id, $staff_member_id = null)
    {
        $this->site_id = $site_id;
        $this->user = \Fisdap\Entity\User::getLoggedInUser();

        if ($staff_member_id) {
            $this->staff_member = \Fisdap\EntityUtils::getEntity("SiteStaffMember", $staff_member_id);
        }

        parent::__construct();
    }

    /**
     * init method to build the form
     * @throws Zend_Form_Exception
     */
    public function init()
    {
        parent::init();

        // To limit the number of external files, modal specific javascript and css are located in:
        //		/js/library/Account/Form/site-sub-forms/site-staff.js
        //		/css/library/Account/Form/site-sub-forms/site-staff.css

        $name_regex = '/^[^#!$@%&*()+={}:;<>?"]+$/';

        $first_name = new Zend_Form_Element_Text('staff_member_first');
        $first_name->setLabel('First Name:')
            ->setRequired(true)
            ->addValidator('regex', false, array($name_regex))
            ->addValidator('stringLength', false, array(1, 32))
            ->setAttribs(array("class" => "fancy-input", "autocomplete" => "off"))
            ->addErrorMessage("Please provide a valid first name. Names must be smaller than 32 characters and cannot contain special characters.<br />");

        $last_name = new Zend_Form_Element_Text('staff_member_last');
        $last_name->setLabel('Last Name:')
            ->setRequired(true)
            ->setAttribs(array("class" => "fancy-input", "autocomplete" => "off"))
            ->addValidator('regex', false, array($name_regex))
            ->addValidator('stringLength', false, array(1, 32))
            ->addErrorMessage("Please provide a valid last name. Names must be smaller than 32 characters and cannot contain special characters.");

        $title = new Zend_Form_Element_Text('staff_member_title');
        $title->setLabel('Title: <span class="optional-field">(optional)</span>');
        $title->addDecorator('Label', array('escape'=>false))
            ->setAttribs(array("class" => "fancy-input", "autocomplete" => "off", "maxlength" => "60"))
            ->addValidator('stringLength', false, array(1, 60))
            ->addErrorMessage("Titles must be smaller than 60 characters.");

        $trigger_phone_masking = ($this->user->getProgram()->country == "USA") ? "add-masking" : "";

        $phone = new Zend_Form_Element_Text('staff_member_phone');
        $phone->setLabel('Phone: <span class="optional-field">(optional)</span>');
        $phone->addDecorator('Label', array('escape'=>false))
              ->setAttribs(array("class" => "fancy-input " . $trigger_phone_masking, "autocomplete" => "off", "maxlength" => "14"));

        $pager = new Zend_Form_Element_Text('staff_member_pager');
        $pager->setLabel('Pager: <span class="optional-field">(optional)</span>');
        $pager->addDecorator('Label', array('escape'=>false))
              ->setAttribs(array("class" => "fancy-input staff_member_short_input " . $trigger_phone_masking, "autocomplete" => "off", "maxlength" => "14"));

        $email = new Fisdap_Form_Element_Email('staff_member_email');
        $email->setLabel('Email: <span class="optional-field">(optional)</span>');
        $email->addDecorator('Label', array('escape'=>false))
              ->addErrorMessage("Please enter a valid email address.")
              ->setAttribs(array("class" => "fancy-input", "autocomplete" => "off"));

        $baseOptions = \Fisdap\EntityUtils::getRepository('BaseLegacy')->getFormOptionsByProgram($this->user->getProgramId(), null, null, $this->site_id, true);
        $bases = new Zend_Form_Element_Select('staff_member_bases');
        $bases->setMultiOptions($baseOptions)
            ->setLabel('Base: <span class="optional-field">(optional)</span>');
        $bases->addDecorator('Label', array('escape'=>false))
             ->setAttribs(array("class" => "chzn-select",
                "data-placeholder" => "All bases...",
                 "style" => "width:315px",
                "multiple" => "multiple"));
        $bases->setRegisterInArrayValidator(false);

        $notes = new Zend_Form_Element_Textarea("staff_member_notes");
        $notes->setLabel('Notes: <span class="optional-field">(optional)</span>')
            ->setRequired(false)
            ->addValidator("StringLength", true, array('max' => '200'))
            ->addErrorMessage('Please enter notes that are less than 200 characters long.')
            ->addDecorator('Label', array('escape'=>false))
            ->setAttribs(array("class" => "fancy-input", 'maxlength' => "200"));

        $this->addElements(array($first_name, $last_name, $title, $phone, $pager, $email, $bases, $notes));

        // populate the form if we're editing an existing staff member
        if ($this->staff_member) {
            $staff_member_id = new Zend_Form_Element_Hidden("staff_member_id");
            $staff_member_id->setValue($this->staff_member->id);
            $this->addElement($staff_member_id);

            $this->setDefaults(array(
                    'staff_member_id' => $this->staff_member->id,
                    'staff_member_first' => $this->staff_member->first_name,
                    'staff_member_last' => $this->staff_member->last_name,
                    'staff_member_title' => $this->staff_member->title,
                    'staff_member_phone' => $this->staff_member->phone,
                    'staff_member_pager' => $this->staff_member->pager,
                    'staff_member_email' => $this->staff_member->email,
                    'staff_member_notes' => $this->staff_member->notes,
                    'staff_member_bases' => array_keys($this->staff_member->getBases())
                ));
        }

        // Set the decorators for the form
        $this->setDecorators(array(
                'FormErrors','PrepareElements',array('ViewScript', array('viewScript' => 'forms/site-sub-forms/modals/staff-member-modal.phtml')),'Form'
            ));
    }

    /**
     * Process valid form data or return validation error messages
     *
     * @param $post
     * @return array
     * @throws Zend_Form_Exception
     */
    public function process($post)
    {
        if ($this->isValid($post)) {

            // if we have a staff member already, we're editing an existing one
            if ($this->staff_member) {
                $staff_member = $this->staff_member;
            } else {
                // otherwise, we're creating a new staff member, so we need to set the site and program this one time only
                $staff_member = new \Fisdap\Entity\SiteStaffMember();
                $staff_member->set_site($this->site_id);
                $staff_member->set_program(\Fisdap\Entity\User::getLoggedInUser()->getProgramId());
            }

            $staff_member->first_name = $post['staff_member_first'];
            $staff_member->last_name = $post['staff_member_last'];
            $staff_member->title = $post['staff_member_title'];
            $staff_member->phone = preg_replace('[\D]', '', $post['staff_member_phone']);
            $staff_member->pager = preg_replace('[\D]', '', $post['staff_member_pager']);
            $staff_member->email = $post['staff_member_email'];
            $staff_member->notes = $post['staff_member_notes'];

            $staff_member->save();

            // now update the base associations
            $staff_member->updateBases(($post['staff_member_bases']) ? ($post['staff_member_bases']) : array());

            return array("success" => true, "new_staff_member_id" => $staff_member->id);
        } else {
            return $this->getMessages();
        }
    } // end process()
}
