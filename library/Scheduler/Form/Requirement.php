<?php

use Fisdap\Data\Site\SiteLegacyRepository;
use Fisdap\Entity\Requirement;

/**
 * @package    Scheduler
 * @subpackage Forms
 */
class Scheduler_Form_Requirement extends Fisdap_Form_Base
{
    /**
     * @var SiteLegacyRepository
     */
    private $siteLegacyRepository;

    public $notification_form;
    public $auto_assign_form;

    /**
     * @var \Fisdap\Entity\ProgramLegacy
     */
    public $program;


    /**
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($options = null, $program = null)
    {
        $this->siteLegacyRepository = \Fisdap\EntityUtils::getRepository('SiteLegacy');
        $this->program = $program ? $program : \Fisdap\Entity\User::getLoggedInUser()->getProgram();

        parent::__construct($options);
    }
    
    public function init()
    {
        parent::init();
        
        // add files for flippys
        $this->addJsFile("/js/jquery.flippy.js");
        $this->addCssFile("/css/jquery.flippy.css");
        
        // IE8 and lower need this for flippy to work
        $this->addJsFile("/js/excanvas.js");
        
        $this->addJsFile("/js/library/Scheduler/Form/requirement.js");
        $this->addCssFile("/css/library/Scheduler/Form/requirement.css");
        
        $custom_title = new Zend_Form_Element_Text("custom_title");
        $custom_title->setAttribs(array("class" => "fancy-input"));
        
        $requirement_repo = \Fisdap\EntityUtils::getRepository('Requirement');
        
        $already_chosen_defaults = $requirement_repo->getAllUniversalAssocations($this->program->id);
        $universal_chosen_val = array();
        foreach ($already_chosen_defaults as $req) {
            $universal_chosen_val[] = $req['id'];
        }
        
        $already_chosen_universal_requirements = new Zend_Form_Element_Hidden("already_chosen_universal_requirements");
        $already_chosen_universal_requirements->setValue(implode(",", $universal_chosen_val));
        
        $default_options = $requirement_repo->getUniversalRequirements(true);
        // organize them by category
        $defaults = $this->createChosen("default_list", "", "350px", "Choose from a list of standard requirements", $default_options, false, true);
        
        $expires = new Zend_Form_Element_Hidden("expires");
        $expires->setValue(1);
        
        $site = new Zend_Form_Element_Hidden("regardlessofsite");
        $site->setValue(1);
        
        $custom_requirement = new Zend_Form_Element_Hidden("custom_requirement");
        $custom_requirement->setValue(0);
        
        $assign = new Zend_Form_Element_Hidden("assign");
        $assign->setValue(1);
        
        $all = new Zend_Form_Element_Hidden("all");
        $all->setValue(1);
        
        $site_ids = new Zend_Form_Element_Hidden("site_ids");
        $site_ids->setValue("null");
        
        $auto_assign = new Zend_Form_Element_Checkbox("auto_assign");
        $auto_assign->setAttribs(array("class" => "auto_assign_slider"));
        $auto_assign->setValue(1);
        
        $today = new DateTime();
        $all_due_date = new Zend_Form_Element_Text("all_due_date");
        $all_due_date->setAttribs(array("class" => "selectDate fancy-input"));
        $all_due_date->setValue($today->format("m/d/Y"));
        
        $account_type_options = \Fisdap\Entity\CertificationLevel::getFormOptions(null, null, "description", $this->program->profession->id);
        $default_vals = array();
        foreach ($account_type_options as $id => $opt) {
            $account_type_options[$id] = $opt . "s";
            $default_vals[] = $id;
        }
        
        $account_type_options["instructor"] = "Instructors";
        $default_vals[] = "instructor";
        $account_types = $this->createChosen("auto_assign_account_types", "", "375px", "Choose account types...", $account_type_options, true, false);
        $account_types->setValue($default_vals);
        
        $category_options = \Fisdap\EntityUtils::getRepository('RequirementCategory')->getFormOptions();
        $category = $this->createChosen("category", "", "240px", "Choose a category...", $category_options, false, true);
        
        $save = new Fisdap_Form_Element_SaveButton('save');
        $save->setLabel("Save");
        
        $due_dates = new Zend_Form_Element_Select("due_dates");
        $due_dates->setAttribs(array("multiple" => "multiple"));
        
        $userContextIds = new Zend_Form_Element_Select("userContextIds");
        $userContextIds->setAttribs(array("multiple" => "multiple"));
        
        $this->notification_form = new Scheduler_Form_NotificationSubForm($this->program->id, null, true);
        $this->auto_assign_form = new Scheduler_Form_AutoAssignSubForm($this->program->id);
        
        $this->addElements(array($already_chosen_universal_requirements, $due_dates, $userContextIds, $save, $auto_assign, $account_types, $all_due_date, $defaults, $custom_title, $expires, $category, $site, $custom_requirement, $site_ids, $all, $assign));
        
        // Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'forms/requirement.phtml')),
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
             ->setAttribs(array("class" => "chzn-select",
                                           "data-placeholder" => $placeholder_text,
                                           "style" => "width:" . $width,
                                           "multiple" => $multi,
                                           "tabindex" => count($options)));
        return $chosen;
    }
    
    public function process($data)
    {
        //var_dump($data['userContextId']);
        \Fisdap\EntityUtils::getEntityManager()->getConnection()->exec("SET SESSION wait_timeout = 300");
        \Zend_Registry::get('db')->query("SET SESSION wait_timeout = 300");
        
        $user_repo = \Fisdap\EntityUtils::getRepository('User');
        $current_users_program = $this->program;
        $compute_compliance_userContextIds = array();
        $creator = $data['userContextId'];
        
        // ----------------------------- Step 1: Get the requirement -----------------------------

        /** @var Requirement $requirement */
        $requirement = \Fisdap\EntityUtils::getEntity("Requirement", $data['requirement_id']);

