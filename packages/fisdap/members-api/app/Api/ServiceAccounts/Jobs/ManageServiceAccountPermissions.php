<?php namespace Fisdap\Api\ServiceAccounts\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\Queries\Exceptions\ResourceNotFound;
use Fisdap\Api\ServiceAccounts\Entities\ServiceAccountPermission;
use Fisdap\Api\ServiceAccounts\Permissions\Repository\ServiceAccountPermissionsRepository;
use Fisdap\Api\ServiceAccounts\Repository\ServiceAccountsRepository;

/**
 * Class ManageServiceAccountPermissions
 *
 * @package Fisdap\Api\ServiceAccounts\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ManageServiceAccountPermissions extends Job
{
    private static $allowedOperations = [
        'grant' => 'add',
        'deny'  => 'remove'
    ];

    /**
     * @var string
     */
    private $operation;

    /**
     * @var string
     */
    private $oauth2ClientId;

    /**
     * @var int[]
     */
    private $permissionIds;


    /**
     * ManageServiceAccountPermissions constructor.
     *
     * @param string $operation
     * @param string $oauth2ClientId
     * @param int[]  $permissionIds
     */
    public function __construct($operation, $oauth2ClientId, array $permissionIds)
    {
        $this->operation = $operation;
        $this->oauth2ClientId = $oauth2ClientId;
        $this->permissionIds = $permissionIds;
    }


    /**
     * @param ServiceAccountsRepository           $serviceAccountsRepository
     * @param ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository
     *
     * @return string[]
     */
    public function handle(
        ServiceAccountsRepository $serviceAccountsRepository,
        ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository
    ) {
        if (! in_array($this->operation, array_keys(self::$allowedOperations))) {
            throw new \InvalidArgumentException(
                "'{$this->operation}' is not a valid operation. Valid operations: " . implode(', ', array_keys(self::$allowedOperations))
            );
        }

        $serviceAccount = $serviceAccountsRepository->findOneBy(['oauth2ClientId' => $this->oauth2ClientId]);

        if (is_null($serviceAccount)) {
            throw new ResourceNotFound("Unable to find service account with an OAuth2 Client ID of {$this->oauth2ClientId}");
        }

        $permissionsManaged = [];

        foreach ($this->permissionIds as $permissionId) {
            /** @var ServiceAccountPermission $permission */
            $permission = $serviceAccountPermissionsRepository->findOneBy(['id' => $permissionId]);

            if ($permission instanceof ServiceAccountPermission) {
                $serviceAccount->{self::$allowedOperations[$this->operation] . 'Permission'}($permission);
                $permissionsManaged[] = $permission->getRouteName();
            }
        }

        $serviceAccountsRepository->update($serviceAccount);

        return $permissionsManaged;
    }
}
