<?php namespace Fisdap\Api\Shifts\Patients\Jobs;


use Fisdap\Api\Jobs\RequestHydrated;
use Fisdap\Data\Patient\PatientRepository;
use Fisdap\Data\Run\RunRepository;


/**
 * A Job command for deleting a patient associated with a given shift.
 *
 * Class DestroyPatient
 * @package Fisdap\Api\Shifts\Patients\Jobs
 * @author  Isaac White <iwhite@fisdap.net>
 *
 * @SWG\Definition(
 *     required={"shiftId", "patientId"}
 * )
 */
final class DestroyPatient extends PatientAbstract implements RequestHydrated
{
    /**
     * @param PatientRepository $patientRepository
     * @return int|null
     */
    public function handle(
        PatientRepository $patientRepository
    ) {
        $patient = $patientRepository->find($this->id);

        if ($patient === null) return null;  // No patient, no problem.

        $patient->set_verification(null);

        foreach ($patient->airways as $airway) {
            $patientRepository->destroy($airway);
        }

        foreach ($patient->other_interventions as $other_intervention) {
            $patientRepository->destroy($other_intervention);
        }

        foreach ($patient->meds as $med) {
            $patientRepository->destroy($med);
        }

        foreach ($patient->cardiac_interventions as $cardiac_intervention) {
            $patientRepository->destroy($cardiac_intervention);
        }

        foreach ($patient->ivs as $iv) {
            $patientRepository->destroy($iv);
        }

        foreach ($patient->vitals as $vital) {
            $patientRepository->destroy($vital);
        }

        if ($patient->signoff) $patientRepository->destroy($patient->signoff);

        if ($patient->narrative) {
            $patientRepository->destroyCollection($patient->narrative->sections->toArray());
            $patientRepository->destroy($patient->narrative);
        }

        if ($patient->run) $patient->run->removePatient($patient);

        // TODO: delete pieces of Patient, then the full object.
        $patientRepository->destroy($patient);

        return $this->id;
    }

    /**
     * Ignore rules, we are destroying the object.
     * @return null
     */
    public function rules()
    {
        return null;
    }

}
