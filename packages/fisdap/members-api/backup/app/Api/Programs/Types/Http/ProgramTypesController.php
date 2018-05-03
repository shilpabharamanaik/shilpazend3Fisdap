<?php namespace Fisdap\Api\Programs\Types\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Programs\Types\ProgramTypeTransformer;
use Fisdap\Data\Program\Type\ProgramTypeLegacyRepository;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Handles HTTP transport and data transformation for program type-related routes
 *
 * @package Fisdap\Api\Programs\Types\Http
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProgramTypesController extends Controller
{
    use ResponseHelpers;


    /**
     * @param Manager                $fractal
     * @param ProgramTypeTransformer $transformer
     */
    public function __construct(Manager $fractal, ProgramTypeTransformer $transformer)
    {
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * @param ProgramTypeLegacyRepository $programTypeLegacyRepository
     *
     * @return JsonResponse
     * @SWG\Get(
     *     tags={"Programs"},
     *     path="/programs/types",
     *     summary="Get a list of program types",
     *     description="Get a list of program types",
     *     @SWG\Response(response="200", description="A list of program types"),
     * )
     */
    public function index(ProgramTypeLegacyRepository $programTypeLegacyRepository)
    {
        return $this->respondWithCollection(
            $programTypeLegacyRepository->findAll(),
            $this->transformer
        );
    }
}
