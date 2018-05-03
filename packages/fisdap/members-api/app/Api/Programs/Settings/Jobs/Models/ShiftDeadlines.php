<?php namespace Fisdap\Api\Programs\Settings\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class ShiftDeadlines
 *
 * @package Fisdap\Api\Programs\Settings\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(definition="ProgramSettingsShiftDeadlines")
 */
final class ShiftDeadlines
{
    /**
     * @var int
     * @see ProgramLegacy::$late_field_deadline
     * @SWG\Property(example=72)
     */
    public $lateFieldDeadlineHours = 72;

    /**
     * @var int
     * @see ProgramLegacy::$late_clinical_deadline
     * @SWG\Property(example=72)
     */
    public $lateClinicalDeadlineHours = 72;

    /**
     * @var int
     * @see ProgramLegacy::$late_lab_deadline
     * @SWG\Property(example=72)
     */
    public $lateLabDeadlineHours = 72;

    /**
     * @var bool
     * @see ProgramSettings::$autolock_late_shifts
     * @SWG\Property(example=false)
     */
    public $autolockLateShifts = false;
}
