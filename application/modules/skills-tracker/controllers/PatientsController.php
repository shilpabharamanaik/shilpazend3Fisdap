<?php

class SkillsTracker_PatientsController extends Fisdap_Controller_SkillsTracker_Private
{

    public function init()
    {
		$this->view->pageTitle = "Patient Care";
		
		parent::init();
    }

    public function indexAction()
    {
		$runId = $this->_getParam('runId');
		
		// is there a run?
		if (!$runId) {
			$noRun = true;
		} else {
			$run = \Fisdap\EntityUtils::getEntity('Run', $runId);
			if (is_null($run)) {
				$noRun = true;
			}
		}
		
		if ($noRun) {
			$this->displayError('You\'ve reached this page in error. No Run ID found.');
			return;
		}
		
		$run->student->id;
		if (!$run->student->dataCanBeViewedBy()) {
			$this->displayError('You currently do not have permission to access the requested page.');
			return;
		}
		
		$patient = \Fisdap\EntityUtils::getRepository('Patient')->findOneByRun($runId);
		$patientId = ($patient) ? $patient->id : null;
		
		$signoffId = $run->signoff->id;
		$shiftId = $run->shift->id;
		
		$this->view->patientId = $patientId;
		
		if($patient->run->verification->verified){
			$this->view->runHasBeenVerified = true;
		}else{
			$this->view->runHasBeenVerified = false;
		}

		$shift_summary_display_helper = new Fisdap_View_Helper_ShiftSummaryDisplayHelper();
		$summary_options = array("show_icon" => true);
		$this->view->shiftInfo = $shift_summary_display_helper->shiftSummaryDisplayHelper(null, null, $run->shift, $summary_options);

		//Check the permissions for the logged in user
		$this->checkPermissions($runId);
		
		$this->view->shiftUrl = "/skills-tracker/shifts/my-shift/shiftId/" . $shiftId;
        $shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $this->_getParam('shiftId'));

		$this->view->shift = $shift;

		$new = $this->_getParam('new');
		$patientForm = new SkillsTracker_Form_PatientCare($patientId, $runId, $new);
		$this->view->patientForm = $patientForm;
		$this->view->narrativeForm = new SkillsTracker_Form_Narrative($patient->narrative->id, $patientId);

        $shiftAttachmentsGateway = $this->container->make('Fisdap\Api\Client\Shifts\Attachments\Gateway\ShiftAttachmentsGateway');
		$this->view->signoffForm = new SkillsTracker_Form_Signoff($signoffId, $runId, null, $shiftAttachmentsGateway);
		$this->view->preceptorWidget = $this->view->addPreceptorWidget($run->student->id, $run->shift->site->id);
		
		//Add intervention modals to view
		$this->view->airwayModal = new SkillsTracker_Form_AirwayModal();
		$this->view->cardiacModal = new SkillsTracker_Form_CardiacModal();
		$this->view->ivModal = new SkillsTracker_Form_IvModal();
		$this->view->otherModal = new SkillsTracker_Form_OtherModal();
		$this->view->medModal = new SkillsTracker_Form_MedModal();
		$this->view->vitalModal = new SkillsTracker_Form_VitalModal();

		$this->view->program = \Fisdap\Entity\User::getLoggedInUser()->getCurrentRoleData()->program;
		$request = $this->getRequest();
		
