<?php namespace Fisdap\Api\Shifts\Patients\Transformation;

use Fisdap\Entity\Patient;
use Fisdap\Fractal\Transformer;

final class PatientsTransformer extends Transformer
{
    private static function appendIfExists($transformArray, $key, $value)
    {
        if (!empty($value)) {
            $transformArray[$key] = $value;
        }

        // This looks pointless, I know, but mobile was complaining that the API was returning 0 when
        // size wasn't sent. So I'm explicitly forcing a null value instead of the default integer for
        // and empty value.
        if (isset($transformArray['size']) && $transformArray['size'] === null) {
            $transformArray['size'] = null;
        }
        if (isset($transformArray['age']) && $transformArray['age'] === null) {
            $transformArray['age'] = null;
        }
        if (isset($transformArray['months']) && $transformArray['months'] === null) {
            $transformArray['months'] = null;
        }

        return $transformArray;
    }

    /**
     * @param $patient
     *
     * @return array
     */
    public function transform($patient)
    {
        if ($patient instanceof Patient) {
            $patient = $patient->toArray();
        }

        $transformed = [];

        if (isset($patient['uuid'])) {
            $transformed['uuid'] = $patient['uuid'];
        }

        $transformed = array_merge($transformed, [
            'id' => $patient['id'],
            'studentId' => $this->getIdFromAssociation('student', $patient),
            'signoffId' => $this->getIdFromAssociation('signoff', $patient),
            'runId' => $this->getIdFromAssociation('run', $patient),
            'verificationId' => $this->getIdFromAssociation('verification', $patient),
            'locked' => $patient['locked'],
            'teamLead' => $patient['teamLead'],
            'teamSize' => $patient['teamSize'],
            'preceptorId' => $this->getIdFromAssociation('preceptor', $patient),
            'responseModeId' => $this->getIdFromAssociation('responseMode', $patient),
            'age' => $patient['age'],
            'months' => $patient['months'],
            'legacyAssessmentId' => $patient['legacyAssessmentId'],
            'legacyRunId' => $patient['legacyRunId'],
            'genderId' => $this->getIdFromAssociation('gender', $patient),
            'ethnicityId' => $this->getIdFromAssociation('ethnicity', $patient),
            'primaryImpressionId' => $this->getIdFromAssociation('primaryImpression', $patient),
            'secondaryImpressionId' => $this->getIdFromAssociation('secondaryImpression', $patient),
            'complaintIds' => $this->getIdsFromAssociation('complaints', $patient),
            'witnessId' => $this->getIdFromAssociation('witness', $patient),
            'pulseReturnId' => $this->getIdFromAssociation('pulseReturn', $patient),
            'mechanismIds' => $this->getIdsFromAssociation('mechanisms', $patient),
            'causeId' => $this->getIdFromAssociation('cause', $patient),
            'intentId' => $this->getIdFromAssociation('intent', $patient),
            'interview' => $patient['interview'],
            'exam' => $patient['exam'],
            'airwaySuccess' => $patient['airwaySuccess'],
            'airwayManagement' => $patient['airwayManagement'],
            'patientCriticalityId' => $this->getIdFromAssociation('patientCriticality', $patient),
            'patientDispositionId' => $this->getIdFromAssociation('patientDisposition', $patient),
            'transportModeId' => $this->getIdFromAssociation('transportMode', $patient),
            'mentalAlertnessId' => $this->getIdFromAssociation('mentalAlertness', $patient),
            'mentalOrientationIds' => $this->getIdsFromAssociation('mentalOrientations', $patient)

            // TODO: We have to build repos for the skills. We also have to modify/use PatientsFinder to join in these things.
        ]);

        $transformed = self::appendIfExists($transformed, 'medications', $patient['medications']);
        $transformed = self::appendIfExists($transformed, 'otherInterventions', $patient['otherInterventions']);
        $transformed = self::appendIfExists($transformed, 'cardiacInterventions', $patient['cardiacInterventions']);
        $transformed = self::appendIfExists($transformed, 'airways', $patient['airways']);
        $transformed = self::appendIfExists($transformed, 'ivs', $patient['ivs']);
        $transformed = self::appendIfExists($transformed, 'vitals', $patient['vitals']);
        if ($patient['signoff']) {
            $transformed = self::appendIfExists($transformed, 'signoff', $patient['signoff']->toArray());

            $ratings = [];
            foreach ((array) $transformed['signoff']['ratings'] as $rating) {
                $ratings[] = $rating->toArray();
            }
            $transformed['signoff']['ratings'] = $ratings;

            if ($patient['verification']) {
                $transformed['signoff']['verification'] = $patient['verification']->toArray();
            }

            // This is only needed to return the signature string after creation.
            // Because of the way pre and post persist works, the string being returned after creation is
            // the compressed string. In order to figure out which situation we're in, I'm checking the encoding
            // of the string.
            if (isset($transformed['signoff']['verification']['signatureString'])) {
                $sigString = $transformed['signoff']['verification']['signatureString'];

                if (!mb_detect_encoding($sigString, 'UTF-8', true)) {
                    $transformed['signoff']['verification']['signatureString'] = gzuncompress($sigString);
                }
            }
        }
        $narrativeSections = [];
        $sections = [];
        foreach ((array) $patient['narrative']['sections'] as $section) {
            $sections[] = $section->toArray();
        }
        $narrativeSections = self::appendIfExists($narrativeSections, 'sections', $sections);
        $transformed = self::appendIfExists($transformed, 'narrative', $narrativeSections);

        $transformed = array_merge($transformed, [
            'subjectId' => $this->getIdFromAssociation('subject', $patient),
            'shiftId' => $this->getIdFromAssociation('shift', $patient),
            'updated' => $patient['updated'],
            'created' => $patient['created']
        ]);


        return $transformed;
    }
}
