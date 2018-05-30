<?php namespace Fisdap\Api\Users\UserContexts\Roles\Jobs\Models;

use Swagger\Annotations as SWG;

/**
 * A model for communication preferences data to be used when creating RoleData entities through a Job (Command) class
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Jobs\Models
 * @author  Ben Getsug <bgetsug@fisdap.net>
 *
 * @SWG\Definition(definition="RoleCommunicationPreferences")
 */
class CommunicationPreferences
{
    /**
     * @var bool
     * @SWG\Property
     */
    public $receiveClinicalLateDataEmails = false;

    /**
     * @var bool
     * @SWG\Property
     */
    public $receiveLabLateDataEmails = false;

    /**
     * @var bool
     * @SWG\Property
     */
    public $receiveFieldLateDataEmails = false;

    /**
     * @var bool
     * @SWG\Property
     */
    public $emailEvent = false;
}
