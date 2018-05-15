<?php

use Fisdap\Data\Site\SiteLegacyRepository;
use Fisdap\Entity\Requirement;
use Fisdap\Entity\SiteLegacy;
use Fisdap\Entity\User;

/**
 * @author     Kate Hanson
 * @package    Account
 * @subpackage Forms
 */
class Account_Form_Requirements extends Fisdap_Form_Base
{
    /**
     * @var SiteLegacyRepository
     */
    private $siteLegacyRepository;

    /**
     * @var Fisdap\Entity\ProgramLegacy
     */
    public $program;
    
    /**
     * @var Fisdap\Entity\SiteLegacy
     */
    public $site;
    
    /**
     * @var integer
     */
    public $sharedStatus;
    
    /**
     * @var boolean
     */
    public $noReqs = false;
    
    /**
     * @var boolean
     */
    public $legacy = false;
    
    /**
     * @var array
     */
    public $program_associations = array();

    
    /**
     * @var array decorators for hidden elements
     */
    public static $hiddenDecorators = array(
            'ViewHelper',
    );


    /**
     * @param SiteLegacy $site the currrent site
     * @param $options mixed additional Zend_Form options
     */
    public function __construct($site, $options = null)
    {
        $this->siteLegacyRepository = \Fisdap\EntityUtils::getRepository('SiteLegacy');

        $this->site = $site;
        $user = User::getLoggedInUser();
        $this->program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $user->getProgramId());
        $this->sharedStatus = $this->program->getSharedStatus($this->site->id);

