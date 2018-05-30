<?php namespace Fisdap\Api\Shifts\Patients\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Data\AirwayManagement\AirwayManagementRepository;
use Fisdap\Data\Ethnicity\EthnicityRepository;
use Fisdap\Data\Gender\GenderRepository;
use Fisdap\Data\Narrative\NarrativeSectionDefinitionRepository;
use Fisdap\Data\Patient\PatientRepository;
use Fisdap\Data\Preceptor\PreceptorLegacyRepository;
use Fisdap\Data\Run\RunRepository;
use Fisdap\Data\Shift\ShiftLegacyRepository;
use Fisdap\Data\Student\StudentLegacyRepository;
use Fisdap\Data\User\UserContext\UserContextRepository;
use Fisdap\Entity\Cause;
use Fisdap\Entity\Complaint;
use Fisdap\Entity\Ethnicity;
use Fisdap\Entity\Gender;
use Fisdap\Entity\Impression;
use Fisdap\Entity\Intent;
use Fisdap\Entity\Mechanism;
use Fisdap\Entity\MentalAlertness;
use Fisdap\Entity\MentalOrientation;
use Fisdap\Entity\MentalResponse;
use Fisdap\Entity\Narrative;
use Fisdap\Entity\Patient;
use Fisdap\Entity\PatientCriticality;
use Fisdap\Entity\PatientDisposition;
use Fisdap\Entity\PreceptorSignoff;
use Fisdap\Entity\PulseReturn;
use Fisdap\Entity\ResponseMode;
use Fisdap\Entity\Run;
use Fisdap\Entity\StudentLegacy;
use Fisdap\Entity\Subject;
use Fisdap\Entity\Verification;
use Fisdap\Entity\Witness;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Swagger\Annotations as SWG;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

/**
 * An Abstract Job command for used for the
 * creation, updating, and deletion of Patients associated with a shift and/or patient id.
 *
 * Class PatientAbstract
 * @package Fisdap\Api\Shifts\Patients\Jobs
 * @author  Isaac White  <isaac.white@ascendlearning.com>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(
 *     definition="Patient"
 * )
 */
abstract class PatientAbstract extends Job implements RequestHydrated
{
    /**
     * @var string|null
     * @SWG\Property(type="string", example="ABC123")
     */
    public $uuid = null;

    /**
     * @var integer
     * @see Patient
     * This should be populated by the URL
     */
    protected $id;

    /**
     * @var integer
     * @see StudentLegacy
     * @SWG\Property(type="integer", example=148286, description="Only works on create. Ignored on update.")
     */
    public $studentId;

    /**
     * @var integer|null
     * @see PreceptorSignoff
     * @SWG\Property(type="integer", example=1)
     */
    public $signoffId = null;

    /**
     * @var integer|null
     * @see Verification
     * @SWG\Property(type="integer", example=1)
     */
    public $verificationId = null;

    /**
     * @var boolean
     * @SWG\Property(type="boolean", example=true)
     */
    public $locked;

