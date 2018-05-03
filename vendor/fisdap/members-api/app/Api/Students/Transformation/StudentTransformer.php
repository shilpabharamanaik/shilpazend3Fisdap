<?php namespace Fisdap\Api\Students\Transformation;

use Fisdap\Entity\StudentLegacy;
use Fisdap\Fractal\Transformer;

/**
 * Prepares student data for JSON output
 *
 * @package Fisdap\Api\Students\Transformation
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
final class StudentTransformer extends Transformer
{
    /**
     * @param $student
     * @return array
     * @internal param array|StudentLegacy $student
     *
     */
    public function transform($student)
    {
        if ($student instanceof StudentLegacy) {
            $student = $student->toArray();
        }

        if (isset($student['created'])) {
            $student['created'] = $this->formatDateTimeAsString($student['created']);
        }

        if (!isset($student['uuid'])) {
            $this->removeFields(["uuid"], $student);
        }

        return $student;
    }
}
