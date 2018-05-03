<?php namespace Fisdap\Api\Timezones\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Timezones\TimezoneTransformer;
use Fisdap\Data\Timezone\TimezoneRepository;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Http\JsonResponse;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Handles HTTP transport and data transformation for timezone-related routes
 *
 * @package Fisdap\Api\Timezones\Http
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class TimezonesController extends Controller
{
    use ResponseHelpers;


    /**
     * @param Manager                $fractal
     * @param TimezoneTransformer    $transformer
     */
    public function __construct(Manager $fractal, TimezoneTransformer $transformer)
    {
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }


    /**
     * @param TimezoneRepository $timezoneRepository
     *
     * @return JsonResponse
     *
     * @SWG\Get(
     *     tags={"Timezones"},
     *     path="/timezones",
     *     summary="Get a list of supported timezones",
     *     description="Get a list of supported timezones",
     *     @SWG\Response(response="200", description="A list of timezones"),
     * )
     */
    public function index(TimezoneRepository $timezoneRepository)
    {
        return $this->respondWithCollection(
            $timezoneRepository->findAll(),
            $this->transformer
        );
    }
}
