<?php

use Fisdap\Api\Client\Auth\UserAuthorization;
use Fisdap\Api\Client\Scenarios\Gateway\ScenariosGateway;
use GuzzleHttp\Client;

class Exchange_ScenarioController extends Fisdap_Controller_Private
{
    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        $this->view->pageTitle = "Create a Scenario";
        
        $this->view->headScript()->appendFile("/js/library/Fisdap/Utils/create-pdf.js");
        
        $scenarioId = $this->_getParam('scenarioId', null);
        
        $scenario = null;
        $scenario = \Fisdap\EntityUtils::getEntity('Scenario', $scenarioId);
        
        // Create one on load so we always have a scenario ID to start saving skill metadata stuff to.
        if ($scenario->id == null) {
            $scenario = \Fisdap\EntityUtils::getEntity('Scenario');

            $scenario->patient = new \Fisdap\Entity\Patient();

            $scenario->patient->save();

            $scenario->author = \Fisdap\Entity\User::getLoggedInUser();
            
            $scenario->state = \Fisdap\EntityUtils::getEntity('ScenarioState', 1);
            
            $scenario->weight_unit = \Fisdap\EntityUtils::getEntity('WeightUnit', 2);
            
            $scenario->save();
        }

        
        $this->view->scenario = $scenario;
        
        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser();


        $logger = Zend_Registry::get('logger');

        $this->view->isStaff = $loggedInUser->isStaff();
        $this->view->canExport = ($loggedInUser->getCurrentUserContext()->hasPermission("Admin Exams") !== false ? true : false);

        // Different author than logged in user, and logged in user isn't a staff member
        if ($scenario->author->id != $loggedInUser->id && !$loggedInUser->isStaff()) {
            $this->view->canEdit = false;
            $repo = \Fisdap\EntityUtils::getRepository('ScenarioReview');
            
            $review = $repo->findOneBy(array('reviewer' => $loggedInUser, 'scenario' => $scenario));
            
            $this->view->userReview = $review;
            
            $this->view->canEdit = false;
        } else {
            $this->view->canEdit = true;
        }

        $form = new Exchange_Form_Scenario($scenario->id);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $post = $request->getPost();
            
            echo "<pre>" . print_r($post, true) . "</pre>";
            
        //$form->process($post);
            
