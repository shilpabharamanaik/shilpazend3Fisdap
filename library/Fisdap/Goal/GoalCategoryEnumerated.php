<?php

namespace Fisdap\Goal;

/**
 *	Calculates student goals for enumerated type goals categories
 *		currently: complaints, impressions
 *		
 *	@autor Maciej
 */
class GoalCategoryEnumerated extends GoalCategoryBase
{
	// links to goalDefs
	const PEDS_RESPIRATORY = 27;
	const RESPIRATORY = 29;
	const TRAUMA = 17;
	const PEDS_TRAUMA = 93;
	const ADULT_TRAUMA = 94;
	const GERIATRIC_TRAUMA = 95;
	const MEDICAL = 24;
	const PEDS_MEDICAL = 98;
	const ADULT_MEDICAL = 99;
	const GERIATRIC_MEDICAL = 100;
    const ADULT_DYSPNEA = 137;
    const PEDS_DYSPNEA = 138;

    //Virginia has different NSC Type definitions, all impressions should be either Trauma or Medical nsc_type.
    protected static $vaTraumaImpressions = array(27,28,29,30,31,32,35,40,41,42);
    protected static $vaMedicalImpressions = array(1,2,3,4,5,6,7,11,12,13,15,16,18,19,26,33,34,36,37,38,39,42,43,44);
	
	/**
	 *	@var array enumerated values
	 */
	protected $idToDefId;
	
	protected function init()
	{
		// get values from $subCategory entity

		$this->enumEntity = ucfirst(substr($this->subCategory, 0, strlen-1));
        /*
		eval("\$this->idToDefId = \\Fisdap\\Entity\\{$this->enumEntity}::getAll(true);");
		
		// error catching
		if (!$this->idToDefId) {
			throw new \Fisdap_Exception_InvalidArgument('Couldn\'t get values in GoalCategoryEnumerated for "' . $this->subCategory . "'");
		}
        */
	}

	protected function forEachPatient(&$patient)
	{
		if (in_array($this->enumEntity, array(
			'Complaint', 'Impression'
		))) {
			$callThis = 'forEachPatient' . $this->enumEntity;
			$this->$callThis($patient);
		}
	}

    // Called by the forEachPatient() method depending on ???
	protected function forEachPatientComplaint(&$patient)
	{
		//$complaints = \Fisdap\Entity\Patient::getComplaintsSQL($patient['id']);
        if (is_array($patient['complaints'])) {
            foreach ($patient['complaints'] as $complaintId) {
                $defId = $this->getDefId($complaintId, 'complaints');
                $this->add($defId, true, $patient);

                if ($defId == self::RESPIRATORY) {
                    // ped respiratory
                    $this->add(self::PEDS_RESPIRATORY, true, $patient,
                        $this->goalSet->ages->isPediatricAge($patient['age'], $patient['months']));
                    // ped dyspnea
                    $this->add(self::PEDS_DYSPNEA, true, $patient,
                        $this->goalSet->ages->isPediatricAge($patient['age'], $patient['months']));
                    // adult dyspnea
                    $this->add(self::ADULT_DYSPNEA, true, $patient,
                        !$this->goalSet->ages->isPediatricAge($patient['age'], $patient['months']));
                }
            }
        }
		//$this->debugvals[] =  "Complaint ID: " . $complaint->id . ' defId: ' . $this->getDefId($complaint->id). '<br/>';
			// keep for debug:		echo $patient->id . ' COMPLAINT ' . $complaint->id . ' ' . $i++ . ' ' . (int)$patient->team_lead . ' ' . $complaint->id . ' ' . $complaint->name .  '<br/>';
			//$res->complaints->counts->all->{$complaint->id}++;
	}

    // Called by the forEachPatient() method depending on ???
	protected function forEachPatientImpression(&$patient)
	{
		//echo 'PRIMARY: ' . $patient->primary_impression->id . ' ' . $patient->primary_impression->name . ' GoalDefId: '. $this->getDefId($patient->primary_impression->id) . '<br/>';
		//echo 'SECONDR: ' . $patient->secondary_impression->id . ' ' . $patient->secondary_impression->name . ' GoalDefId: '. $this->getDefId($patient->secondary_impression->id). '<br/>';
		$primImprDefId = $this->getDefId($patient['primary_impression_id'], 'impressions');
		$secImprDefId = $this->getDefId($patient['secondary_impression_id'], 'impressions');
		
		$this->add($primImprDefId, true, $patient);
		
		$this->add($secImprDefId, true, $patient, $primImprDefId<>$secImprDefId);

        //check if we have a trauma impression for VA goals
        if(in_array($patient['primary_impression_id'], self::$vaTraumaImpressions) || in_array($patient['secondary_impression_id'], self::$vaTraumaImpressions) ){
            $this->add(self::PEDS_TRAUMA, true, $patient, $this->goalSet->ages->isPediatricAge($patient['age'], $patient['months']));
            $this->add(self::ADULT_TRAUMA, true, $patient, $this->goalSet->ages->isAdultAge($patient['age'], $patient['months']));
            $this->add(self::GERIATRIC_TRAUMA, true, $patient, $this->goalSet->ages->isGeriatricAge($patient['age'], $patient['months']));
        }

        //check if we have a medical impression for VA goals
        if(in_array($patient['primary_impression_id'], self::$vaMedicalImpressions) || in_array($patient['secondary_impression_id'], self::$vaMedicalImpressions) ){
            $this->add(self::PEDS_MEDICAL, true, $patient, $this->goalSet->ages->isPediatricAge($patient['age'], $patient['months']));
            $this->add(self::ADULT_MEDICAL, true, $patient, $this->goalSet->ages->isAdultAge($patient['age'], $patient['months']));
            $this->add(self::GERIATRIC_MEDICAL, true, $patient, $this->goalSet->ages->isGeriatricAge($patient['age'], $patient['months']));
        }
	}

    /**
     * Find the goal definition ID matching the enumerated value (complaint/impression)
     *
     * @param integer $enumId The ID of the enumerated value
     * @param string $type The type of enumerated value to look for: complaints or impressions
     * @return integer the Goal Definition ID
     */
    protected function getDefId($enumId, $type)
	{
        foreach($this->data->enumerated[$type] as $enumValue) {
            if ($enumValue['id'] == $enumId) {
                return $enumValue['goal_def_id'];
            }
        }

		//return $this->idToDefId[$enumId]['goal_def_id'];
	}

}
