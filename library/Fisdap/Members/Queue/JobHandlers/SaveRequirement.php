<?php namespace Fisdap\Members\Queue\JobHandlers;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Capsule\Manager as Queue;


/**
 * Class SaveRequirement
 *
 * @package Fisdap\Members\Queue\JobHandlers
 * @author  jmortenson
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class SaveRequirement extends JobHandler
{
    /**
     * @var Queue
     */
    private $queue;


    /**
     * @param Queue $queue
     */
    public function __construct(Queue $queue)
    {
        $this->queue = $queue;
        parent::__construct();
    }


    /**
     * @inheritdoc
     */
    public function fire(Job $job, $data)
    {
        $this->logStart($job);

        $cacheId = $data['cacheId'];

        // create an instance of the Edit Requirement form, which contains the logic to process this requirement
        $program = \Fisdap\EntityUtils::getEntity("ProgramLegacy", $data["program_id"]);
        $req = \Fisdap\EntityUtils::getEntity("Requirement", $data['requirement_id']);

        // there is specialty logic in the Requirement form versus the Edit Requirement form
        $requirementForm = ($data['edit']) ? new \Scheduler_Form_EditRequirement($req, null, $program)
            : new \Scheduler_Form_Requirement(null, $program);

        // save the changes to the requirement itself
        $compute_compliance_userContextIds = $requirementForm->process($data);

        // now we queue up the users in batches for compliance recalculation
        $batch_size = 100;
        $batch = [];

        foreach ($compute_compliance_userContextIds as $userContextId) {
            $batch[] = $userContextId;

            // when we get a full batch, queue the job
            if (count($batch) >= $batch_size) {
                $this->updateCache($cacheId, 1);
                $this->queue->push('UpdateCompliance', ["cacheId" => $cacheId, "userContextIds" => $batch]);
                $batch = [];
            }
        }

        // queue up the stragglers, too
        if (count($batch) > 0) {
            $this->updateCache($cacheId, 1);
            $this->queue->push('UpdateCompliance', ["cacheId" => $cacheId, "userContextIds" => $batch]);
        }

        $this->logSuccess($job);

        $job->delete();

        $this->updateCache($cacheId, -1);
    }


    /**
     * @param $cacheId  string the id of the cache you want to update
     * @param $jobCount int the number of jobs to add to the count
     */
    private function updateCache($cacheId, $jobCount)
    {
        $jobStatus = $this->cache->load($cacheId);
        $newStatus['jobs'] = $jobStatus['jobs'] + $jobCount;
        $this->cache->save($newStatus, $cacheId, [], 0);
    }
}