<?php

namespace Fisdap\Goal;

/**
 *	Calculates student goals for Viriginia specific goals
 *	@author stape
 */
class GoalCategoryVirginia extends GoalCategoryBase
{
    const CARDIO = 112;
    const RESP = 113;
    const AMS = 114;
    const OB_DELIVERY = 115;
    const NEONATAL = 116;
    const OB_ASSESSMENT = 117;

    
    protected function forEachPatient(&$patient)
    {
        //Get the complaint IDs for this patient into a flattened array
        $complaintIds = array();
        if (is_array($patient['complaints'])) {
            $complaintIds = $patient['complaints'];
        }
        $impressions = array($patient['primary_impression_id'], $patient['secondary_impression_id']);
        
        
        //Cardiovascular distress - Impressions: Cardiac or Cardiac Arrest, Complaints: Chest Pain
        $this->add(self::CARDIO, true, $patient, (in_array(3, $impressions) || in_array(4, $impressions) || in_array(1, $complaintIds)), true);
        
        //Respiratory distress - Impressions: Respiratory, Complaints: Breathing problem
        $this->add(self::RESP, true, $patient, (in_array(18, $impressions) || in_array(2, $complaintIds)), true);
        
        //Altered Mental Status - Complaints: AMS
        $this->add(self::AMS, true, $patient, (in_array(4, $complaintIds)), true);
        
        //Obstetrics delivery - Impressions:  OB-Birth Vaginal or OB-Birth C Sect.
        $this->add(self::OB_DELIVERY, true, $patient, (in_array(36, $impressions) || in_array(37, $impressions)), true);
        
        //Obstetrics assessment - Impressions:  OB-Pregnancy Probs. OB-GYN, or OB-Labor
        $this->add(self::OB_ASSESSMENT, true, $patient, (in_array(11, $impressions) || in_array(34, $impressions) || in_array(33, $impressions)), true);
        
        //Neonatal assessment/care - Only applies to infant patients
        $ageGroup = $this->goalSet->ages->getAgeGroupForAge($patient['age'], $patient['months']);
        $this->add(self::NEONATAL, true, $patient, ($ageGroup == 'newborn'), true);
    }
}
