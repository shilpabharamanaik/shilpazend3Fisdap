<?php

namespace Fisdap\Api\Programs\Sites\Preceptors\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Programs\Sites\Preceptors\Jobs\CreatePreceptor;
use Fisdap\Api\Programs\Sites\Preceptors\PreceptorTransformer;
use Fisdap\Data\Preceptor\PreceptorLegacyRepository;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Class PreceptorController
 * @package Fisdap\Api\Programs\Sites\Preceptors\Http
 * @author  Isaac White <iwhite@fisdap.net>
 */
final class PreceptorsController extends Controller
{
    use ResponseHelpers, CommonInputParameters;
    
    /**
     * @var PreceptorLegacyRepository
     */
    private $repository;

    /**
     * PreceptorsController constructor.
     * @param Manager $fractal
     * @param PreceptorTransformer $transformer
     * @param PreceptorLegacyRepository $repository
     */
    public function __construct(
        Manager $fractal,
        PreceptorTransformer $transformer,
        PreceptorLegacyRepository $repository
    ) {
        $this->fractal     = $fractal;
        $this->transformer = $transformer;
        $this->repository  = $repository;
    }

    /**
     * @param integer $preceptorId
     *
     * @return JsonResponse
     *
     * @SWG\Get(
     *     tags={"Sites"},
     *     path="/sites/preceptors/{preceptorId}",
     *     summary="Get a specified preceptor ",
     *     description="Get a specified preceptor ",
     *     @SWG\Parameter(name="preceptorId", in="path", required=true, type="integer"),
     *     @SWG\Response(
     *      response="200",
     *      description="This returns a specified preceptor ({preceptorId})")
     * )
     */
    public function show($preceptorId)
    {
        return $this->respondWithItem(
            $this->repository->find($preceptorId),
            $this->transformer
        );
    }

    /**
     * @param $siteId
     * @param CreatePreceptor $createPreceptorJob
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\Post(
     *     tags={"Sites"},
     *     path="/sites/{siteId}/preceptors",
     *     summary="Create a preceptor",
     *     description="Create a preceptor",
     *     @SWG\Parameter(name="siteId", in="path", required=true, type="integer", default="3663"),
     *     @SWG\Parameter(
     *      name="Preceptor", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Preceptor")
     *     ),
     *     @SWG\Response(
     *      response="201",
     *      description="This creates a new preceptor for a specified site ({siteId}) and returns the information
     *          of the preceptor")
     * )
     */
    public function store($siteId, CreatePreceptor $createPreceptorJob, BusDispatcher $busDispatcher)
    {
        $createPreceptorJob->setSiteId($siteId);
        $preceptor = $busDispatcher->dispatch($createPreceptorJob);

        $this->setStatusCode(HttpResponse::HTTP_CREATED);

        return $this->respondWithItem($preceptor, $this->transformer);
    }

    /**
     * @param integer $siteId
     *
     * @param AuthManager $auth
     * @return JsonResponse
     * @SWG\Get(
     *     tags={"Sites"},
     *     path="/sites/{siteId}/preceptors",
     *     summary="Get all preceptors at a particular site regardless of program",
     *     description="Get all preceptors at a particular site regardless of program",
     *     @SWG\Parameter(name="siteId", in="path", required=true, type="integer"),
     *     @SWG\Response(
     *      response="200",
     *      description="This returns all preceptors that are affiliated with the specified site ({siteId})")
     * )
     */
    public function getPreceptorsBySite($siteId, AuthManager $auth)
    {
        return $this->respondWithCollection($this->repository->getPreceptorsBySite($siteId, $auth), $this->transformer);
    }
}
