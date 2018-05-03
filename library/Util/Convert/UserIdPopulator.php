<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_UserIdPopulator extends Util_Convert_Converter
{
	public function convert($programID)
	{
        //Convert students
        $query = "SELECT Student_id, idx FROM StudentData sd, UserAuthData uad WHERE sd.Username = uad.email AND Program_id = $programID";
        $result = $this->db->query($query);
        
        while($account = $result->fetch()) {
            $query = "UPDATE StudentData SET user_id = " . $account['idx'] . " WHERE Student_id = " . $account['Student_id'] . " LIMIT 1";
            $this->db->query($query);
            echo "Populated " . $account['idx'] . "\n";
        }
        
        //Convert instructors
        $query = "SELECT Instructor_id, idx FROM InstructorData id, UserAuthData uad WHERE id.Username = uad.email AND ProgramId = $programID";
        $result = $this->db->query($query);
        
        while($account = $result->fetch()) {
            $query = "UPDATE InstructorData SET user_id = " . $account['idx'] . " WHERE Instructor_id = " . $account['Instructor_id'] . " LIMIT 1";
            $this->db->query($query);
            echo "Populated " . $account['idx'] . "\n";
        }
    }
}