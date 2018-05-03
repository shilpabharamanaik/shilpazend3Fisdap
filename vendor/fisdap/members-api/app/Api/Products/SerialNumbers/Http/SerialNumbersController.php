<?php namespace Fisdap\Api\Products\SerialNumbers\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Products\SerialNumbers\Jobs\CreateSerialNumber;
use Fisdap\Api\Products\SerialNumbers\SerialNumberTransformer;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Handles HTTP transport and data transformation for routes related to serial numbers (SerialNumberLegacy Entity)
 *
 * @package Fisdap\Api\Products\SerialNumbers\Http
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SerialNumbersController extends Controller
{
    use ResponseHelpers;


    /**
     * @param Manager $fractal
     * @param SerialNumberTransformer $transformer
     */
    public function __construct(Manager $fractal, SerialNumberTransformer $transformer)
    {
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * @param CreateSerialNumber $createSerialNumberJob
     * @param BusDispatcher      $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\Post(
     *     tags={"Products"},
     *     path="/products/serial-numbers",
     *     summary="Create a serial number",
     *     description="Create a serial number",
     *     @SWG\Parameter(
     *      name="SerialNumber", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/SerialNumber")
     *     ),
     *     @SWG\Response(
     *      response="201",
     *      description="A created serial number")
     * )
     */
    public function store(CreateSerialNumber $createSerialNumberJob, BusDispatcher $busDispatcher)
    {
        $serialNumber = $busDispatcher->dispatch($createSerialNumberJob);

        $this->setStatusCode(HttpResponse::HTTP_CREATED);

        return $this->respondWithItem($serialNumber, $this->transformer);
    }
}
