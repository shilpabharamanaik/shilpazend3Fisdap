<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_MedConverter extends Util_Convert_SkillConverter
{
	/**
	 * This function converts all IVorIO skills over for a given patient.
	 *
	 * @param Patient
	 */
	public function convert($patient, $shift = null)
	{
		if ($shift) {
			$query = "SELECT * FROM MedData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$shift['Student_id']} AND Shift_id = {$shift['Shift_id']} AND Run_id = -1 AND Assessment_id = -1";
			$skills = $this->db->query($query);
		} else {
			$query = "SELECT * FROM MedData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$patient['student_id']} ";
			
			if($patient['legacy_run_id'] != null){
				$query .= "AND Run_id = '{$patient['legacy_run_id']}' ";
			}elseif($patient['legacy_assessment_id'] != null){
				$query .= "AND Assessment_id = '{$patient['legacy_assessment_id']}' ";
			}else{
				return;
			}
	
			$skills = $this->db->query($query);
		}
		
		while ($oldSkill = $skills->fetch()) {
			// Start by just copying over the necessary data...
			//$newSkill = new \Fisdap\Entity\Med();
			$tableName = "fisdap2_meds";
			$insertData = array();
			
			//$newSkill->medication = \Fisdap\EntityUtils::getEntity('MedType', $oldSkill['Medication']);
			//$newSkill->dose = $oldSkill['Dose'];
			//
			//$newSkill->route = \Fisdap\EntityUtils::getEntity('MedRoute', $oldSkill['Route']);
			//
			//$newSkill->performed_by = ($oldSkill['PerformedBy']==0)?true:false;
			//$newSkill->subject = $this->parseSubjectType($oldSkill['SubjectTypeGroup']);
			
			
			$insertData['medication_id'] = $oldSkill['Medication'];
			$insertData['dose'] = $oldSkill['Dose'];
			$insertData['route_id'] = $oldSkill['Route'];
			$insertData['performed_by'] = ($oldSkill['PerformedBy']==0) ? 1 : 0;
			$insertData['subject_id'] = $this->parseSubjectType($oldSkill['SubjectTypeGroup']);
			
			if ($shift) {
				$this->setRelatedFields($insertData, $shift, 'shift');
			} else {
				$this->setRelatedFields($insertData, $patient, 'patient');
			}
			
			//$newSkill->save(false);
			$this->db->insert($tableName, $insertData);
		}
	}
}
