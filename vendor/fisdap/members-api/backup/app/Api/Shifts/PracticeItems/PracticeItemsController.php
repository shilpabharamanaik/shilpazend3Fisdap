<?php namespace Fisdap\Api\Shifts\PracticeItems;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;


/**
 * Class PracticeItemsController
 *
 * @package Fisdap\Api\Shifts\PracticeItems
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo this is an example controller, not fully implemented
 */
final class PracticeItemsController extends Controller
{
    use ResponseHelpers, CommonInputParameters;


    /**
     * @var PracticeItemsFinder
     */
    private $finder;


    /**
     * @param PracticeItemsFinder     $finder
     * @param Manager                 $fractal
     * @param PracticeItemTransformer $transformer
     */
    public function __construct(PracticeItemsFinder $finder, Manager $fractal, PracticeItemTransformer $transformer)
    {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * @param int $shiftId
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPracticeItems($shiftId)
    {
        return $this->respondWithCollection(
            $this->finder->getPracticeItems(
                $shiftId,
                $this->initAndGetIncludes(), $this->getIncludeIds(),
                $this->getFirstResult(), $this->getMaxResults()
            ),
            $this->transformer
        );
    }
}