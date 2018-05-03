<?php namespace Fisdap\Api\Users\UserContexts\Permissions\Events;

use Fisdap\Api\Events\Event;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\RoleData;
use Fisdap\Entity\User;


/**
 * Event to fire when role permissions have been set
 *
 * @package Fisdap\Api\Users\UserContexts\Permissions\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class PermissionsWereSet extends Event
{
    /**
     * @var \DateTime
     */
    private $entryTime;

    /**
     * @var int
     */
    private $changedRoleDataId;

    /**
     * @var int|null
     */
    private $changerRoleDataId = null;

    /**
     * @var int
     */
    private $permissions;


    /**
     * PermissionsWereSet constructor.
     *
     * @param RoleData|InstructorLegacy $roleData
     * @param User                      $currentUser
     *
     * @throws \Exception
     */
    public function __construct(RoleData $roleData, User $currentUser = null)
    {
        $this->entryTime = new \DateTime;
        $this->changedRoleDataId = $roleData->getId();
        $this->permissions = $roleData->permissions;

        if (!is_null($currentUser)) {
            $this->changerRoleDataId = $currentUser->context()->getRoleData()->getId();
        }
    }


    /**
     * @return \DateTime
     */
    public function getEntryTime()
    {
        return $this->entryTime;
    }


    /**
     * @return int
     */
    public function getChangedRoleDataId()
    {
        return $this->changedRoleDataId;
    }


    /**
     * @return int|null
     */
    public function getChangerRoleDataId()
    {
        return $this->changerRoleDataId;
    }


    /**
     * @return int
     */
    public function getPermissions()
    {
        return $this->permissions;
    }
}