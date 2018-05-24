<?php namespace Fisdap\Api\Shifts\Patients\Jobs;

use Fisdap\Api\Shifts\Patients\Events\PatientWasCreated;
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
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Swagger\Annotations as SWG;

/**
 * Class CreatePatient
 * @package Fisdap\Api\Shifts\Patients\Jobs
 * @author  Isaac White <isaac.white@ascendlearning.com>
 */
final class CreatePatient extends PatientAbstract
{

    public function handle(
        PatientRepository $patientRepository,
        ShiftLegacyRepository $shiftLegacyRepository,
        StudentLegacyRepository $studentLegacyRepository,
        PreceptorLegacyRepository $preceptorLegacyRepository,
        GenderRepository $genderRepository,
        EthnicityRepository $ethnicityRepository,
        EventDispatcher $eventDispatcher,
        BusDispatcher $busDispatcher,
        RunRepository $runRepository,
        NarrativeSectionDefinitionRepository $narrativeSectionDefinitionRepository,
        UserContextRepository $userContextRepository,
        AirwayManagementRepository $airwayManagementRepository
    ) {
        $patient = $this->setupPatient(
            $patientRepository,
            $shiftLegacyRepository,
            $studentLegacyRepository,
            $preceptorLegacyRepository,
            $genderRepository,
            $ethnicityRepository,
            $busDispatcher,
            $runRepository,
            $narrativeSectionDefinitionRepository,
            $userContextRepository,
            $airwayManagementRepository
        );

        $patientRepository->store($patient);
        $eventDispatcher->fire(new PatientWasCreated($patient->getId()));

        return $patient;
    }
    
    public function rules()
    {
        $rules = parent::rules();
        $rules['studentId'] = 'required|integer';

        return $rules;
    }
}
