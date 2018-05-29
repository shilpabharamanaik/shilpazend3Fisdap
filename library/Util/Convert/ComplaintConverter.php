<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_ComplaintConverter extends Util_Convert_SkillConverter
{
	/**
	 * This function converts all old Fisdap BLS skills over for a given patient.
	 *
	 * @param Patient
	 */
	public function convert($patient)
	{
		$query = "SELECT * FROM PtComplaintData WHERE Student_id = {$patient['student_id']} ";
		
		if($patient['legacy_run_id'] != null){
			$query .= "AND Run_id = '{$patient['legacy_run_id']}' ";
		}elseif($patient['legacy_assessment_id'] != null){
			$query .= "AND Asses_id = '{$patient['legacy_assessment_id']}' ";
		}else{
			return;
		}

		$results = $this->db->query($query);

		
		while($oldSkill = $results->fetch()){
			$insertData = array(
				"patient_id" => $patient['id'],
				"complaint_id" => $oldSkill['Complaint_id'],
			);
			$this->db->insert("fisdap2_patients_complaints", $insertData);
		}
        //$patient->setComplaintIds($complaints_to_add);
        //$patient->save(false);
	}
}
