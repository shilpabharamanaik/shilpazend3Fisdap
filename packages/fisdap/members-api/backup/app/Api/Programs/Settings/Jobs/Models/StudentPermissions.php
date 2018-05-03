<?php namespace Fisdap\Api\Programs\Settings\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class StudentPermissions
 *
 * @package Fisdap\Api\Programs\Settings\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(definition="ProgramSettingsStudentPermissions")
 */
final class StudentPermissions
{
    /**
     * @var bool
     * @see ProgramLegacy::$can_students_pick_lab
     * @see ProgramSettings::$student_pick_lab
     * @SWG\Property(example=true)
     */
    public $pickLab = true;

    /**
     * @var bool
     * @see ProgramLegacy::$can_students_create_lab
     * @SWG\Property(example=true)
     */
    public $createLab = true;

    /**
     * @var bool
     * @see ProgramLegacy::$can_students_pick_clinical
     * @see ProgramSettings::$student_pick_clinical
     * @SWG\Property(example=true)
     */
    public $pickClinical = true;

    /**
     * @var bool
     * @see ProgramLegacy::$can_students_create_clinical
     * @SWG\Property(example=false)
     */
    public $createClinical = false;

    /**
     * @var bool
     * @see ProgramLegacy::$can_students_pick_field
     * @see ProgramSettings::$student_pick_field
     * @SWG\Property(example=true)
     */
    public $pickField = true;
    
    /**
     * @var bool
     * @see ProgramLegacy::$can_students_create_field
     * @SWG\Property(example=false)
     */
    public $createField = false;

    /**
     * @var bool
     * @see ProgramLegacy::$student_view_full_calendar
     * @see ProgramSettings::$student_view_full_calendar
     * @SWG\Property(example=false)
     */
    public $viewFullCalendar = false;

    /**
     * @var bool
     * @see ProgramLegacy::$allow_absent_with_permission
     * @SWG\Property(example=true)
     */
    public $allowAbsentWithPermission = true;

    /**
     * @var bool
     * @see ProgramLegacy::$include_narrative
     * @SWG\Property(example=true)
     */
    public $includeNarrative = true;

    /**
     * @var bool
     * @see ProgramSettings::$student_switch_field
     * @SWG\Property(example=false)
     */
    public $switchField = false;

    /**
     * @var bool
     * @see ProgramSettings::$switch_field_needs_permission
     * @SWG\Property(example=false)
     */
    public $switchFieldNeedsPermission = false;

    /**
     * @var bool
     * @see ProgramSettings::$student_switch_clinical
     * @SWG\Property(example=false)
     */
    public $switchClinical = false;

    /**
     * @var bool
     * @see ProgramSettings::$switch_clinical_needs_permission
     * @SWG\Property(example=false)
     */
    public $switchClinicalNeedsPermission = false;

    /**
     * @var bool
     * @see ProgramSettings::$student_switch_lab
     * @SWG\Property(example=false)
     */
    public $switchLab = false;

    /**
     * @var bool
     * @see ProgramSettings::$switch_lab_needs_permission
     * @SWG\Property(example=false)
     */
    public $switchLabNeedsPermission = false;
}
