<?php namespace Fisdap\Api\Programs\Jobs\Models;

use Swagger\Annotations as SWG;


/**
 * Class Phones
 *
 * @package Fisdap\Api\Programs\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *          
 * @SWG\Definition(definition="ProgramPhones", required={"phone"})
 */
final class Phones
{
    /**
     * @var string
     * @SWG\Property(type="string", example="111-222-3456 x7890")
     */
    public $phone;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $fax = null;
}