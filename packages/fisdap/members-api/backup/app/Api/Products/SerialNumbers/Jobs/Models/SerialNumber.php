<?php namespace Fisdap\Api\Products\SerialNumbers\Jobs\Models;

/**
 * Class SerialNumber
 *
 * @package Fisdap\Api\Products\SerialNumbers\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class SerialNumber
{
    /**
     * @var int|null
     */
    public $id = null;

    /**
     * @var string
     */
    public $number;

    /**
     * @var int|null
     */
    public $userId = null;

    /**
     * @var int|null
     */
    public $userContextId = null;
}