            //$this->_redirect('/exchange/scenario/list');
        } else {
            $this->view->form = $form;
        }
    }

    public function exportAction()
    {
        $scenarioId = $this->_getParam('scenarioId', null);
        $container = Zend_Registry::get('container');
        $idmsConfig = $container->make('config')->get('idms');
        $idmsClient = new Client;
        $idmsResponse = $idmsClient->post(
            $idmsConfig['base_url'] . '/token',
            [
                'auth' => [
                    $idmsConfig['client_id'],
                    $idmsConfig['client_secret']
                ],
                'form_params' => [
                    'grant_type' => 'client_credentials',
                ]
            ]
        );

        $idmsResponse = json_decode($idmsResponse->getBody()->getContents(), true);

        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
        $userContextId = $loggedInUser->context()->getId();
        $userAuthorization = new UserAuthorization($idmsResponse['access_token'], $userContextId);

        $container->instance('Fisdap\Api\Client\Auth\UserAuthorization', $userAuthorization);

        /** @var ScenariosGateway $scenariosGateway */
        $scenariosGateway = $container->make(ScenariosGateway::class);
        $scenarioData = $scenariosGateway->exportToALSI($scenarioId);

        $this->_helper->redirector->gotoUrl($scenarioData['URL']);
    }

    public function saveReviewAction()
    {
        $scenarioId = $this->_getParam('scenarioId', null);
        $reviewId = $this->_getParam('reviewId', null);
        $reviewText = $this->_getParam('review', "");
        
        $reviewEnt = null;
        
        if ($reviewId) {
            $reviewEnt = \Fisdap\EntityUtils::getEntity('ScenarioReview', $reviewId);
        } else {
            $reviewEnt = \Fisdap\EntityUtils::getEntity('ScenarioReview');
        }
        
        $reviewEnt->scenario = \Fisdap\EntityUtils::getEntity('Scenario', $scenarioId);
        $reviewEnt->review = $reviewText;
        $reviewEnt->reviewer = \Fisdap\Entity\User::getLoggedInUser();
        
        $reviewEnt->save();
        
        $this->_redirect('/exchange/scenario/list');
    }
    
    public function listAction()
    {
        $this->view->pageTitle = "Submit a Scenario";
        
        // If the user is staff, provide a dropdown to change whose scenarios we should show.
        // Otherwise, show only the logged in users scenarios.
        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
        
        $this->view->isStaff = $loggedInUser->isStaff();
        
        // Get all submitted scenarios...
        $scenarios = \Fisdap\EntityUtils::getRepository('Scenario')->findAll();
        
        
        $userScenarios = array();
        $otherScenarios = array();
        
        $filterUserId = $this->_getParam('uid', false);
        if ($_GET['sort'] == 'status') {
            $sort = 'state';
            $scenarios = \Fisdap\EntityUtils::getRepository('Scenario')->getAllScenarios($sort);
        }
        if ($_GET['sort'] == 'title') {
            $sort = 'title';
            $scenarios = \Fisdap\EntityUtils::getRepository('Scenario')->getAllScenarios($sort);
        }
        
        foreach ($scenarios as $scenario) {
            if (true) {
                $userScenarios[] = $scenario;
            } else {
                if ($filterUserId) {
                    if ($scenario->author->id == $filterUserId) {
                        $otherScenarios[] = $scenario;
                    }
                } else {
                    $otherScenarios[] = $scenario;
                }
            }
        }
        
        $this->view->userScenarios = $userScenarios;
        $this->view->otherScenarios = $otherScenarios;
    }
    public function listSortedAction()
    {
        $this->view->pageTitle = "Submit a Scenario";
        $loggedInUser = \Fisdap\Entity\User::getLoggedInUser();
        
        $this->view->isStaff = $loggedInUser->isStaff();
        
        // Get all submitted scenarios...
        $scenarios = \Fisdap\EntityUtils::getRepository('Scenario')->findAll();
        
        $userScenarios = array();
        $otherScenarios = array();
        
        $filterUserId = $this->_getParam('uid', false);
        
        usort($scenarios, function ($a, $b) {
            if ($a->state < $b->state) {
                return 1;
            } elseif ($a->state > $b->state) {
                return -1;
            } else {
                return 0;
            }
        });
        
        foreach ($scenarios as $scenario) {
            if (true) {
                $userScenarios[] = $scenario;
            } else {
                if ($filterUserId) {
                    if ($scenario->author->id == $filterUserId) {
                        $otherScenarios[] = $scenario;
                    }
                } else {
                    $otherScenarios[] = $scenario;
                }
            }
        }
        
        $this->view->userScenarios = $userScenarios;
        $this->view->otherScenarios = $otherScenarios;
    }
    
    public function saveAddVitalAction()
    {
        $scenarioId = $this->_getParam('scenario_id');
    
        $form = new Exchange_Form_Scenario($scenarioId);
    
        $form->process($this->_getAllParams());
    
        $scenario = \Fisdap\EntityUtils::getEntity('Scenario', $scenarioId);
        
        $vitalEnt = new \Fisdap\Entity\Vital();
        
        $scenario->patient->addVital($vitalEnt);
        $scenario->patient->save();
        
        $this->_helper->json(true);
    }

    public function saveAction()
    {
        $scenarioId = $this->_getParam('scenario_id');
        
        $form = new Exchange_Form_Scenario($scenarioId);
        
        $form->process($this->_getAllParams());
        
        $this->_helper->json(true);
    }
    
    public function deleteAction()
    {
        $scenario = \Fisdap\EntityUtils::getEntity('Scenario', $this->_getParam('scenarioId'));
        
        if ($scenario->id) {
            $scenario->patient->delete(false);
            $scenario->delete();
        }
        
        $this->_redirect('/exchange/scenario/list');
    }
    
    public function toggleSkillAlsAction()
    {
        $skillId = $this->_getParam('skillId');
        $scenarioId = $this->_getParam('scenarioId');
        $state = $this->_getParam('state');
        $skillType = $this->_getParam('skillType');
        
        // Try to find an existing record to update
        $scenarioSkillData = $this->getScenarioSkill($scenarioId, $skillId, $skillType);
        
        $scenarioSkillData->is_als = ($state === "als")?true:false;
        
        $scenarioSkillData->save();
        
        $this->_helper->json(true);
    }
    
    public function setSkillPriorityAction()
    {
        $params = $this->_getAllParams();
        
        $scenarioId = $params['scenarioId'];
        
        list($skillType, $skillId) = explode('_', $params['skillId']);
        
        $scenarioSkillData = $this->getScenarioSkill($scenarioId, $skillId, $skillType);
        
        $scenarioSkillData->priority = $params['priority'];
        
        $scenarioSkillData->save();
        
        $this->_helper->json($params);
    }
    
    private function getScenarioSkill($scenarioId, $skillId, $skillType)
    {
        $repo = \Fisdap\EntityUtils::getRepository('ScenarioSkill');
        
        $scenarioSkillData = $repo->findOneBy(array('skill_id' => $skillId, 'skill_type' => $skillType));
        
        if (!$scenarioSkillData) {
            $scenarioSkillData = new \Fisdap\Entity\ScenarioSkill();
            $scenarioSkillData->scenario = \Fisdap\EntityUtils::getEntity('Scenario', $scenarioId);
            $scenarioSkillData->skill_id = $skillId;
            $scenarioSkillData->skill_type = $skillType;
        }
        
        return $scenarioSkillData;
    }
    
    public function deleteSkillAction()
    {
        $pieces = explode("_", $this->_getParam('id'));
        $entityName = $pieces[0];
        $id = $pieces[1];
    
        $skill = \Fisdap\EntityUtils::getEntity($entityName, $id);
    
        $skill->soft_deleted = 1;
        $skill->save();
    
        $this->_helper->json("<div>$entityName #$id successfully deleted. <a href='#' id='undo-delete-" . implode("_", $pieces) . "'>Undo!</a></div>");
    }
    
    public function undoDeleteSkillAction()
    {
        $pieces = explode("_", $this->_getParam('id'));
        $entityName = $pieces[0];
        $id = $pieces[1];
    
        $skill = \Fisdap\EntityUtils::getEntity($entityName, $id);
    
        if (!empty($skill->shift) && !$skill->shift->isEditable()) {
            $this->_helper->json(false);
            return;
        }
    
        $skill->soft_deleted = 0;
        $skill->save();
    
        $this->_helper->json(true);
    }
    
    public function duplicateSkillAction()
    {
        $id = $this->_getParam('id');
        $entityName = $this->_getParam('entityName');
        $scenarioId = $this->_getParam('scenarioId');
    
        $ent = \Fisdap\EntityUtils::getEntity($entityName, $id);
        
        $newEnt = clone($ent);
        $newEnt->save();
    
        // Add in a new ScenarioSkills record for this- then we can update the priority/ALS-BLS states for this new skill...
        $newScenarioSkillData = $this->getScenarioSkill($scenarioId, $newEnt->id, $entityName);
        $oldScenarioSkillData = $this->getScenarioSkill($scenarioId, $id, $entityName);
        
        $newScenarioSkillData->priority = $oldScenarioSkillData->priority;
        $newScenarioSkillData->is_als = $oldScenarioSkillData->is_als;
        
        $newScenarioSkillData->save();
        
        $list = $this->view->interventionList($ent->patient->id, null, false, "field", array("Iv", "Med", "Other", "Airway", "Cardiac"), "Exchange");
        
        $this->_helper->json($list);
    }
    
    public function hardDeleteSkillsAction()
    {
        $patientId = $this->_getParam('patientId');
        $skills = array();
    
        if ($patientId) {
            $skills = \Fisdap\EntityUtils::getRepository('Patient')->getSkillsByPatient($patientId);
            
            foreach ($skills as $skill) {
                if ($skill->soft_deleted) {
                    $skill->delete();
                }
            }
        }
    
        $this->_helper->json(true);
    }
    
    public function setSkillOrderAction()
    {
        $skills = $this->_getParam('ids');
        foreach ($skills as $order => $skill) {
            $pieces = explode("_", $skill);
            $entityName = $pieces[0];
            $id = $pieces[1];
    
            $ent = \Fisdap\EntityUtils::getEntity($entityName, $id);
            $ent->skill_order = $order;
            $ent->save(false);
        }
        
        \Fisdap\EntityUtils::getEntityManager()->flush();
    
    
        $this->_helper->json(true);
    }
}
