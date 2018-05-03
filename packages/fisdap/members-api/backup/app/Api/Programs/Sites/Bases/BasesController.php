<?php namespace Fisdap\Api\Programs\Sites\Bases;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Programs\Sites\Bases\Finder\FindsBases;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;

/**
 * Handles HTTP transport and data transformation for base-related routes
 *
 * @package Fisdap\Api\Programs\Sites\Bases
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class BasesController extends Controller
{
    use ResponseHelpers, CommonInputParameters;


    /**
     * @var FindsBases
     */
    private $finder;


    /**
     * @param FindsBases      $finder
     * @param Manager         $fractal
     * @param BaseTransformer $transformer
     */
    public function __construct(FindsBases $finder, Manager $fractal, BaseTransformer $transformer)
    {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * Retrieve the specified base from storage
     *
     * @param  int $baseId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Programs"},
     *     path="/programs/sites/bases/{baseId}",
     *     summary="Get a base",
     *     description="Get a base",
     *     @SWG\Parameter(name="baseId", in="path", required=true, type="integer"),
     *     @SWG\Response(response="200", description="A base"),
     * )
     */
    public function show($baseId)
    {
        return $this->respondWithItem(
            $this->finder->findById($baseId, $this->initAndGetIncludes(), $this->getIncludeIds(), true),
            $this->transformer
        );
    }


    /**
     * @param int $studentId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Students"},
     *     path="/students/{studentId}/shifts/bases/distinct",
     *     summary="List distinct bases across all a student's shifts",
     *     description="List distinct bases across all a student's shifts",
     *     @SWG\Parameter(name="studentId", in="path", required=true, type="integer"),
     *     @SWG\Response(response="200", description="A list of bases"),
     * )
     */
    public function getDistinctStudentShiftBases($studentId)
    {
        return $this->respondWithCollection(
            $this->finder->findDistinctStudentShiftBases($studentId),
            $this->transformer
        );
    }
}
