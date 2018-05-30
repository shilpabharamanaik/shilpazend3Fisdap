<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_ShiftConverter extends Util_Convert_Converter
{
    public function convert($userModel)
    {
        if (!$userModel->isInstructor()) {
            //$dql = "SELECT s FROM Fisdap\Entity\ShiftLegacy s WHERE s.student = :id";
            //
            //$query = $this->em->createQuery($dql);
            //$query->setParameter('id', $userModel->getCurrentRoleData()->id);
            //
            //$shifts = $query->getResult();
            $query = "SELECT * FROM ShiftData WHERE Student_id = " . $userModel->getCurrentRoleData()->id;
            $results = $this->db->query($query);
            
            $runConverter = new Util_Convert_RunConverter();
            $patientConverter = new Util_Convert_PatientConverter();
            
            while ($shift = $results->fetch()) {
                global $debug;
                $updateData = array();

                if ($debug) {
                    echo "Converting " . $shift['Type'] . " shift ID #: " . $shift['Shift_id'] . "\n";
                }
                
                // For these, move the patients directly onto the shift...
                if ($shift['Type'] == 'field') {
                    // Migrate the run data, then import the patients onto the run
                    $runConverter->convert($shift);
                } else {
                    // Migrate the patient data over for these shifts as well...
                    $patientConverter->convert($shift, "shift");
                }
                
                // Set the correct attendance for this shift...
                // 0 = Not Complete
                // 1 = Complete
                // 2 = Absent
                // 3 = Absent w/ permission.
                // Check completed flag first- only use tardy flag in cases
                // where completed is 0 or 1
                if ($shift['Completed'] == 0 || $shift['Completed'] == 1) {
                    // Check the tardy flag here...
                    if ($shift['Tardy'] == 0) {
                        //$shift->attendence = \Fisdap\EntityUtils::getEntity('ShiftAttendence', 1);
                        $updateData['attendence_id'] = 1;
                    } else {
                        //$shift->attendence = \Fisdap\EntityUtils::getEntity('ShiftAttendence', 2);
                        $updateData['attendence_id'] = 2;
                    }
                } elseif ($shift['Completed'] == 2) {
                    //$shift->attendence = \Fisdap\EntityUtils::getEntity('ShiftAttendence', 3);
                    $updateData['attendence_id'] = 3;
                } elseif ($shift['Completed'] == 3) {
                    //$shift->attendence = \Fisdap\EntityUtils::getEntity('ShiftAttendence', 4);
                    $updateData['attendence_id'] = 4;
                }
                
                //$shift->save(false);
                $this->db->update("ShiftData", $updateData, "Shift_id = " . $shift['Shift_id']);
            }
        }
    }
}
