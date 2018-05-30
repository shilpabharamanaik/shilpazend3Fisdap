<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_UserConverter extends Util_Convert_Converter
{
    public function convert($userModel)
    {
        global $debug;
        
        if ($debug) {
            echo "Converting user ID: #" . $userModel->id . "\n";
        }
        
        //// Update a few fields on the user then send it off to the shift converter...
        //if($userModel->getCurrentRoleName() == 'student'){
        //	$snQuery = "SELECT * FROM SerialNumbers sn WHERE Student_id = " . $userModel->student->id;
        //}else{
        //	$snQuery = "SELECT * FROM SerialNumbers sn WHERE Instructor_id = " . $userModel->instructor->id;
        //}
        //
        //$snRes = $this->db->query($snQuery)->fetchAll();
        //
        //foreach($snRes as $sn){
        //	$this->db->query("UPDATE SerialNumbers SET User_id = " . $userModel->id . " WHERE SN_id = " . $sn['SN_id']);
        //}
        
        if ($userModel->getCurrentRoleName() == 'student') {
            global $debug;
        
            if ($debug) {
                echo "Converting shifts for student ID: #" . $userModel->getCurrentRoleData()->id . "\n";
            }
            
            $shiftConverter = new Util_Convert_ShiftConverter();
            $shiftConverter->convert($userModel);
        }
        
        $userModel->flush();
    }
}
