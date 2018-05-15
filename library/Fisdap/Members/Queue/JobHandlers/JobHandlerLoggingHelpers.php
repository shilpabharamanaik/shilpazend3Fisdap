<?php namespace Fisdap\Members\Queue\JobHandlers;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Jobs\RedisJob;
use Psr\Log\LoggerInterface;

/**
 * Class JobHandlerLoggingHelpers
 *
 * @package Fisdap\Members\Queue\JobHandlers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait JobHandlerLoggingHelpers
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var float
     */
    protected $startTime;


    /**
     * @param Job|RedisJob    $job
     * @param string          $message
     * @param string          $severity
     */
    protected function log(Job $job, $message, $severity = 'info')
    {
        $this->logger->$severity($message, ['jobId' => $job->getJobId(), 'pid' => getmypid()]);
    }


    /**
     * @param Job|RedisJob $job
     */
    protected function logStart(Job $job)
    {
        $this->startTime = microtime(true);
        $this->logger->notice(
            'Starting ' . get_class($this) . ' job...',
            ['jobId' => $job->getJobId(), 'pid' => getmypid()]
        );
    }


    /**
     * @param Job|RedisJob $job
     */
    protected function logSuccess(Job $job)
    {
        $runTime = null;

        if (isset($this->startTime)) {
            $runTime = round(microtime(true) - $this->startTime, 2) . 's';
        }

        $this->logger->notice(
            'Completed ' . get_class($this) . ' job',
            ['jobId' => $job->getJobId(), 'pid' => getmypid(), 'runTime' => $runTime]
        );
    }
}
