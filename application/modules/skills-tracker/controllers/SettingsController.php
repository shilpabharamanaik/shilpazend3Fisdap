<?php

use Fisdap\Data\Program\RequiredShiftEvaluations\ProgramRequiredShiftEvaluationsRepository;

/**
 *
 * @package    SkillsTracker
 * @subpackage Controllers
 */
class SkillsTracker_SettingsController extends Fisdap_Controller_SkillsTracker_Private
{
    //public function init()
    //{
    //	parent::init();
    //}
    //
    public function indexAction(ProgramRequiredShiftEvaluationsRepository $programReqRepo)
    {
        //Check permissions
        if (!$this->user->isInstructor()) {
            $this->displayError("You don't have permission to view this page.");
            return;
        } elseif (!$this->user->hasPermission("Edit Program Settings")) {
            $this->displayPermissionError("Edit Program Settings");
            return;
        }
        
        $this->view->headScript()->appendFile("/js/jquery.cluetip.js");
        $this->view->headLink()->appendStylesheet("/css/jquery.cluetip.css");
        
        $form = new SkillsTracker_Form_Settings();
        
        $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
        $this->view->labSkillsLabel = $program->hasSkillsPractice() ? "Skills Practice" : "Lab Skills";
        
        if ($this->getRequest()->isPost()) {
            $form->process($this->getRequest()->getPost());
            $this->flashMessenger->addMessage("Your changes have been saved.");
            $this->_redirect("/skills-tracker/settings/");
        } else {
            $this->view->pageTitle = "Skills Tracker Settings";
            $this->view->pageTitleLinkURL = "/skills-tracker/shifts";
            $this->view->pageTitleLinkText = "<< Back to Skills &amp; Patient Care";
            $this->view->form = $form;
            $this->view->skillsheetModal = new SkillsTracker_Form_AttachSkillsheetModal();
        }
        $this->view->customNarrativeModal = new SkillsTracker_Form_CustomNarrativeModal();
        $this->view->requireEvalsModal = new SkillsTracker_Form_RequireEvalsModal($programReqRepo);
    }
    
    public function eurekaTestAction()
    {
        $this->view->pageTitle = "Eureka Graph Testing";
        $this->view->eurekaModal = $this->view->eurekaModal();
    }
    
    public function getEurekaDataAction()
    {
        $defId = $this->_getParam('defId');
        $studentId = $this->_getParam('studentId');
        
        $dateRange = array("start_date" => $this->_getParam('startDate'),
                           "end_date"   => $this->_getParam('endDate')
                          );
        
        $items = \Fisdap\EntityUtils::getRepository('PracticeItem')->getItemsForReport($defId, $studentId, $dateRange);
        $eurekaViewHelper = new SkillsTracker_View_Helper_EurekaModal();
        
        $this->_helper->json($eurekaViewHelper->generateEurekaList($items, $defId, $studentId));
    }
    
    public function addPracticeDefinitionAction()
    {
        $catId = $this->_getParam('catId');
        $category = \Fisdap\EntityUtils::getEntity("PracticeCategory", $catId);

        $def = \Fisdap\EntityUtils::getEntity("PracticeDefinition");
        $def->program = \Fisdap\Entity\User::getLoggedInUser()->getCurrentProgram();
        $def->certification_level = $category->certification_level;
        $def->category = $category;
        $def->name = "New Practice Item";
        $def->active = 1;
        $def->save();
        
        $this->_helper->json($this->view->practiceDefinitionRow($category, $def, null));
    }
    
    public function autosaveAction()
    {
        $data = array();
        
        $form_data = $this->_getParam('form_data');
        $skills = $this->_getParam('skills');
        
        if ($skills) {
            foreach ($skills as $skillElement) {
                $elementName = substr($skillElement['name'], 0, -2);
                
                if (!$data[$elementName]) {
                    $data[$elementName] = array();
                }
                
                array_push($data[$elementName], $skillElement['value']);
            }
        }
        
        if ($form_data) {
            foreach ($form_data as $element) {
                $data[$element['name']] = $element['value'];
            }
        }
            
        $form = new SkillsTracker_Form_Settings();
        $form->process($data);
        $this->_helper->json(true);
    }
    
