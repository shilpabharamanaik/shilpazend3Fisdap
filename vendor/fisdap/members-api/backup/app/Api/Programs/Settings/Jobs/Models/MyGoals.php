<?php namespace Fisdap\Api\Programs\Settings\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class MyGoals
 *
 * @package Fisdap\Api\Programs\Settings\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(definition="ProgramSettingsMyGoals")
 */
final class MyGoals
{
    /**
     * @var bool
     * @see ProgramSettings::$include_lab_in_mygoals
     * @SWG\Property(example=true)
     */
    public $includeLab = true;

    /**
     * @var bool
     * @see ProgramSettings::$include_field_in_mygoals
     * @SWG\Property(example=true)
     */
    public $includeField = true;

    /**
     * @var bool
     * @see ProgramSettings::$include_clinical_in_mygoals
     * @SWG\Property(example=true)
     */
    public $includeClinical = true;

    /**
     * @var array
     * @see ProgramSettings::$subject_types_in_mygoals
     * @SWG\Property(type="array", items=@SWG\Items(type="integer"), example={1})
     */
    public $subjectTypes = [1];
}
