<?php namespace Fisdap\Members\Queue\JobHandlers;

use Fisdap\Data\Requirement\DoctrineRequirementRepository;
use Fisdap\Data\Requirement\RequirementRepository;
use Illuminate\Contracts\Queue\Job;

/**
 * Class UpdateCompliance
 *
 * @package Fisdap\Members\Queue\JobHandlers
 * @author  khanson?
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class UpdateCompliance extends JobHandler
{
    /**
     * @var RequirementRepository|DoctrineRequirementRepository
     */
    private $requirementRepository;


    /**
     * @param RequirementRepository $requirementRepository
     */
    public function __construct(RequirementRepository $requirementRepository)
    {
        parent::__construct();
        $this->requirementRepository = $requirementRepository;
    }


    /**
     * @inheritdoc
     */
    public function fire(Job $job, $data)
    {
        $this->logStart($job);

        // make sure we have database connections
        $this->reopenDBConnections();

        // allow MySQL connections to last longer
        $mysqlTimeout = 3600;
        $this->em->getConnection()->exec("SET SESSION wait_timeout = {$mysqlTimeout}");
        $this->db->query("SET SESSION wait_timeout = {$mysqlTimeout}");

        $cacheId = $data['cacheId'];
        $userContextIds = $data['userContextIds'];

        // update compliance for this batch of user roles
        $this->requirementRepository->updateCompliance($userContextIds);

        $this->logSuccess($job);

        $job->delete();

        $this->updateCache($cacheId, -1);
    }


    /**
     * @param $cacheId string the id of the cache you want to update
     * @param $jobCount int the number of jobs to add to the count
     */
    private function updateCache($cacheId, $jobCount)
    {
        $jobStatus = $this->cache->load($cacheId);
        $newStatus['jobs'] = $jobStatus['jobs'] + $jobCount;
        $this->cache->save($newStatus, $cacheId, [], 0);
    }
}
