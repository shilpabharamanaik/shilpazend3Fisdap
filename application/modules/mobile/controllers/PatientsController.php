<?php

class Mobile_PatientsController extends Mobile_Controller_SkillsTrackerPrivate
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
	{
		$this->view->run = \Fisdap\EntityUtils::getEntity('Run', $this->_getParam('runId'));
		
		if($this->view->run){
			$this->view->patient = $this->view->run->patients->first();
		}else{
			$this->view->patient = null;
		}

		if (!$this->view->patient->id) {
			$this->_redirect('/mobile/patients/patient/runId/' . $this->view->run->id);
			return;
		}
		
		if (!$this->view->run->shift->isViewable()) {
			$this->displayError("You do not have permission to view this patient.", "mobile");
			return;
		}
		
		$this->view->messages = $this->flashMessenger->getMessages();
    }

    public function patientAction()
    {
        $run = \Fisdap\EntityUtils::getEntity('Run', $this->_getParam('runId'));
		if (!$run->id) {
			$shift = \Fisdap\EntityUtils::getEntity('ShiftLegacy', $this->_getParam('shiftId'));
			
			if (!$shift->id) {
				$this->displayError("You do not have permission to edit this patient.", "mobile");
				return;
			}
			
			$run = \Fisdap\EntityUtils::getEntity('Run');
			$shift->addRun($run);
			$shift->save();
		}
        
        if ($run->id && !$this->canEditData($run->id, "Run")) {
            $this->displayError("You do not have permission to edit this patient.", "mobile");
            return;
        }
        
		$patientId = $run->patients->first()->id;
        
		$this->view->form = new Mobile_Form_Patient($patientId, $run->id);
        $this->view->preceptorWidget = $this->view->addPreceptorWidget($run->student->id, $run->shift->site->id);

		$request = $this->getRequest();

		if ($request->isPost()) {
			$result = $this->view->form->process($request->getPost());

			if ($result instanceof Zend_Form) {
				$this->view->form = $result;
			} else {
				$this->_redirect('/mobile/patients/index/runId/' . $run->id);
			}
		}
	}

    public function vitalsAction()
    {
        $vitalId = $this->_getParam('id');
        $patientId = $this->_getParam('patientId');
        $shiftId = $this->_getParam('shiftId');
        
        if ($vitalId && !$this->canEditData($vitalId, "Vital")) {
            $this->displayError("You do not have permission to edit these vital signs.", "mobile");
            return;
        }
        
        if ($patientId && !$this->canEditData($patientId, "Patient")) {
            $this->displayError("You do not have permission to edit this patient.", "mobile");
            return;
        }
        
        if ($shiftId && !$this->canEditData($shiftId, "ShiftLegacy")) {
            $this->displayError("You do not have permission to edit this shift.", "mobile");
            return;
        }
        
        $this->view->form = new Mobile_Form_Vitals($vitalId, $patientId, $shiftId);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $result = $this->view->form->process($request->getPost());
            
            if ($result instanceof Zend_Form) {
                $this->view->form = $result;
            } else {
                $vital = \Fisdap\EntityUtils::getEntity('Vital', $result);
                $this->_redirect("/mobile/patients/index/runId/" . $vital->run->id);
            }
        }
    }

    public function airwaysAction()
    {
        $airwayId = $this->_getParam('id');
        $patientId = $this->_getParam('patientId');
        $shiftId = $this->_getParam('shiftId');
        
        if ($airwayId && !$this->canEditData($airwayId, "Airway")) {
            $this->displayError("You do not have permission to edit this airway.", "mobile");
            return;
        }
        
        if ($patientId && !$this->canEditData($patientId, "Patient")) {
            $this->displayError("You do not have permission to edit this patient.", "mobile");
            return;
        }
        
        if ($shiftId && !$this->canEditData($shiftId, "ShiftLegacy")) {
            $this->displayError("You do not have permission to edit this shift.", "mobile");
            return;
        }
        
        $this->view->form = new Mobile_Form_Airways($airwayId, $patientId, $shiftId);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $result = $this->view->form->process($request->getPost());
            
            if ($result instanceof Zend_Form) {
                $this->view->form = $result;
            } else {
                $airway = \Fisdap\EntityUtils::getEntity('Airway', $result);
                $this->_redirect("/mobile/patients/index/runId/" . $airway->run->id);
            }
        }
    }

    public function cardiacInterventionsAction()
    {
        $cardiacId = $this->_getParam('id');
        $patientId = $this->_getParam('patientId');
        $shiftId = $this->_getParam('shiftId');
        
        if ($cardiacId && !$this->canEditData($cardiacId, "CardiacIntervention")) {
            $this->displayError("You do not have permission to edit this cardiac intervention.", "mobile");
            return;
        }
        
        if ($patientId && !$this->canEditData($patientId, "Patient")) {
            $this->displayError("You do not have permission to edit this patient.", "mobile");
            return;
        }
        
        if ($shiftId && !$this->canEditData($shiftId, "ShiftLegacy")) {
            $this->displayError("You do not have permission to edit this shift.", "mobile");
            return;
        }
        
        $this->view->form = new Mobile_Form_CardiacInterventions($cardiacId, $patientId, $shiftId);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $result = $this->view->form->process($request->getPost());
            
            if ($result instanceof Zend_Form) {
                $this->view->form = $result;
            } else {
                $cardiac = \Fisdap\EntityUtils::getEntity('CardiacIntervention', $result);
                $this->_redirect("/mobile/patients/index/runId/" . $cardiac->run->id);
            }
        }
    }

    public function ivsAction()
    {
        $ivId = $this->_getParam('id');
        $patientId = $this->_getParam('patientId');
        $shiftId = $this->_getParam('shiftId');
        
        if ($ivId && !$this->canEditData($ivId, "Iv")) {
            $this->displayError("You do not have permission to edit this IV.", "mobile");
            return;
        }
        
        if ($patientId && !$this->canEditData($patientId, "Patient")) {
            $this->displayError("You do not have permission to edit this patient.", "mobile");
            return;
        }
        
        if ($shiftId && !$this->canEditData($shiftId, "ShiftLegacy")) {
            $this->displayError("You do not have permission to edit this shift.", "mobile");
            return;
        }
        
        $this->view->form = new Mobile_Form_Ivs($ivId, $patientId, $shiftId);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $result = $this->view->form->process($request->getPost());
            
            if ($result instanceof Zend_Form) {
                $this->view->form = $result;
            } else {
                $iv = \Fisdap\EntityUtils::getEntity('Iv', $result);
                $this->_redirect("/mobile/patients/index/runId/" . $iv->run->id);
            }
        }
    }
    
    public function otherInterventionsAction()
    {
        $otherId = $this->_getParam('id');
        $patientId = $this->_getParam('patientId');
        $shiftId = $this->_getParam('shiftId');
        
        if ($otherId && !$this->canEditData($otherId, "OtherIntervention")) {
            $this->displayError("You do not have permission to edit this other intervention.", "mobile");
            return;
        }
        
        if ($patientId && !$this->canEditData($patientId, "Patient")) {
            $this->displayError("You do not have permission to edit this patient.", "mobile");
            return;
        }
        
        if ($shiftId && !$this->canEditData($shiftId, "ShiftLegacy")) {
            $this->displayError("You do not have permission to edit this shift.", "mobile");
            return;
        }
        
        $this->view->form = new Mobile_Form_OtherInterventions($otherId, $patientId, $shiftId);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $result = $this->view->form->process($request->getPost());
            
            if ($result instanceof Zend_Form) {
                $this->view->form = $result;
            } else {
                $other = \Fisdap\EntityUtils::getEntity('OtherIntervention', $result);
                $this->_redirect("/mobile/patients/index/runId/" . $other->run->id);
            }
        }
    }
    
    public function medsAction()
    {
        $medId = $this->_getParam('id');
        $patientId = $this->_getParam('patientId');
        $shiftId = $this->_getParam('shiftId');
        
        if ($medId && !$this->canEditData($medId, "Med")) {
            $this->displayError("You do not have permission to edit this medication.", "mobile");
            return;
        }
        
        if ($patientId && !$this->canEditData($patientId, "Patient")) {
            $this->displayError("You do not have permission to edit this patient.", "mobile");
            return;
        }
        
        if ($shiftId && !$this->canEditData($shiftId, "ShiftLegacy")) {
            $this->displayError("You do not have permission to edit this shift.", "mobile");
            return;
        }
        
        $this->view->form = new Mobile_Form_Meds($medId, $patientId, $shiftId);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $result = $this->view->form->process($request->getPost());
            
            if ($result instanceof Zend_Form) {
                $this->view->form = $result;
            } else {
                $med = \Fisdap\EntityUtils::getEntity('Med', $result);
                $this->_redirect("/mobile/patients/index/runId/" . $med->run->id);
            }
        }
    }
    
    public function narrativeAction()
    {
        $narrativeId = $this->_getParam('id');
        $patientId = $this->_getParam('patientId');
        
        if ($narrativeId && !$this->canEditData($narrativeId, "Narrative")) {
            $this->displayError("You do not have permission to edit this narrative.", "mobile");
            return;
        }
        
        if ($patientId && !$this->canEditData($patientId, "Patient")) {
            $this->displayError("You do not have permission to edit this patient.", "mobile");
            return;
        }
	
	if (!$narrativeId && $patientId) {
	    $patient = \Fisdap\EntityUtils::getEntity('Patient', $patientId);
	    if (!$patient->narrative) {
		$narrative = \Fisdap\EntityUtils::getEntity('Narrative');
		$program_id = \Fisdap\Entity\User::getLoggedInUser()->getProgramId();
		$sections = \Fisdap\EntityUtils::getRepository('NarrativeSectionDefinition')->getNarrativeSectionsByProgram($program_id, true);
			
		// loop through the narrative sections and add each
		foreach ($sections as $section) {
		    $section_instance = new \Fisdap\Entity\NarrativeSection;
		    $section_instance->narrative = $narrative;
		    $section_instance->definition = $section;
		    $section_instance->set_section_text('');
		    $narrative->addSection($section_instance);
		}
	    
		$patient->set_narrative($narrative);
		$patient->save();
	    } else {
		$narrative = $patient->narrative;
	    }

	    $narrativeId = $narrative->id;  
	}

        $this->view->form = new Mobile_Form_Narrative($narrativeId);
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $result = $this->view->form->process($request->getPost());
            
            if ($result instanceof Zend_Form) {
                $this->view->form = $result;
            } else {
                $narrative = \Fisdap\EntityUtils::getEntity('Narrative', $result);
                $this->_redirect("/mobile/patients/index/runId/" . $narrative->run->id);
            }
        }
    }
    
    public function deleteSkillAction()
    {
        $id = $this->_getParam('id');
        $skillType = $this->_getParam('skillType');
        
        $skill = \Fisdap\EntityUtils::getEntity($skillType, $id);
        $runId = $skill->run->id;
        
        if ($this->canEditData($id, $skillType)) {
            $this->flashMessenger->addMessage("$skillType #$id successfully deleted.");
            $skill->delete();
        } else {
            $this->flashMessenger->addMessage("$skillType #$id was not deleted because you do not have permission to do so.");
        }
        
        $this->_redirect("/mobile/patients/index/runId/" . $runId);
    }
    
    private function canEditData($skillId, $skillType, $user = null) {
        if (is_null($user)) {
            $user = \Fisdap\Entity\User::getLoggedInUser();
        }
        
        $skill = \Fisdap\EntityUtils::getEntity($skillType, $skillId);
        
		if ($skill instanceof \Fisdap\Entity\ShiftLegacy) {
			$runLocked = false;
			$locked = $skill->locked;
			$audited = $skill->audited;
			$type = $skill->type;
		} else if ($skill instanceof \Fisdap\Entity\Run) {
			$runLocked = $skill->locked;
			$locked = $skill->shift->locked;
			$audited = $skill->shift->audited;
			$type = $skill->shift->type;
		} else {
			$runLocked = $skill->run->locked;
			$locked = $skill->shift->locked;
			$audited = $skill->shift->audited;
			$type = $skill->shift->type;
		}
		
        $allowed = true;
        
        if ($user->getCurrentRoleName() == "instructor") {
            //Make sure the instructor has permission this particular type of Skills Tracker data
            $allowed = $allowed && $user->hasPermission("Edit " . ucfirst($type) . " Data");
            
            //Make sure the student is in this instructor's Program
            $allowed = $allowed && ($user->getCurrentRoleData()->program->id == $skill->student->program->id);
			
			//Make sure the shift isn't audited
			$allowed = $allowed && !$audited;
        } else {
			//Make sure the student owns this data
            $allowed = $allowed && ($user->getCurrentRoleData()->id == $skill->student->id);
            
			//Make sure the run and shift are unlocked and not audited
			$allowed = $allowed && !$runLocked && !$locked && !$audited;
        }
        
        return $allowed;
    }
}

