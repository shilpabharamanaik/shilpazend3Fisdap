<?php namespace Fisdap\Members\Queue\JobHandlers;

use Fisdap\Data\Requirement\RequirementRepository;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Capsule\Manager as Queue;

/**
 * Processes requests made from the RequirementAssignModal on the Manage Requirements page
 *
 * @package Fisdap\Members\Queue\JobHandlers
 * @author  smcintyre
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @todo looks like we've copy/pasted code from the SaveRequirement handler...maybe refactor?
 */
class SaveRequirementAssignModal extends JobHandler
{
    /**
     * @var RequirementRepository
     */
    private $requirementRepository;

    /**
     * @param Queue                 $queue
     * @param RequirementRepository $requirementRepository
     */
    public function __construct(Queue $queue, RequirementRepository $requirementRepository)
    {
        $this->queue = $queue;
        $this->requirementRepository = $requirementRepository;

        parent::__construct();
    }


    /**
     * @inheritdoc
     */
    public function fire(Job $job, $data)
    {
        $this->logStart($job);

        $cacheId = $data['cacheId'];

        $requirement_ids = $data['requirement_ids'];
        $userContextIds = $data['userContextIds'];
        $due_date = $data['due_date'];
        $program_id = $data['program_id'];
        $assigner_userContextId = $data['assigner_userContextId'];

        // Process the Requirement Assign Modal
        $form = new \Scheduler_Form_RequirementAssignModal($this->requirementRepository);
        $compute_compliance_userContextIds = $form->process(
            $requirement_ids,
            $userContextIds,
            $due_date,
            $program_id,
            $assigner_userContextId
        );

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
