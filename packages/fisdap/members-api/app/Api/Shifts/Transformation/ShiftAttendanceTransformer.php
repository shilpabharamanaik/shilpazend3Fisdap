<?php namespace Fisdap\Api\Shifts\Transformation;

use League\Fractal\TransformerAbstract as Transformer;


/**
 * Prepares shift attendance data for JSON output
 *
 * @package Fisdap\Api\Shifts
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ShiftAttendanceTransformer extends Transformer
{
    /**
     * @param array $shiftAttendance
     *
     * @return array
     */
    public function transform(array $shiftAttendance)
    {
        return $shiftAttendance;
    }
}