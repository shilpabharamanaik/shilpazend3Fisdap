<?php namespace Fisdap\Api\Programs\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class Accreditation
 *
 * @package Fisdap\Api\Programs\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @SWG\Definition(definition="ProgramAccreditation")
 */
final class Accreditation
{
    /**
     * @var bool|null
     * @SWG\Property(type="boolean")
     */
    public $accredited = null;

    /**
     * @var int|null
     * @see ProgramLegacy::$coaemsp_program_id
     * @SWG\Property(type="integer")
     */
    public $coaemspProgramId = null;

    /**
     * ProgramLegacy::$year_accredited
     *
     * @var int|null
     * @SWG\Property(type="integer")
     */
    public $yearAccredited = null;
}
