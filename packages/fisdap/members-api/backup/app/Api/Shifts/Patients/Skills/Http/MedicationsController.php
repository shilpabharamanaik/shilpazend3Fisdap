<?php

namespace Fisdap\Api\Shifts\Patients\Skills\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Entity\MedRoute;
use Fisdap\Entity\MedType;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;

/**
 * Class MedicationsController
 * @package Fisdap\Api\Patients\Skills\Http
 * @author  Isaac White <iwhite@fisdap.net>
 */
final class MedicationsController extends Controller
{
    use ResponseHelpers;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * SkillsController constructor.
     * @param Manager $fractal
     * @param EnumeratedTransformer $transformer
     * @param EntityManagerInterface $em
     */
    public function __construct(Manager $fractal, EnumeratedTransformer $transformer, EntityManagerInterface $em)
    {
        $this->fractal = $fractal;
        $this->transformer = $transformer;
        $this->em = $em;
    }
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/skills/medications/types",
     *     summary="Return a list of all medication type types",
     *     description="Return a list of all medication type types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of medication type types. The Response Model show one such record.",
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
    public function getMedicationTypes()
    {
        return $this->respondWithCollection($this->em->getRepository(MedType::class)->findBy([], ['name' => 'ASC']), $this->transformer);
    }
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/skills/medications/routes",
     *     summary="Return a list of all medication route types",
     *     description="Return a list of all medication route types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of medication route types. The Response Model show one such record.",
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
    public function getMedicationRoutes()
    {
        return $this->respondWithCollection($this->em->getRepository(MedRoute::class)->findAll(), $this->transformer);
    }
}
