<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_AirwayConverter extends Util_Convert_SkillConverter
{
    /**
     * This function converts all airway skills over for a given patient.
     *
     * @param Patient
     */
    public function convert($patient, $shift = null)
    {
        if ($shift) {
            $query = "SELECT * FROM ALSAirwayData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$shift['Student_id']} AND Shift_id = {$shift['Shift_id']} AND Run_id = -1 AND Assessment_id = -1";
            $skills = $this->db->query($query);
        } else {
            $query = "SELECT * FROM ALSAirwayData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$patient['student_id']} ";
            
            if ($patient['legacy_run_id'] != null) {
                $query .= "AND Run_id = '{$patient['legacy_run_id']}' ";
            } elseif ($patient['legacy_assessment_id'] != null) {
                $query .= "AND Assessment_id = '{$patient['legacy_assessment_id']}' ";
            } else {
                return;
            }

            $skills = $this->db->query($query);
        }
        
        while ($oldSkill = $skills->fetch()) {
            // Start by just copying over the necessary data...
            $tableName = "fisdap2_airways";
            $insertData = array();
            
            $insertData['size'] = $oldSkill['ETSize'];
            $insertData['attempts'] = $oldSkill['NumAttempts'];
            $insertData['success'] = $oldSkill['Success'];
            $insertData['skill_order'] = $oldSkill['ETTime'];
            
            // Some corrections to the data here...
            if ($oldSkill['Type'] == 2) { // Esophageal Gastric Obturator
                $insertData['procedure_id'] = 1; // EOA/EGTA
            } elseif ($oldSkill['Type'] == 4) { // Pharyngeotracheal Lumen
                $insertData['procedure_id'] = 3; //Combitube
            } elseif ($oldSkill['Type'] == 8) { // Carotid Sinus Massage
                $tableName = "fisdap2_cardiac_interventions";
                $insertData = array('procedure_id' => 5); // Cardiac - Carotid Sinus Massage
            } elseif ($oldSkill['Type'] == 7) { // Valsalva Maneuver
                $tableName = "fisdap2_cardiac_interventions";
                $insertData = array('procedure_id' => 6); // Cardiac - Valsalva's Maneuver
            } else {
                $insertData['procedure_id'] = $oldSkill['Type'];
            }
            
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
