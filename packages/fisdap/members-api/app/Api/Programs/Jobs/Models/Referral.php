<?php namespace Fisdap\Api\Programs\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class Referral
 *
 * @package Fisdap\Api\Programs\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @SWG\Definition(definition="ProgramReferral", required={"source"})
 */
final class Referral
{
    /**
     * @var string
     * @see ProgramLegacy::$referral
     * @SWG\Property(type="string",
     *     enum={"Blog or Forum","Event","Friend or Colleague","Search Engine","Social Media","Other"}
     * )
     */
    public $source;

    /**
     * @var string|null
     * @see ProgramLegacy::$ref_description
     * @SWG\Property(type="string")
     */
    public $description = null;
}
