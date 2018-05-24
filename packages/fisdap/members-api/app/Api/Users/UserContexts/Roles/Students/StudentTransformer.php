<?php namespace Fisdap\Api\Users\UserContexts\Roles\Students;

use Fisdap\Api\ClassSections\ClassSectionStudentTransformer;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Fractal\Transformer;


/**
 * Prepares student user role data for JSON output
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Students
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class StudentTransformer extends Transformer
{
    /**
     * @var string[]
     */
    protected $defaultIncludes = [
        'classSectionStudent'
    ];


    /**
     * @param array|StudentLegacy $student
     *
     * @return array
     */
    public function transform($student)
    {
        if ($student instanceof StudentLegacy) {
            $student = $student->toArray();
        }

        $this->removeFields([
                // these fields are duplicated from the User entity and have been deprecated
                'username',
                'first_name',
                'last_name',
                'box_number',
                'address',
                'city',
                'state',
                'zip',
                'home_phone',
                'work_phone',
                'cell_phone',
                'pager',
                'email',
                'birth_date',
                'contact_phone',
                'contact_name',
                'contact_relation',
                // these fields are duplicated from the ProgramLegacy entity,
                'program_abbreviation',
                //deprecated
                'mentor_id',
                'emt_graduation_year',
                'good_data_flag',
                'testing_expiration_date',
                'default_goal_set_id'
            ], $student);

        return $student;
    }


    /**
     * @param array|StudentLegacy $student
     *
     * @return \League\Fractal\Resource\Collection|null
     */
    public function includeClassSectionStudent($student)
    {
        if ($student instanceof StudentLegacy) {
            $studentClassSections = $student->classSectionStudent;
        } else {
            $studentClassSections = isset($student['classSectionStudent']) ? $student['classSectionStudent'] : null;
        }

        if (isset($studentClassSections)) {
            return $this->collection($studentClassSections, new ClassSectionStudentTransformer);
        }

        return null;
    }
}