		if ($request->isPost()) {
			$post = $request->getPost();

			switch($post['formName']) {
				case "PatientCare":
					$result = $patientForm->process($request->getPost());
			
					if ($result instanceof Zend_Form) {
						$this->view->patientForm = $result;
					} else {
						$this->_redirect('/skills-tracker/patients/index/runId/' . $runId . "#patientCare");
					}
					break;
				case "Signoff":
					$result = $this->view->signoffForm->process($request->getPost());
			
					if ($result instanceof Zend_Form) {
						$this->view->signoffForm = $result;
					} else {
						$this->_redirect('/skills-tracker/patients/index/runId/' . $runId . "#signoff");
					}
					break;
				case "Narrative":
					
					break;
			}
		}
    }
	
	public function savePatientCareAjaxAction()
	{
		$runId = $this->_getParam('runId');
		$run = \Fisdap\EntityUtils::getEntity('Run', $runId);
		
		//Make sure the shift we're working with is editable
		if (!$run->shift->isEditable()) {
			$this->_helper->json(false);
			return;
		}
		
		$patient = \Fisdap\EntityUtils::getRepository('Patient')->findOneByRun($runId);
		$patientId = ($patient) ? $patient->id : null;
		
		$patientForm = new SkillsTracker_Form_PatientCare($patientId, $runId);
		
		$result = $patientForm->process($this->getRequest()->getPost());
		
		if ($result) {
			$patient = \Fisdap\EntityUtils::getRepository('Patient')->findOneByRun($runId);
			
			$this->_helper->json($patient->id);
		}
	}
	
	public function saveNarrativeAjaxAction()
	{
		$patientId = $this->_getParam('patientId');
		$patient = \Fisdap\EntityUtils::getEntity('Patient', $patientId);
		
		if (!empty($patient->shift) && !$patient->shift->isEditable()) {
			$this->_helper->json(false);
			return;
		}
		
		$narrativeForm = new SkillsTracker_Form_Narrative($patient->narrative->id);
		$result = $narrativeForm->process($this->getRequest()->getPost());
		
		if ($result) {
			$this->_helper->json($patient->narrative->id);
		}
	}
	
	public function saveSignoffAjaxAction()
	{
		$params = $this->_getAllParams();
		$run = \Fisdap\EntityUtils::getEntity('Run', $params['runId']);
		
		//Make sure the shift we're working with is editable
		if (!is_null($run->shift) && !is_null($run->shift->isViewable())) {
			if (!$run->shift->isViewable()) {
				$this->_helper->json(false);
				return;
			}
		}
		
		$signoffForm = new SkillsTracker_Form_Signoff($params['signoffId'], $params['runId']);
		$result = $signoffForm->process($params);

		$this->_helper->json($result);
	}
	
	public function generateInterventionListAction()
	{
		$patientId = $this->_getParam('patientId');
		$shiftId = $this->_getParam('shiftId');
		
		// If we only got a patient ID and not a shift ID, assume we're trying to reload this widget on the exchange scenario page.
		// Otherwise we'll treat it as if it were coming from the skills-tracker patient care page.
		if($shiftId > 0){
			$list = $this->view->interventionList($patientId, $shiftId);
		}else{
			$list = $this->view->interventionList($patientId, null, false, "field", array("Iv", "Med", "Other", "Airway", "Cardiac"), "Exchange");
		}
		
		$this->_helper->json($list);
	}
	
	public function generateAirwayFormAction()
	{
		$airwayId = $this->_getParam('airwayId');
		$clinical_quick_add = ($this->_getParam('clinical_quick_add_airway_modal') === "true") ? true : false;
		
		$form = new SkillsTracker_Form_AirwayModal($airwayId, $clinical_quick_add);
		
		$this->_helper->json($form->__toString());
	}
	
	public function validateAirwayAction()
	{
		$formValues = $this->_getAllParams();
		$clinical_quick_add = ($this->_getParam('clinical_quick_add_airway_modal') === "true") ? true : false;
		
		$form = new SkillsTracker_Form_AirwayModal($formValues['hiddenId'], $clinical_quick_add);
		
		$this->_helper->json($form->process($formValues));
	}
	
	public function generateCardiacFormAction()
	{
		$cardiacId = $this->_getParam('cardiacId');
		$form = new SkillsTracker_Form_CardiacModal($cardiacId);
		
		$this->_helper->json($form->__toString());
	}
	
	public function validateCardiacAction()
	{
		$formValues = $this->_getAllParams();
		$form = new SkillsTracker_Form_CardiacModal($formValues['hiddenId']);
		
		$this->_helper->json($form->process($formValues));
	}
	
	public function generateIvFormAction()
	{
		$ivId = $this->_getParam('ivId');
		$form = new SkillsTracker_Form_IvModal($ivId);
		
		$this->_helper->json($form->__toString());
	}
	
	public function validateIvAction()
	{
		$formValues = $this->_getAllParams();
		$form = new SkillsTracker_Form_IvModal($formValues['hiddenId']);
		
		$this->_helper->json($form->process($formValues));
	}
	
	public function generateMedFormAction()
	{
		$medId = $this->_getParam('medId');
		$form = new SkillsTracker_Form_MedModal($medId);
		
		$this->_helper->json($form->__toString());
	}
	
	public function validateMedAction()
	{
		$formValues = $this->_getAllParams();
		$form = new SkillsTracker_Form_MedModal($formValues['hiddenId']);
		
		$this->_helper->json($form->process($formValues));
	}
	
	public function generateOtherFormAction()
	{
		$otherId = $this->_getParam('otherId');
		$form = new SkillsTracker_Form_OtherModal($otherId);
		
		$this->_helper->json($form->__toString());
	}
	
	public function validateOtherAction()
	{
		$formValues = $this->_getAllParams();
		$form = new SkillsTracker_Form_OtherModal($formValues['hiddenId']);
		
		$this->_helper->json($form->process($formValues));
	}
	
	public function generateVitalFormAction()
	{
		$vitalId = $this->_getParam('vitalId');
		$form = new SkillsTracker_Form_VitalModal($vitalId);
		
		$this->_helper->json($form->__toString());
	}
	
	public function validateVitalAction()
	{
		$formValues = $this->_getAllParams();
		$form = new SkillsTracker_Form_VitalModal($formValues['hiddenId']);
		
		$this->_helper->json($form->process($formValues));
	}
	
	
	public function deleteSkillAction()
	{
		$pieces = explode("_", $this->_getParam('id'));
		$entityName = $pieces[0];
		$id = $pieces[1];
		
		$skill = \Fisdap\EntityUtils::getEntity($entityName, $id);
		
		if (!$skill->shift->isEditable()) {
			$this->_helper->json(false);
			return;
		}
		
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
		
		if (!$skill->shift->isEditable()) {
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
		$patientId = $this->_getParam('patientId');
		$shiftId = $this->_getParam('shiftId');
		$skills = array();
		
		if ($patientId) {
			$skills = \Fisdap\EntityUtils::getRepository('Patient')->getSkillsByPatient($patientId);
		} else if ($shiftId) {
			$skills = \Fisdap\EntityUtils::getRepository('ShiftLegacy')->getSkillsByShift($shiftId, array('shiftOnly' => true));
		}
		$ent = \Fisdap\EntityUtils::getEntity($entityName, $id);
		
		if (!$ent->shift->isEditable()) {
			$this->_helper->json(false);
			return;
		}
		
		$newEnt = clone($ent);
		$newEnt->skill_order =  array_pop($skills)->skill_order + 1;
		$newEnt->save();
		
		if($entityName == "Airway"){
			if($ent->airway_management){
				$new_airway_management = clone($ent->airway_management);
				$new_airway_management->airway = $newEnt;
				$new_airway_management->save();
			}
		}
		
		if ($ent->patient->id) {
			$list = $this->view->interventionList($ent->patient->id, $ent->shift->id);			
		} else if ($ent->shift->id) {
			$list = $this->view->interventionList(null, $ent->shift->id);
		}
		
		$this->_helper->json($list);
	}
	
	public function hardDeleteSkillsAction()
	{
		$patientId = $this->_getParam('patientId');
		$shiftId = $this->_getParam('shiftId');
		$skills = array();
		
		if ($patientId) {
			$skills = \Fisdap\EntityUtils::getRepository('Patient')->getSkillsByPatient($patientId);
		} else if ($shiftId) {
			$skills = \Fisdap\EntityUtils::getRepository('ShiftLegacy')->getSkillsByShift($shiftId, array('shiftOnly' => true));
		}
		
		foreach ($skills as $skill) {
			if ($skill->soft_deleted) {
				$skill->delete();
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
		$this->em->flush();
		
		
		$this->_helper->json(true);
	}
	
	public function unverifyAction()
	{
		$runId = $this->_getParam('runId');
		
		if ($runId) {
			$run = \Fisdap\EntityUtils::getEntity('Run', $runId);
            $patient = \Fisdap\EntityUtils::getRepository('Patient')->findOneByRun($runId);

			if (!$run->shift->isEditable()) {
				$this->_helper->json(false);
				return;
			}

			//check for and clear any connection between verification/attachment
			$shiftAttachment = $run->verification->getShiftAttachment();

			if ($shiftAttachment) {
			    $shiftAttachment->removeVerification($run->verification);
			    $run->verification->setShiftAttachment(null);
            }
			
			// m990 - need to unlock the run if it gets unverified.
            $patient->set_verification(null);
			$run->set_verification(null);
			$run->locked = false;
            // Also need to unlock the patient.
            $patient->setLocked(false);
			
			$run->save();
		}
		
		$this->_helper->json(true);
	}
	
	public function generatePatientIdAction()
	{
		$runId = $this->_getParam('runId');
		
		$patient = \Fisdap\EntityUtils::getEntity('Patient');
		
		$run = \Fisdap\EntityUtils::getEntity('Run', $runId);
		
		if (!$run->shift->isEditable()) {
			$this->_helper->json(false);
			return;
		}
		
		$run->addPatient($patient);
		$run->save();
		
		$this->_helper->json($patient->id);
	}
	
	public function validatePreceptorAction()
	{
		$formValues = $this->_getAllParams();
		$form = new SkillsTracker_Form_AddPreceptor($formValues['studentId'], $formValues['siteId']);
		
		$this->_helper->json($form->process($formValues));
	}
	
	public function getNarrativeAction()
	{
		//xdebug_break();
		$patientId = $this->_getParam('patientId');
		
		// Make sure we actually have a patient saved down- otherwise this will
		// fail.
		if ($patientId) {

			$patient = \Fisdap\EntityUtils::getEntity('Patient', $patientId);
			$text_boxes = array();

			if($patient->narrative == null) {
			    $narrative = \Fisdap\EntityUtils::getEntity('Narrative');
			    $patient->set_narrative($narrative);
			    $seed_text = $narrative->getNarrativeSeed();
			    $program_id = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
			    $sections = \Fisdap\EntityUtils::getRepository('NarrativeSectionDefinition')->getNarrativeSectionsByProgram($program_id, true);
			    foreach ($sections as $section) {
				$tb_id = $section->id."_text";
				if ($section->seeded) {
				    $text_boxes[$tb_id] = $seed_text;
				} else {
				    $text_boxes[$tb_id] = "";
				}
			    }
			} else {
			    $narrative = $patient->narrative;
			    $seed_text = $narrative->getNarrativeSeed();
			    foreach ($narrative->sections as $section) {
			        $tb_id = $section->definition->id."_text";
			        $saved_text = $section->section_text;
			        if (($saved_text == '') && $section->definition->seeded) {
			            $text_boxes[$tb_id] = $seed_text;
			        } else {
				    $text_boxes[$tb_id] = $saved_text;
			        }
			    }
			}
			
			return $this->_helper->json($text_boxes);
		} else {
			return $this->_helper->json(array());
		}
	}
	
	public function generateSummarySeedAction()
	{
		$runId = $this->_getParam('runId');
		$run = \Fisdap\EntityUtils::getEntity('Run', $runId);
		
		$seed = null;
		
		if ($run->patients->first()->id) {
			$seed = $run->patients->first()->generateSummarySeed();
		}
		
		$this->_helper->json($seed);
	}
	
	/**
	 * Check to see if a shift has been locked
	 * @param integer $runId the ID of the run
	 */
	private function checkPermissions($runId)
	{
		$run = \Fisdap\EntityUtils::getEntity('Run', $runId);
		
		if ($this->user->getCurrentRoleName() == 'student') {
			if ($run->locked && $run->shift->locked) {
				$this->displayError("This shift and run have been locked. In order to add patient care information, please contact your instructor in order to unlock them both.");
			} else if ($run->locked) {
				$this->displayError("This run has been locked. In order to add patient care information, please contact your instructor in order to unlock it.");
			} else if ($run->shift->locked) {
				$this->displayError("This shift has been locked. In order to add patient care information, please contact your instructor in order to unlock it.");				
			}
		}
		
		if ($run->shift->isFuture()) {
			$this->displayError("You cannot add data to a shift in the future.");
			return;
		}
	}
}