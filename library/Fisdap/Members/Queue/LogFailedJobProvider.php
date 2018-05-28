<?php namespace Fisdap\Members\Queue;

use Illuminate\Queue\Failed\FailedJobProviderInterface;
use Psr\Log\LoggerInterface;


/**
 * Class LogFailedJobProvider
 *
 * @package Fisdap\Members\Queue
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class LogFailedJobProvider implements FailedJobProviderInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Log a failed job into storage.
     *
     * @param  string $connection
     * @param  string $queue
     * @param  string $payload
     *
     * @return void
     */
    public function log($connection, $queue, $payload)
    {
        $this->logger->error("Failed queue job on $connection:$queue", json_decode($payload, true));
    }

    /**
     * Get a list of all of the failed jobs.
     *
     * @return array
     */
    public function all()
    {
        // TODO: Implement all() method.
    }

    /**
     * Get a single failed job.
     *
     * @param  mixed $id
     *
     * @return array
     */
    public function find($id)
    {
        // TODO: Implement find() method.
    }

    /**
     * Delete a single failed job from storage.
     *
     * @param  mixed $id
     *
     * @return bool
     */
    public function forget($id)
    {
        // TODO: Implement forget() method.
    }

    /**
     * Flush all of the failed jobs from storage.
     *
     * @return void
     */
    public function flush()
    {
        // TODO: Implement flush() method.
}}