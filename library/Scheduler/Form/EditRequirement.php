<?php

use Fisdap\Data\Site\SiteLegacyRepository;


/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_EditRequirement extends Fisdap_Form_Base
{
    /**
     * @var SiteLegacyRepository
     */
    private $siteLegacyRepository;


    /**
     * @var \Fisdap\Entity\ProgramLegacy
     */
    public $program;

    /**
     * @var \Fisdap\Entity\Requirement
     */
    public $requirement;

    /**
     * @var bool
     */
    public $is_universal;

    /**
     * @var bool
     */
    public $is_site_requirement;

    /*
     * @var array
     */
    public $site_data;

    /*
     * @var array
     */
    public $attachment_data;

    /*
     * @var array
     */
    public $cert_levels;

    /**
     * @var int
     */
    public $number_of_instructors;

    /**
     * @var int
     */
    public $number_of_students;

    /**
     * @var array of RequirementAssocations
     */
    public $existing_site_associations;

    /**
     * @var array of RequirementAttachments
     */
    public $existing_attachments;

    public $notification_form;
    public $auto_assign_form;


    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($requirement = null, $options = null, $program = null)
    {
        $this->siteLegacyRepository = \Fisdap\EntityUtils::getRepository('SiteLegacy');

        $this->program = $program ? $program : \Fisdap\Entity\User::getLoggedInUser()->getProgram();
        $this->requirement = $requirement;
        $this->is_site_requirement = false;
        $this->site_data = array();
        $this->is_universal = $requirement->universal;
        $this->cert_levels = array();
        $this->existing_site_associations = array();
        $this->existing_attachments = array();
        parent::__construct($options);
    }

    public function init()
    {
        parent::init();
        $this->addFiles();

        // if this is a custom requirement, we'll need expires and what not
        if (!$this->is_universal) {
            $custom_title = new Zend_Form_Element_Text("custom_title");
            $custom_title->setAttribs(array("class" => "fancy-input"));
            $custom_title->setValue($this->requirement->name);

            $category_options = \Fisdap\EntityUtils::getRepository('RequirementCategory')->getFormOptions();
            $category = $this->createChosen("category", "", "240px", "Choose a category...", $category_options, false, true);
            $category->setValue($this->requirement->category->id);

            $this->addElements(array($custom_title, $category));
        }

        $req_id = new Zend_Form_Element_Hidden("requirement_id");
        $req_id->setValue($this->requirement->id);

        $search_attachments = new Zend_Form_Element_Text("search_attachments");
        $search_attachments->setAttribs(array("class" => "fancy-input"));

        // everything related to sites
        $removing_site_ids = new Zend_Form_Element_Hidden("removing_site_ids");
        $adding_site_ids = new Zend_Form_Element_Hidden("adding_site_ids");
        $existing_site_ids = new Zend_Form_Element_Hidden("existing_site_ids");
        $existing_site_ids_value = $this->initSiteData();

        $regardlessofsite = ($this->is_site_requirement) ? 0 : 1;
        $site = new Zend_Form_Element_Hidden("regardlessofsite");
        $site->setValue($regardlessofsite);
        $existing_site_ids->setValue(implode(",", $existing_site_ids_value));

        // Auto assign stuff
        $auto_assign = new Zend_Form_Element_Checkbox("auto_assign");
        $auto_assign->setAttribs(array("class" => "auto_assign_slider"));
        $auto_assign->setValue(1);

        $account_type_options = \Fisdap\Entity\CertificationLevel::getFormOptions(null, null, "description", $this->program->profession->id);
        $default_vals = array();
        foreach ($account_type_options as $id => $opt) {
            $account_type_options[$id] = $opt . "s";
            $default_vals[] = $id;
            $this->cert_levels[$id] = $opt . "s";
        }

        $account_type_options["instructor"] = "Instructors";
        $default_vals[] = "instructor";
        $account_types = $this->createChosen("auto_assign_account_types", "", "375px", "Choose account types...", $account_type_options, true, false);
        $account_types->setValue($default_vals);

        // the multiselect to keep track of due dates and new user role ids
        $due_dates = new Zend_Form_Element_Select("due_dates");
        $userContextIds = new Zend_Form_Element_Select("userContextIds");
        $due_dates->setAttribs(array("multiple" => "multiple"));
        $userContextIds->setAttribs(array("multiple" => "multiple"));
        $removing_attachment_ids = new Zend_Form_Element_Hidden("removing_attachment_ids");

        $this->initAttachmentData();

        $save = new Fisdap_Form_Element_SaveButton('save');
        $save->setLabel("Save");

        $this->notification_form = new Scheduler_Form_NotificationSubForm($this->program->id, $this->requirement->id);
        $this->auto_assign_form = new Scheduler_Form_AutoAssignSubForm($this->program->id, $this->requirement->id);

        $this->addElements(array($req_id, $search_attachments, $removing_attachment_ids, $due_dates, $userContextIds,
            $save, $auto_assign, $account_types, $site, $existing_site_ids, $adding_site_ids, $removing_site_ids));

        // Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'forms/edit-requirement.phtml')),
            'Form'
        ));

        $this->addSubForms(array(
            'notificationForm' => $this->notification_form,
            'autoAssignForm' => $this->auto_assign_form
        ));

    }

    private function createChosen($element_name, $label, $width, $placeholder_text, $options, $multi = "multiple", $add_null = false)
    {
        $chosen = new Zend_Form_Element_Select($element_name);

        if ($add_null) {
            $null_val = array("null" => " ");
            $actual_options = $null_val + $options;
        } else {
            $actual_options = $options;
        }

        $chosen->setMultiOptions($actual_options)
            ->setLabel($label)
            ->setAttribs(array("class" => "chzn-select", "data-placeholder" => $placeholder_text,
                "style" => "width:" . $width, "multiple" => $multi, "tabindex" => count($options)));

        return $chosen;
    }

    public function process($data)
    {
        \Fisdap\EntityUtils::getEntityManager()->getConnection()->exec("SET SESSION wait_timeout = 300");
        \Zend_Registry::get('db')->query("SET SESSION wait_timeout = 300");

        $current_users_program = $this->program;
        $requirement = $this->requirement;
        $compute_compliance_userContextIds = array();
        $editor = $data['userContextId'];


        // ----------------------------- Step 1: If custom, update the expiration/category -----------------------------
        if (!$this->is_universal) {
            $requirement->set_name($data['custom_title'], $editor);
            $requirement->set_category($data['category'], $editor);
        }

        // ----------------------------- Step 2: Update the notifications -----------------------------
        $this->notification_form->process($data);
        $sendNotification = $current_users_program->sendNewRequirementNotification($requirement->id);
        //Figure out if we have a new requirement notification for this requirement

        // ----------------------------- Step 3: Update the associations -----------------------------

        // is this a site or program requirement?
        if ($data['regardlessofsite'] == 1) {
            if ($data['existing_site_ids']) {
                // remove each site association
                $this->removeAssociations($data['existing_site_ids']);
            }
            // now add a single association to make it a program level requirement, if there isn't one already
            if (!$requirement->getProgramLevelAssocByProgram($current_users_program->id)) {
                $requirement->createProgramAssociation($current_users_program);
            }
        } else {

            // this is a site level requirement

            // if a program association existed previously: remove it here
            $program_assoc = $requirement->getProgramLevelAssocByProgram($current_users_program->id);
            if ($program_assoc) {
                $requirement->requirement_associations->removeElement($program_assoc);
                $program_assoc->delete();
                $requirement->save();
            }

            // first remove all 'removing_site_ids'
            if ($data['removing_site_ids']) {
                $this->removeAssociations($data['removing_site_ids']);
            }

            // now add new associations
            if ($data['adding_site_ids']) {
                $site_ids = explode(",", $data['adding_site_ids']);
                $requirement->createSiteAssociations($site_ids, $current_users_program);
                // and now assign to users who are attending a future shift at this site
                $userContextIds_from_sites = $this->siteLegacyRepository->getUserContextsAttendingSites($site_ids, $current_users_program);
                $compute_compliance_userContextIds = $requirement->assignRequirementToUserContexts($userContextIds_from_sites, new DateTime(), $compute_compliance_userContextIds, $sendNotification, $editor);
            }

        } // end regardlessofsite

        // ----------------------------- Step 4: Update the attachments -----------------------------

        // a) Remove attachments
        if ($data['removing_attachment_ids']) {
            foreach (explode(",", $data['removing_attachment_ids']) as $attachment_id) {
                $attachment = $this->existing_attachments[$attachment_id];
                $compute_compliance_userContextIds[] = $attachment->user_context->id;
                $this->existing_attachments[$attachment_id] = "removed";
                $attachment->delete();
            }

            $requirement->save();
        }

        // b) Update the due dates for existing attachments
        if ($this->existing_attachments) {
            foreach ($this->existing_attachments as $attachment) {
                if ($attachment != "removed") {

                    // get the due date element based on this ID
                    if (!is_null($attachment->due_date) && ($attachment->due_date->format("m/d/Y") != $data['due_date_' . $attachment->id])) {
                        $attachment->set_due_date($data['due_date_' . $attachment->id], $editor);
                        $attachment->save();
                        $compute_compliance_userContextIds[] = $attachment->user_context->id;
                    }
                }

            }
        }

        // c) Assign new attachments
        if ($data['due_dates']) {
            $compute_compliance_userContextIds = $requirement->assignRequirementByDueDateGroup($data['due_dates'], $data['userContextIds'], $compute_compliance_userContextIds, $sendNotification, $editor);
        }

        // ----------------------------- Step 5: Update the auto assignments -----------------------------
        $this->auto_assign_form->process($data);

        $requirement->save();

        return array_unique($compute_compliance_userContextIds);
    } // end process()

    public function removeAssociations($comma_separated_site_ids)
    {
        foreach (explode(",", $comma_separated_site_ids) as $site_id) {
            $assoc = $this->existing_site_associations[$site_id];
            if ($assoc) {
                $this->requirement->requirement_associations->removeElement($assoc);
                $assoc->delete();
                $this->requirement->save();
            }
        }
    }

    public function addFiles()
    {
        // add files for flippys and other plugins
        $this->addJsFile("/js/jquery.flippy.js");
        $this->addCssFile("/css/jquery.flippy.css");
        $this->addJsFile("/js/excanvas.js");
        $this->addJsFile("/js/library/Scheduler/Form/requirement.js");
        $this->addJsFile("/js/library/Scheduler/Form/edit-requirement.js");
        $this->addCssFile("/css/library/Scheduler/Form/requirement.css");
        $this->addCssFile("/css/library/Scheduler/Form/edit-requirement.css");
    }

    public function initSiteData()
    {
        $existing_site_ids_value = array();
        // they have to have at least 1 assocation to even get this page
        foreach ($this->requirement->getAllAssociationsByProgram($this->program->id) as $assoc) {
            if ($assoc->site) {
                $this->is_site_requirement = true;

                if ($assoc->site->getAssociationByProgram($this->program->id)->active) {
                    $existing_site_ids_value[] = $assoc->site->id;
                    $this->existing_site_associations[$assoc->site->id] = $assoc;
                    $key = $assoc->site->name . "-" . $assoc->site->id;
                    $this->site_data[$key] = array("site_id" => $assoc->site->id, "name" => $assoc->site->name, "type" => $assoc->site->type, "address" => $assoc->site->getSiteAddress());
                }
            }
        }

        ksort($this->site_data);
        return $existing_site_ids_value;
    }

    public function initAttachmentData()
    {
        $today = new DateTime();
        $this->attachment_data = array();
        $this->number_of_students = 0;
        $this->number_of_instructors = 0;

        $attachments = \Fisdap\EntityUtils::getRepository('Requirement')->getAttachmentsByRequirementAndProgram($this->requirement, $this->program);

        foreach ($attachments as $attachment) {
            // make sure its a user in our program and it is a non-archived attachment
            if (!$attachment->archived) {
                $this->existing_attachments[$attachment->id] = $attachment;
                $user = $attachment->user_context->user;
                $site_ids = $this->siteLegacyRepository->getScheduledSitesByUserContext($attachment->user_context->id);
                $users_sites = array();
                foreach ($site_ids as $id) {
                    $users_sites[] = $id['id'];
                }

                $cert = ($attachment->user_context->certification_level) ? $attachment->user_context->certification_level->description : "Instructor";
                $cert_id = ($attachment->user_context->certification_level) ? $attachment->user_context->certification_level->id : "instructor";

                if ($cert == "Instructor") {
                    $this->number_of_instructors++;
                } else {
                    $this->number_of_students++;
                }

                $status = $status_class = $expired_date_display = "";
                $create_due_date_element = $past_due = false;

                if ($today < $attachment->due_date) {
                    $create_due_date_element = true;
                } else {
                    if ($attachment->completed) {
                        if ($this->requirement->expires) {
                            // expiration date is in the future
                            if ($today < $attachment->expiration_date) {
                                $status = "expires:";
                                $expired_date_display = $attachment->expiration_date->format("m/d/Y");
                            } else {
                                // they're expired
                                $status = "<span class='non-compliant'>expired</span>";
                                $status_class = "non-compliant";
                                $expired_date_display = $attachment->expiration_date->format("m/d/Y");
                            }
                        } else {
                            $status = "<span class='compliant'>compliant</span>";
                        }
                    } else {
                        $create_due_date_element = true;
                        $past_due = true;
                        $status = "<span class='past-due non-compliant'>past due</span>";
                        $status_class = "non-compliant";
                    }
                }

                if ($create_due_date_element) {
                    $due_date_element = new Zend_Form_Element_Text("due_date_" . $attachment->id);
                    $due_date_element->setAttribs(array("class" => "selectDate fancy-input"));
                    is_null($attachment->due_date) ? $due_date_element->setValue(null) : $due_date_element->setValue($attachment->due_date->format("m/d/Y"));
                    $this->addElements(array($due_date_element));
                }

                $key = str_replace(' ', '', $user->last_name) . str_replace(' ', '', $user->first_name) . "-" . $attachment->id;
                $this->attachment_data[$key] = array("id" => $attachment->id,
                    "site_ids" => implode(",", $users_sites),
                    "past_due" => $past_due,
                    "name" => $user->getName(),
                    "cert_id" => $cert_id,
                    "userContextId" => $attachment->user_context->id,
                    "cert_level" => $cert,
                    "status" => $status,
                    "status_class" => $status_class,
                    "expired_date" => $expired_date_display);
            }
        } // end for each attachment

        ksort($this->attachment_data);
    }
}
