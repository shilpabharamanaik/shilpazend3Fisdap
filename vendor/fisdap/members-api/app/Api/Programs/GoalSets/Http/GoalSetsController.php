<?php namespace Fisdap\Api\Programs\GoalSets\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Programs\GoalSets\Finder\FindsGoalSets;
use Fisdap\Api\Programs\GoalSets\Transformation\GoalSetTransformer;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Handles HTTP transport and data transformation for goal-set-related routes
 *
 * @package Fisdap\Api\Programs\GoalSets\Http
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class GoalSetsController extends Controller
{
    use ResponseHelpers, CommonInputParameters;

    /**
     * @var FindsGoalSets
     */
    private $finder;


    /**
     * @param FindsGoalSets $finder
     * @param Manager $fractal
     * @param GoalSetTransformer $transformer
     */
    public function __construct(FindsGoalSets $finder, Manager $fractal, GoalSetTransformer $transformer)
    {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * Retrieve a program's goal-sets
     *
     * @param  int $programId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Programs"},
     *     path="/programs/{programId}/goal-sets",
     *     summary="Get a program's goal-sets",
     *     description="Get a program's goal-sets",
     *     @SWG\Parameter(name="programId", in="path", required=true, type="integer"),
     *     @SWG\Response(response="200", description="A list of goal-sets"),
     * )
     */
    public function show($programId)
    {
        return $this->respondWithCollection(
            $this->finder->findProgramGoalSets($programId),
            $this->transformer
        );
    }
}
