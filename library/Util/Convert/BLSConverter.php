<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_BLSConverter extends Util_Convert_SkillConverter
{
    /**
     * This function converts all old Fisdap BLS skills over for a given patient.
     *
     * @param Patient
     */
    public function convert($patient, $shift = null)
    {
        if ($shift) {
            $query = "SELECT * FROM BLSSkillsData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$shift['Student_id']} AND Shift_id = {$shift['Shift_id']} AND Run_id <= 0 AND Assesment_id <= 0";
            $results = $this->db->query($query);
        } else {
            $query = "SELECT * FROM BLSSkillsData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$patient['student_id']} ";
            
            if ($patient['legacy_run_id'] != null) {
                $query .= "AND Run_id = '{$patient['legacy_run_id']}' ";
            } elseif ($patient['legacy_assessment_id'] != null) {
                $query .= "AND Assesment_id = '{$patient['legacy_assessment_id']}' ";
            } else {
                return;
            }
    
            $results = $this->db->query($query);
        }
        
        while ($oldSkill = $results->fetch()) {
            $saveSkill = true;
            
            switch ($oldSkill['SkillCode']) {
                // Airway conversions
                case 9:
                    $tableName = "fisdap2_airways";
                    $insertData = array("procedure_id" => 28, "success" => 1);
                    break;
                case 6:
                    $tableName = "fisdap2_airways";
                    $insertData = array("procedure_id" => 13);
                    break;
                
                case 14:
                    if ($oldSkill['SkillModifier'] == 1) {
                        $tableName = "fisdap2_airways";
                        $insertData = array("procedure_id" => 26);
                    } elseif ($oldSkill['SkillModifier'] == 2) {
                        $tableName = "fisdap2_airways";
                        $insertData = array("procedure_id" => 27);
                    } else {
                        $saveSkill = false;
                    }
                    break;
                    
                
                // Cardiac conversions
                case 12:
                    $tableName = "fisdap2_cardiac_interventions";
                    $insertData = array("procedure_id" => 1);
                    break;
                
                // Other conversions
                case 2:
                    $tableName = "fisdap2_other_interventions";
                    $insertData = array("procedure_id" => 42);
                    break;
                case 7:
                    $tableName = "fisdap2_other_interventions";
                    $insertData = array("procedure_id" => 41);
                    break;
                case 10:
                    $tableName = "fisdap2_other_interventions";
                    $insertData = array("procedure_id" => 31);
                    break;
                case 11:
                    $tableName = "fisdap2_other_interventions";
                    $insertData = array("procedure_id" => 40);
                    break;
                case 13:
                    $tableName = "fisdap2_other_interventions";
                    $insertData = array("procedure_id" => 39);
                    break;
                case 15:
                    $tableName = "fisdap2_other_interventions";
                    $insertData = array("procedure_id" => 33);
                    break;
                case 16:
                    $tableName = "fisdap2_other_interventions";
                    $insertData = array("procedure_id" => 38);
                    break;
                
                //Vitals Conversion
                case 5:
                    $tableName = "fisdap2_vitals";
                    $insertData = array();
                    break;
                
                
                //Special cases for exam and interview
                case 3:
                    if ($oldSkill['PerformedBy']==1) {
                        $patientUpdateData = array('interview' => 1);
                        $this->db->update("fisdap2_patients", $patientUpdateData, "id = " . $patient['id']);
                    } elseif ($oldSkill['PerformedBy'] == 2) {
                        $patientUpdateData = array('interview' => 0);
                        $this->db->update("fisdap2_patients", $patientUpdateData, "id = " . $patient['id']);
                    }
                    
                    $saveSkill = false;
                    break;
                
                case 4:
                    if ($oldSkill['PerformedBy']==1) {
                        $patientUpdateData = array('exam' => 1);
                        $this->db->update("fisdap2_patients", $patientUpdateData, "id = " . $patient['id']);
                    } elseif ($oldSkill['PerformedBy'] == 2) {
                        $patientUpdateData = array('exam' => 0);
                        $this->db->update("fisdap2_patients", $patientUpdateData, "id = " . $patient['id']);
                    }
                    
                    $saveSkill = false;
                    break;
                
                default:
                    $saveSkill = false;
            }
            
            if ($saveSkill) {
                $insertData['performed_by'] = ($oldSkill['PerformedBy']==1) ? 1 : 0;
                $insertData['subject_id'] = $this->parseSubjectType($oldSkill['SubjectTypeGroup']);
                
                //Add success and attempts to Long Board
                if ($tableName == "fisdap2_other_interventions" && $insertData['procedure_id'] == 41) {
                    $insertData['success'] = 1;
                    $insertData['attempts'] = 1;
                }
                
                if ($shift) {
                    $this->setRelatedFields($insertData, $shift, 'shift');
                } else {
                    $this->setRelatedFields($insertData, $patient, 'patient');
                }
                
                $this->db->insert($tableName, $insertData);
            }
        }
    }
}
