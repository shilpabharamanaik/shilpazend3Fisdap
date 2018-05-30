<?php namespace Fisdap\Api\Shifts\PracticeItems;

use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Queries\Specifications\ByShift;
use Fisdap\Data\Practice\PracticeItemRepository;
use Fisdap\Queries\Specifications\CommonSpec;
use Happyr\DoctrineSpecification\Spec;

/**
 * Service for retrieving practice items
 *
 * @package Fisdap\Api\Shifts\PracticeItems
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo this is an example service, not fully implemented
 */
class PracticeItemsFinder
{
    /**
     * @var PracticeItemRepository
     */
    protected $repository;


    /**
     * @param PracticeItemRepository $repository
     */
    public function __construct(PracticeItemRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * @param int   $shiftId
     * @param array $associations
     * @param array $associationIds
     * @param int   $firstResult
     * @param int   $maxResults
     *
     * @throws \Fisdap\Api\Queries\Exceptions\ResourceNotFound
     * @return array shifts
     */
    public function getPracticeItems(
        $shiftId,
        array $associations = [],
        array $associationIds = [],
        $firstResult = null,
        $maxResults = null
    ) {
        $spec = CommonSpec::makeSpecWithAssociations($associations, $associationIds);

        $spec->andX(new ByShift($shiftId));

        $practiceItems = $this->repository->match($spec, Spec::asArray(), $firstResult, $maxResults);

        if (empty($practiceItems)) {
            throw new ResourceNotFound("No practice items found for shift id '$shiftId");
        }

        return $practiceItems;
    }
}
