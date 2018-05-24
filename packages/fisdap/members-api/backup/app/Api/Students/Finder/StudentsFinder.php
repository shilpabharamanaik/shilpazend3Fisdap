<?php namespace Fisdap\Api\Students\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Queries\Specifications\ById;
use Fisdap\Data\Student\StudentLegacyRepository;
use Fisdap\Queries\Specifications\CommonSpec;
use Happyr\DoctrineSpecification\Spec;


/**
 * Service for retrieving one or more students by various criteria
 *
 * @package Fisdap\Api\Students
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class StudentsFinder implements FindsStudents
{
    /**
     * @var StudentLegacyRepository
     */
    protected $repository;


    /**
     * @param StudentLegacyRepository $repository
     */
    public function __construct(StudentLegacyRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @inheritdoc
     */
    public function getById($id, array $associations = null, array $associationIds = null, $asArray = false) {
        $spec = CommonSpec::makeSpecWithAssociations($associations, $associationIds);
        $spec->andX(new ById($id));

        $students = $this->repository->match($spec, $asArray ? Spec::asArray() : null);

        if (empty($students)) {
            throw new ResourceNotFound("No student found with ID '$id'");
        }

        return array_shift($students);
    }
}