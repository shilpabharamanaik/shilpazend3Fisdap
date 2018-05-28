<?php

namespace Fisdap\Goal;

/**
 *	Calculates student goals for Team Leads category
 *	@autor Maciej
 */
class GoalCategoryLeads extends GoalCategoryBase
{
	// links to goalDefs
	const LEAD_TOTALS = 76;
	const LEAD_PEDS = 77;
	const LEAD_UNCONSCIOUS = 78;
	const LEAD_ALS = 79;
	
	const LEAD_FIELD_ALS = 109;
	const LEAD_FIELD_BLS = 110;
	const LEAD_FIELD_TOTALS = 111;
	
	// Legacy / deprecated
	
	const LEAD_TRANSFERS = 80;
	const LEAD_ASAP_RESPONSES = 81;
	const LEAD_EMERGENCY_RESPONSES = 82;
	
	protected function forEachShift(&$shift)
	{
	}
	
	protected function forEachPatient(&$patient)
	{
		$this->debugvals[] = 'Patient ' . $patient['id'] . ' Shift:' . $patient['Type'];
		
		if ($patient['Type'] == 'clinical') {
			return false;
		}

		$isAls = \Fisdap\Entity\Patient::isALSPatientSQL($patient['id'], $this->dataReq->getAlsType());
		
		$this->add(self::LEAD_TOTALS, $patient['team_lead'], $patient);
		
		$this->add(self::LEAD_PEDS, $patient['team_lead'], $patient,
			$this->goalSet->ages->isPediatricAge($patient['age'], $patient['months'], true));
		
		$this->add(self::LEAD_UNCONSCIOUS, $patient['team_lead'], $patient,
			\Fisdap\Entity\Patient::isUnconsciousSQL($patient['id']));
		
		$this->add(self::LEAD_ALS, $patient['team_lead'], $patient, $isAls);
		
		$this->add(self::LEAD_FIELD_ALS, $patient['team_lead'], $patient, ($patient['Type'] == 'field' && $isAls));
		$this->add(self::LEAD_FIELD_BLS, $patient['team_lead'], $patient, ($patient['Type'] == 'field' && !$isAls));
		$this->add(self::LEAD_FIELD_TOTALS, $patient['team_lead'], $patient, ($patient['Type'] == 'field'));
		
		return true;
	}
	

}

