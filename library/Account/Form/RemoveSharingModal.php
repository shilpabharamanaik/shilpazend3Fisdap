<?php

use Fisdap\Data\Site\SiteLegacyRepository;
use Fisdap\Entity\ProgramLegacy;
use Fisdap\Entity\SiteLegacy;

/**
 * This produces a modal form for removing a program from shared scheduler
 *
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_RemoveSharingModal extends Fisdap_Form_BaseJQuery
{
    /**
     * @var SiteLegacyRepository
     */
    private $siteLegacyRepository;

    /**
     * @var ProgramLegacy
     */
    public $program;
    
    /**
     * @var SiteLegacy
     */
    public $site;
    
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
    
        parent::__construct();
    }
    
    public function init()
    {
        parent::init();
        $this->addJsFile("/js/jquery.sliderCheckbox.js");
        $this->addCssFile("/css/jquery.sliderCheckbox.css");
        
        $drop_students = new Zend_Form_Element_Checkbox('drop_students');
        $drop_students->setAttribs(array("class" => "slider-checkbox"));

        $program_id = new Zend_Form_Element_Hidden('program_id');
        $site_id = new Zend_Form_Element_Hidden('site_id');

        $this->addElements(array($program_id, $site_id, $drop_students));
        $this->setElementDecorators(self::$checkboxDecorators, array('drop_students'));
        $this->setElementDecorators(self::$hiddenDecorators, array('program_id', 'site_id'));

        // set defaults
        if ($this->program->id) {
            $this->setDefaults(array(
                'program_id' => $this->program->id,
                'site_id' => $this->site->id,
            ));
        }

        $this->setDecorators(array(
            'PrepareElements',
            array('ViewScript', array('viewScript' => "removeSharingModal.phtml")),
            'Form',
            array('DialogContainer', array(
                'id'          	=> 'removeSharingDialog',
                'class'         => 'removeSharingDialog',
                'jQueryParams' 	=> array(
                    'tabPosition' 	=> 'top',
                    'modal' 	=> true,
                    'autoOpen' 	=> false,
                    'resizable' 	=> false,
                    'width' 	=> 750,
                    'title'	 	=> 'Remove from network'
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
            
            $user = \Fisdap\Entity\User::getLoggedInUser();
            $em = \Fisdap\EntityUtils::getEntityManager();
            $removed_program_id = $form_data['program_id'];
            $removed_program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $removed_program_id);
            $site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $form_data['site_id']);
            $drop_students = $form_data['drop_students'];
            
            // get all the shared shifts for this site from today on
            $today = date("Y-m-d");
            $batch_size = 1000;
            $counter = 0;
            $network_events = \Fisdap\EntityUtils::getRepository("EventLegacy")->getNetworkEvents($removed_program->scheduler_beta, $site->id, $today);
            foreach ($network_events as $event_id) {
                $event = \Fisdap\EntityUtils::getEntity("EventLegacy", $event_id);
                
                // if this event belongs to this program, they take it with them
                if ($event->program->id == $removed_program_id) {
                    // remove this event from sharing for everybody
                    foreach ($event->event_shares as $event_share) {
                        $event_share->removeShare($drop_students, false);
                    }
                } else {
                    // otherwise, only remove this program from sharing (if it was even shared to begin with)
                    $event_share = $event->getEventShareByProgram($removed_program_id);
                    if ($event_share) {
                        $event_share->removeShare($drop_students, false);
                    }
                }
                //$event->save(false);
                
                $counter++;
                if ($counter >= $batch_size) {
                    $em->flush();
                    $em->clear();
                    $counter = 0;
                }
            }
            $em->flush();

            //Re-grab the site
            $site = \Fisdap\EntityUtils::getEntity("SiteLegacy", $form_data['site_id']);
            $removed_program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $removed_program_id);
            
            $share = $site->getShareByProgram($removed_program_id);
            
            $users_to_recompute = array();
            $converted_reqs = 0;
            if ($share->admin) {
                // if this is an EXISTING program that's being removed from admin,
                // deal with any requirements shared by this program
                $req_repo = \Fisdap\EntityUtils::getRepository("Requirement");
                $globalRequirements = $req_repo->getGlobalRequirementsBySite($site->id, $removed_program_id);
                foreach ($globalRequirements as $requirement) {
                    $associations = $req_repo->getSiteRequirementAssociations($requirement->id, $site->id, $removed_program_id, true);
                    foreach ($associations as $association) {
                        if ($association->global && $association->program->id == $removed_program_id) {
                            // since this program is no longer an admin, unshare this req
                            $association->global = 0;
                            $association->save();
                            $converted_reqs++;
                        }
                    }
                }
            }
            
            // delete the network sharing association
            $share->delete();

            // send the mail
            $user = \Fisdap\Entity\User::getLoggedInUser();
            $mail = new \Fisdap_TemplateMailer();
            $mail->addTo($removed_program->getProgramContact()->email)
                     ->setSubject("Sharing removed")
                 ->setViewParam("name", $user->getName())
                 ->setViewParam("email", $user->email)
                 ->setViewParam("site", $site->name)
                 ->sendHtmlTemplate("sharing-removed.phtml");
                 
            // we'll need to update compliance for this program, since the students won't get the global reqs anymore
            $users_to_recompute = array();

            // if there were some shared reqs that needed to be removed, we'll need to recalculate compliance for all users in the network
            if ($converted_reqs > 0) {
                $user_contexts_from_site = $this->siteLegacyRepository->getUserContextsAttendingSharedSite($site->id);
            } else {
                // otherwise, we can just do it for this program
                $user_contexts_from_site = $this->siteLegacyRepository->getUserContextsAttendingSites(array($site->id), $removed_program);
            }
            foreach ($user_contexts_from_site as $user_context) {
                $users_to_recompute[] = $user_context->id;
            }
            $users_to_recompute = array_unique($users_to_recompute);
            
            return array("site_id" => $site->id, "compliance" => $users_to_recompute);
        }
            
        return $this->getMessages();
    }
}
