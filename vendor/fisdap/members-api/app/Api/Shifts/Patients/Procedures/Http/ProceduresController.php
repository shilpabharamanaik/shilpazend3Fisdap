<?php
namespace Fisdap\Api\Shifts\Patients\Procedures\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Shifts\Patients\Procedures\Transformation\AirwayProcedureTransformer;
use Fisdap\Api\Shifts\Patients\Procedures\Transformation\IvProcedureTransformer;
use Fisdap\Api\Shifts\Patients\Procedures\Transformation\OtherProcedureTransformer;
use Fisdap\Entity\AirwayProcedure;
use Fisdap\Entity\IvProcedure;
use Fisdap\Entity\OtherProcedure;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Class ProceduresController
 * @package Fisdap\Api\Shifts\Patients\Procedures\Http
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class ProceduresController extends Controller
{
    use ResponseHelpers;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * ProceduresController constructor.
     *
     * @param Manager $fractal
     * @param EntityManagerInterface $em
     */
    public function __construct(Manager $fractal, EntityManagerInterface $em)
    {
        $this->fractal = $fractal;
        $this->em = $em;
    }

    /**
     * @param OtherProcedureTransformer $otherProcedureTransformer
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/procedures/other",
     *     summary="Return a list of all other procedures",
     *     description="Return a list of all other procedures. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of other procedures. The Response Model show one such record.",
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
    public function getOtherProcedures(OtherProcedureTransformer $otherProcedureTransformer)
    {
        return $this->respondWithCollection(
            $this->em->getRepository(OtherProcedure::class)->findBy([], ['name' => 'ASC']),
            $otherProcedureTransformer
        );
    }

    /**
     * @param AirwayProcedureTransformer $airwayProcedureTransformer
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/procedures/airways",
     *     summary="Return a list of all airway procedures",
     *     description="Return a list of all airway procedures. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of airway procedures. The Response Model show one such record.",
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
    public function getAirwayProcedures(AirwayProcedureTransformer $airwayProcedureTransformer)
    {
        return $this->respondWithCollection(
            $this->em->getRepository(AirwayProcedure::class)->findBy([], ['name' => 'ASC']),
            $airwayProcedureTransformer
        );
    }

    /**
     * @param IvProcedureTransformer $ivProcedureTransformer
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/procedures/ivs",
     *     summary="Return a list of all iv procedures",
     *     description="Return a list of all iv procedures. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of iv procedures. The Response Model show one such record.",
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
    public function getIvProcedures(IvProcedureTransformer $ivProcedureTransformer)
    {
        return $this->respondWithCollection(
            $this->em->getRepository(IvProcedure::class)->findBy([], ['name' => 'ASC']),
            $ivProcedureTransformer
        );
    }
}
