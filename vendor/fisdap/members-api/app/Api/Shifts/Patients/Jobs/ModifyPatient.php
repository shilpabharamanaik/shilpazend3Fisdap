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
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * A Job command for updating a Patient associated with a shift (Patient).
 *
 * Class ModifyPatient
 * @package Fisdap\Api\Shifts\Patients\Jobs
 * @author  Isaac White <iwhite@fisdap.net>
 *
 */
final class ModifyPatient extends PatientAbstract
{
    /**
     * @param PatientRepository $patientRepository
     * @param ShiftLegacyRepository $shiftLegacyRepository
     * @param StudentLegacyRepository $studentLegacyRepository
     * @param PreceptorLegacyRepository $preceptorLegacyRepository
     * @param GenderRepository $genderRepository
     * @param EthnicityRepository $ethnicityRepository
     * @param EventDispatcher $eventDispatcher
     * @param BusDispatcher $busDispatcher
     * @param RunRepository $runRepository
     * @param UserContextRepository $userContextRepository
     * @param NarrativeSectionDefinitionRepository $narrativeSectionDefinitionRepository
     * @param AirwayManagementRepository $airwayManagementRepository
     * @return \Fisdap\Api\Jobs\EntityBaseClass|\Fisdap\Entity\Patient
     */
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
        UserContextRepository $userContextRepository,
        NarrativeSectionDefinitionRepository $narrativeSectionDefinitionRepository,
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

        $patientRepository->update($patient);
        $eventDispatcher->fire(new PatientWasCreated($patient->getId()));

        return $patient;
    }
}