        parent::__construct($options);
    }
    
    
    public function init()
    {
        parent::init();
        $user = User::getLoggedInUser();
        $programId = $this->program->id;
        $sharing = ($this->sharedStatus == 3 || $this->sharedStatus == 4);
        $legacy = !$this->program->scheduler_beta;
        
        $this->addJsFile("/js/library/Account/Form/site-sub-forms/requirements.js");
        $this->addCssFile("/css/library/Account/Form/site-sub-forms/requirements.css");
        
        $availableRequirements = new Zend_Form_Element_Select('availableRequirements');
        $availableRequirements->setAttribs(array('size' => '10', multiple => true))
             ->setLabel("Available Site Requirements")
             ->setRegisterInArrayValidator(false);

        $activeRequirements = new Zend_Form_Element_Select('activeRequirements');
        $activeRequirements->setAttribs(array('size' => '10', multiple => true))
             ->setLabel("Required at ".$this->site->abbreviation)
             ->setRegisterInArrayValidator(false);

        // get the site requirements
        $req_repo = \Fisdap\EntityUtils::getRepository("Requirement");
        $site_requirements = $req_repo->getFormOptions($programId, false, true, true);
        
        if (!$site_requirements['Site'] && !$site_requirements['Shared']) {
            $this->noReqs = true;
        }

        // go through and add THIS program's requirements to the appropriate list
        foreach ($site_requirements['Site'] as $requirement_id => $requirement_name) {
            $associations = $req_repo->getSiteRequirementAssociations($requirement_id, $this->site->id, $programId, false);
            $this->program_associations = $associations;
            
            // if it's not associated with this site, add it to the available list
            if (count($associations) == 0) {
                $availableRequirements->addMultiOption($requirement_id, $requirement_name);
            } else {
                // otherwise, add each association to the appropriate list
                foreach ($associations as $association) {
                    if ($association->active) {
                        $option_id = $association->global ? $requirement_id."_shared" : $requirement_id;
                        $activeRequirements->addMultiOption($option_id, $requirement_name);
                    } else {
                        $availableRequirements->addMultiOption($requirement_id, $requirement_name);
                    }
                }
            }
        }
        
        // if appropriate, go through and add shared requirements
        if ($sharing) {
            foreach ($site_requirements['Shared'] as $requirement_id => $requirement_name) {
                $associations = $req_repo->getSiteRequirementAssociations($requirement_id, $this->site->id, $programId, true);
                foreach ($associations as $association) {
                    if ($association->active && $association->program->id != $programId) {
                        $option_id = $requirement_id."_shared_".$association->program->abbreviation."_".$association->program->id;
                        $activeRequirements->addMultiOption($option_id, $requirement_name);
                    }
                }
            }
        }
        
        // if this program doesn't use beta scheduler, show them the join beta message
        if ($legacy) {
            $module = 'scheduler';
            $viewscript = 'index/join-beta.phtml';
            $this->legacy = true;
        } else {
            $module = 'account';
            $viewscript = 'forms/site-sub-forms/requirements.phtml';
        }
        
        // add hidden selects
        $hiddenAvailable = new Zend_Form_Element_Select('availableRequirements_hidden');
        $hiddenAvailable->setAttribs(array('size' => '10', multiple => true))
             ->setLabel("Available Site Requirements")
             ->setRegisterInArrayValidator(false);

        $hiddenActive = new Zend_Form_Element_Select('activeRequirements_hidden');
        $hiddenActive->setAttribs(array('size' => '10', multiple => true))
             ->setLabel("Required at ".$this->site->abbreviation)
             ->setRegisterInArrayValidator(false);
             
        $this->addElements(array(
            $availableRequirements,
            $activeRequirements,
            $hiddenAvailable,
            $hiddenActive
        ));
        
        // Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => $viewscript,
                          'viewModule' => $module)),
            'Form'
        ));
    }
    
    public function process($data)
    {
        $compute_compliance_userContextIds = [];
        $site_id_array = array($this->site->id);

        $share_reqs = $data['share'];
        $unshare_reqs = $data['unshare'];
        
        // add some reqs
        if ($data['add']) {
            foreach ($data['add'] as $req_id) {
                
                // if this req is slated for sharing, go ahead and share it now not later
                $global = false;
                if ($share_reqs) {
                    if (($key = array_search($req_id, $share_reqs)) !== false) {
                        $global = true;
                        unset($share_reqs[$key]);
                    }
                }
                $requirement = \Fisdap\EntityUtils::getEntity("Requirement", $req_id);
                $requirement->createSiteAssociations($site_id_array, $this->program, $global);
                
                // and now assign to users who are attending a future shift at this site
                // and add all users attending this site in the future to the list for updating compliance
                $sendNotification = $this->program->sendNewRequirementNotification($requirement->id);

                if ($global) {
                    $user_contexts_from_site = $this->siteLegacyRepository->getUserContextsAttendingSharedSite($this->site->id);
                } else {
                    $user_contexts_from_site = $this->siteLegacyRepository->getUserContextsAttendingSites($site_id_array, $this->program);
                }

                /** @var Requirement $requirement */
                $compute_compliance_userContextIds = $requirement->assignRequirementToUserContexts(
                    $user_contexts_from_site,
                                                     new DateTime(),
                                                     $compute_compliance_userContextIds,
                                                     $sendNotification
                );
                $requirement->save();
            }
        }
        
        // remove some reqs
        if ($data['remove']) {
            foreach ($data['remove'] as $req_data) {
                $req_data = explode("_", $req_data);
                $req_id = $req_data[0];
                $global = ($req_data[1] == "shared") ? true : false;
                
                // remove the association
                $requirement = \Fisdap\EntityUtils::getEntity("Requirement", $req_id);
                $association = $requirement->getSiteAssocByProgram($this->site->id, $this->program->id);
                $requirement->requirement_associations->removeElement($association);
                $association->delete();
                $requirement->save();
                
                // if this req was slated for sharing, take it off that list since it's moot
                if ($share_reqs) {
                    if (($key = array_search($req_data, $share_reqs)) !== false) {
                        unset($share_reqs[$key]);
                    }
                }

                // if this req was slated for unsharing, take it off that list since it's moot
                if ($unshare_reqs) {
                    if (($key = array_search($req_data, $unshare_reqs)) !== false) {
                        unset($unshare_reqs[$key]);
                    }
                }
                
                // figure out which users this will affect
                if ($global) {
                    $user_contexts_from_site = $this->siteLegacyRepository->getUserContextsAttendingSharedSite($this->site->id);
                } else {
                    $user_contexts_from_site = $this->siteLegacyRepository->getUserContextsAttendingSites($site_id_array, $this->program);
                }

                $userContextIds = [];

                foreach ($user_contexts_from_site as $user_context) {
                    $userContextIds[] = $user_context->id;
                }
 
                $compute_compliance_userContextIds = array_merge($compute_compliance_userContextIds, $userContextIds);
            }
        }

        // share some reqs
        if ($share_reqs) {
            foreach ($share_reqs as $req_id) {
                /** @var Requirement $requirement */
                $requirement = \Fisdap\EntityUtils::getEntity("Requirement", $req_id);
                $association = $requirement->getSiteAssocByProgram($this->site->id, $this->program->id);
                $association->global = true;
    
                // assign to network users who are attending a future shift at this site
                // and add all users attending this site in the future to the list for updating compliance
                $sendNotification = $this->program->sendNewRequirementNotification($requirement->id);
                $user_contexts_from_site = $this->siteLegacyRepository->getUserContextsAttendingSharedSite($this->site->id);
                $compute_compliance_userContextIds = $requirement->assignRequirementToUserContexts(
                    $user_contexts_from_site,
                                                     new DateTime(),
                                                     $compute_compliance_userContextIds,
                                                     $sendNotification
                );
                $requirement->save();
            }
        }
        
        // unshare some reqs
        if ($data['unshare']) {
            foreach ($data['unshare'] as $req_data) {
                $req_data = explode("_", $req_data);
                $req_id = $req_data[0];
                // this association may not belong to the user's program
                $association_program_id = ($req_data[3]) ? $req_data[3] : $this->program->id;
                $requirement = \Fisdap\EntityUtils::getEntity("Requirement", $req_id);
                $association = $requirement->getSiteAssocByProgram($this->site->id, $association_program_id);
                $association->global = false;
                $requirement->save();
            
                // figure out which users this will affect
                $user_contexts_from_site = $this->siteLegacyRepository->getUserContextsAttendingSharedSite($this->site->id);
                $userContextIds = array();
                foreach ($user_contexts_from_site as $user_context) {
                    $userContextIds[] = $user_context->id;
                }
 
                $compute_compliance_userContextIds = array_merge($compute_compliance_userContextIds, $userContextIds);
            }
        }
        
        return array_unique($compute_compliance_userContextIds);
    } // end process()
}
