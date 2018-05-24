<?php namespace Fisdap\AppHealthChecks\HealthChecks;

use Psr\Log\LoggerInterface;


/**
 * Checks storing, retrieving, and deleting objects in Couchbase
 *
 * @package Fisdap\AppHealthChecks\HealthChecks
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CouchbaseHealthCheck extends HealthCheck
{
    /**
     * @var \Couchbase
     */
    private $couchbase;


    /**
     * @param \Couchbase      $couchbase
     * @param LoggerInterface $logger
     */
    public function __construct(\Couchbase $couchbase, LoggerInterface $logger) {
        parent::__construct($logger);
        $this->couchbase = $couchbase;
    }


    public function getName()
    {
        return 'Couchbase';
    }


    public function check()
    {
        $this->start();

        try {
            $tempObject = new \stdClass;
            $tempObject->str_attr = 'test';
            $tempObject->int_attr = 123;

            $uniqueKey = uniqid('membersHealthCheck');

            // create a temporary object in couchbase, setting TTL to 5 seconds
            $this->couchbase->set($uniqueKey, $tempObject, 5);

            $getResult = $this->couchbase->get($uniqueKey);

            if ($getResult != $tempObject) {
                throw new HealthCheckFailure('Test object does not match result retrieved from Couchbase');
            }

            $this->couchbase->delete($uniqueKey);

            $this->status = self::STATUS_SUCCESS;

        } catch (\Exception $e) {
            $this->handleError($e);
        }

        $this->stop();
    }
} 