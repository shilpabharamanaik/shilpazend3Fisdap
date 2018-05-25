<?php

namespace Fisdap\Goal;

/**
 *	Calculates student goals for Skills category
 *	@autor stape
 */
class GoalCategoryHours extends GoalCategoryBase
{
	const OTHER_CLINICAL = 104;
	const TOTAL_CLINICAL = 105;
	const ALS = 106;
	const TOTAL_ALS_FIELD_CLINICAL = 107;
	const TOTAL_PATIENT_CONTACTS = 108;
	const TOTAL_FIELD = 125;
	const TOTAL_FIELD_CLINICAL = 126;
	const ALS_PATIENT_CONTACTS = 135;
	
	// links to goalDefs
	protected $deptGoals = array(
		'ER' => 96,
        'Emergency Room' => 96,
		'Peds' => 97,
		'Pediatrics' => 97,
		'Pediatric' => 97,
		'ICU' => 101,
        'Intensive Care Unit' => 101,
		'Cardiac Cath. Lab' => 101,
		'Cardiac Care Unit' => 101,
        'CCU' => 101,
        'CCL' => 101,
		'Neonatal ICU' => 101,
        'NICU' => 101,
		'CICU' => 101,
		'Labor & Delivery' => 102,
        'Labor' => 102,
        'L&D' => 102,
		'OR' => 103,
        'Operating Room' => 103,
		'Respiratory' => 122,
		'Psychiatric' => 123,
		'Psych' => 123,
		'Psychiatric Unit' => 123,
		'Pediatric ED' => 124
	);
	
	protected $shiftIds = array();
	
	protected function forEachShift(&$shift)
	{
		//Make sure these department goals only get counted if the shift is clinical
		$this->add($this->deptGoals[$shift['base_name']], true, $shift, $shift['Type'] == "clinical", true, $shift['Hours']);
		
		//Add other clinical goal if the given base is not listed and it's a clinical shift
		$this->add(self::OTHER_CLINICAL, true, $shift, ((!isset($this->deptGoals[$shift['base_name']]) || $shift['base_name'] == "Respiratory") && $shift['Type'] == "clinical"), true, $shift['Hours']);
		
		//Add total clinical goal if the shift is clinical
		$this->add(self::TOTAL_CLINICAL, true, $shift, $shift['Type'] == "clinical", true, $shift['Hours']);

		//Add total field goal if this shift is field
		$this->add(self::TOTAL_FIELD, true, $shift, $shift['Type'] == "field", true, $shift['Hours']);

		//Add total field and clinical not accounting for any ALS restrictions
		$this->add(self::TOTAL_FIELD_CLINICAL, true, $shift, $shift['Type'] == "field" || $shift['Type'] == "clinical", true, $shift['Hours']);
		
		//Add total clinical/field goal if the shift is clinical, we'll add the same goal for ALS patients in the forEachPatient function
		$this->add(self::TOTAL_ALS_FIELD_CLINICAL, true, $shift, ($shift['Type'] == "clinical"), true, $shift['Hours']);
		
	}
	
	protected function forEachPatient(&$patient)
	{
		//Determine if the patient/call was ALS
		$isAls = \Fisdap\Entity\Patient::isALSPatientSQL($patient['id'], $this->dataReq->getAlsType());
		
		//Add hours to the ALS goal, but only for distinct shifts so hours don't get double counted
		if ($isAls && !in_array($patient['shift_id'], $this->shiftIds)) {
			$this->shiftIds[] = $patient['shift_id'];
			$this->add(self::ALS, true, $patient, $patient['Type'] == "field", true, $patient['Hours']);
			$this->add(self::TOTAL_ALS_FIELD_CLINICAL, true, $patient, $patient['Type'] == "field", true, $patient['Hours']);
		}

		if ($isAls) {
			$this->add(self::ALS_PATIENT_CONTACTS, true, $patient, ($patient['Type'] == "field"), true);
		}

		//Count each patient contact that was a field or clinical contact
		$this->add(self::TOTAL_PATIENT_CONTACTS, true, $patient, ($patient['Type'] == 'field' || $patient['Type'] == 'clinical'), true);
	}

}
