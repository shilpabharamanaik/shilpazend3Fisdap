<?php

namespace Fisdap\Api\Shifts\Patients\Http;

use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Transformation\EnumeratedTransformer;
use Fisdap\Entity\AirwayManagementSource;
use Fisdap\Entity\Complaint;
use Fisdap\Entity\Impression;
use Fisdap\Entity\MentalAlertness;
use Fisdap\Entity\MentalOrientation;
use Fisdap\Entity\PatientCriticality;
use Fisdap\Entity\PatientDisposition;
use Fisdap\Entity\ResponseMode;
use Fisdap\Entity\Subject;
use Fisdap\Fractal\ResponseHelpers;
use League\Fractal\Manager;

/**
 * Class EnumerationsController
 * @package Fisdap\Api\Shifts\Patients\Http
 * @author  Isaac White <iwhite@fisdap.net>
 */
final class EnumerationsController extends Controller
{
    use ResponseHelpers;

    /**
     * @var EntityManagerInterface
     */
    private $em;

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
     *     path="/patients/ambulance-response-modes",
     *     summary="Return a list of all ambulance response mode types",
     *     description="Return a list of all ambulance response mode types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of ambulance response mode types. The Response Model show one such record.",
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
    public function getAmbulanceResponseModes()
    {
        return $this->respondWithCollection(
            $this->em->getRepository(ResponseMode::class)->findBy([], ['name' => 'ASC']),
            $this->transformer
        );
    }
    
    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/impressions",
     *     summary="Return a list of all impression types",
     *     description="Return a list of all impression types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of impression types. The Response Model show one such record.",
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
    public function getImpressions()
    {
        return $this->respondWithCollection($this->em->getRepository(Impression::class)->findBy([], ['name' => 'ASC']), $this->transformer);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/complaints",
     *     summary="Return a list of all complaint types",
     *     description="Return a list of all complaint types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of complaint types. The Response Model show one such record.",
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
    public function getComplaints()
    {
        return $this->respondWithCollection($this->em->getRepository(Complaint::class)->findBy([], ['name' => 'ASC']), $this->transformer);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/subjects",
     *     summary="Return a list of all subject types",
     *     description="Return a list of all subject types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of subject types. The Response Model show one such record.",
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
    public function getSubjects()
    {
        return $this->respondWithCollection($this->em->getRepository(Subject::class)->findBy([], ['name' => 'ASC']), $this->transformer);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/criticalities",
     *     summary="Return a list of all criticality types",
     *     description="Return a list of all criticality types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of criticality types. The Response Model show one such record.",
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
    public function getPatientsCriticalities()
    {
        return $this->respondWithCollection(
            $this->em->getRepository(PatientCriticality::class)->findBy([], ['name' => 'ASC']),
            $this->transformer
        );
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/dispositions",
     *     summary="Return a list of all disposition types",
     *     description="Return a list of all disposition types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of disposition types. The Response Model show one such record.",
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
    public function getDispositions()
    {
        return $this->respondWithCollection(
            $this->em->getRepository(PatientDisposition::class)->findBy([], ['name' => 'ASC']),
            $this->transformer
        );
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/airway-management-sources",
     *     summary="Return a list of all airway management source types",
     *     description="Return a list of all airway management source types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of airway management source types. The Response Model show one such record.",
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
    public function getAirwayManagementSources()
    {
        return $this->respondWithCollection(
            $this->em->getRepository(AirwayManagementSource::class)->findBy([], ['name' => 'ASC']),
            $this->transformer
        );
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/mental-alertness",
     *     summary="Return a list of all AVPU mental alertness types",
     *     description="Return a list of all AVPU mental alertness types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of AVPU mental alertness types. The Response Model show one such record.",
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
    public function getMentalAlertness()
    {
        return $this->respondWithCollection(
            $this->em->getRepository(MentalAlertness::class)->findBy([], ['name' => 'ASC']),
            $this->transformer
        );
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     *
     * @SWG\Get(
     *     tags={"Patients"},
     *     path="/patients/mental-orientations",
     *     summary="Return a list of all AVPU mental orientation types",
     *     description="Return a list of all AVPU mental orientation types. The Response Model show one such record.",
     *     @SWG\Response(response=200, description="A list of AVPU mental orientation types. The Response Model show one such record.",
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
    public function getMentalOrientations()
    {
        return $this->respondWithCollection(
            $this->em->getRepository(MentalOrientation::class)->findBy([], ['name' => 'ASC']),
            $this->transformer
        );
    }
}
