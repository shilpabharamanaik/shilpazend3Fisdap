<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_RunConverter extends Util_Convert_Converter
{
    public function convert($shift)
    {
        // User accounts are either Students or Instructors.
        $query = "
			SELECT
				*
			FROM
				RunData rd
			WHERE
				rd.shift_id = " . $shift['Shift_id'] . "
		";

        $results = $this->db->query($query);

        $patientConverter = new Util_Convert_PatientConverter();
        
        while ($run = $results->fetch()) {
            global $debug;
        
            if ($debug) {
                echo "Converting run ID #: " . $run['Run_id'] . "\n";
            }
            
            //$newRun = \Fisdap\EntityUtils::getEntity('Run', $run['Run_id']);
            $tableName = "fisdap2_runs";
            $insertData = array();

            //$newRun->id = $run['Run_id'];
            $insertData['id'] = $run['Run_id'];

            //$shift->addRun($newRun);
            $insertData['shift_id'] = $run['Shift_id'];
            $insertData['student_id'] = $run['Student_id'];
            
            //$newRun->save(false);
            $this->db->insert($tableName, $insertData);
            
            $newRun = $this->db->query("SELECT * FROM fisdap2_runs WHERE id = " . $run['Run_id'])->fetch();
            
            
            // Move over the patients for this run...
            $patientConverter->convert($newRun, "run");
        }
    }
}
