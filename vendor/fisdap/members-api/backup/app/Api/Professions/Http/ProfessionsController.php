<?php namespace Fisdap\Api\Professions\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Professions\ProfessionTransformer;
use Fisdap\Data\Profession\ProfessionRepository;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Handles HTTP transport and data transformation for profession-related routes
 *
 * @package Fisdap\Api\Professions\Http
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProfessionsController extends Controller
{
    use CommonInputParameters, ResponseHelpers;


    /**
     * @param Manager               $fractal
     * @param ProfessionTransformer $transformer
     */
    public function __construct(Manager $fractal, ProfessionTransformer $transformer)
    {
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * @param ProfessionRepository $professionRepository
     *
     * @return JsonResponse
     *
     * @SWG\Get(
     *     tags={"Professions"},
     *     path="/professions",
     *     summary="Get a list of professions with their certifications",
     *     description="Get a list of professions with their certifications",
     *     @SWG\Response(response="200", description="A list of professions"),
     * )
     */
    public function index(ProfessionRepository $professionRepository)
    {
        $this->initAndGetIncludes();

        return $this->respondWithCollection(
            $professionRepository->findAll(),
            $this->transformer
        );
    }
}
