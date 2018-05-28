<?php

namespace Fisdap\Goal;

/**
 *	Calculates student goals for Skills category
 *	@autor Maciej
 */
class GoalCategorySkills extends GoalCategoryBase
{
	// links to goalDefs
	const ET_Intubation = 54;
	const Live_Intubation = 75;
	const Meds_ALL = 2;
	const IV_PROCEDURE = 3;
	const VENTILATIONS = 6;
	const AIRWAY_SUCCESS = 92;
    const ECG_INTERPRET = 118;
    const IO_PROCEDURE = 119;
    const MAN_DEFIB = 120;
    const CHEST_COMPRESSION = 121;

	
	public $skillsIds;
	
	public function __construct($studentId, $goalSet, &$data, $dataReqs, $studentName, $subCategory = NULL)
	{
		$this->skillsIds = array(
			self::ET_Intubation,
			self::Live_Intubation,
		);
		parent::__construct($studentId, $goalSet, $data, $dataReqs, $studentName, $subCategory = NULL);
	}
	
	protected function forEachShift(&$shift)
	{
		$ivs = \Fisdap\Entity\Iv::getAllByShiftSQL($shift['Shift_id']);
		$meds = \Fisdap\Entity\Med::getAllByShiftSQL($shift['Shift_id']);
		$airways = \Fisdap\Entity\Airway::getAllByShiftSQL($shift['Shift_id']);
        $cardiac_interventions = \Fisdap\Entity\CardiacIntervention::getAllByShiftSQL($shift['Shift_id']);

        if ($ivs) {
            foreach ($ivs as $iv) {
                if (!\Fisdap\Entity\Iv::countsTowardGoalSQL($iv, $this->dataReq)) {
                    continue;
                }

                $procId = $iv['procedure_id'];
                if ($procId == 1 || $procId == 2 || $procId == 8) { //Count IVs and IVs with Blood Draw
                    $patient = \Fisdap\Entity\Patient::getExamInterviewTeamLeadArray($iv['patient_id']);
                    $this->add(self::IV_PROCEDURE, $iv['performed_by'], $patient);

                    if ($procId == 2) {
                        $this->add(self::IO_PROCEDURE, $iv['performed_by'], $patient);
                    }
                }
            }
        }

        if ($meds) {
            foreach ($meds as $med) {
                if (!\Fisdap\Entity\Med::countsTowardGoalSQL($med, $this->dataReq)) {
                    continue;
                }
                $patient = \Fisdap\Entity\Patient::getExamInterviewTeamLeadArray($med['patient_id']);
                $this->add(self::Meds_ALL, $med['performed_by'], $patient);
            }
        }

        if ($airways) {
            foreach ($airways as $airway) {
                if (!\Fisdap\Entity\Airway::countsTowardGoalSQL($airway, $this->dataReq)) {
                    continue;
                }

                $procId = $airway['procedure_id'];

                //ET Intubation count. Includes Orotracheal, Endotracheal, Nasotracheal
                if (in_array($procId, array(5, 6, 10))) {
                    $patient = \Fisdap\Entity\Patient::getExamInterviewTeamLeadArray($airway['patient_id']);
                    $this->add(self::ET_Intubation, $airway['performed_by'], $patient);
                    //Add to live intubation count if the subject was a live human
                    if ($airway['subject_id'] == 1) {
                        $patient = \Fisdap\Entity\Patient::getExamInterviewTeamLeadArray($airway['patient_id']);
                        $this->add(self::Live_Intubation, $airway['performed_by'], $patient);
                    }
                }

                if ($procId == 28) {		// old fisdap: BLS Skill #9
                    $patient = \Fisdap\Entity\Patient::getExamInterviewTeamLeadArray($airway['patient_id']);
                    $this->add(self::VENTILATIONS, $airway['performed_by'], $patient);
                }
            }
            unset($patient);
        }

        if ($cardiac_interventions) {
            foreach ($cardiac_interventions as $cardiac) {

                    $patient = \Fisdap\Entity\Patient::getExamInterviewTeamLeadArray($cardiac['patient_id']);
                    $this->add(self::ECG_INTERPRET, $cardiac['rhythm_performed_by'], $patient);

                    //if it had a manual defibrillation associated with the cardiac entry
                    if ($cardiac['procedure_id'] == 2 && $cardiac['procedure_method_id'] == 2) {
                        $this->add(self::MAN_DEFIB, $cardiac['performed_by'], $patient);
                    }

                    //if there were chest compressions of any kind
                    if ($cardiac['procedure_id'] == 1) {
                        $this->add(self::CHEST_COMPRESSION, $cardiac['performed_by'], $patient);
                    }
            }

        }


		return true;
		//var_dump($goal->def);		var_dump($this->categoryGoals); exit;
	}

}
