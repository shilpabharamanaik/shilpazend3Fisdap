<?php namespace Fisdap\Api\Shifts\Patients;

use Fisdap\Fractal\Transformer;


/**
 * Prepares patient data for JSON output
 *
 * @package Fisdap\Api\Shifts\Patients
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class PatientTransformer extends Transformer
{
    /**
     * @param array $patient
     *
     * @return array
     */
    public function transform(array $patient)
    {
        $this->addCommonTimestamps($patient, $patient);

        return $patient;
    }
} 