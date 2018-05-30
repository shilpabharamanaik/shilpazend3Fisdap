<?php namespace Fisdap\AppHealthChecks\HealthChecks;

use Psr\Log\LoggerInterface;

/**
 * Template for a health check
 *
 * @package Fisdap\AppHealthChecks\HealthChecks
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
abstract class HealthCheck implements ChecksHealth
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var float
     */
    protected $startTime = 0;

    /**
     * @var float
     */
    protected $stopTime = 0;

    /**
     * @var string
     */
    protected $status = self::STATUS_UNKNOWN;

    /**
     * @var string|null
     */
    protected $error = null;

    /**
     * @var int
     */
    public static $totalErrors = 0;

    /**
     * @var int
     */
    public static $totalRunTime = 0;


    protected function start()
    {
        $this->startTime = microtime(true);
    }


    protected function stop()
    {
        $this->stopTime = microtime(true);
        self::$totalRunTime += $this->getRunTime();
    }


    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @return string
     */
    abstract public function getName();


    abstract public function check();


    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @return float Total runtime in milliseconds (ms)
     */
    public function getRunTime()
    {
        return round(($this->stopTime - $this->startTime) * 1000, 2);
    }


    /**
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }


    /**
     * @param \Exception $e
     */
    protected function handleError(\Exception $e)
    {
        $exceptionMessage = $e->getMessage();
        $this->status = self::STATUS_FAILURE;
        $this->error = $exceptionMessage;
        $this->logger->error("{$this->getName()} Health Check Failure - $exceptionMessage");
        self::$totalErrors++;
    }
}
