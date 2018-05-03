<?php namespace Fisdap\Api\Users\Jobs\Models;

use Swagger\Annotations as SWG;


/**
 * Class Phones
 *
 * @package Fisdap\Api\Users\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 *
 * @SWG\Definition(definition="UserPhones")
 */
class Phones
{
    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $homePhone = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $workPhone = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $cellPhone = null;


    /**
     * Phones constructor.
     *
     * @param null|string $homePhone
     * @param null|string $workPhone
     * @param null|string $cellPhone
     */
    public function __construct($homePhone = null, $workPhone = null, $cellPhone = null)
    {
        $this->homePhone = $homePhone;
        $this->workPhone = $workPhone;
        $this->cellPhone = $cellPhone;
    }
}