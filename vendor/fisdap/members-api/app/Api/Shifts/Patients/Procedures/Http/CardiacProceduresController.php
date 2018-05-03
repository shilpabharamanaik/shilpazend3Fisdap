<?php
namespace Fisdap\Api\Shifts\Patients\Procedures\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Shifts\Patients\Procedures\Transformation\CardiacProcedureTransformer;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Entity\CardiacEctopy;
use Fisdap\Entity\CardiacPacingMethod;
use Fisdap\Entity\CardiacProcedure;
use Fisdap\Entity\CardiacProcedureMethod;
use Fisdap\Entity\RhythmType;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Class IvProceduresController
 * @package Fisdap\Api\Shifts\Patients\Procedures\Http
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class CardiacProceduresController extends Controller
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
     * @param CardiacProcedureTransformer $cardiacProcedureTransformer
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/procedures/cardiac",
     *     summary="Return a list of all cardiac procedures",
     *     description="Return a list of all cardiac procedures. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of cardiac procedures. The Response Model show one such record.",
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
    public function getCardiacProcedures(CardiacProcedureTransformer $cardiacProcedureTransformer)
    {
        return $this->respondWithCollection(
            $this->em->getRepository(CardiacProcedure::class)->findBy([], ['name' => 'ASC']),
            $cardiacProcedureTransformer
        );
    }

    /**
     * @param EnumeratedTransformer $transformer
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/procedures/cardiac/ectopies",
     *     summary="Return a list of all cardiac ectopies",
     *     description="Return a list of all cardiac ectopies. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of cardiac ectopies. The Response Model show one such record.",
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
    public function getCardiacEctopies(EnumeratedTransformer $transformer)
    {
        return $this->respondWithCollection(
            $this->em->getRepository(CardiacEctopy::class)->findBy([], ['name' => 'ASC']),
            $transformer
        );
    }

    /**
     * @param EnumeratedTransformer $transformer
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/procedures/cardiac/pacing-methods",
     *     summary="Return a list of all cardiac pacing methods",
     *     description="Return a list of all cardiac pacing methods. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of cardiac pacing methods. The Response Model show one such record.",
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
    public function getCardiacPacingMethods(EnumeratedTransformer $transformer)
    {
        return $this->respondWithCollection(
            $this->em->getRepository(CardiacPacingMethod::class)->findBy([], ['name' => 'ASC']),
            $transformer
        );
    }

    /**
     * @param EnumeratedTransformer $transformer
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/procedures/cardiac/procedure-methods",
     *     summary="Return a list of all cardiac procedure methods",
     *     description="Return a list of all cardiac procedure methods. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of cardiac procedure methods. The Response Model show one such record.",
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
    public function getCardiacProcedureMethods(EnumeratedTransformer $transformer)
    {
        return $this->respondWithCollection(
            $this->em->getRepository(CardiacProcedureMethod::class)->findBy([], ['name' => 'ASC']),
            $transformer
        );
    }

    /**
     * @param EnumeratedTransformer $transformer
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/procedures/cardiac/rhythm-types",
     *     summary="Return a list of all cardiac rhythm types",
     *     description="Return a list of all cardiac rhythm types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of rhythm types. The Response Model show one such record.",
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
    public function getCardiacRhythmTypes(EnumeratedTransformer $transformer)
    {
        return $this->respondWithCollection(
            $this->em->getRepository(RhythmType::class)->findBy([], ['name' => 'ASC']),
            $transformer
        );
    }
}
