<?php namespace Fisdap\AppHealthChecks\HealthChecks;

use Psr\Log\LoggerInterface;


/**
 * Checks basic database connectivity and query functionality
 *
 * @package Fisdap\AppHealthChecks\HealthChecks
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class DatabaseHealthCheck extends HealthCheck
{
    /**
     * @var \PDO
     */
    private $database;


    /**
     * @param \PDO            $database
     * @param LoggerInterface $logger
     *
     * @throws \Exception
     */
    public function __construct(\PDO $database, LoggerInterface $logger) {
        parent::__construct($logger);
        $this->database = $database;
    }


    public function getName()
    {
        return 'Database';
    }


    public function check()
    {
        $this->start();

        try {

            $statement = $this->database->query('SELECT 1');

            if ($statement !== false) {
                $this->status = self::STATUS_SUCCESS;
            } else {
                throw new HealthCheckFailure("Database query (SELECT 1) failed");
            }

        } catch (\Exception $e) {
            $this->handleError($e);
        }

        $this->stop();
    }
} 