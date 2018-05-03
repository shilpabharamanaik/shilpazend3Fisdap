<?php namespace Fisdap\Api\Users\UserContexts\Permissions\Finder;

use Fisdap\Entity\Permission;
use Fisdap\ErrorHandling\Exceptions\NoPermissions;


/**
 * Contract for retrieving and determining user permissions
 *
 * @package Fisdap\Api\Users\Permissions
 */
interface FindsPermissions
{
    /**
     * @return Permission[]
     */
    public function all();


    /**
     * @param int $permissionId
     *
     * @return Permission|null
     */
    public function one($permissionId);


    /**
     * @param $instructorId
     *
     * @return array
     * @throws NoPermissions
     */
    public function getInstructorPermissions($instructorId);


    /**
     * @param int $instructorId
     *
     * @return array
     * @throws NoPermissions
     */
    public function getInstructorPermissionNames($instructorId);


    /**
     * @param string $permissionName
     * @param array  $instructorPermissions
     *
     * @return bool
     */
    public function hasInstructorPermission($permissionName, array $instructorPermissions);
}