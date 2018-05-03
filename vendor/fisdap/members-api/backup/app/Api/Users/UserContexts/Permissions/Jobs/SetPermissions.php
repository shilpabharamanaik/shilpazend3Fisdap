<?php namespace Fisdap\Api\Users\UserContexts\Permissions\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Users\CurrentUser\CurrentUser;
use Fisdap\Api\Users\UserContexts\Permissions\Events\PermissionsWereSet;
use Fisdap\Data\Instructor\InstructorLegacyRepository;
use Fisdap\Data\Permission\PermissionRepository;
use Fisdap\Entity\InstructorLegacy;
use Fisdap\Entity\RoleData;
use Illuminate\Contracts\Events\Dispatcher as EventDispatcher;

/**
 * A Job (Command) for setting role permissions
 *
 * @package Fisdap\Api\Users\UserContexts\Permissions\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class SetPermissions extends Job
{
    /**
     * @var RoleData|InstructorLegacy
     */
    private $roleData;

    /**
     * @var int[]
     */
    private $permissionIds;


    /**
     * SetPermissions constructor.
     *
     * @param RoleData $roleData
     * @param int[]    $permissionIds
     */
    public function __construct(RoleData $roleData, array $permissionIds)
    {
        $this->roleData = $roleData;
        $this->permissionIds = $permissionIds;
    }


    /**
     * @param PermissionRepository       $permissionRepository
     * @param InstructorLegacyRepository $instructorLegacyRepository
     * @param CurrentUser                $currentUser
     * @param EventDispatcher            $eventDispatcher
     */
    public function handle(
        PermissionRepository $permissionRepository,
        InstructorLegacyRepository $instructorLegacyRepository,
        CurrentUser $currentUser,
        EventDispatcher $eventDispatcher
    ) {
        // currently, InstructorLegacy is the only entity supporting permissions
        if (! $this->roleData instanceof InstructorLegacy) {
            return;
        }

        $permissionBitValues = [];

        /** @var  $permissions */
        $permissions = $permissionRepository->getById($this->permissionIds);

        foreach ($permissions as $permission) {
            $permissionBitValues[] = $permission->getBitValue();
        }

        $this->roleData->set_permissions(array_sum($permissionBitValues));

        $instructorLegacyRepository->update($this->roleData);

        $eventDispatcher->fire(new PermissionsWereSet($this->roleData, $currentUser->user()));
    }
}
