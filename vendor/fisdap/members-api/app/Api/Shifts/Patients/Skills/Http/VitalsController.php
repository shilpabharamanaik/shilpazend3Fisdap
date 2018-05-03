<?php


namespace Fisdap\Api\Shifts\Patients\Skills\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Entity\VitalLungSound;
use Fisdap\Entity\VitalPulseQuality;
use Fisdap\Entity\VitalRespQuality;
use Fisdap\Entity\VitalSkin;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;

/**
 * Class VitalsController
 * @package Fisdap\Api\Patients\Skills\Http
 * @author  Isaac White <iwhite@fisdap.net>
 */
final class VitalsController extends Controller
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
        $this->fractal     = $fractal;
        $this->transformer = $transformer;
        $this->em          = $em;
    }
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/skills/vitals/pulse-qualities",
     *     summary="Return a list of all vital pulse-quality types",
     *     description="Return a list of all vital pulse-quality types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of vital pulse-quality types. The Response Model show one such record.")
     * )
     */
    public function getVitalsPulseQualities()
    {
        return $this->respondWithCollection(
            $this->em->getRepository(VitalPulseQuality::class)->findBy([], ['name' => 'ASC']),
            $this->transformer
        );
    }
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/skills/vitals/respiratory-qualities",
     *     summary="Return a list of all vital respiratory-quality types",
     *     description="Return a list of all vital respiratory-quality types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of vital respiratory-quality types. The Response Model show one such record.")
     * )
     */
    public function getVitalsRespiratoryQualities()
    {
        return $this->respondWithCollection(
            $this->em->getRepository(VitalRespQuality::class)->findBy([], ['name' => 'ASC']),
            $this->transformer
        );
    }
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/skills/vitals/skin-conditions",
     *     summary="Return a list of all vital skin-condition types",
     *     description="Return a list of all vital skin-condition types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of vital skin-condition types. The Response Model show one such record.",
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
    public function getVitalsSkinConditions()
    {
        return $this->respondWithCollection($this->em->getRepository(VitalSkin::class)->findBy([], ['name' => 'ASC']), $this->transformer);
    }
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/skills/vitals/lung-sounds",
     *     summary="Return a list of all vital lung-sound types",
     *     description="Return a list of all vital lung-sound types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of vital lung-sound types. The Response Model show one such record.",
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
    public function getVitalsLungSounds()
    {
        return $this->respondWithCollection(
            $this->em->getRepository(VitalLungSound::class)->findBy([], ['name' => 'ASC']),
            $this->transformer
        );
    }
}
