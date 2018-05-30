<?php namespace Fisdap\Api\Programs\Sites\Bases\Finder;

use Fisdap\Api\Programs\Sites\Bases\Queries\Specifications\BasesMatchingShifts;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\ResourceFinder\ResourceFinder;
use Fisdap\Data\Base\BaseLegacyRepository;
use Fisdap\Queries\Specifications\Distinct;
use Happyr\DoctrineSpecification\Spec;

/**
 * Service for retrieving bases
 *
 * @package Fisdap\Api\Programs\Sites\Bases
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class BasesFinder extends ResourceFinder implements FindsBases
{
    /**
     * @var BaseLegacyRepository
     */
    protected $repository;


    /**
     * @param BaseLegacyRepository $repository
     */
    public function __construct(BaseLegacyRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @inheritdoc
     */
    public function findDistinctStudentShiftBases($studentId)
    {
        $distinctStudentShiftBases = $this->repository->match(
            Spec::andX(
                new Distinct,
                new BasesMatchingShifts,
                Spec::eq('student', $studentId, 'shift')
            ),
            Spec::asArray()
        );

        if (empty($distinctStudentShiftBases)) {
            throw new ResourceNotFound("No distinct shift bases found for student with id '$studentId");
        }

        return $distinctStudentShiftBases;
    }
}
