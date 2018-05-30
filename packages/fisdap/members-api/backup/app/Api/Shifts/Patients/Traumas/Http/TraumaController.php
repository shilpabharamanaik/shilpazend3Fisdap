<?php

namespace Fisdap\Api\Shifts\Patients\Traumas\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Entity\Cause;
use Fisdap\Entity\Intent;
use Fisdap\Entity\Mechanism;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;

/**
 * Class TraumasController
 * @package Fisdap\Api\Shifts\Patients\Traumas\Http
 * @author  Isaac White <iwhite@fisdap.net>
 */
class TraumaController extends Controller
{
    use ResponseHelpers;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(Manager $fractal, EnumeratedTransformer $transformer, EntityManagerInterface $em)
    {
        $this->em          = $em;
        $this->fractal     = $fractal;
        $this->transformer = $transformer;
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/traumas/causes",
     *     summary="Return a list of all patient trauma causes types",
     *     description="Return a list of all patient trauma causes types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of patient trauma causes types. The Response Model show one such record.",
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
    public function getCauses()
    {
        return $this->respondWithCollection($this->em->getRepository(Cause::class)->findBy([], ['name' => 'ASC']), $this->transformer);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/traumas/intents",
     *     summary="Return a list of all patient trauma intents types",
     *     description="Return a list of all patient trauma intents types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of patient trauma intents types. The Response Model show one such record.",
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
    public function getIntents()
    {
        return $this->respondWithCollection($this->em->getRepository(Intent::class)->findBy([], ['name' => 'ASC']), $this->transformer);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/traumas/mechanisms",
     *     summary="Return a list of all patient trauma mechanisms types",
     *     description="Return a list of all patient trauma mechanisms types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of patient trauma mechanisms types. The Response Model show one such record.",
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
    public function getMechanisms()
    {
        return $this->respondWithCollection($this->em->getRepository(Mechanism::class)->findBy([], ['name' => 'ASC']), $this->transformer);
    }
}
