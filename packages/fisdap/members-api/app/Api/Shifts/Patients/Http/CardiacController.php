<?php


namespace Fisdap\Api\Shifts\Patients\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Entity\PulseReturn;
use Fisdap\Entity\Witness;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;

/**
 * Class CardiacController
 * @package Fisdap\Api\Shifts\Patients\Http
 * @author  Isaac White <iwhite@fisdap.net>
 */
final class CardiacController extends Controller
{
    use ResponseHelpers;
    
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * CardiacController constructor.
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
     *     tags={"Patients"},
     *     path="/patients/cardiac/witness-statuses",
     *     summary="Return a list of all witness status types",
     *     description="Return a list of all witness status types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of witness status types. The Response Model show one such record.",
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
    public function getWitnessStatuses()
    {
        return $this->respondWithCollection($this->em->getRepository(Witness::class)->findAll(), $this->transformer);
    }
    
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/cardiac/pulse-returns",
     *     summary="Return a list of all pulse return types",
     *     description="Return a list of all pulse return types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of pulse return types. The Response Model show one such record.",
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
    public function getPulseReturns()
    {
        return $this->respondWithCollection(
            $this->em->getRepository(PulseReturn::class)->findAll(),
            $this->transformer
        );
    }
}
