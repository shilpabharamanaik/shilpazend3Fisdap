<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_IvConverter extends Util_Convert_SkillConverter
{
    /**
     * This function converts all IVorIO skills over for a given patient.
     *
     * @param Patient
     */
    public function convert($patient, $shift = null)
    {
        if ($shift) {
            $query = "SELECT * FROM IVData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$shift['Student_id']} AND Shift_id = {$shift['Shift_id']} AND Run_id = -1 AND Assessment_id = -1";
            $skills = $this->db->query($query);
        } else {
            $query = "SELECT * FROM IVData skilltable INNER JOIN SubjectTypeTable sst ON sst.SubjectType_id = skilltable.SubjectType_id WHERE Student_id = {$patient['student_id']} ";
            
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
            //$newSkill = new \Fisdap\Entity\Iv();
            $insertData = array();

            // Figure out the procedure...
            if ($oldSkill['BloodDraw'] == 1 && $oldSkill['IVorIO'] == 0) {
                $insertData['procedure_id'] = 8;
            } elseif ($oldSkill['IVorIO'] == 0) {
                $insertData['procedure_id'] = 1;
            } elseif ($oldSkill['IVorIO'] == 1) {
                $insertData['procedure_id'] = 2;
            }
            
            //$newSkill->gauge = $oldSkill['IVGage'];
            $insertData['gauge'] = $oldSkill['IVGage'];
            
            if ($oldSkill['IVLocation'] == 11) {
                //$newSkill->site = \Fisdap\EntityUtils::getEntity('IvSite', 6);
                $insertData['site_id'] = 6;
            } elseif ($oldSkill['IVLocation'] == 12) {
                //$newSkill->site = \Fisdap\EntityUtils::getEntity('IvSite', 7);
                $insertData["site_id"] = 7;
            } else {
                //$newSkill->site = \Fisdap\EntityUtils::getEntity('IvSite', $oldSkill['IVLocation']);
                $insertData['site_id'] = $oldSkill['IVLocation'];
            }
            
            //$newSkill->fluid = \Fisdap\EntityUtils::getEntity('IvFluid', $oldSkill['FluidType']);
            $insertData['fluid_id'] = $oldSkill['FluidType'];
            
            //$newSkill->attempts = $oldSkill['NumAttempts'];
            $insertData['attempts'] = $oldSkill['NumAttempts'];

            //$newSkill->success = $oldSkill['Success'];
            $insertData['success'] = $oldSkill['Success'];

            //$newSkill->skill_order = $oldSkill['IVTime'];
            $insertData['skill_order'] = $oldSkill['IVTime'];
            
            
            $insertData['performed_by'] = ($oldSkill['PerformedBy'] == 0) ? 1 : 0;
            $insertData['subject_id'] = $this->parseSubjectType($oldSkill['SubjectTypeGroup']);
            if ($shift) {
                $this->setRelatedFields($insertData, $shift, 'shift');
            } else {
                $this->setRelatedFields($insertData, $patient, 'patient');
            }
            
            //$newSkill->save(false);
            $this->db->insert('fisdap2_ivs', $insertData);
        }
    }
}