        // is this a custom requirement?
        if ($data['custom_requirement']) {
            // we need to set its title, expiration, and category
            $requirement->set_name($data['custom_title'], $creator);
            $requirement->set_expires($data['expires'], $creator);
            $requirement->set_category($data['category'], $creator);
            $requirement->save();
        }

        // ----------------------------- Step 2: Update the notifications -----------------------------
        $notification_form = new Scheduler_Form_NotificationSubForm($current_users_program->id, $requirement->id);
        $notification_form->process($data);
        
        //Figure out if we have a new requirement notification for this requirement
        $sendNotification = $current_users_program->sendNewRequirementNotification($requirement->id);
        
        // ----------------------------- Step 3: Create the associations -----------------------------
        
        // is this a site or program requirement?
        if ($data['regardlessofsite'] == 1) {
            // we created the association in the controller, so we're good here
        } else {
            // remove the temporary program association here
            $program_assoc = $requirement->getProgramLevelAssocByProgram($current_users_program->id);
            if ($program_assoc) {
                $requirement->requirement_associations->removeElement($program_assoc);
                $program_assoc->delete();
                $requirement->save();
            }

            $site_ids = explode(",", $data['site_ids']);
            $requirement->createSiteAssociations($site_ids, $current_users_program);
            // and now assign to users who are attending a future shift at this site
            $userContextIds_from_sites = $this->siteLegacyRepository->getUserContextsAttendingSites($site_ids, $current_users_program);
            $compute_compliance_userContextIds = $requirement->assignRequirementToUserContexts($userContextIds_from_sites, new DateTime(), $compute_compliance_userContextIds, $sendNotification, $creator);
        } // end regardlessofsite
        
        // ----------------------------- Step 4: Create the attachments -----------------------------
        
        // did the user say they wanted to assign?
        if ($data['assign']) {
            
            // Are we assigning to all users for this program?
            if ($data['all']) {
                // ---- First, go grab the user role ids (for all active students who have scheduler and instructors in this program) and merge them into one array
                $instructor_userContextIds = $user_repo->getAllInstructorsByProgram($current_users_program->id, null, true);
                $active_students = $user_repo->getAllStudentsByProgram($current_users_program->id, array("graduationStatus" => array(1)));
                $userContextIds = [];
                
                // ---- Second, make sure the students we've grabbed have Scheduler
                foreach ($active_students as $student) {
                    if (((boolean)($student['configuration'] & 8192) || (boolean)($student['configuration'] & 2))) {
                        $userContextIds[] = $student['userContextId'];
                    }
                }
                
                // ---- Add the instructors
                foreach ($instructor_userContextIds as $instructor) {
                    $userContextIds[] = $instructor['id'];
                }
                
                // ---- Fourth, assign the requirement
                $compute_compliance_userContextIds = $requirement->assignRequirementToUserContexts($userContextIds, $data['all_due_date'], $compute_compliance_userContextIds, $sendNotification, $creator);
            } else {
                // Our user has grouped UserContexts and due dates.
                // We'll need to step through each selected due date option and get its respective list of user role ids
                $compute_compliance_userContextIds = $requirement->assignRequirementByDueDateGroup($data['due_dates'], $data['userContextIds'], $compute_compliance_userContextIds, $sendNotification, $creator);
            }
        } // end if assign
        
        $requirement->save();
        
        // ----------------------------- Step 5: Update the auto assignments -----------------------------
        $auto_assign_form = new Scheduler_Form_AutoAssignSubForm($current_users_program->id, $requirement->id);
        $auto_assign_form->process($data);
        
        
        return $compute_compliance_userContextIds;
    } // end process()
}
