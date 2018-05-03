<?php namespace Fisdap\Api\Shifts\Patients\Skills\Http;


use Doctrine\ORM\EntityManagerInterface;
use Fisdap\Api\Http\Controllers\Controller;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\Airways\DeleteAirway;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\Airways\SetAirways;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\CardiacInterventions\DeleteCardiacIntervention;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\CardiacInterventions\SetCardiacInterventions;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\Ivs\DeleteIv;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\Ivs\SetIvs;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\Meds\DeleteMed;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\Meds\SetMeds;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\OtherInterventions\DeleteOtherIntervention;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\OtherInterventions\SetOtherInterventions;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\Vitals\DeleteVital;
use Fisdap\Api\Shifts\Patients\Skills\Jobs\Vitals\SetVitals;
use Fisdap\Api\Shifts\Patients\Skills\Transformation\SkillsTransformer;
use Fisdap\Data\Patient\PatientRepository;
use Fisdap\Data\Shift\ShiftLegacyRepository;
use Fisdap\Fractal\ResponseHelpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use League\Fractal\Manager;

final class SkillsController extends Controller
{
    use ResponseHelpers;
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * SkillsController constructor.
     * @param Manager $fractal
     * @param SkillsTransformer $transformer
     * @param EntityManagerInterface $em
     */
    public function __construct(Manager $fractal, SkillsTransformer $transformer, EntityManagerInterface $em)
    {
        $this->fractal     = $fractal;
        $this->transformer = $transformer;
        $this->em          = $em;
    }

