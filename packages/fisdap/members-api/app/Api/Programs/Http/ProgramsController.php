<?php namespace Fisdap\Api\Programs\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Programs\Finder\FindsPrograms;
use Fisdap\Api\Programs\Jobs\CreateProgram;
use Fisdap\Api\Programs\Transformation\ProgramTransformer;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Handles HTTP transport and data transformation for program-related routes
 *
 * @package Fisdap\Api\Programs\Http
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ProgramsController extends Controller
{
    use CommonInputParameters, ResponseHelpers;

    /**
     * @var FindsPrograms
     */
    private $finder;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param FindsPrograms                                          $finder
     * @param Manager                                                $fractal
     * @param EntityManagerInterface $em
     * @param \Fisdap\Api\Programs\Transformation\ProgramTransformer $transformer
     */
    public function __construct(
        FindsPrograms $finder,
        Manager $fractal,
        EntityManagerInterface $em,
        ProgramTransformer $transformer
    ) {
        $this->finder = $finder;
        $this->fractal = $fractal;
        $this->transformer = $transformer;
        $this->em = $em;
    }


    /**
     * Retrieve the specified resource from storage
     *
     * @param  int $programId
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Programs"},
     *     path="/programs/{programId}",
     *     summary="Get a program by ID",
     *     description="Get a program by ID",
     *     @SWG\Parameter(name="programId", in="path", required=true, type="integer"),
     *     @SWG\Parameter(
     *      name="includes", in="query", type="array", items=@SWG\Items(type="string"), collectionFormat="csv",
     *      enum={
     *       "airway_procedures",
     *       "cardiac_procedures",
     *       "iv_procedures",
     *       "lab_assessments",
     *       "med_types",
     *       "other_procedures"
     *      }
     *     ),
     *     @SWG\Parameter(
     *      name="includeIds", in="query", type="array", items=@SWG\Items(type="string"), collectionFormat="csv",
     *      enum={
     *       "airway_procedures",
     *       "cardiac_procedures",
     *       "iv_procedures",
     *       "lab_assessments",
     *       "med_types",
     *       "other_procedures"
     *      }
     *     ),
     *     @SWG\Response(response="200", description="A program")
     * )
     */
    public function show($programId)
    {
        return $this->respondWithItem(
            $this->finder->getById($programId, $this->initAndGetIncludes(), $this->getIncludeIds()),
            $this->transformer
        );
    }


    /**
     * @param CreateProgram $createProgramJob
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\Post(
     *     tags={"Programs"},
     *     path="/programs",
     *     summary="Create a program",
     *     description="Create a program",
     *     @SWG\Parameter(
     *      name="Program", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Program")
     *     ),
     *     @SWG\Response(
     *      response="201",
     *      description="A created program")
     * )
     */
    public function store(CreateProgram $createProgramJob, BusDispatcher $busDispatcher)
    {
        $program = $busDispatcher->dispatch($createProgramJob);

        $this->setStatusCode(HttpResponse::HTTP_CREATED);

        return $this->respondWithItem($program, $this->transformer);
    }
}
