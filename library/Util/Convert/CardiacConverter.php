<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_CardiacConverter extends Util_Convert_SkillConverter
{
    /**
     * This function converts all airway skills over for a given patient.
     *
     * @param Patient
     */
    public function convert($patient, $shift = null)
    {
        if ($shift) {
            $query = "SELECT * FROM EKGData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$shift['Student_id']} AND Shift_id = {$shift['Shift_id']} AND Run_id = -1 AND Assessment_id = -1";
            $skills = $this->db->query($query);
        } else {
            $query = "SELECT * FROM EKGData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$patient['student_id']} ";
            
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
            $tableName = "fisdap2_cardiac_interventions";
            $insertData = array();
            $ectopies = array();
            
            $insertData['rhythm_performed_by'] = ($oldSkill['PerformedBy']==0) ? 1 : 0;
            $insertData['twelve_lead'] = $oldSkill['TwelveLead'];
            
            // Some corrections to the data here...
            if ($oldSkill['Rhythm'] == 1) {
                $insertData['rhythm_type_id'] = 25;
                $ectopies[] = 5;
            } elseif ($oldSkill['Rhythm'] == 17) {
                $insertData['rhythm_type_id'] = 25;
                $ectopies[] = 3;
            } elseif ($oldSkill['Rhythm'] == 18) {
                $insertData['rhythm_type_id'] = 25;
                $ectopies[] = 3;
                $ectopies[] = 5;
            } else {
                $insertData['rhythm_type_id'] = $oldSkill['Rhythm'];
            }
            
            if ($oldSkill['Defibrillation']) {
                $insertData['procedure_id'] = 2;
                $insertData['procedure_method_id'] = 1;
                $insertData['performed_by'] = ($oldSkill['PerformedBy']==0) ? 1 : 0;
            } elseif ($oldSkill['Sync']) {
                $insertData['procedure_id'] = 3;
                $insertData['performed_by'] = ($oldSkill['PerformedBy']==0) ? 1 : 0;
            } elseif ($oldSkill['Pacing']) {
                $insertData['procedure_id'] = 4;
                $insertData['pacing_method_id'] = 1;
                $insertData['performed_by'] = ($oldSkill['PerformedBy']==0) ? 1 : 0;
            }
            
            $insertData['subject_id'] = $this->parseSubjectType($oldSkill['SubjectTypeGroup']);

            if ($shift) {
                $this->setRelatedFields($insertData, $shift, 'shift');
            } else {
                $this->setRelatedFields($insertData, $patient, 'patient');
            }
            
            //$newSkill->save(false);
            $this->db->insert($tableName, $insertData);
            
            //Save the ectopy data
            $cardiac_id = $this->db->lastInsertId();
            foreach ($ectopies as $ectopy) {
                $insertData = array(
                    "cardiac_intervention_id" => $cardiac_id,
                    "ectopy_id" => $ectopy,
                );
                $this->db->insert("fisdap2_cardiac_interventions_ectopy", $insertData);
            }
        }
    }
}
