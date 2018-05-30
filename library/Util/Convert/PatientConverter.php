<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_PatientConverter extends Util_Convert_Converter
{
    /**
     * This function can fetch back patients for either shifts or runs-
     * Shifts should pull their patients from Assessment Data, and Runs should
     * pull their data from the Run Data and Patient Data.
     *
     * @param Shift or Run Entity $model Entity to use to attach patients to.
     */
    public function convert($model, $type = "run")
    {
        if ($type == "shift") {
            $shift_id = $model['Shift_id'];

            $query = "
				SELECT
					ad.*,
					gt.GenderTitle,
					et.EthnicTitle
				FROM
					AssesmentData ad
					LEFT JOIN GenderTable gt ON gt.gender_id = ad.gender
					LEFT JOIN EthnicityTable et ON et.ethnic_id = ad.ethnicity
				WHERE
					ad.Shift_id = $shift_id";
            $patients = $this->db->query($query);

            while ($patient = $patients->fetch()) {
                //get insert data for new patient record
                $insertPatientData = $this->populatePatientData($patient);
                
                //grab mechanism of injury info to store once the patient has been saved
                if (isset($insertPatientData['set_mechanisms'])) {
                    $mechanisms = $insertPatientData['set_mechanisms'];
                    unset($insertPatientData['set_mechanisms']);
                }
                
                //grab msa_responses info to store once the patient has been saved
                if (isset($insertPatientData['set_msa_responses'])) {
                    $msa_responses = $insertPatientData['set_msa_responses'];
                    unset($insertPatientData['set_msa_responses']);
                }
                
                $insertPatientData['legacy_assessment_id'] = $patient['Asses_id'];

                //create new run and attach it to shift
                $this->db->insert("fisdap2_runs", array("shift_id" => $model['Shift_id'], "student_id" => $model['Student_id']));
                $run_id = $this->db->lastInsertId();
                
                //attach new patient to run
                $insertPatientData['run_id'] = $run_id;
                $insertPatientData['shift_id'] = $shift_id;
                $insertPatientData['student_id'] = $model['Student_id'];
                
                $this->db->insert('fisdap2_patients', $insertPatientData);
                $patient_id = $this->db->lastInsertId();
                
                //insert mechanisms
                if (count($mechanisms) > 0) {
                    foreach ($mechanisms as $mechanism) {
                        $this->db->insert("fisdap2_patients_mechanisms", array("patient_id" => $patient_id, "mechanism_id" => $mechanism));
                    }
                }
                
                //insert msa_responses
                if (count($msa_responses) > 0) {
                    foreach ($msa_responses as $msa_response) {
                        $this->db->insert("fisdap2_patients_responses", array("patient_id" => $patient_id, "response_id" => $msa_response));
                    }
                }
                
                $newPatient = $this->db->query("SELECT * FROM fisdap2_patients WHERE id = $patient_id")->fetch();
                
                $this->convertSkills($newPatient);
            }
            
            $this->convertOrphanSkills($model);
        } elseif ($type == "run") {
            $run_id = $model['id'];

            $query = "
				SELECT
					rd.*,
					gt.GenderTitle,
					et.EthnicTitle
				FROM
					RunData rd
					LEFT JOIN GenderTable gt ON gt.gender_id = rd.Gender
					LEFT JOIN EthnicityTable et ON et.ethnic_id = rd.Ethnicity
				WHERE
					Run_id = $run_id";

            $patients = $this->db->query($query);

            // Should only be 1, but just in case...
            while ($patient = $patients->fetch()) {
                //get insert data for new patient record
                $insertPatientData = $this->populatePatientData($patient);
                
                //grab mechanism of injury info to store once the patient has been saved
                if (isset($insertPatientData['set_mechanisms'])) {
                    $mechanisms = $insertPatientData['set_mechanisms'];
                    unset($insertPatientData['set_mechanisms']);
                }
                
                //grab msa_responses info to store once the patient has been saved
                if (isset($insertPatientData['set_msa_responses'])) {
                    $msa_responses = $insertPatientData['set_msa_responses'];
                    unset($insertPatientData['set_msa_responses']);
                }
                
                $insertPatientData['legacy_run_id'] = $patient['Run_id'];

                //Set run-specific info
                if ($patient['Witness'] == 4 || $patient['Witness'] == 5) {
                    $insertPatientData['witness_id'] = 7;
                } elseif ($patient['Witness'] > 0) {
                    $insertPatientData['witness_id'] = $patient['Witness'];
                }
                
                if ($patient['PulseReturn'] > 0) {
                    $insertPatientData['pulse_return_id'] = $patient['PulseReturn'];
                }
                
                if ($patient['Disposition'] == 3) {
                    $insertPatientData['patient_disposition_id'] = 1;
                    $insertPatientData['transport_mode_id'] = 3;
                } else {
                    $insertPatientData['patient_disposition_id'] = $patient['Disposition'];
                }
                
                if ($patient['Priority'] == 3) {
                    $insertPatientData['transport_mode_id'] = 3;
                }
                
                
                $insertPatientData['team_lead'] = $patient['TeamLeader'];
                $insertPatientData['preceptor_id'] = $patient['Precept_id'];
                $insertPatientData['team_size'] = $patient['NumInTeam'];
                
                //attach new patient to run
                $insertPatientData['run_id'] = $run_id;
                $insertPatientData['shift_id'] = $model['shift_id'];
                $insertPatientData['student_id'] = $model['student_id'];
                
                $this->db->insert('fisdap2_patients', $insertPatientData);
                $patient_id = $this->db->lastInsertId();
                
                //insert mechanisms
                if (count($mechanisms) > 0) {
                    foreach ($mechanisms as $mechanism) {
                        $this->db->insert("fisdap2_patients_mechanisms", array("patient_id" => $patient_id, "mechanism_id" => $mechanism));
                    }
                }
                
                //insert msa_responses
                if (count($msa_responses) > 0) {
                    foreach ($msa_responses as $msa_response) {
                        $this->db->insert("fisdap2_patients_responses", array("patient_id" => $patient_id, "response_id" => $msa_response));
                    }
                }
                
                $newPatient = $this->db->query("SELECT * FROM fisdap2_patients WHERE id = $patient_id")->fetch();
                
                $this->convertSkills($newPatient);
            }
        }
    }
    
    /**
     * This function takes an old Fisdap patient record and creates a new
     * Patient entity, seeds it with data from the record, and returns it.
     *
     * @param Object $result DQL Result object containing the possible existing
     * patient, or null if no current patient was found.
     * @param Array $data Array containing the patient information.
     *
     * @return \Fisdap\Entity\Patient Entity.
     */
    public function populatePatientData($patient)
    {
        $insertPatientData = array();
        
        $insertPatientData['primary_impression_id'] = $patient['Diagnosis'];
        $insertPatientData['secondary_impression_id'] = $patient['Diag2'];
        
        $insertPatientData['gender_id'] = $patient['Gender'];
        $insertPatientData['ethnicity_id'] = $patient['Ethnicity'];
        
        $insertPatientData['age'] = $patient['Age'];
        $insertPatientData['months'] = $patient['Months'];
        
        if (isset($patient['SubjectType_id'])) {
            $newSubjId = 1;
            
            $query = "SELECT * FROM SubjectTypeTable WHERE SubjectType_id = " . $patient['SubjectType_id'];
            $oldSubjectType = $this->db->query($query)->fetchAll();
            
            if (count($oldSubjectType) == 1) {
                $oldSubject = $oldSubjectType[0];
                
                list($sName, $sType) = explode(' - ', $oldSubject['SubjectTypeGroup']);
                
                if ($sName == 'Human' && $sType == 'Live') {
                    $newSubjId = 1;
                }
                if ($sName == 'Human' && $sType == 'Cadaver') {
                    $newSubjId = 2;
                }
                if ($sName == 'Animal' && $sType == 'Live') {
                    $newSubjId = 3;
                }
                if ($sName == 'Animal' && $sType == 'Cadaver') {
                    $newSubjId = 4;
                }
                if ($sName == 'Manikin' && $sType == 'Simulator') {
                    $newSubjId = 5;
                }
                if ($sName == 'Manikin' && $sType == 'Other') {
                    $newSubjId = 6;
                }
            }
            
            $insertPatientData['subject_id'] = $newSubjId;
        }
        
        // MOI and LOC exist in both types- set the appropriate fields here...
        switch ($patient['MOI']) {
            case 8: // GSW/Stabbing
                $insertPatientData['cause_id'] = 13;
                break;
            case 9:  // Heat/Cold Emerg.
                $insertPatientData['cause_id'] = 9;
                break;
            case 10: // MVA Trauma
                $insertPatientData['cause_id'] = 17;
                break;
            case 14: // Other Trauma
                $insertPatientData['set_mechanisms'] = array(4);
                break;
            case 17: // Family Violence
                $insertPatientData['cause_id'] = 5;
                break;
            case 20: // Blunt Trauma
                $insertPatientData['set_mechanisms'] = array(1);
                break;
            case 21: // Penetrating Trauma
                $insertPatientData['set_mechanisms'] = array(3);
                break;
            case 22: // Sexual Assault
                $insertPatientData['cause_id'] = 19;
                $insertPatientData['intent_id'] = 3;
                break;
            case 24: // Fall
                $insertPatientData['cause_id'] = 11;
                break;
            case 25: // Assault
                $insertPatientData['intent_id'] = 3;
                break;
        }
        
        switch ($patient['LOC']) {
            case 1: // Unknown
                $insertPatientData['msa_alertness_id'] = 1; // N/A
                break;
            case 2: // A - Alert and Oriented
                $insertPatientData['msa_alertness_id'] = 2; // Yes
                break;
            case 3: // V - Responds to Voice
                $insertPatientData['msa_alertness_id'] = 3; // No
                $insertPatientData['set_msa_responses'] = array(1);
                break;
            case 4: // P - Responds to Pain
                $insertPatientData['msa_alertness_id'] = 3; // No
                $insertPatientData['set_msa_responses'] = array(2);
                break;
            case 5: // U - Unresponsive
                $insertPatientData['msa_alertness_id'] = 3; // No
                $insertPatientData['set_msa_responses'] = array(3);
                break;
            case 6: // A - Alert and Disoriented
                $insertPatientData['msa_alertness_id'] = 3; // No
                break;
        }
        
        global $debug;
        
        if ($debug) {
            echo "Created patient...\n";
        }
        
        return $insertPatientData;
    }
    
    public function convertSkills($patient)
    {
        $ac = new Util_Convert_AirwayConverter();
        $ac->convert($patient);
        
        $cc = new Util_Convert_CardiacConverter();
        $cc->convert($patient);
        
        $ic = new Util_Convert_IvConverter();
        $ic->convert($patient);
        
        $mc = new Util_Convert_MedConverter();
        $mc->convert($patient);
        
        $oc = new Util_Convert_OtherConverter();
        $oc->convert($patient);
        
        $vc = new Util_Convert_VitalConverter();
        $vc->convert($patient);
        
        $bc = new Util_Convert_BLSConverter();
        $bc->convert($patient);
        
        $compConverter = new Util_Convert_ComplaintConverter();
        $compConverter->convert($patient);
    }
    
    public function convertOrphanSkills($shift)
    {
        $ac = new Util_Convert_AirwayConverter();
        $ac->convert(null, $shift);
        
        $cc = new Util_Convert_CardiacConverter();
        $cc->convert(null, $shift);
        
        $ic = new Util_Convert_IvConverter();
        $ic->convert(null, $shift);
        
        $mc = new Util_Convert_MedConverter();
        $mc->convert(null, $shift);
        
        $oc = new Util_Convert_OtherConverter();
        $oc->convert(null, $shift);
        
        $vc = new Util_Convert_VitalConverter();
        $vc->convert(null, $shift);
        
        $bc = new Util_Convert_BLSConverter();
        $bc->convert(null, $shift);
    }
}
