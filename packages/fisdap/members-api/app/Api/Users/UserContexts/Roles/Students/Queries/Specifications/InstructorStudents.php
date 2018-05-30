<?php namespace Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications;

use Fisdap\Api\Users\Queries\Specifications\UsersWithContextsAndProgram;
use Fisdap\Api\Users\Queries\InstructorStudentQueryParameters;
use Fisdap\Queries\Specifications\Distinct;
use Fisdap\Queries\Specifications\Partial;
use Fisdap\Queries\Specifications\PartialOverrides;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;

/**
 * Class InstructorStudents
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class InstructorStudents extends BaseSpecification
{
    /**
     * @var InstructorStudentQueryParameters
     */
    private $queryParams;


    /**
     * @param null|string $queryParams
     * @param string $dqlAlias
     * @internal param int $instructorId
     */
    public function __construct($queryParams, $dqlAlias = null)
    {
        parent::__construct($dqlAlias);

        $this->queryParams = $queryParams;
    }


    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        return Spec::andX(
            new Distinct,
            new UsersWithContextsAndProgram,
            new ActiveStudents,
            new StudentsOfInstructorClassSections('student'),
            Spec::eq('instructor', $this->queryParams->getInstructorId(), 'classSectionInstructor'),
            ($this->queryParams->getDateFrom() ? Spec::gt('updated_on', $this->queryParams->getDateFrom()) : null),
            Spec::orderBy('last_name', 'ASC'),
            Spec::orderBy('first_name', 'ASC'),
            new PartialOverrides([
                new Partial(['id', 'first_name', 'last_name', 'updated_on']),
                new Partial(['id', 'end_date'], 'userContexts'),
                new Partial(['id', 'name'], 'certification_level'),
                new Partial(['id'], 'student'),
                new Partial(['id'], 'program'),
                new Partial(['id'], 'graduation_status'),
                new Partial(['id', 'section'], 'classSectionStudent'),
                new Partial(['id', 'start_date', 'end_date'], 'classSection'),
                new Partial(['id', 'instructor'], 'classSectionInstructor'),
            ])
        );
    }
}
