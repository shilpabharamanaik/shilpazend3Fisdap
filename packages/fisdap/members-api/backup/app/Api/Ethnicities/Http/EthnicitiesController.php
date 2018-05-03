<?php

namespace Fisdap\Api\Ethnicities\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Data\Ethnicity\EthnicityRepository;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;

/**
 * Class EthnicitiesController
 * @package Fisdap\Api\Ethnicities\Http
 * @author  Isaac White <iwhite@fisdap.net>
 */
final class EthnicitiesController extends Controller
{
    use ResponseHelpers;

    /**
     * EthnicitiesController constructor.
     * @param Manager $fractal
     * @param EnumeratedTransformer $transformer
     */
    public function __construct(Manager $fractal, EnumeratedTransformer $transformer)
    {
        $this->fractal     = $fractal;
        $this->transformer = $transformer;
    }

    /**
     * @param EthnicityRepository $ethnicityRepository
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Ethnicity"},
     *     path="/ethnicities",
     *     summary="Return a list of all ethnicity types",
     *     description="Return a list of all ethnicity types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of ethnicity types. The Response Model show one such record.",
     *     schema=@SWG\Schema(
     *          properties={
     *              @SWG\Property(
     *                  property="data", type="array", items=@SWG\Items(
     *                      ref="#/definitions/Enumerated"
     *                  )
     *              )
     *          }
     *      ))
     * )
     */
    public function index(EthnicityRepository $ethnicityRepository)
    {
        return $this->respondWithCollection($ethnicityRepository->findAll(), $this->transformer);
    }
}
