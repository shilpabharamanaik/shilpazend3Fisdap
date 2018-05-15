<?php namespace Fisdap\Members\Queue\JobHandlers;

use Illuminate\Contracts\Queue\Job;

/**
 * Interface HandlesJob
 *
 * @package Fisdap\Members\Queue\JobHandlers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface HandlesJob
{
    /**
     * Run the job. This is what is called by the listener process
     *
     * @param Job          $job  Job object
     * @param array|string $data Data about the Job
     */
    public function fire(Job $job, $data);
}
