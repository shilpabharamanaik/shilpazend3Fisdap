<?php namespace Fisdap\Api\Users\UserContexts\Roles\Instructors\Permissions;

use Fisdap\Api\Users\UserContexts\Permissions\Finder\FindsPermissions;
use Fisdap\Api\Users\UserContexts\Permissions\UserContextPermission;
use Fisdap\Entity\UserContext;

/**
 * Validates whether an (instructor) user role has permission to 'view all data'
 *
 * @package Fisdap\Api\Users\UserContexts\Roles\Instructors\Permissions
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ViewAllData implements UserContextPermission
{
    const VIEW_ALL_DATA_PERMISSION = 'view all data';


    /**
     * @var FindsPermissions
     */
    private $permissionsFinder;


    /**
     * @param FindsPermissions $permissionsFinder
     */
    public function __construct(FindsPermissions $permissionsFinder)
    {
        $this->permissionsFinder = $permissionsFinder;
    }


    /**
     * @inheritdoc
     */
    public function permitted(UserContext $userContext)
    {
        $instructorPermissions = $this->permissionsFinder->getInstructorPermissionNames(
            $userContext->getRoleData()->getId()
        );

        return $this->permissionsFinder->hasInstructorPermission(
            self::VIEW_ALL_DATA_PERMISSION,
            $instructorPermissions
        );
    }
}
