<?php namespace Fisdap\Api\ServiceAccounts\Jobs;

use Fisdap\Api\Jobs\Job;
use Fisdap\Api\ServiceAccounts\Repository\ServiceAccountsRepository;


/**
 * Class DeleteServiceAccounts
 *
 * @package Fisdap\Api\ServiceAccounts\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class DeleteServiceAccounts extends Job
{
    /**
     * @var string[]
     */
    private $oauth2ClientIds;


    /**
     * DeleteServiceAccounts constructor.
     *
     * @param string[] $oauth2ClientIds
     */
    public function __construct(array $oauth2ClientIds)
    {
        $this->oauth2ClientIds = $oauth2ClientIds;
    }


    /**
     * @param ServiceAccountsRepository $serviceAccountsRepository
     */
    public function handle(ServiceAccountsRepository $serviceAccountsRepository)
    {
        $serviceAccountsRepository->destroyCollection($this->oauth2ClientIds);
    }
}