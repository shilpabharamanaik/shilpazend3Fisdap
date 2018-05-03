<?php namespace Fisdap\Members\Queue\Workers;

use Illuminate\Queue\Capsule\Manager as Queue;
use Illuminate\Queue\Jobs\Job;

/**
 * Class Worker
 *
 * Provides general template for Fisdap workers, scripts that automatically
 * handle background tasks from a work queue
 *
 * @package scripts\workers
 * @author jmortenson
 * @deprecated
 */
class Worker {

    /**
     * THe queue manager instance.
     *
     * @var Queue
     */
    protected $manager;

    // the Logger for log messages
    protected $logger;

    /**
     * Create a new queue worker.
     *
     * @param Queue $manager
     * @deprecated
     */
    public function __construct(Queue $manager)
    {
        $this->manager = $manager;

        $this->logger = \Zend_Registry::get('logger');
    }

    /**
     * Listen to the given queue.
     *
     * @param  string  $connectionName
     * @param  string  $queue
     * @param  int     $delay
     * @param  int     $memory
     * @param  int     $sleep
     * @param  int     $maxTries
     * @return void
     */
    public function pop($connectionName, $queue = null, $delay = 0, $memory = 128, $sleep = 3, $maxTries = 0)
    {
        // set memory_limit to the desired $memory limit
        if ($memory > 0) {
            ini_set('memory_limit', intval($memory) . 'M');
        }

        $connection = $this->manager->connection($connectionName);

        $job = $this->getNextJob($connection, $queue);

        // If we're able to pull a job off of the stack, we will process it and
        // then make sure we are not exceeding our memory limits for the run
        // which is to protect against run-away memory leakages from here.
        if ( ! is_null($job))
        {
            // Clear the Doctrine Entity Manager (detaches all entities so no data state is retained)
            \Fisdap\EntityUtils::getEntityManager()->clear();
            // clear the current logged in user (an exception where an entity is stored in Zend Registry)
            \Zend_Registry::getInstance()->offsetUnset('LoggedInUser');

            // process the job
            $this->process(
                $this->manager->getName($connectionName), $job, $maxTries, $delay
            );

            // check to see if we are reaching maximum memory threshhold. Self-terminate if so
            // supervisord will restart the process
            // $memory is in MB
            // @todo this would be better to handle with forked processes I think - Jesse
            if ((memory_get_usage() / 1000 / 1000) > (.8 * $memory)) {
                $this->logger->info('Queue worker self-terminated after processing a job because it got too close (' . round(memory_get_usage() / 1000 / 1000) . 'MB) to memory limit (' . $memory . 'MB).');
                exit;
            }
        }
        else {
            $this->sleep($sleep);
        }
    }

    /**
     * Get the next job from the queue connection.
     *
     * @param  \Illuminate\Queue\Queue  $connection
     * @param  string  $queue
     * @return Job|null
     */
    protected function getNextJob($connection, $queue)
    {
        if (is_null($queue)) return $connection->pop();

        foreach (explode(',', $queue) as $queue)
        {
            if ( ! is_null($job = $connection->pop($queue))) return $job;
        }
    }

    /**
     * Process a given job from the queue.
     *
     * @param  string  $connection
     * @param  Job  $job
     * @param  int  $maxTries
     * @param  int  $delay
     * @return void
     *
     * @throws \Exception
     */
    public function process($connection, Job $job, $maxTries = 0, $delay = 0)
    {
        // DEBUG ONLY DEBUG ONLY
        //return $this->logFailedJob($connection, $job);

        if ($maxTries > 0 && $job->attempts() > $maxTries)
        {
            return $this->logFailedJob($connection, $job);
        }

        try
        {
            // First we will fire off the job. Once it is done we will see if it will
            // be auto-deleted after processing and if so we will go ahead and run
            // the delete method on the job. Otherwise we will just keep moving.
            $job->fire();

            if ($job->autoDelete()) $job->delete();
        }

        catch (\Exception $e)
        {
            // If we catch an exception, we will attempt to release the job back onto
            // the queue so it is not lost. This will let is be retried at a later
            // time by another listener (or the same one). We will do that here.
            if ( ! $job->isDeleted()) $job->release($delay);

            throw $e;
        }
    }

    /**
     * Log a failed job into storage and bury it.
     *
     * @param  string  $connection
     * @param  Job  $job
     * @return void
     */
    protected function logFailedJob($connection, Job $job)
    {
        $logger = \Zend_Registry::get('logger');
        $context = array(
            'jobBody' => $job->getRawBody(),
            'queue' => $job->getQueue(),
            'connection' => $connection,
        );
        $logger->error('Job failed', $context);

        $bugsnag = \Zend_Registry::get('bugsnag');
        if ($bugsnag instanceof \Bugsnag_Client) {
            $bugsnag->setMetaData($context);
            $bugsnag->notifyError('Job failed', 'Job failed');
        }

        $job->bury();
    }


    /**
     * Raise the failed queue job event.
     *
     * @param  string  $connection
     * @param  Job  $job
     * @return void

    protected function raiseFailedJobEvent($connection, Job $job)
    {
        if ($this->events)
        {
            $data = json_decode($job->getRawBody(), true);

            $this->events->fire('illuminate.queue.failed', array($connection, $job, $data));
        }
    }
     */

    /**
     * Sleep the script for a given number of seconds.
     *
     * @param  int   $seconds
     * @return void
     */
    public function sleep($seconds)
    {
        sleep($seconds);
    }

    /**
     * Get the queue manager instance.
     *
     * @return Queue
     */
    public function getManager()
    {
        return $this->manager;
    }

    /**
     * Set the queue manager instance.
     *
     * @param  Queue $manager
     * @return void
     */
    public function setManager(Queue $manager)
    {
        $this->manager = $manager;
    }
}