<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_VitalConverter extends Util_Convert_SkillConverter
{
	/**
	 * This function converts all Vitals skills over for a given patient.
	 *
	 * @param Patient
	 */
	public function convert($patient, $shift = null)
	{
		if ($shift) {
			$query = "SELECT * FROM VitalsData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$shift['Student_id']} AND Shift_id = {$shift['Shift_id']} AND Run_id = -1 AND Assessment_id = -1";
			$skills = $this->db->query($query);
		} else {
			$query = "SELECT * FROM VitalsData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$patient['student_id']} ";
			
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
			//$newSkill = new \Fisdap\Entity\Vital();
			$tableName = "fisdap2_vitals";
			$insertData = array();
			
			//$newSkill->systolic_bp = $oldSkill['SystolicBP'];
			//$newSkill->diastolic_bp = $oldSkill['DiastolicBP'];
			//$newSkill->pulse_rate = $oldSkill['PulseRate'];
			//$newSkill->resp_rate = $oldSkill['RespRate'];
			//$newSkill->spo2 = $oldSkill['SpO2'];
			//$newSkill->pupils_equal = $oldSkill['PupilsEqual'];
			//$newSkill->pupils_round = $oldSkill['PupilsRound'];
			//$newSkill->pupils_reactive = $oldSkill['PupilsReactive'];
			//$newSkill->blood_glucose = $oldSkill['BloodGlucose'];
			//$newSkill->apgar = $oldSkill['APGAR'];
			//$newSkill->gcs = $oldSkill['GlasgowComaScore'];
			//$newSkill->skill_order = $oldSkill['VitalsTime'];
			
			$insertData['systolic_bp'] = $oldSkill['SystolicBP'];
			$insertData['diastolic_bp'] = $oldSkill['DiastolicBP'];
			$insertData['pulse_rate'] = $oldSkill['PulseRate'];
			$insertData['resp_rate'] = $oldSkill['RespRate'];
			$insertData['spo2'] = $oldSkill['SpO2'];
			$insertData['pupils_equal'] = $oldSkill['PupilsEqual'];
			$insertData['pupils_round'] = $oldSkill['PupilsRound'];
			$insertData['pupils_reactive'] = $oldSkill['PupilsReactive'];
			$insertData['blood_glucose'] = $oldSkill['BloodGlucose'];
			$insertData['apgar'] = $oldSkill['APGAR'];
			$insertData['gcs'] = $oldSkill['GlasgowComaScore'];
			$insertData['skill_order'] = $oldSkill['VitalsTime'];			
			
			if($oldSkill['PulseQuality'] > 0){
				//$newSkill->pulse_quality = \Fisdap\EntityUtils::getEntity('VitalPulseQuality', $oldSkill['PulseQuality']);
				$insertData['pulse_quality_id'] = $oldSkill['PulseQuality'];
			}
			
			if($oldSkill['RespQuality'] > 0){
				//$newSkill->resp_quality = \Fisdap\EntityUtils::getEntity('VitalRespQuality', $oldSkill['RespQuality']);
				$insertData['resp_quality_id'] = $oldSkill['RespQuality'];
			}
			
			// Fetch out the skins...
			$skinsQuery = "SELECT * FROM VitalsSkinData WHERE Vitals_id = {$oldSkill['Vitals_id']} ";
			$skins = $this->db->query($skinsQuery)->fetchAll();
			
			$skin_ids = array();
			foreach($skins as $skin){
				$skin_ids[] = $skin['Skin_id'];
			}
			//$newSkill->skins = $skin_ids;
			
			// Fetch out the lung sounds...
			$lungSoundsQuery = "SELECT * FROM VitalsLungSoundsData WHERE Vitals_id = {$oldSkill['Vitals_id']} ";
			$lungSounds = $this->db->query($lungSoundsQuery)->fetchAll();
			
			$lung_ids = array();
			foreach($lungSounds as $lungSound){
				$lung_ids[] = $lungSound['LungSounds_id'];
			}
			//$newSkill->lung_sounds = $lung_ids;
			
			//$newSkill->performed_by = ($oldSkill['PerformedBy']==0)?true:false;
			//$newSkill->subject = $this->parseSubjectType($oldSkill['SubjectTypeGroup']);
			
			$insertData['performed_by'] = ($oldSkill['PerformedBy']==0) ? 1 : 0;
			$insertData['subject_id'] = $this->parseSubjectType($oldSkill['SubjectTypeGroup']);
			
			if ($shift) {
				$this->setRelatedFields($insertData, $shift, 'shift');
			} else {
				$this->setRelatedFields($insertData, $patient, 'patient');
			}
			
			//$newSkill->save(false);
			$this->db->insert($tableName, $insertData);
			$vital_id = $this->db->lastInsertId();

			
			//Save the skin data
			foreach ($skin_ids as $id) {
				$insertData = array(
					"vital_id" => $vital_id,
					"skin_id" => $id,
				);
				$this->db->insert("fisdap2_vitals_skins", $insertData);
			}
			
			//Save the lung sounds data
			foreach ($lung_ids as $id) {
				$insertData = array(
					"vital_id" => $vital_id,
					"lung_sound_id" => $id,
				);
				$this->db->insert("fisdap2_vitals_lung_sounds", $insertData);
			}
		}
	}
}
