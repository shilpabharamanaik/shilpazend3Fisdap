<?php namespace Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications;

use Fisdap\Api\Users\Queries\ProgramStudentQueryParameters;
use Fisdap\Api\Users\Queries\Specifications\UsersWithContextsAndProgram;
use Fisdap\Queries\Specifications\Partial;
use Fisdap\Queries\Specifications\PartialOverrides;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class ProgramStudents
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ProgramStudents extends BaseSpecification
{
    /**
     * @var ProgramStudentQueryParameters
     */
    private $queryParams;


    /**
     * @param ProgramStudentQueryParameters     $queryParams
     * @param string                            $dqlAlias
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
            new UsersWithContextsAndProgram,
            new ActiveStudents,
            Spec::eq('id', $this->queryParams->getProgramId(), 'program'),
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
            ])
        );
    }
}