<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_OtherConverter extends Util_Convert_SkillConverter
{
    /**
     * This function converts all IVorIO skills over for a given patient.
     *
     * @param Patient
     */
    public function convert($patient, $shift = null)
    {
        if ($shift) {
            $query = "SELECT * FROM OtherALSData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$shift['Student_id']} AND Shift_id = {$shift['Shift_id']} AND Run_id = -1 AND Assessment_id = -1";
            $skills = $this->db->query($query);
        } else {
            $query = "SELECT * FROM OtherALSData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$patient['student_id']} ";
            
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
            //$newSkill = new \Fisdap\Entity\OtherIntervention();
            $tableName = "fisdap2_other_interventions";
            $insertData = array();
            $saveSkill = true;
            
            //$newSkill->procedure = \Fisdap\EntityUtils::getEntity('OtherProcedure', $oldSkill['OtherSkill']);
            
            switch ($oldSkill['OtherSkill']) {
                // Airway conversions
                case 1:
                    //$newSkill = new \Fisdap\Entity\Airway();
                    //$newSkill->procedure = \Fisdap\EntityUtils::getEntity('AirwayProcedure', 15);
                    $tableName = "fisdap2_airways";
                    $insertData = array("procedure_id" => 15);
                    break;
                case 12:
                    //$newSkill = new \Fisdap\Entity\Airway();
                    //$newSkill->procedure = \Fisdap\EntityUtils::getEntity('AirwayProcedure', 12);
                    $tableName = "fisdap2_airways";
                    $insertData = array("procedure_id" => 12);
                    break;
                case 23:
                    //$newSkill = new \Fisdap\Entity\Airway();
                    //$newSkill->procedure = \Fisdap\EntityUtils::getEntity('AirwayProcedure', 14);
                    $tableName = "fisdap2_airways";
                    $insertData = array("procedure_id" => 14);
                    break;
                case 24:
                    //$newSkill = new \Fisdap\Entity\Airway();
                    //$newSkill->procedure = \Fisdap\EntityUtils::getEntity('AirwayProcedure', 25);
                    $tableName = "fisdap2_airways";
                    $insertData = array("procedure_id" => 25);
                    break;
                
                // Cardiac conversions
                case 2:
                    //$newSkill = new \Fisdap\Entity\CardiacIntervention();
                    //$newSkill->twelve_lead = true;
                    $tableName = "fisdap2_cardiac_interventions";
                    $insertData = array("twelve_lead" => true);
                    break;
                case 16:
                    //$newSkill = new \Fisdap\Entity\CardiacIntervention();
                    //$newSkill->procedure = \Fisdap\EntityUtils::getEntity('CardiacProcedure', 6);
                    $tableName = "fisdap2_cardiac_interventions";
                    $insertData = array("procedure_id" => 6);
                    break;
                case 17:
                    //$newSkill = new \Fisdap\Entity\CardiacIntervention();
                    //$newSkill->procedure = \Fisdap\EntityUtils::getEntity('CardiacProcedure', 5);
                    $tableName = "fisdap2_cardiac_interventions";
                    $insertData = array("procedure_id" => 5);
                    break;
                
                // IV conversions
                case 14:
                    //$newSkill = new \Fisdap\Entity\Iv();
                    //$newSkill->procedure = \Fisdap\EntityUtils::getEntity('IvProcedure', 6);
                    $tableName = "fisdap2_ivs";
                    $insertData = array("procedure_id" => 6);
                    break;
                case 18:
                    //$newSkill = new \Fisdap\Entity\Iv();
                    //$newSkill->procedure = \Fisdap\EntityUtils::getEntity('IvProcedure', 3);
                    $tableName = "fisdap2_ivs";
                    $insertData = array("procedure_id" => 3);
                    break;
                case 20:
                    //$newSkill = new \Fisdap\Entity\Iv();
                    //$newSkill->procedure = \Fisdap\EntityUtils::getEntity('IvProcedure', 5);
                    $tableName = "fisdap2_ivs";
                    $insertData = array("procedure_id" => 5);
                    break;
                
                // Other conversions
                case 10:
                    //$newSkill->procedure = \Fisdap\EntityUtils::getEntity('OtherProcedure', 9);
                    $insertData['procedure_id'] = 9;
                    break;
                
                case 13:
                    $saveSkill = false;
                    break;
                
                default:
                    $insertData['procedure_id'] = $oldSkill['OtherSkill'];
                    break;
            }
            
            if ($saveSkill) {
                $insertData['performed_by'] = ($oldSkill['PerformedBy'] == 0) ? 1 : 0;
                $insertData['subject_id'] = $this->parseSubjectType($oldSkill['SubjectTypeGroup']);
                
                //Add success and attempts to Nasogastric Tube, Chest Tube and Long Board
                if ($tableName == "fisdap2_other_interventions" && ($insertData['procedure_id'] == 3 || $insertData['procedure_id'] == 8)) {
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
