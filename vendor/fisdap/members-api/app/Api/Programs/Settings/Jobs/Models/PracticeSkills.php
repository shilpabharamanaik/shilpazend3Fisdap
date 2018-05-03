<?php namespace Fisdap\Api\Programs\Settings\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class PracticeSkills
 *
 * @package Fisdap\Api\Programs\Settings\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(definition="ProgramSettingsPracticeSkills")
 */
final class PracticeSkills
{
    /**
     * @var bool
     * @see ProgramSettings::$practice_skills_field
     * @SWG\Property(example=false)
     */
    public $field = false;

    /**
     * @var bool
     * @see ProgramSettings::$practice_skills_clinical
     * @SWG\Property(example=false)
     */
    public $clinical = false;
}
