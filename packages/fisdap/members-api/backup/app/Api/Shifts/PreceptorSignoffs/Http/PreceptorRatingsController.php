<?php namespace Fisdap\Api\Shifts\PreceptorSignoffs\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Entity\PreceptorRatingRaterType;
use Fisdap\Entity\PreceptorRatingType;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;

/**
 * Class PreceptorRatingController
 *
 * @package Fisdap\Api\Shifts\PreceptorSignoff\Http
 * @author  Isaac White <isaac.white@ascendlearning.com>
 */
final class PreceptorRatingsController extends Controller
{
    use ResponseHelpers;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * PreceptorRatingController constructor.
     *
     * @param Manager $fractal
     * @param EnumeratedTransformer $transformer
     * @param EntityManagerInterface $em
     */
    public function __construct(Manager $fractal, EnumeratedTransformer $transformer, EntityManagerInterface $em)
    {
        $this->fractal     = $fractal;
        $this->transformer = $transformer;
        $this->em          = $em;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Shifts", "Preceptors"},
     *     path="/shifts/preceptor-rating-types",
     *     summary="Return a list of all preceptor rating type types",
     *     description="Return a list of all preceptor rating type types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of preceptor rating type types. The Response Model show one such record.",
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
    public function getPreceptorRatingTypes()
    {
        return $this->respondWithCollection($this->em->getRepository(PreceptorRatingType::class)->findAll(), $this->transformer);
    }
    
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Shifts", "Preceptors"},
     *     path="/shifts/preceptor-rating-rater-types",
     *     summary="Return a list of all preceptor rating rater type types",
     *     description="Return a list of all preceptor rating rater type types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of preceptor rating rater type types. The Response Model show one such record.",
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
    public function getPreceptorRatingRaterTypes()
    {
        return $this->respondWithCollection($this->em->getRepository(PreceptorRatingRaterType::class)->findAll(), $this->transformer);
    }
}
