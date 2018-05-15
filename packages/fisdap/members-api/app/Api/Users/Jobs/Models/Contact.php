<?php namespace Fisdap\Api\Users\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * Class Contact
 *
 * @package Fisdap\Api\Users\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 *
 * @SWG\Definition(definition="UserContact")
 */
class Contact
{
    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $name = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $phone = null;

    /**
     * @var string|null
     * @SWG\Property(type="string")
     */
    public $relation = null;


    /**
     * Contact constructor.
     *
     * @param null|string $name
     * @param null|string $phone
     * @param null|string $relation
     */
    public function __construct($name = null, $phone = null, $relation = null)
    {
        $this->name = $name;
        $this->phone = $phone;
        $this->relation = $relation;
    }
}
