<?php
namespace Fisdap\Api\Gender\Http;

use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Data\Gender\GenderRepository;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Class GenderController
 * @package Fisdap\Api\Gender\Http
 * @author Isaac White <iwhite@fisdap.net>
 */
final class GenderController extends Controller
{
    use ResponseHelpers;

    public function __construct(Manager $fractal, EnumeratedTransformer $transformer)
    {
        $this->fractal     = $fractal;
        $this->transformer = $transformer;
    }

    /**
     * @param GenderRepository $genderRepository
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Gender"},
     *     path="/gender",
     *     summary="Return a list of all gender types",
     *     description="Return a list of all gender types. The Response Model show one such record. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of gender types. The Response Model show one such record.",
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
    public function index(GenderRepository $genderRepository)
    {
        return $this->respondWithCollection($genderRepository->findAll(), $this->transformer);
    }
}
