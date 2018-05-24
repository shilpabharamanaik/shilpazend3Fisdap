<?php namespace Fisdap\Api\ServiceAccounts\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\ServiceAccounts\Permissions\Repository\ServiceAccountPermissionsRepository;


/**
 * Class DeleteServiceAccountPermissions
 *
 * @package Fisdap\Api\ServiceAccounts\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class DeleteServiceAccountPermissions extends Job
{
    /**
     * @var int[]
     */
    private $ids;


    /**
     * DeleteServiceAccounts constructor.
     *
     * @param int[] $ids
     */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
    }


    /**
     * @param ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository
     */
    public function handle(ServiceAccountPermissionsRepository $serviceAccountPermissionsRepository)
    {
        $serviceAccountPermissionsRepository->destroyCollection($this->ids);
    }
}