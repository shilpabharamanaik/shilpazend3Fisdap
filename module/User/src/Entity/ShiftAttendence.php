<?php namespace User\Entity;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use User\EntityUtils;


/**
 * Entity class for Shift History Change.
 *
 * @Entity(repositoryClass="Fisdap\Data\ShiftAttendance\DoctrineShiftAttendanceRepository")
 * @Table(name="fisdap2_shift_attendence")
 */
class ShiftAttendence extends Enumerated
{
    public static function getFormOptions($na = false, $sort=true, $displayName = "name")
	{
        
		$options = array();
		$repo = EntityUtils::getEntityManager()->getRepository('\Fisdap\Entity\ShiftAttendence');
		$results = $repo->findAll();
		
		foreach($results as $result) {
			$options[$result->id] = $result->name;
		}
        
		return $options;
	}

    public static function getFormOptionsByProgram($programId) {

        $options = self::getFormOptions();

        $program = EntityUtils::getEntity('ProgramLegacy', $programId);

        if (!$program->program_settings->allow_tardy) {
            unset($options[2]);
        }

        if (!$program->program_settings->allow_absent) {
            unset($options[3]);
        }

        if (!$program->allow_absent_with_permission) {
            unset($options[4]);
        }

        return $options;
    }

    
    public static function getFormOptionsInstructor()
    {
        
        $options = array();
        $repo = EntityUtils::getEntityManager()->getRepository('\Fisdap\Entity\ShiftAttendence');
        $results = $repo->findAll();
        
        foreach($results as $result) {
            $options[$result->id] = $result->name;
        }
        
        return $options;
    }
}