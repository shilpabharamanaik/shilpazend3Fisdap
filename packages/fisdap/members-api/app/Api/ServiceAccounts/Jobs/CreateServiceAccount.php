<?php namespace Fisdap\Api\ServiceAccounts\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\ServiceAccounts\Entities\ServiceAccount;
use Fisdap\Api\ServiceAccounts\Entities\ServiceAccountPermission;
use Fisdap\Api\ServiceAccounts\Permissions\Repository\ServiceAccountPermissionsRepository;
use Fisdap\Api\ServiceAccounts\Repository\ServiceAccountsRepository;

/**
 * Class CreateServiceAccount
 *
 * @package Fisdap\Api\ServiceAccounts\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CreateServiceAccount extends Job
{
    /**
     * @var string
     */
    private $oauth2ClientId;
    
    /**
     * @var string
     */
    private $name;
    
    /**
     * @var int[]|array
     */
    private $permissionIds;


    /**
     * CreateServiceAccount constructor.
     *
     * @param string       $oauth2ClientId
     * @param string       $name
     * @param int[]|array  $permissionIds
     */
    public function __construct($oauth2ClientId, $name, array $permissionIds = [])
    {
        $this->oauth2ClientId = $oauth2ClientId;
        $this->name = $name;
        $this->permissionIds = $permissionIds;
    }


    /**
     * @param ServiceAccountsRepository           $serviceAccountsRepository
     * @param ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository
     */
    public function handle(
        ServiceAccountsRepository $serviceAccountsRepository,
        ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository
    ) {
        $serviceAccount = new ServiceAccount($this->oauth2ClientId, $this->name);
        
        foreach ($this->permissionIds as $permissionId) {
            /** @var ServiceAccountPermission $permission */
            $permission = $serviceAccountPermissionsRepository->findOneBy(['id' => $permissionId]);
            
            if ($permission instanceof ServiceAccountPermission) {
                $serviceAccount->addPermission($permission);
            }
        }
        
        $serviceAccountsRepository->store($serviceAccount);
    }
}
