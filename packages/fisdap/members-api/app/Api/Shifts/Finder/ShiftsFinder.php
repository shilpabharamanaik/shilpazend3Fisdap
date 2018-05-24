<?php namespace Fisdap\Api\Shifts\Finder;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Queries\Specifications\ById;
use Fisdap\Api\Shifts\Queries\ShiftQueryParameters;
use Fisdap\Api\Shifts\Queries\Specifications\InstructorShifts;
use Fisdap\Api\Shifts\Queries\Specifications\ProgramShifts;
use Fisdap\Api\Shifts\Queries\Specifications\StudentShifts;
use Fisdap\Api\Users\UserContexts\Roles\Students\Queries\Specifications\AssociatedStudentProgramId;
use Fisdap\Data\Shift\ShiftLegacyRepository;
use Fisdap\Entity\ShiftLegacy;
use Fisdap\Queries\Specifications\CommonSpec;
use Happyr\DoctrineSpecification\Spec;


/**
 * Service for retrieving one or more shifts by various criteria
 *
 * @package Fisdap\Api\Shifts
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ShiftsFinder implements FindsShifts
{
    /**
     * @var ShiftLegacyRepository
     */
    protected $repository;


    /**
     * @param ShiftLegacyRepository $repository
     */
    public function __construct(ShiftLegacyRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @inheritdoc
     */
    public function getById($id, array $associations = null, array $associationIds = null, $asArray = false) {
        $spec = CommonSpec::makeSpecWithAssociations($associations, $associationIds);
        $spec->andX(new ById($id));

        $shifts = $this->repository->match($spec, $asArray ? Spec::asArray() : null);

        if (empty($shifts)) {
            throw new ResourceNotFound("No shift found with ID '$id'");
        }

        return array_shift($shifts);
    }


    /**
     * @inheritdoc
     */
    public function getStudentShifts(ShiftQueryParameters $queryParams)
    {
        $studentShifts = $this->repository->match(
            new StudentShifts($queryParams),
            $queryParams->getFirstResult(),
            $queryParams->getMaxResults()
        );

        if (empty($studentShifts)) {
            throw new ResourceNotFound("No shifts found for student");
        }

        return $studentShifts;
    }


    /**
     * @inheritdoc
     */
    public function getProgramShifts(ShiftQueryParameters $queryParams)
    {
        $programShifts = $this->repository->match(
            new ProgramShifts($queryParams),
            Spec::asArray(),
            $queryParams->getFirstResult(),
            $queryParams->getMaxResults()
        );

        if (empty($programShifts)) {
            throw new ResourceNotFound("No shifts found for program");
        }

        return $programShifts;
    }


    /**
     * @inheritdoc
     */
    public function getInstructorShifts(ShiftQueryParameters $queryParams)
    {
        $instructorShifts = $this->repository->match(
            new InstructorShifts($queryParams),
            Spec::asArray(),
            $queryParams->getFirstResult(),
            $queryParams->getMaxResults()
        );

        if (empty($instructorShifts)) {
            throw new ResourceNotFound("No shifts found for instructor");
        }

        return $instructorShifts;
    }


    /**
     * @inheritdoc
     */
    public function getShiftStudentProgramId($id)
    {
        $studentProgramId = $this->repository->match(
            Spec::andX(
                new ById($id),
                new AssociatedStudentProgramId()
            ),
            Spec::asArray()
        );

        if (empty($studentProgramId)) {
            throw new ResourceNotFound("Student program ID not found for shift ID '$id'.");
        }

        return $studentProgramId[0]['student']['program']['id'];
    }
}