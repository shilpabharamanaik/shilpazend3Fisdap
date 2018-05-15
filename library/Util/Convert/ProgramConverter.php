<?php
/**
 * Conversion tool for converting skills tracker data to Fisdap 2.0
 */

class Util_Convert_ProgramConverter extends Util_Convert_Converter
{
    public function convert($programID)
    {
        $program = \Fisdap\EntityUtils::getEntity('ProgramLegacy', $programID);
        $programRepository = \Fisdap\EntityUtils::getRepository('ProgramLegacy');
        
        $userPopulator = new Util_Convert_UserIdPopulator();
        //$userPopulator->convert($programID);
        
        // Get the users for this program...
        //$users = $programRepository->getAssociatedUsers($programID);
        $students = $programRepository->getStudents($programID);
        $instructors = $programRepository->getInstructors($programID);
        
        $userConverter = new Util_Convert_UserConverter();
        
        $total = count($students) + count($instructors);
        
        $count = 0;
        
        foreach ($students as $student) {
            $count++;
            
            $user = \Fisdap\Entity\User::getByUsername($student->username);
            if ($user) {
                $userConverter->convert($student->user);
                echo $count . "/" . $total . ": Converted student '" . $student->username . "'\n";
            } else {
                echo $count . "/" . $total . ": UserAuthData record not found for student '" . $student->username . "'!\n";
            }
        }
        
        foreach ($instructors as $instructor) {
            $count++;
            
            $user = \Fisdap\Entity\User::getByUsername($instructor->username);
            if ($user) {
                echo $count . "/" . $total . ": Converted instructor '" . $instructor->username . "'\n";
            } else {
                echo $count . "/" . $total . ": UserAuthData record not found for instructor '" . $instructor->username . "'!\n";
            }
        }
        
        echo "Flushing...  Might take a little time...\n\n";
        $program->save();
        
        // Create the settings for this program if they don't exist...
        $query = "SELECT * FROM fisdap2_program_settings ps WHERE ps.program_id = " . $programID;
        
        $programSettings = $this->db->query($query)->fetchAll();
        
        if (count($programSettings) == 0) {
            $this->db->query("INSERT INTO fisdap2_program_settings (program_id) VALUES (" . $programID . ")");
        }
        
        echo "Done!\n";
    }
}