    public function addCategoryAction()
    {
        $certLevelId = $this->_getParam('certLevel');
        $cert = \Fisdap\EntityUtils::getEntity("CertificationLevel", $certLevelId);

        $practiceCategory = \Fisdap\EntityUtils::getEntity("PracticeCategory");
        $practiceCategory->program = \Fisdap\Entity\User::getLoggedInUser()->getCurrentProgram();
        $practiceCategory->certification_level = $cert;
        $practiceCategory->name = "New Category";
        $practiceCategory->save();
        
        $this->_helper->json($this->view->practiceCategoryTable($practiceCategory));
    }
    
    public function addSkillsheetAction()
    {
        $defId = $this->_getParam('defId');
        $skillsheetId = $this->_getParam('skillsheetId');
        
        $def = \Fisdap\EntityUtils::getEntity("PracticeDefinition", $defId);

        $def->skillsheet = $skillsheetId;
        $def->save();
        
        $this->_helper->json(true);
    }

    public function updatePpcpAction()
    {
        $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();

        $populator = new \Util_PracticePopulator();
        $populator->updatePPCPSkillsheets($program);

        $this->_helper->json(true);
    }

    public function updateSkillsheetsAction()
    {
        $program = \Fisdap\Entity\ProgramLegacy::getCurrentProgram();
        
        $populator = new \Util_PracticePopulator();
        $populator->updateSkillsheets($program);

        $this->updatePpcpAction();
    }
    
    public function numberOfPracticeItemsAction()
    {
        $number = count(\Fisdap\EntityUtils::getRepository('PracticeItem')->getAllByDefinition($this->_getParam('defId')));
        $this->_helper->json($number);
    }
    
    public function changeDefinitionNameAction()
    {
        $defId = $this->_getParam('defId');
        $newName = $this->_getParam('name');
        $def = \Fisdap\EntityUtils::getEntity("PracticeDefinition", $defId);
        
        $def->name = $newName;
        $def->save();
        
        $this->_helper->json($def->name);
    }
        
    public function generateCustomNarrativeFormAction()
    {
        $form = new SkillsTracker_Form_CustomNarrativeModal();
        $this->_helper->json($form->__toString());
    }
    
    public function saveCustomNarrativeSettingsAction()
    {
        $formValues = $this->_getAllParams();
        $form = new SkillsTracker_Form_CustomNarrativeModal();
        $this->_helper->json($form->process($formValues));
    }

    public function generateRequireEvalsFormAction(ProgramRequiredShiftEvaluationsRepository $programReqRepo)
    {
        $form = new SkillsTracker_Form_RequireEvalsModal($programReqRepo);
        $this->_helper->json($form->__toString());
    }

    public function saveRequireEvalsSettingsAction(ProgramRequiredShiftEvaluationsRepository $programReqRepo)
    {
        $formValues = $this->_getAllParams();
        $form = new SkillsTracker_Form_RequireEvalsModal($programReqRepo);
        $this->_helper->json($form->process($formValues));
    }

    public function narrativePreviewAction()
    {
        $data = $this->_getAllParams();
        $this->view->formatForm = $data;
        
        $section_ids = unserialize($data['section_ids']);
        $sections = array();
        
        foreach ($section_ids as $id) {
            $section = new \Fisdap\Entity\NarrativeSectionDefinition;

            $section->id = $id;
            $section->name = $data[$id.'_name'];
            $section->size = $data[$id.'_size'];
            $section->seeded = $data[$id.'_seed'];
            $section->active = $data[$id.'_active'];
            $section->section_order = $data[$id.'_order'];
            
            $sections[$section->section_order] = $section;
        }

        ksort($sections);
        
        $is_preview = true;
        
        $form = new SkillsTracker_Form_Narrative(null, null, $sections, $is_preview);
        $this->view->narrativeForm = $form->__toString();
        $this->view->headLink()->appendStylesheet("/css/blank.css");
        $this->view->headScript()->appendFile("/js/skills-tracker/settings/index.js");
    }
}
