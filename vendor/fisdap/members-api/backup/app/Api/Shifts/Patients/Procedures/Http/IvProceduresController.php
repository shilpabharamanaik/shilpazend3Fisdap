<?php
namespace Fisdap\Api\Shifts\Patients\Procedures\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Shifts\Patients\Procedures\Transformation\IvSiteTransformer;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Entity\IvSite;
use Fisdap\Entity\IvFluid;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Class IvProceduresController
 * @package Fisdap\Api\Shifts\Patients\Procedures\Http
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class IvProceduresController extends Controller
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
     * @param IvSiteTransformer $ivSiteTransformer
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/procedures/ivs/sites",
     *     summary="Return a list of all iv procedure sites",
     *     description="Return a list of all iv procedure sites. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of iv procedure sites. The Response Model show one such record.",
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
    public function getIvSites(IvSiteTransformer $ivSiteTransformer)
    {
        return $this->respondWithCollection(
            $this->em->getRepository(IvSite::class)->findBy([], ['name' => 'ASC']),
            $ivSiteTransformer
        );
    }

    /**
     * @param EnumeratedTransformer $enumeratedTransformer
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/procedures/ivs/fluids",
     *     summary="Return a list of all iv procedure fluids",
     *     description="Return a list of all iv procedure fluids. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of iv procedure fluids. The Response Model show one such record.",
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
    public function getIvFluids(EnumeratedTransformer $enumeratedTransformer)
    {
        return $this->respondWithCollection(
            $this->em->getRepository(IvFluid::class)->findBy([], ['name' => 'ASC']),
            $enumeratedTransformer
        );
    }
}