    /**
     * @param $patientId
     * @param $airwayId
     * @param SetAirways $setAirways
     * @param PatientRepository $patientRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/patients/{patientId}/skills/airways/{airwayId}",
     *     summary="Create/Update an Airway for Patient",
     *     description="Creates or updates an Airway Skill for the specified Patient. If {airwayId} is left blank a new Airway Skill will be created.",
     *     @SWG\Parameter(
     *      name="patientId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Patient this Airway Skill belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="airwayId", in="path", required=false, type="integer",
     *      description="The Airway Skill's ID. If blank (or not found) a new Airway Skill will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Airway", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Airway"),
     *      description=""
     *     ),
     *     @SWG\Response(response="201", description="Airway created", schema=@SWG\Schema(ref="#/definitions/Airway")),
     *     @SWG\Response(response="200", description="Airway updated", schema=@SWG\Schema(ref="#/definitions/Airway"))
     * )
     */
    public function setPatientAirways($patientId, $airwayId, SetAirways $setAirways, PatientRepository $patientRepository, BusDispatcher $busDispatcher)
    {
        $patient = $patientRepository->getOneById($patientId);
        if (!$patient) {
            throw new ResourceNotFound("No Patient found with id '$patientId'.");
        }

        $setAirways->setPatient($patient);
        $setAirways->setAirwayId($airwayId);

        $airways = $busDispatcher->dispatch($setAirways);
        $airways->save();

        if ($airwayId == $airways->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($airways, $this->transformer);
    }

    /**
     * @param $patientId
     * @param $cardiacId
     * @param SetCardiacInterventions $setCardiacInterventions
     * @param PatientRepository $patientRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @internal param $cardiacId
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/patients/{patientId}/skills/cardiacs/{cardiacId}",
     *     summary="Create/Update a Cardiac Intervention for Patient",
     *     description="Creates or updates a Cardiac Intervention for specified Patient. If {cardiacId} is left blank a new Cardiac Intervention will be created.",
     *     @SWG\Parameter(
     *      name="patientId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Patient this Cardiac Intervention belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="cardiacId", in="path", required=false, type="integer",
     *      description="The Cardiac Intervention's ID. If blank (or not found) a new Cardiac Intervention will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Cardiac", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Cardiac_Intervention"),
     *      description=""
     *     ),
     *     @SWG\Response(response="201", description="Cardiac Intervention created", schema=@SWG\Schema(ref="#/definitions/Cardiac_Intervention")),
     *     @SWG\Response(response="200", description="Cardiac Intervention updated", schema=@SWG\Schema(ref="#/definitions/Cardiac_Intervention"))
     * )
     */
    public function setPatientCardiacs($patientId, $cardiacId, SetCardiacInterventions $setCardiacInterventions, PatientRepository $patientRepository, BusDispatcher $busDispatcher)
    {
        $patient = $patientRepository->getOneById($patientId);
        if (!$patient) {
            throw new ResourceNotFound("No Patient found with id '$patientId'.");
        }

        $setCardiacInterventions->setPatient($patient);
        $setCardiacInterventions->setCardiacId($cardiacId);
        $cardiac = $busDispatcher->dispatch($setCardiacInterventions);
        $cardiac->save();

        if ($cardiacId == $cardiac->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($cardiac, $this->transformer);
    }

    /**
     * @param $patientId
     * @param $ivId
     * @param SetIvs $setIvs
     * @param PatientRepository $patientRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/patients/{patientId}/skills/ivs/{ivId}",
     *     summary="Create/Update an Iv for Patient",
     *     description="Creates or updates an Iv Skill for the specified Patient. If {ivId} is left blank a new Iv Skill will be created.",
     *     @SWG\Parameter(
     *      name="patientId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Patient this Iv Skill belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="ivId", in="path", required=false, type="integer",
     *      description="The Iv Skill's ID. If blank (or not found) a new Iv Skill will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Iv", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Iv"),
     *      description=""
     *     ),
     *     @SWG\Response(response="201", description="Iv created", schema=@SWG\Schema(ref="#/definitions/Iv")),
     *     @SWG\Response(response="200", description="Iv updated", schema=@SWG\Schema(ref="#/definitions/Iv"))
     * )
     */
    public function setPatientIvs($patientId, $ivId, PatientRepository $patientRepository, SetIvs $setIvs, BusDispatcher $busDispatcher)
    {
        $patient = $patientRepository->getOneById($patientId);
        if (!$patient) {
            throw new ResourceNotFound("No Patient found with id '$patientId'.");
        }

        $setIvs->setPatient($patient);
        $setIvs->setIvId($ivId);
        $ivs = $busDispatcher->dispatch($setIvs);
        $ivs->save();

        if ($ivId == $ivs->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($ivs, $this->transformer);
    }

    /**
     * @param $patientId
     * @param $medId
     * @param SetMeds $setMeds
     * @param PatientRepository $patientRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/patients/{patientId}/skills/medications/{medicationId}",
     *     summary="Create/Update a Medication for Patient",
     *     description="Creates or updates a Medication Skill for the specified Patient. If {medicationId} is left blank a new Medication Skill will be created.",
     *     @SWG\Parameter(
     *      name="patientId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Patient this Medication Skill belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="medId", in="path", required=false, type="integer",
     *      description="The Medication Skill's ID. If blank (or not found) a new Medication Skill will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Medication", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Medication"),
     *      description=""
     *     ),
     *     @SWG\Response(response="201", description="Medication created", schema=@SWG\Schema(ref="#/definitions/Medication")),
     *     @SWG\Response(response="200", description="Medication updated", schema=@SWG\Schema(ref="#/definitions/Medication"))
     * )
     */
    public function setPatientMedications($patientId, $medId, PatientRepository $patientRepository, SetMeds $setMeds, BusDispatcher $busDispatcher)
    {
        $patient = $patientRepository->getOneById($patientId);
        if (!$patient) {
            throw new ResourceNotFound("No Patient found with id '$patientId'.");
        }

        $setMeds->setPatient($patient);
        $setMeds->setMedicationId($medId);
        $meds = $busDispatcher->dispatch($setMeds);
        $meds->save();

        if ($medId == $meds->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($meds, $this->transformer);
    }

    /**
     * @param $patientId
     * @param $otherInterventionId
     * @param SetOtherInterventions $setOtherInterventions
     * @param PatientRepository $patientRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/patients/{patientId}/skills/others/{otherInterventionId}",
     *     summary="Create/Update an Other for Patient",
     *     description="Creates or updates an Other Intervention for the specified Patient. If {otherInterventionId} is left blank a new Other Intervention will be created.",
     *     @SWG\Parameter(
     *      name="patientId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Patient this Other Intervention belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="airwayId", in="path", required=false, type="integer",
     *      description="The Other Intervention's ID. If blank (or not found) a new Other Intervention will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Other", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Other_Intervention"),
     *      description=""
     *     ),
     *     @SWG\Response(response="201", description="Other Intervention created", schema=@SWG\Schema(ref="#/definitions/Other_Intervention")),
     *     @SWG\Response(response="200", description="Other Intervention updated", schema=@SWG\Schema(ref="#/definitions/Other_Intervention"))
     * )
     */
    public function setPatientOthers($patientId, $otherInterventionId, PatientRepository $patientRepository, SetOtherInterventions $setOtherInterventions, BusDispatcher $busDispatcher)
    {
        $patient = $patientRepository->getOneById($patientId);
        if (!$patient) {
            throw new ResourceNotFound("No Patient found with id '$patientId'.");
        }

        $setOtherInterventions->setPatient($patient);
        $setOtherInterventions->setOtherInterventionId($otherInterventionId);
        $others = $busDispatcher->dispatch($setOtherInterventions);
        $others->save();

        if ($otherInterventionId == $others->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($others, $this->transformer);
    }

    /**
     * @param $patientId
     * @param $vitalId
     * @param SetVitals $setVitals
     * @param PatientRepository $patientRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/patients/{patientId}/skills/vitals/{vitalId}",
     *     summary="Create/Update a Vital for Patient",
     *     description="Creates or updates a Vital Skill for the specified Patient. If {vitalId} is left blank a new Vital Skill will be created.",
     *     @SWG\Parameter(
     *      name="patientId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Patient this Vital Skill belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="vitalId", in="path", required=false, type="integer",
     *      description="The Vital Skill's ID. If blank (or not found) a new Vital Skill will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Vital", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Vital"),
     *      description=""
     *     ),
     *     @SWG\Response(response=201, description="Vital created", schema=@SWG\Schema(ref="#/definitions/Vital")),
     *     @SWG\Response(response=200, description="Vital updated", schema=@SWG\Schema(ref="#/definitions/Vital"))
     * )
     */
    public function setPatientVitals($patientId, $vitalId, PatientRepository $patientRepository, SetVitals $setVitals, BusDispatcher $busDispatcher)
    {
        $patient = $patientRepository->getOneById($patientId);
        if (!$patient) {
            throw new ResourceNotFound("No Patient found with id '$patientId'.");
        }

        $setVitals->setPatient($patient);
        $setVitals->setVitalId($vitalId);
        $vitals = $busDispatcher->dispatch($setVitals);
        $vitals->save();

        if ($vitalId == $vitals->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($vitals, $this->transformer);
    }

    /**
     * @param $shiftId
     * @param $airwayId
     * @param SetAirways $setAirways
     * @param ShiftLegacyRepository $shiftRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/shifts/{shiftId}/skills/airways/{airwayId}",
     *     summary="Create/Update an Airway for Shift",
     *     description="Creates or updates an Airway Skill for the specified Shift. If {airwayId} is left blank a new Airway Skill will be created.",
     *     @SWG\Parameter(
     *      name="shiftId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Shift this Airway Skill belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="airwayId", in="path", required=false, type="integer",
     *      description="The Airway Skill's ID. If blank (or not found) a new Airway Skill will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Airway", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Airway"),
     *      description=""
     *     ),
     *     @SWG\Response(response="201", description="Airway created", schema=@SWG\Schema(ref="#/definitions/Airway")),
     *     @SWG\Response(response="200", description="Airway updated", schema=@SWG\Schema(ref="#/definitions/Airway"))
     * )
     */
    public function setShiftAirways($shiftId, $airwayId, SetAirways $setAirways, ShiftLegacyRepository $shiftRepository, BusDispatcher $busDispatcher)
    {
        $shift = $shiftRepository->getOneById($shiftId);
        if (!$shift) {
            throw new ResourceNotFound("No Shift found with id '$shiftId'.");
        }

        $setAirways->setShift($shift);
        $setAirways->setAirwayId($airwayId);

        $airways = $busDispatcher->dispatch($setAirways);
        $airways->save();

        if ($airwayId == $airways->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($airways, $this->transformer);
    }

    /**
     * @param $shiftId
     * @param $cardiacId
     * @param SetCardiacInterventions $setCardiacInterventions
     * @param ShiftLegacyRepository $shiftRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @internal param $cardiacId
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/shifts/{shiftId}/skills/cardiacs/{cardiacId}",
     *     summary="Create/Update a Cardiac Intervention for Shift",
     *     description="Creates or updates a Cardiac Intervention for specified Shift. If {cardiacId} is left blank a new Cardiac Intervention will be created.",
     *     @SWG\Parameter(
     *      name="shiftId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Shift this Cardiac Intervention belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="cardiacId", in="path", required=false, type="integer",
     *      description="The Cardiac Intervention's ID. If blank (or not found) a new Cardiac Intervention will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Cardiac", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Cardiac_Intervention"),
     *      description=""
     *     ),
     *     @SWG\Response(response="201", description="Cardiac Intervention created", schema=@SWG\Schema(ref="#/definitions/Cardiac_Intervention")),
     *     @SWG\Response(response="200", description="Cardiac Intervention updated", schema=@SWG\Schema(ref="#/definitions/Cardiac_Intervention"))
     * )
     */
    public function setShiftCardiacs($shiftId, $cardiacId, SetCardiacInterventions $setCardiacInterventions, ShiftLegacyRepository $shiftRepository, BusDispatcher $busDispatcher)
    {
        $shift = $shiftRepository->getOneById($shiftId);
        if (!$shift) {
            throw new ResourceNotFound("No Shift found with id '$shiftId'.");
        }

        $setCardiacInterventions->setShift($shift);
        $setCardiacInterventions->setCardiacId($cardiacId);
        $cardiac = $busDispatcher->dispatch($setCardiacInterventions);
        $cardiac->save();

        if ($cardiacId == $cardiac->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($cardiac, $this->transformer);
    }

    /**
     * @param $shiftId
     * @param $ivId
     * @param SetIvs $setIvs
     * @param ShiftLegacyRepository $shiftRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/shifts/{shiftId}/skills/ivs/{ivId}",
     *     summary="Create/Update an Iv for Shift",
     *     description="Creates or updates an Iv Skill for the specified Shift. If {ivId} is left blank a new Iv Skill will be created.",
     *     @SWG\Parameter(
     *      name="shiftId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Shift this Iv Skill belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="ivId", in="path", required=false, type="integer",
     *      description="The Iv Skill's ID. If blank (or not found) a new Iv Skill will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Iv", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Iv"),
     *      description=""
     *     ),
     *     @SWG\Response(response="201", description="Iv created", schema=@SWG\Schema(ref="#/definitions/Iv")),
     *     @SWG\Response(response="200", description="Iv updated", schema=@SWG\Schema(ref="#/definitions/Iv"))
     * )
     */
    public function setShiftIvs($shiftId, $ivId, ShiftLegacyRepository $shiftRepository, SetIvs $setIvs, BusDispatcher $busDispatcher)
    {
        $shift = $shiftRepository->getOneById($shiftId);
        if (!$shift) {
            throw new ResourceNotFound("No Shift found with id '$shiftId'.");
        }

        $setIvs->setShift($shift);
        $setIvs->setIvId($ivId);
        $ivs = $busDispatcher->dispatch($setIvs);
        $ivs->save();

        if ($ivId == $ivs->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($ivs, $this->transformer);
    }

    /**
     * @param $shiftId
     * @param $medId
     * @param SetMeds $setMeds
     * @param ShiftLegacyRepository $shiftRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/shifts/{shiftId}/skills/medications/{medicationId}",
     *     summary="Create/Update a Medication for Shift",
     *     description="Creates or updates a Medication Skill for the specified Shift. If {medicationId} is left blank a new Medication Skill will be created.",
     *     @SWG\Parameter(
     *      name="shiftId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Shift this Medication Skill belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="medId", in="path", required=false, type="integer",
     *      description="The Medication Skill's ID. If blank (or not found) a new Medication Skill will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Medication", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Medication"),
     *      description=""
     *     ),
     *     @SWG\Response(response="201", description="Medication created", schema=@SWG\Schema(ref="#/definitions/Medication")),
     *     @SWG\Response(response="200", description="Medication updated", schema=@SWG\Schema(ref="#/definitions/Medication"))
     * )
     */
    public function setShiftMedications($shiftId, $medId, ShiftLegacyRepository $shiftRepository, SetMeds $setMeds, BusDispatcher $busDispatcher)
    {
        $shift = $shiftRepository->getOneById($shiftId);
        if (!$shift) {
            throw new ResourceNotFound("No Shift found with id '$shiftId'.");
        }

        $setMeds->setShift($shift);
        $setMeds->setMedicationId($medId);
        $meds = $busDispatcher->dispatch($setMeds);
        $meds->save();

        if ($medId == $meds->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($meds, $this->transformer);
    }

    /**
     * @param $shiftId
     * @param $otherInterventionId
     * @param SetOtherInterventions $setOtherInterventions
     * @param ShiftLegacyRepository $shiftRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/shifts/{shiftId}/skills/others/{otherInterventionId}",
     *     summary="Create/Update an Other for Shift",
     *     description="Creates or updates an Other Intervention for the specified Shift. If {otherInterventionId} is left blank a new Other Intervention will be created.",
     *     @SWG\Parameter(
     *      name="shiftId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Shift this Other Intervention belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="airwayId", in="path", required=false, type="integer",
     *      description="The Other Intervention's ID. If blank (or not found) a new Other Intervention will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Other", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Other_Intervention"),
     *      description=""
     *     ),
     *     @SWG\Response(response="201", description="Other Intervention created", schema=@SWG\Schema(ref="#/definitions/Other_Intervention")),
     *     @SWG\Response(response="200", description="Other Intervention updated", schema=@SWG\Schema(ref="#/definitions/Other_Intervention"))
     * )
     */
    public function setShiftOthers($shiftId, $otherInterventionId, ShiftLegacyRepository $shiftRepository, SetOtherInterventions $setOtherInterventions, BusDispatcher $busDispatcher)
    {
        $shift = $shiftRepository->getOneById($shiftId);
        if (!$shift) {
            throw new ResourceNotFound("No Shift found with id '$shiftId'.");
        }

        $setOtherInterventions->setShift($shift);
        $setOtherInterventions->setOtherInterventionId($otherInterventionId);
        $others = $busDispatcher->dispatch($setOtherInterventions);
        $others->save();

        if ($otherInterventionId == $others->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($others, $this->transformer);
    }

    /**
     * @param $shiftId
     * @param $vitalId
     * @param SetVitals $setVitals
     * @param ShiftLegacyRepository $shiftRepository
     * @param BusDispatcher $busDispatcher
     * @return JsonResponse
     * @SWG\Put(
     *     tags={"Skills"},
     *     path="/shifts/{shiftId}/skills/vitals/{vitalId}",
     *     summary="Create/Update a Vital for Shift",
     *     description="Creates or updates a Vital Skill for the specified Shift. If {vitalId} is left blank a new Vital Skill will be created.",
     *     @SWG\Parameter(
     *      name="shiftId", in="path", required=true, type="integer", default=123,
     *      description="The ID of the Shift this Vital Skill belongs to."
     *     ),
     *     @SWG\Parameter(
     *      name="vitalId", in="path", required=false, type="integer",
     *      description="The Vital Skill's ID. If blank (or not found) a new Vital Skill will be created."
     *     ),
     *     @SWG\Parameter(
     *      name="Vital", in="body", required=true, schema=@SWG\Schema(ref="#/definitions/Vital"),
     *      description=""
     *     ),
     *     @SWG\Response(response=201, description="Vital created", schema=@SWG\Schema(ref="#/definitions/Vital")),
     *     @SWG\Response(response=200, description="Vital updated", schema=@SWG\Schema(ref="#/definitions/Vital"))
     * )
     */
    public function setShiftVitals($shiftId, $vitalId, ShiftLegacyRepository $shiftRepository, SetVitals $setVitals, BusDispatcher $busDispatcher)
    {
        $shift = $shiftRepository->getOneById($shiftId);
        if (!$shift) {
            throw new ResourceNotFound("No Shift found with id '$shiftId'.");
        }

        $setVitals->setShift($shift);
        $setVitals->setVitalId($vitalId);
        $vitals = $busDispatcher->dispatch($setVitals);
        $vitals->save();

        if ($vitalId == $vitals->id) {
            $this->setStatusCode(HttpResponse::HTTP_OK);
        } else {
            $this->setStatusCode(HttpResponse::HTTP_CREATED);
        }
        return $this->respondWithItem($vitals, $this->transformer);
    }

    /**
     * @param $airwayId
     * @param DeleteAirway $deleteAirwayJob
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\DELETE(
     *     tags={"Skills"},
     *     path="/skills/airways/{airwayId}",
     *     summary="Deletes an airway",
     *     description="Deletes an airway",
     *     @SWG\Parameter(name="airwayId", in="path", required=true, type="integer", default=123),
     *     @SWG\Response(response="200", description="Airway deleted"),
     *     @SWG\Response(response="204", description="Nothing to Delete"),
     * )
     */
    public function deleteAirway($airwayId, DeleteAirway $deleteAirwayJob, BusDispatcher $busDispatcher)
    {
        $deleteAirwayJob->setId($airwayId);

        $airway = $busDispatcher->dispatch($deleteAirwayJob);

        $returnCode = $airway !== null ? HttpResponse::HTTP_OK : HttpResponse::HTTP_NO_CONTENT;
        $this->setStatusCode($returnCode);

        return $this->respondWithArray([]);
    }

    /**
     * @param $cardiacId
     * @param DeleteCardiacIntervention $deleteCardiacJob
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\DELETE(
     *     tags={"Skills"},
     *     path="/skills/cardiacs/{cardiacInterventionId}",
     *     summary="Deletes an cardiac",
     *     description="Deletes an cardiac",
     *     @SWG\Parameter(name="cardiacId", in="path", required=true, type="integer", default=123),
     *     @SWG\Response(response="200", description="Cardiac Intervention deleted"),
     *     @SWG\Response(response="204", description="Nothing to Delete"),
     * )
     */
    public function deleteCardiac($cardiacId, DeleteCardiacIntervention $deleteCardiacJob, BusDispatcher $busDispatcher)
    {
        $deleteCardiacJob->setId($cardiacId);

        $cardiac = $busDispatcher->dispatch($deleteCardiacJob);

        $returnCode = $cardiac !== null ? HttpResponse::HTTP_OK : HttpResponse::HTTP_NO_CONTENT;
        $this->setStatusCode($returnCode);

        return $this->respondWithArray([]);
    }

    /**
     * @param $ivId
     * @param DeleteIv $deleteIvJob
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\DELETE(
     *     tags={"Skills"},
     *     path="/skills/ivs/{ivId}",
     *     summary="Deletes an iv",
     *     description="Deletes an iv",
     *     @SWG\Parameter(name="ivId", in="path", required=true, type="integer", default=123),
     *     @SWG\Response(response="200", description="Iv deleted"),
     *     @SWG\Response(response="204", description="Nothing to Delete"),
     * )
     */
    public function deleteIv($ivId, DeleteIv $deleteIvJob, BusDispatcher $busDispatcher)
    {
        $deleteIvJob->setId($ivId);

        $iv = $busDispatcher->dispatch($deleteIvJob);

        $returnCode = $iv !== null ? HttpResponse::HTTP_OK : HttpResponse::HTTP_NO_CONTENT;
        $this->setStatusCode($returnCode);

        return $this->respondWithArray([]);
    }

    /**
     * @param $medId
     * @param DeleteMed $deleteMedJob
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\DELETE(
     *     tags={"Skills"},
     *     path="/skills/medications/{medicationId}",
     *     summary="Deletes a medication",
     *     description="Deletes a medication",
     *     @SWG\Parameter(name="medId", in="path", required=true, type="integer", default=123),
     *     @SWG\Response(response="200", description="Medication deleted"),
     *     @SWG\Response(response="204", description="Nothing to Delete"),
     * )
     */
    public function deleteMed($medId, DeleteMed $deleteMedJob, BusDispatcher $busDispatcher)
    {
        $deleteMedJob->setId($medId);

        $med = $busDispatcher->dispatch($deleteMedJob);

        $returnCode = $med !== null ? HttpResponse::HTTP_OK : HttpResponse::HTTP_NO_CONTENT;
        $this->setStatusCode($returnCode);

        return $this->respondWithArray([]);
    }

    /**
     * @param $otherId
     * @param DeleteOtherIntervention $deleteOtherJob
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\DELETE(
     *     tags={"Skills"},
     *     path="/skills/others/{otherInterventionId}",
     *     summary="Deletes an other intervention",
     *     description="Deletes an other intervention",
     *     @SWG\Parameter(name="otherId", in="path", required=true, type="integer", default=123),
     *     @SWG\Response(response="200", description="Other Intervention deleted"),
     *     @SWG\Response(response="204", description="Nothing to Delete"),
     * )
     */
    public function deleteOther($otherId, DeleteOtherIntervention $deleteOtherJob, BusDispatcher $busDispatcher)
    {
        $deleteOtherJob->setId($otherId);

        $other = $busDispatcher->dispatch($deleteOtherJob);

        $returnCode = $other !== null ? HttpResponse::HTTP_OK : HttpResponse::HTTP_NO_CONTENT;
        $this->setStatusCode($returnCode);

        return $this->respondWithArray([]);
    }

    /**
     * @param $vitalId
     * @param DeleteVital $deleteVitalJob
     * @param BusDispatcher $busDispatcher
     *
     * @return JsonResponse
     *
     * @SWG\DELETE(
     *     tags={"Skills"},
     *     path="/skills/vitals/{vitalId}",
     *     summary="Deletes a vital",
     *     description="Deletes a vital",
     *     @SWG\Parameter(name="vitalId", in="path", required=true, type="integer", default=123),
     *     @SWG\Response(response="200", description="Vital deleted"),
     *     @SWG\Response(response="204", description="Nothing to Delete"),
     * )
     */
    public function deleteVital($vitalId, DeleteVital $deleteVitalJob, BusDispatcher $busDispatcher)
    {
        $deleteVitalJob->setId($vitalId);

        $vital = $busDispatcher->dispatch($deleteVitalJob);

        $returnCode = $vital !== null ? HttpResponse::HTTP_OK : HttpResponse::HTTP_NO_CONTENT;
        $this->setStatusCode($returnCode);

        return $this->respondWithArray([]);
    }

}

