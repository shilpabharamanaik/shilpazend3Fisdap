<?php namespace Fisdap\AppHealthChecks\HealthChecks;

use Illuminate\Redis\Database;
use Psr\Log\LoggerInterface;


/**
 * Checks storing, retrieving, and deleting a string in Redis
 *
 * @package Fisdap\AppHealthChecks\HealthChecks
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class RedisHealthCheck extends HealthCheck
{
    /**
     * @var Database|\Redis
     */
    private $redis;


    /**
     * @param Database        $redis
     * @param LoggerInterface $logger
     *
     * @throws \Exception
     */
    public function __construct(Database $redis, LoggerInterface $logger)
    {
        parent::__construct($logger);
        $this->redis = $redis;
    }


    public function getName()
    {
        return 'Redis';
    }


    public function check()
    {
        $this->start();

        try {
            $tempString = 'foo';

            $uniqueKey = uniqid('membersHealthCheck');

            // set a temporary value in Redis
            $this->redis->set($uniqueKey, $tempString);

            $getResult = $this->redis->get($uniqueKey);

            if ($getResult !== $tempString) {
                throw new HealthCheckFailure('Test string does not match result retrieved from Redis');
            }

            $this->redis->del($uniqueKey);

            $this->status = self::STATUS_SUCCESS;

        } catch (\Exception $e) {
            $this->handleError($e);
        }

        $this->stop();
    }
}