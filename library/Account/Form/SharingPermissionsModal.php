<?php

use Fisdap\Data\Site\SiteLegacyRepository;
use Fisdap\Entity\BaseLegacy;
use Fisdap\Entity\Requirement;
use Fisdap\Entity\User;

/**
 * This produces a modal form for setting shared scheduler permissions
 *
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_SharingPermissionsModal extends Fisdap_Form_BaseJQuery
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
     * @var \Fisdap\Entity\SiteLegacy
     */
    public $site;
    
    /**
     * @var int
     */
    public $is_new;
    
    /**
     * @var array decorators for the checkboxes
     */
    public static $checkboxDecorators = array(
        'ViewHelper',
        'Errors',
        array('HtmlTag', array('tag' => 'div', 'class'=>'checkboxes')),
    );

    /**
     * @var array decorators for hidden elements
     */
    public static $hiddenDecorators = array(
            'ViewHelper',
            array('HtmlTag', array('tag' => 'div', 'class' => 'hidden')),
    );


    public function __construct($program_id = null, $site_id = null)
    {
        $this->siteLegacyRepository = \Fisdap\EntityUtils::getRepository('SiteLegacy');

        $this->program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $program_id);
        $this->site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $site_id);

        if ($program_id && $site_id) {
            $this->is_new = $this->program->sharesSite($this->site->id) ? 0 : 1;
        }

        parent::__construct();
    }
    
    public function init()
    {
        parent::init();

        $this->addJsFile("/js/jquery.flippy.js");
        $this->addCssFile("/css/jquery.flippy.css");
        // IE8 and lower need this for flippy to work
        $this->addJsFile("/js/excanvas.js");
        
        $this->addJsFile("/js/jquery.sliderCheckbox.js");
        $this->addCssFile("/css/jquery.sliderCheckbox.css");
        $this->addCssFile("/css/library/Account/Form/sharing-permissions-modal.css");
        
        $share_existing = new Zend_Form_Element_Checkbox('share_existing');
        $share_existing->setAttribs(array("class" => "slider-checkbox"));

        $receive_existing = new Zend_Form_Element_Checkbox('receive_existing');
        $receive_existing->setAttribs(array("class" => "slider-checkbox"));
        
        $admin = new Zend_Form_Element_Hidden('admin');
        $see_students = new Zend_Form_Element_Hidden('see_students');
        $new_share = new Zend_Form_Element_Hidden('new_share');
        $program_id = new Zend_Form_Element_Hidden('program_id');
        $site_id = new Zend_Form_Element_Hidden('site_id');

        $this->addElements(array($share_existing, $receive_existing, $admin, $see_students, $new_share, $program_id, $site_id));
        $this->setElementDecorators(self::$checkboxDecorators, array('share_existing', 'receive_existing'));
        $this->setElementDecorators(self::$hiddenDecorators, array('admin', 'see_students', 'new_share', 'program_id', 'site_id'));

        // set defaults
        if ($this->program->id) {
            $this->setDefaults(array(
                'admin' => $this->program->isAdmin($this->site->id),
                'see_students' => $this->program->seesSharedStudents($this->site->id),
                'new_share' => $this->is_new,
                'program_id' => $this->program->id,
                'site_id' => $this->site->id,
                'share_existing' => true,
            ));
        }

        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "sharingPermissionsModal.phtml")),
            'Form',
            array('DialogContainer', array(
                'id'          	=> 'sharingPermissionsDialog',
                'class'         => 'sharingPermissionsDialog',
                'jQueryParams' 	=> array(
                    'tabPosition' 	=> 'top',
                    'modal' 	=> true,
                    'autoOpen' 	=> false,
                    'resizable' 	=> false,
                    'width' 	=> 850,
                    'title'	 	=> 'Edit sharing permissions'
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
        if ($this->isValid($form_data)) {
            \Fisdap\EntityUtils::getEntityManager()->getConnection()->exec("SET SESSION wait_timeout = 28800");
            \Zend_Registry::get('db')->query("SET SESSION wait_timeout = 28800");
            
            $new = $form_data['new_share'];
            $program_id = $form_data['program_id'];
            $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $form_data['program_id']);
            $site_id = $form_data['site_id'];
            $site = $this->siteLegacyRepository->getOneById($form_data['site_id']);
            $em = \Fisdap\EntityUtils::getEntityManager();
            $beta_scheduler = $program->scheduler_beta;
            $users_to_recompute = array();
            
            // update the appropriate permissions
            $share = $site->getShareByProgram($program_id);
            $share->approved = 1;
            $share->admin = $form_data['admin'];
            $share->see_students = $form_data['see_students'];
            $share->save(false);

            if ($new) {
                // Share existing shifts if applicable
                $today = date("Y-m-d");
                if ($form_data['share_existing']) {
                    $batch_size = 1000;
                    $counter = 0;
                    $network_events = \Fisdap\EntityUtils::getRepository("EventLegacy")->getNetworkEvents($beta_scheduler, $site_id, $today);
                    
                    foreach ($network_events as $event_id) {
                        $event = \Fisdap\EntityUtils::getEntity("EventLegacy", $event_id);
                        
                        $event->share($program, false);
                        
                        $counter++;
                        if ($counter >= $batch_size) {
                            $flushTime = microtime(true);
                            $em->flush();
                            $em->clear();
                            $counter = 0;
                            
                            //Regrab the program since we cleared the EntityManager
                            $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $form_data['program_id']);
                            $site = $this->siteLegacyRepository->getOneById($form_data['site_id']);
                        }
                    }
                    $em->flush();
                }
                
                // Regrab the site since we cleared the EntityManager
                $site = $this->siteLegacyRepository->getOneById($form_data['site_id']);
                $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $form_data['program_id']);

                // Receive existing shifts if applicable
                if ($form_data['receive_existing']) {
                    $batch_size = 1000;
                    $counter = 0;
                    $program_events = \Fisdap\EntityUtils::getRepository("EventLegacy")->getProgramEvents($beta_scheduler, $program_id, $site_id, $today);
                    
                    foreach ($program_events as $event_id) {
                        $event = \Fisdap\EntityUtils::getEntity("EventLegacy", $event_id);
                        foreach ($site->site_shares as $network_share) {
                            if ($network_share->approved) {
                                $network_program = $network_share->program;
                                $event->share($network_program, false);
                            }
                        }
                        
                        $counter++;
                        if ($counter >= $batch_size) {
                            $em->flush();
                            $em->clear();
                            $counter = 0;
                            
                            //Regrab the site since we cleared the EntityManager
                            $site = $this->siteLegacyRepository->getOneById($form_data['site_id']);
                            $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $form_data['program_id']);
                        }
                    }
                    $em->flush();
                }
                
                $site = $this->siteLegacyRepository->getOneById($form_data['site_id']);
                $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $form_data['program_id']);
                
                // merge default departments
                if ($site->type == 'clinical') {
                    $allDefaults = array(
                        "Cardiac Care Unit",
                        "Cardiac Cath. Lab",
                        "Clinic",
                        "ER",
                        "ICU",
                        "IV Team",
                        "Labor & Delivery",
                        "Neonatal ICU",
                        "OR",
                        "Post Op",
                        "Pre Op",
                        "Psychiatric Unit",
                        "Respiratory Therapy",
                        "Triage",
                        "Urgent Care",
                        "Anesthesia",
                        "Burn Unit"
                    );
                    
                    // grab ALL bases from the database
                    // since we already saved the share, this will include any duplicate default departments
                    $bases = BaseLegacy::getBases($site->id, $program->id, false, true) + BaseLegacy::getBases($site->id, $program->id, true, true);
                    // grab all the default departments from this group
                    $defaults = array_intersect($bases, $allDefaults);

                    // go through all the bases and see if there are any defaults that need to be merged
                    $baseRepo = \Fisdap\EntityUtils::getRepository('BaseLegacy');
                    foreach ($bases as $base_id => $name) {
                        // see if this base is a default
                        if (in_array($name, $defaults)) {
                            // grab the first default with this name from the list
                            $first_def_id = array_search($name, $defaults);
                            // if this base id is different from the first one in the list, it's a duplicate
                            if ($base_id != $first_def_id) {
                                $baseRepo->mergeBases($first_def_id, $base_id);
                                unset($defaults[$base_id]);
                            }
                        }
                    }
                    $em->flush();
                }
                
                // send the notification
                $user = User::getLoggedInUser();
                $mail = new \Fisdap_TemplateMailer();
                $mail->addTo($program->getProgramContact()->email)
                     ->setSubject("Sharing approved")
                     ->setViewParam("name", $user->getName())
                     ->setViewParam("email", $user->email)
                     ->setViewParam("site", $site->name)
                     ->sendHtmlTemplate("sharing-approved.phtml");

                // now loop through all the global requirements for this site and assign to users who are attending a future shift at this site
                // and add those users to the list for updating compliance
                $req_repo = \Fisdap\EntityUtils::getRepository("Requirement");
                $globalRequirements = $req_repo->getGlobalRequirementsBySite($site->id, $program->id);
                $users_to_recompute = array();
                /** @var Requirement $requirement */
                foreach ($globalRequirements as $requirement) {
                    $sendNotification = $program->sendNewRequirementNotification($requirement->id);
                    $user_contexts_from_site = $this->siteLegacyRepository->getUserContextsAttendingSites(array($site_id), $program);
                    $users_to_recompute = $requirement->assignRequirementToUserContexts(
                        $user_contexts_from_site,
                                     new DateTime(),
                                     $users_to_recompute,
                                     $sendNotification
                    );
                    $requirement->save();
                }
            } elseif ($form_data['admin'] == 0) {
                // if this is an EXISTING program that's being demoted from admin,
                // deal with any requirements shared by this program
                $req_repo = \Fisdap\EntityUtils::getRepository("Requirement");
                $globalRequirements = $req_repo->getGlobalRequirementsBySite($site->id, $program->id);
                $users_to_recompute = array();
                $converted_reqs = 0;
                foreach ($globalRequirements as $requirement) {
                    $associations = $req_repo->getSiteRequirementAssociations($requirement->id, $site->id, $program->id, true);
                    foreach ($associations as $association) {
                        if ($association->global && $association->program->id == $program->id) {
                            // since this program is no longer an admin, unshare this req
                            $association->global = 0;
                            $association->save();
                            $converted_reqs++;
                        }
                    }
                }
                
                // if there were some shared reqs that needed to be removed, we'll need to recalculate compliance for
                // all users in the network
                if ($converted_reqs > 0) {
                    $user_contexts_from_site = $this->siteLegacyRepository->getUserContextsAttendingSharedSite($site->id);
                    foreach ($user_contexts_from_site as $user_context) {
                        $users_to_recompute[] = $user_context->id;
                    }
                }
            }
            $users_to_recompute = array_unique($users_to_recompute);
            
            $em->flush();

            return array("new" => $new, "site_id" => $site->id, "compliance" => $users_to_recompute);
        }
            
        return $this->getMessages();
    }
}
