<?php namespace Fisdap\Api\Shifts\Patients\Http;


use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Shifts\Patients\Finder\PatientsFinder;
use Fisdap\Api\Shifts\Patients\Jobs\CreatePatient;
use Fisdap\Api\Shifts\Patients\Jobs\DestroyPatient;
use Fisdap\Api\Shifts\Patients\Jobs\ModifyPatient;
use Fisdap\Api\Shifts\Patients\Queries\PatientQueryParameters;
use Fisdap\Api\Shifts\Patients\Transformation\PatientsTransformer;
use Fisdap\Fractal\CommonInputParameters;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use League\Fractal\Manager;
use Swagger\Annotations as SWG;

/**
 * Class PatientsController
 * @package Fisdap\Api\Shifts\Patients\Http
 * @author  Isaac White <isaac.white@ascendlearning.com>
 */
final class PatientsController extends Controller
{
    use CommonInputParameters, ResponseHelpers;

    /**
     * PatientsController constructor.
     * @param Manager $fractal
     * @param PatientsTransformer $transformer
     */
    public function __construct(Manager $fractal, PatientsTransformer $transformer)
    {
        $this->fractal = $fractal;
        $this->transformer = $transformer;
    }

    /**
     * @param $patientId
     * @param PatientsFinder $finder
     * @return JsonResponse
     * @internal param $PatientRepository
     *
     * @SWG\GET(
     *     tags={"Shifts"},
     *     path="/shifts/patients/{patientId}",
     *     summary="Gets a patient record",
     *     description="Gets a patient record for the provided patient id",
     *     @SWG\Parameter(name="patientId", in="path", required=true, type="integer", default=14033228),
     *     @SWG\Response(response="200", description="Patient Record",
     *          schema=@SWG\Schema(
     *              properties={
     *                  @SWG\Property(
     *                      property="data", type="array", items=@SWG\Schema(ref="#/definitions/Patient"))
     *      }))
     * )
     */
    public function getPatient($patientId, PatientsFinder $finder)
    {
        return $this->respondWithItem(
            $finder->getById($patientId, $this->initAndGetIncludes(), $this->getIncludeIds()),
            $this->transformer
        );
    }


    /**
     * @param $shiftId
     * @param PatientsFinder $finder
     * @return JsonResponse
     *
     * @SWG\GET(
     *     tags={"Shifts"},
     *     path="/shifts/{shiftId}/patients",
     *     summary="Get a list of patients for a shift",
     *     description="Get a list of patients for a shift",
     *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer", default="4321927"),
     *     @SWG\Parameter(name="dateFrom", in="query", type="string", description="UTC timestamp for returning only
    records that have been modified since provided timestamp."),
     *     @SWG\Response(response="200", description="Patient Records", schema=@SWG\Schema(ref="#/definitions/Patient"))
     * )
     */
    public function getPatientsForShift($shiftId, PatientsFinder $finder)
    {
        $queryParams = new PatientQueryParameters();
        $queryParams->setShiftId($shiftId);
        $queryParams->setDateFrom(\Request::get('dateFrom'));

        return $this->respondWithCollection(
            $finder->findShiftPatients($queryParams),
            $this->transformer
        );
    }

    /**
     * @param $shiftId
     * @param CreatePatient $createPatientJob
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\POST(
     *     tags={"Shifts"},
     *     path="/shifts/{shiftId}/patients",
     *     summary="Creates a patient",
     *     description="Create a patient for the provided shift",
     *     @SWG\Parameter(name="shiftId", in="path", required=true, type="integer", default=4321927,
     *                    description="Required to assign a new patient shift."),
     *     @SWG\Parameter(
     *      name="Patient", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Patient"),
     *      description="Required value(s): studentId"
     *     ),
     *     @SWG\Response(response="201", description="Patient created",
     *          schema=@SWG\Schema(
     *              properties={
     *                  @SWG\Property(
     *                      property="data", type="array", items=@SWG\Schema(ref="#/definitions/Patient"))
     *      })),
     *     @SWG\Response(response=422, description="Required Fields not satisfied",
     *          schema=@SWG\Schema(
     *              properties={
     *                  @SWG\Property(
     *                      property="data", type="array", items={"<error message>"})
     *      }))
     * )
     */
    public function createPatient($shiftId, CreatePatient $createPatientJob, BusDispatcher $busDispatcher)
    {
        $createPatientJob->setShiftId($shiftId);
        $patient = $busDispatcher->dispatch($createPatientJob);

        $this->setStatusCode(HttpResponse::HTTP_CREATED);

        return $this->respondWithItem($patient, $this->transformer);
    }

    /**
     * @param $patientId
     * @param ModifyPatient $modifyPatientJob
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\PATCH(
     *     tags={"Shifts"},
     *     path="/shifts/patients/{patientId}",
     *     summary="Updates a patient",
     *     description="Updates a patient",
     *     @SWG\Parameter(name="patientId", in="path", required=true, type="integer", default=14033228),
     *     @SWG\Parameter(
     *      name="Patient", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Patient")
     *     ),
     *     @SWG\Response(response="200", description="Patient updated", schema=@SWG\Schema(ref="#/definitions/Patient"))
     * )
     */
    public function updatePatient($patientId, ModifyPatient $modifyPatientJob, BusDispatcher $busDispatcher)
    {
        $modifyPatientJob->setPatientId($patientId);

        $patient = $busDispatcher->dispatch($modifyPatientJob);

        $this->setStatusCode(HttpResponse::HTTP_OK);
        return $this->respondWithItem($patient, $this->transformer);
    }

    /**
     * @param $patientId
     * @param DestroyPatient $destroyPatientJob
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\DELETE(
     *     tags={"Shifts"},
     *     path="/shifts/patients/{patientId}",
     *     summary="Deletes a patient",
     *     description="Deletes a patient",
     *     @SWG\Parameter(name="patientId", in="path", required=true, type="integer", default=14033228),
     *     @SWG\Response(response="200", description="Patient deleted"),
     *     @SWG\Response(response="204", description="Nothing to Delete"),
     * )
     */
    public function deletePatient($patientId, DestroyPatient $destroyPatientJob, BusDispatcher $busDispatcher)
    {
        $destroyPatientJob->setPatientId($patientId);
        $patient = $busDispatcher->dispatch($destroyPatientJob);

        $returnCode = $patient !== null ? HttpResponse::HTTP_OK : HttpResponse::HTTP_NO_CONTENT;
        $this->setStatusCode($returnCode);

        return $this->respondWithArray([]);
    }
}
