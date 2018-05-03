<?php namespace Fisdap\Api\Programs\Settings\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class Signoff
 *
 * @package Fisdap\Api\Programs\SettingsJobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 *
 * @SWG\Definition(definition="ProgramSettingsSignoff")
 */
final class Signoff
{
    /**
     * @var bool
     * @see ProgramSettings::$allow_educator_signoff_signature
     * @SWG\Property(example=false)
     */
    public $allowWithSignature = false;

    /**
     * @var bool
     * @see ProgramSettings::$allow_educator_signoff_login
     * @SWG\Property(example=false)
     */
    public $allowWithLogin = false;

    /**
     * @var bool
     * @see ProgramSettings::$allow_educator_signoff_email
     * @SWG\Property(example=false)
     */
    public $allowWithEmail = false;

    /**
     * @var bool
     * @see ProgramSettings::$allow_educator_signoff_attachment
     * @SWG\Property(example=false)
     */
    public $allowWithAttachment = false;
    
    /**
     * @var bool
     * @see ProgramSettings::$allow_signoff_on_patient
     * @SWG\Property(example=false)
     */
    public $allowOnPatient = false;

    /**
     * @var bool
     * @see ProgramSettings::$allow_signoff_on_shift
     * @SWG\Property(example=false)
     */
    public $allowOnShift = false;
}
