<?php namespace Fisdap\Api\Users\UserContexts\Permissions;

use Fisdap\Entity\UserContext;


/**
 * Contract for a UserContext-based permission
 *
 * @package Fisdap\Api\Permissions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface UserContextPermission
{
    /**
     * Validates whether the specified UserContext has permission
     *
     * @param UserContext $userContext
     *
     * @return bool
     */
    public function permitted(UserContext $userContext);
}