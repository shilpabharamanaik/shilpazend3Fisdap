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
class Account_Form_Sharing extends Fisdap_Form_Base
{
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
     * @var array
     */
    public $associated_programs;
    
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
        $this->site = $site;
        $user = \Fisdap\Entity\User::getLoggedInUser();
        $this->program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $user->getProgramId());
        $this->sharedStatus = $this->program->getSharedStatus($this->site->id);
        
        $this->sharingPermissionsModal = new Account_Form_SharingPermissionsModal();
        $this->removeSharingModal = new Account_Form_RemoveSharingModal();
        
        parent::__construct($options);
    }
    
    
    public function init()
    {
        parent::init();
        $user = \Fisdap\Entity\User::getLoggedInUser();
        
        $this->addJsFile("/js/library/Account/Form/site-sub-forms/sharing.js");
        $this->addCssFile("/css/library/Account/Form/site-sub-forms/sharing.css");
        
        $nonsharingProgram = new Zend_Form_Element_Select('nonsharingProgram');
        $nonsharingProgram->setAttribs(array('size' => '10'))
             ->setLabel("Not Sharing")
             ->setAttribs(array('class' => 'inactiveList'))
             ->setRegisterInArrayValidator(false);

        $sharingProgram = new Zend_Form_Element_Select('sharingProgram');
        $sharingProgram->setAttribs(array('size' => '10'))
             ->setLabel("Sharing")
             ->setAttribs(array('class' => 'activeList'))
             ->setRegisterInArrayValidator(false);

        $isAdmin = $this->program->isAdmin($this->site->id);
        if (!$isAdmin  && !$user->isStaff()) {
            $nonsharingProgram->setAttribs(array('disabled' => 'disabled'));
            $sharingProgram->setAttribs(array('disabled' => 'disabled'));
        }

        $nonRequested = array();
        $this->associated_programs = $this->site->getAssociatedPrograms();
        foreach ($this->associated_programs as $associated_program) {
            if ($associated_program['admin']) {
                $program_name = "*".$associated_program['name'];
            } else {
                $program_name = $associated_program['name'];
            }

            if (strlen($program_name) > 45) {
                $program_name = substr($program_name, 0, 42)."...";
            }

            if ($associated_program['shared']) {
                $sharingProgram->addMultiOption($associated_program['id'], $program_name);
            } else {
                $nonsharingProgram->addMultiOption($associated_program['id'], $program_name);
                if (!$associated_program['pending']) {
                    $nonRequested[] = $associated_program['id'];
                }
            }
        }

        $nonRequestedPrograms = new Zend_Form_Element_Hidden('nonRequestedPrograms');
        
        $this->addElements(array(
            $nonsharingProgram,
            $sharingProgram,
            $nonRequestedPrograms
        ));
        
        $this->setElementDecorators(self::$hiddenDecorators, array('nonRequestedPrograms'));
        
        // Set the decorators for the form
        $this->setDecorators(array(
            'FormErrors',
            'PrepareElements',
            array('ViewScript', array('viewScript' => 'forms/site-sub-forms/sharing.phtml')),
            'Form'
        ));
        
        $this->setDefaults(array(
                "nonRequestedPrograms" => implode(', ', $nonRequested),
        ));
    }
    
    public function process($data)
    {
        return true;
    } // end process()
}
