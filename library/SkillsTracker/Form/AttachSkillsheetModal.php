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
 * This produces a modal form for adding/editing Airways
 */

/**
 * @package    SkillsTracker
 * @subpackage Forms
 */
class SkillsTracker_Form_AttachSkillsheetModal extends Fisdap_Form_Base
{
    public $groups = array();
    
    /**
     * @var array the decorators for the form
     */
    protected static $_formDecorators = array(
        'FormErrors',
        'PrepareElements',
        array('ViewScript', array('viewScript' => "settings/attach-skillsheet-modal.phtml"))
    );
    
    public function init()
    {
        parent::init();
        
        $programId = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
        $programEntity = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $programId);
        
        $this->groups = \Fisdap\EntityUtils::getRepository('CertificationLevel')->getAllCertificationLevelInfo($programEntity->profession->id);
        
        $this->addJsFile("/js/library/SkillsTracker/Form/skillsheet-modal.js");
        $this->addCssFile("/css/library/SkillsTracker/Form/skillsheet-modal.css");
        $this->setDecorators(self::$_formDecorators);
        
        // get the hooks for each certification level
        foreach ($this->groups as $group) {
            $evals = \Fisdap\EntityUtils::getRepository("EvalDefLegacy")->getEvalsByHook($this->getHookId($group['id']), $programId);

            $evalOptions = array();

            foreach ($evals as $eval) {
                $evalOptions[$eval['id']] = $eval['name'];
            }
            
            
            $skillsheets = new Zend_Form_Element_Select($group['abbreviation'] . '_skillsheet_select');
            $skillsheets->setMultiOptions($evalOptions)
                        ->setAttribs(array("class"=>"skillsheetSelect"))
                        ->setLabel("Skillsheet:");
            $this->addElements(array($skillsheets));
        }
        
        $defId = new Zend_Form_Element_Hidden("hiddenDefIdForSkillsheet");
        $this->addElements(array($defId));
    }
    
    public function getHookId($certLevelId)
    {
        $hookId = 0;
        switch ($certLevelId) {
            case 1:
                $hookId = 124;
                break;
            case 3:
                $hookId = 126;
                break;
            case 5:
                $hookId = 125;
                break;
            case 8:
                $hookId = 126;
                break;
            default:
                $hookId = 126;
                break;
        }
        
        return $hookId;
    }
}