    /**
     * @var boolean
     * @SWG\Property(type="boolean", example=false)
     */
    public $teamLead;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=5)
     */
    public $teamSize;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=999)
     */
    public $preceptorId;

    /**
     * @var integer|null
     * @see ResponseMode
     * @SWG\Property(type="integer", example=3)
     */
    public $responseModeId = null;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=500)
     */
    public $age;

    /**
     * @var integer
     * @SWG\Property(type="integer", example=12)
     */
    public $months;

    /**
     * @var integer|null
     * @SWG\Property(type="integer")
     * TODO: Does this do anything?
     */
    public $legacyAssessmentId = null;

    /**
     * @var integer|null
     * @SWG\Property(type="integer")
     * TODO: Does this do anything?
     */
    public $legacyRunId = null;

    /**
     * @var integer
     * @see Gender
     * @SWG\Property(type="integer", example=1)
     */
    public $genderId;

    /**
     * @var integer|null
     * @see Ethnicity
     * @SWG\Property(type="integer", example=5)
     */
    public $ethnicityId;

    /**
     * @var integer
     * @see Impression
     * @SWG\Property(type="integer", example=4)
     */
    public $primaryImpressionId;

    /**
     * @var integer|null
     * @see Impression
     * @SWG\Property(type="integer", example=1)
     */
    public $secondaryImpressionId = null;

    /**
     * @var array
     * @see Complaint
     * @SWG\Property(type="integer", example={5,6,7})
     */
    public $complaintIds;

    /**
     * Note: Only needed if primary and/or secondary impression are 'cardiac arrest'
     * (primaryImpressionId|secondaryImpressionId = 4)
     *
     * @var integer|null
     * @see Witness
     * @SWG\Property(type="integer", example=1)
     */
    public $witnessId = null;

    /**
     * Note: Only needed if primary and/or secondary impression are 'cardiac arrest'
     * (primaryImpressionId|secondaryImpressionId = 4)
     *
     * @var integer|null
     * @see PulseReturn
     * @SWG\Property(type="integer", example=4)
     */
    public $pulseReturnId = null;

    /**
     * Note: Only needed if primary and/or secondary impression are 'Trauma - *'
     * (primaryImpressionId|secondaryImpressionId = 27-32)
     *
     * @var array
     * @see Mechanism
     * @SWG\Property(type="array", items="integer", example={2,3,4})
     */
    public $mechanismIds = array();

    /**
     * Note: Only needed if primary and/or secondary impression are 'Trauma - *'
     * (primaryImpressionId|secondaryImpressionId = 27-32)
     *
     * @var integer|null
     * @see Cause
     * @SWG\Property(type="integer", example=5)
     */
    public $causeId = null;

    /**
     * Note: Only needed if primary and/or secondary impression are 'Trauma - *'
     * (primaryImpressionId|secondaryImpressionId = 27-32)
     *
     * @var integer|null
     * @see Intent
     * @SWG\Property(type="integer", example=3)
     */
    public $intentId = null;

    /**
     * @var boolean|null
     * @SWG\Property(type="boolean", example=true)
     */
    public $interview = null;

    /**
     * @var boolean|null
     * @SWG\Property(type="boolean", example=false)
     */
    public $exam = null;

    /**
     * @var boolean|null
     * @SWG\Property(type="boolean", example=true)
     */
    public $airwaySuccess = null;

    /**
     * @var \Fisdap\Api\Shifts\Patients\Jobs\SetAirwayManagement
     * @SWG\Property(items=@SWG\Items(ref="#/definitions/Airway_Management"))
     */
    public $airwayManagement = null;

    /**
     * @var integer|null
     * @see PatientCriticality
     * @SWG\Property(type="integer")
     */
    public $patientCriticalityId = null;

    /**
     * @var integer|null
     * @see PatientDisposition
     * @SWG\Property(type="integer")
     */
    public $patientDispositionId = null;

    /**
     * @var integer|null
     * @see ResponseMode
     * @SWG\Property(type="integer", example=2)
     */
    public $transportModeId = null;

    /**
     * @var integer|null
     * @see MentalAlertness
     * @SWG\Property(type="integer")
     */
    public $mentalAlertnessId = null;

    /**
     * @var array
     * @see MentalOrientation
     */
    public $mentalOrientationIds = array();

    /**
     * @var array
     * @see MentalResponse
     */
    public $msaResponses = [];

    /**
     * @var \Fisdap\Api\Shifts\Patients\Skills\Jobs\Meds\SetMeds[]
     * @SWG\Property(type="array", items=@SWG\Items(ref="#/definitions/Medication"))
     */
    public $medications = [];

    /**
     * @var \Fisdap\Api\Shifts\Patients\Skills\Jobs\OtherInterventions\SetOtherInterventions[]
     * @SWG\Property(type="array", items=@SWG\Items(ref="#/definitions/Other_Intervention"))
     */
    public $otherInterventions = [];

    /**
     * @var \Fisdap\Api\Shifts\Patients\Skills\Jobs\CardiacInterventions\SetCardiacInterventions[]
     * @SWG\Property(type="array", items=@SWG\Items(ref="#/definitions/Cardiac_Intervention"))
     */
    public $cardiacInterventions = [];

    /**
     * @var \Fisdap\Api\Shifts\Patients\Skills\Jobs\Airways\SetAirways[]
     * @SWG\Property(type="array", items=@SWG\Items(ref="#/definitions/Airway"))
     */
    public $airways = [];

    /**
     * @var \Fisdap\Api\Shifts\Patients\Skills\Jobs\Ivs\SetIvs[]
     * @SWG\Property(type="array", items=@SWG\Items(ref="#/definitions/Iv"))
     */
    public $ivs = [];

    /**
     * @var \Fisdap\Api\Shifts\Patients\Skills\Jobs\Vitals\SetVitals[]
     * @SWG\Property(type="array", items=@SWG\Items(ref="#/definitions/Vital"))
     */
    public $vitals = [];

    /**
     * @var \Fisdap\Api\Shifts\Patients\Narratives\Jobs\SetNarrative
     * @See Narrative
     * @SWG\Property(items=@SWG\Items(ref="#/definitions/Narrative"))
     */
    public $narrative = null;

    /**
     * @var \Fisdap\Api\Shifts\PreceptorSignoffs\Jobs\ModifySignoff
     * @SWG\Property(type=@SWG\Items(ref="#/definitions/PreceptorSignoff"))
     */
    public $signoff;

    /**
     * @var integer|null
     * @see Subject
     * @SWG\Property(type="integer")
     */
    public $subjectId = null;

    /**
     * @var integer
     * @see ShiftLegacy
     * @SWG\Property(type="integer")
     * This should be populated by the URL
     */
    protected $shiftId;

    /**
     * @var array
     */
    private $missingFields;

    /**
     * @param integer $shiftId
     */
    public function setShiftId($shiftId)
    {
        $this->shiftId = $shiftId;
    }

    public function setPatientId($id)
    {
        $this->id = $id;
    }

    /**
     * @param PatientRepository $patientRepository
     * @param ShiftLegacyRepository $shiftLegacyRepository
     * @param StudentLegacyRepository $studentLegacyRepository
     * @param PreceptorLegacyRepository $preceptorLegacyRepository
     * @param GenderRepository $genderRepository
     * @param EthnicityRepository $ethnicityRepository
     * @param BusDispatcher $busDispatcher
     * @param RunRepository $runRepository
     * @param NarrativeSectionDefinitionRepository $narrativeSectionDefinitionRepository
     * @param UserContextRepository $userContextRepository
     * @param AirwayManagementRepository $airwayManagementRepository
     * @return \Fisdap\Api\Jobs\EntityBaseClass|Patient
     */
    public function setupPatient(
        PatientRepository $patientRepository,
        ShiftLegacyRepository $shiftLegacyRepository,
        StudentLegacyRepository $studentLegacyRepository,
        PreceptorLegacyRepository $preceptorLegacyRepository,
        GenderRepository $genderRepository,
        EthnicityRepository $ethnicityRepository,
        BusDispatcher $busDispatcher,
        RunRepository $runRepository,
        NarrativeSectionDefinitionRepository $narrativeSectionDefinitionRepository,
        UserContextRepository $userContextRepository,
        AirwayManagementRepository $airwayManagementRepository
    ) {
        $modify = false;
        $patient = new Patient;
        $run = new Run;


        if ($this->id) {
            $modify = true;
            $patient = $this->validResource($patientRepository, 'id');
            $this->setShiftId($patient->shift->id);
            // This check MUST happen here.
            // Since Entities are persisted, any change is instant, so this has to happen first!
            $this->lockupPatient($shiftLegacyRepository);
        } else {
            $shift = $this->validResource($shiftLegacyRepository, 'shiftId');
            $shift->addRun($run);
            $patient->setShift($shift);

            // This check MUST happen here.
            // Since Entities are persisted, any change is instant, so this has to happen first!

            $this->lockupPatient($shiftLegacyRepository);
            if ($this->studentId) {
                $student = $this->validResource($studentLegacyRepository, 'studentId');
                $patient->setStudent($student);
            }
        }

        $patient->setUUID($this->uuid);

        if ($this->preceptorId) {
            $preceptor = $this->validResource($preceptorLegacyRepository, 'preceptorId');
            $patient->setPreceptor($preceptor);
        } else {
            $patient->setPreceptor(null);
        }

        if ($this->genderId) {
            $gender = $this->validResource($genderRepository, 'genderId');
            $patient->setGender($gender);
        }

        if ($this->primaryImpressionId) {
            $primaryImpression = $this->validResourceEntityManager(Impression::class, $this->primaryImpressionId, true);
            $patient->setPrimaryImpression($primaryImpression);
        }

        if ($this->ethnicityId) {
            $ethnicity = $this->validResource($ethnicityRepository, 'ethnicityId');
            $patient->setEthnicity($ethnicity);
        }

        // Not Required
        $secondaryImpression = $this->validResourceEntityManager(Impression::class, $this->secondaryImpressionId);
        $witness = $this->validResourceEntityManager(Witness::class, $this->witnessId);
        $pulseReturn = $this->validResourceEntityManager(PulseReturn::class, $this->pulseReturnId);
        $cause = (isset($this->causeId) && $this->causeId != null ? $this->validResourceEntityManager(Cause::class, $this->causeId) : null);
        $intent = (isset($this->intentId) && $this->intentId != null ? $this->validResourceEntityManager(Intent::class, $this->intentId) : null);
        $patientCriticality = $this->validResourceEntityManager(PatientCriticality::class, $this->patientCriticalityId);
        $patientDisposition = $this->validResourceEntityManager(PatientDisposition::class, $this->patientDispositionId);
        $subject = $this->validResourceEntityManager(Subject::class, $this->subjectId);
        $mentalAlertness = $this->validResourceEntityManager(MentalAlertness::class, $this->mentalAlertnessId);
        $responseMode = $this->validResourceEntityManager(ResponseMode::class, $this->responseModeId);
        $transportMode = $this->validResourceEntityManager(ResponseMode::class, $this->transportModeId);


        // If the subject is null, then do a get, which returns a default
        $subject = $subject ? $subject : $patient->getSubject();

        // Populate the patient
        if (!$modify) {
            $run->addPatient($patient);
            $runRepository->store($run);
        }

        if (!is_null($this->teamLead)) {
            $patient->setTeamLead($this->teamLead);
        }

        if (!is_null($this->teamSize)) {
            $patient->setTeamSize($this->teamSize);
        }

        if (!is_null($this->locked)) {
            $patient->setLocked($this->locked);
        }

        if (!is_null($this->age)) {
            $patient->setAge($this->age);
        }

        if (!is_null($this->months)) {
            $patient->setMonths($this->months);
        }

        if (!is_null($mentalAlertness)) {
            $patient->setMsaAlertness($mentalAlertness);
        }

        if (!is_null($this->mentalOrientationIds)) {
            $patient->setMsaOrientations($this->mentalOrientationIds);
        }

        if (!is_null($responseMode)) {
            $patient->setResponseMode($responseMode);
        }

        if (!is_null($transportMode)) {
            $patient->setTransportMode($transportMode);
        }

        if (!is_null($secondaryImpression)) {
            $patient->setSecondaryImpression($secondaryImpression);
        }

        $patient->setComplaintIds($this->complaintIds);

        if (!is_null($witness)) {
            $patient->setWitness($witness);
        }

        if (!is_null($pulseReturn)) {
            $patient->setPulseReturn($pulseReturn);
        }

        if (!is_null($this->mechanismIds)) {
            $patient->set_mechanisms($this->mechanismIds);
        }

        $patient->setCause($cause);

        $patient->setIntent($intent);

        if (!is_null($this->interview)) {
            $patient->setInterview($this->interview);
        }

        if (!is_null($this->exam)) {
            $patient->setExam($this->exam);
        }

        if (!is_null($this->airwaySuccess)) {
            $patient->setAirwaySuccess($this->airwaySuccess);
        }

        if (!is_null($patientCriticality)) {
            $patient->setPatientCriticality($patientCriticality);
        }

        if (!is_null($patientDisposition)) {
            $patient->setPatientDisposition($patientDisposition);
        }

        if (!is_null($subject)) {
            $patient->setSubject($subject);
        }

        if (!is_null($this->airwayManagement) && !is_null($this->airwaySuccess)) {
            $this->airwayManagement->setPatient($patient);
            $airMan = $busDispatcher->dispatch($this->airwayManagement);
            $patient->setAirwayManagement($airMan);
        } elseif (is_null($this->airwayManagement) || is_null($this->airwaySuccess)) {
            $airwayManagement = $airwayManagementRepository->findOneBy(['patient' => $this->id]);
            if (!is_null($airwayManagement)) {
                $airwayManagementRepository->destroy($airwayManagement);
            }
            $patient->setAirwayManagement(null);
        }

        foreach ($this->medications as $med) {
            $med->setPatient($patient);
            $medication = $busDispatcher->dispatch($med);
            $patient->addMed($medication);
        }

        foreach ($this->vitals as $vitalJob) {
            $vitalJob->setPatient($patient);
            $vital = $busDispatcher->dispatch($vitalJob);
            $patient->addVital($vital);
        }

        foreach ($this->otherInterventions as $otherIntervention) {
            $otherIntervention->setPatient($patient);
            $otherInter = $busDispatcher->dispatch($otherIntervention);
            $patient->addOtherIntervention($otherInter);
        }

        foreach ($this->cardiacInterventions as $cardiacIntervention) {
            $cardiacIntervention->setPatient($patient);
            $cardInter = $busDispatcher->dispatch($cardiacIntervention);
            $patient->addCardiacIntervention($cardInter);
        }

        foreach ($this->airways as $airway) {
            $airway->setPatient($patient);
            $air = $busDispatcher->dispatch($airway);
            $patient->addAirway($air);
        }

        foreach ($this->ivs as $iv) {
            $iv->setPatient($patient);
            $intravenous = $busDispatcher->dispatch($iv);
            $patient->addIv($intravenous);
        }


        if (isset($this->narrative)) {
            $programId = $userContextRepository->find($patient->getStudent()->user_context)->program->id;

            $narrativeSectionDefinitions = $narrativeSectionDefinitionRepository->findBy(['program_id' => $programId]);
            $this->narrative->setNarrative($patient->getNarrative());

            $this->narrative->setNarrativeSectionDefinitions($narrativeSectionDefinitions);

            $narrative = $busDispatcher->dispatch($this->narrative);

            if ($narrative !== null) {
                $patient->setNarrative($narrative);
            }
        }

        if (isset($this->signoff)) {
            $this->signoff->setPatientId($patient->getId());
            $signoff = $busDispatcher->dispatch($this->signoff);
            $patient->setSignoff($signoff);
        }

        return $patient;
    }

    public function canBeLocked($shiftType = 'field')
    {
        $rtv = true;
        $this->missingFields = array();

        if ($this->studentId == null) {
            $rtv = false;
            array_push($this->missingFields, "studentId");
        }

        if ($shiftType === 'field' && $this->preceptorId == null) {
            $rtv = false;
            array_push($this->missingFields, "preceptorId");
        }

        if ($this->age < 0 && $this->months < 0) {
            $rtv = false;
            array_push($this->missingFields, "age/months");
        }

        if ($this->genderId == null) {
            $rtv = false;
            array_push($this->missingFields, "genderId");
        }

        if ($this->primaryImpressionId == null) {
            $rtv = false;
            array_push($this->missingFields, "primaryImpressionId");
        }

        return $rtv;
    }

    public function getMissingFields()
    {
        return $this->missingFields;
    }

    /**
     * Validation rules for request.
     * @return array
     */
    public function rules()
    {
        // No shiftId, this should be handled by the request URL.
        $rules = [
            'id'                  => 'integer',
            'teamLead'            => 'boolean',
            'teamSize'            => 'integer',
            'locked'              => 'boolean',
            'preceptorId'         => 'integer',
            'age'                 => 'integer',
            'months'              => 'integer',
            'genderId'            => 'integer',
            'primaryImpressionId' => 'integer',

            'verificationId'        => 'integer',
            'ethnicityId'           => 'integer',
            'secondaryImpressionId' => 'integer',
            'complaintId'           => 'integer',
            'witnessId'             => 'integer',
            'pulseReturnId'         => 'integer',
            'mechanismId'           => 'integer',
            'causeId'               => 'integer',
            'intentId'              => 'integer',
            'interview'             => 'boolean',
            'exam'                  => 'boolean',
            'airwaySuccess'         => 'boolean',
            'patientCriticalityId'  => 'integer',
            'patientDispositionId'  => 'integer',
            'mentalAlertnessId'     => 'integer',
            'responseModeId'        => 'integer',
            'transportModeId'       => 'integer',
            'subjectId'             => 'integer',
        ];

        return $rules;
    }

    private function lockupPatient(ShiftLegacyRepository $shiftLegacyRepository)
    {
        // This check MUST happen here.
        // Since Entities are persisted, any change is instant, so this has to happen first!
        if ($this->locked == true) {
            $shift = $this->validResource($shiftLegacyRepository, 'shiftId');
            if (!$this->canBeLocked($shift->getType())) {
                throw new MissingMandatoryParametersException(
                    "Cannot lock Patient. Required fields are missing: ".implode(", ", $this->getMissingFields())
                );
            }
        }
    }
}
