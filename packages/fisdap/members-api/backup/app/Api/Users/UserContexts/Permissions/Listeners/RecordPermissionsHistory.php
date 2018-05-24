<?php namespace Fisdap\Api\Users\UserContexts\Permissions\Listeners;

use Fisdap\Api\Users\UserContexts\Permissions\Events\PermissionsWereSet;
use Fisdap\Data\Instructor\InstructorLegacyRepository;
use Fisdap\Data\Permission\PermissionHistoryLegacyRepository;
use Fisdap\Entity\PermissionHistoryLegacy;
use Fisdap\Logging\Events\EventLogging;
use Illuminate\Contracts\Queue\ShouldQueue;


/**
 * Records a change to role permissions
 *
 * @package Fisdap\Api\Users\UserContexts\Permissions\Listeners
 */
final class RecordPermissionsHistory implements ShouldQueue
{
    use EventLogging;


    /**
     * @var InstructorLegacyRepository
     */
    private $instructorLegacyRepository;

    /**
     * @var PermissionHistoryLegacyRepository
     */
    private $permissionHistoryLegacyRepository;


    /**
     * RecordPermissionsHistory constructor.
     *
     * @param InstructorLegacyRepository        $instructorLegacyRepository
     * @param PermissionHistoryLegacyRepository $permissionHistoryLegacyRepository
     */
    public function __construct(
        InstructorLegacyRepository $instructorLegacyRepository,
        PermissionHistoryLegacyRepository $permissionHistoryLegacyRepository
    ) {
        $this->instructorLegacyRepository = $instructorLegacyRepository;
        $this->permissionHistoryLegacyRepository = $permissionHistoryLegacyRepository;
    }


    /**
     * @param PermissionsWereSet $event
     */
    public function handle(PermissionsWereSet $event)
    {
        $permissionHistory = new PermissionHistoryLegacy;
        $permissionHistory->setEntryTime($event->getEntryTime());
        $permissionHistory->setChangedInstructor($this->instructorLegacyRepository->getOneById($event->getChangedRoleDataId()));
        $permissionHistory->setPermissions($event->getPermissions());

        $changerRoleId = $event->getChangerRoleDataId();

        if ( ! is_null($changerRoleId)) {
            $permissionHistory->setChanger($this->instructorLegacyRepository->getOneById($changerRoleId));
        }

        $this->permissionHistoryLegacyRepository->store($permissionHistory);
    }
}