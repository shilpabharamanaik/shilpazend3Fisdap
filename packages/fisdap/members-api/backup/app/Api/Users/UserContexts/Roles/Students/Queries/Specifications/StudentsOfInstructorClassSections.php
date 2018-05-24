<?php namespace Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications;

use Carbon\Carbon;
use Fisdap\Queries\Specifications\QueryModifiers\LeftFetchJoin;
use Happyr\DoctrineSpecification\BaseSpecification;
use Happyr\DoctrineSpecification\Spec;


/**
 * Class StudentsOfInstructorClassSections
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class StudentsOfInstructorClassSections extends BaseSpecification
{
    /**
     * @var bool
     */
    private $active;


    /**
     * @param null $dqlAlias,
     * @param bool $active
     */
    public function __construct($dqlAlias = null, $active = true)
    {
        parent::__construct($dqlAlias);

        $this->active = $active;
    }


    /**
     * @return \Happyr\DoctrineSpecification\Logic\AndX
     */
    public function getSpec()
    {
        $spec = Spec::andX(
            new LeftFetchJoin('classSectionStudent', 'classSectionStudent'),
            new LeftFetchJoin('section', 'classSection', 'classSectionStudent'),
            new LeftFetchJoin('section_instructor_associations', 'classSectionInstructor', 'classSection')
        );

        if ($this->active === true) {
            $spec->andX(Spec::lte('start_date', Carbon::now(), 'classSection'));
            $spec->andX(Spec::gte('end_date', Carbon::now(), 'classSection'));
        }

        return $spec;
    }
}