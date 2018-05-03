<?php namespace Fisdap\Logging\Events;

use App;
use Psr\Log\LoggerInterface;


/**
 * Utility Trait for logging event activity
 *
 * @package Fisdap\Logging\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait EventLogging
{
    /**
     * @var LoggerInterface
     */
    protected $eventLogger;


    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    protected function setEventLogger(LoggerInterface $logger)
    {
        $this->eventLogger = $logger;

        return $this;
    }


    /**
     * @return LoggerInterface
     */
    protected function getEventLogger()
    {
        return $this->eventLogger ?: App::make(EventLogger::class);
    }


    /**
     * @param string $message
     * @param array  $context
     */
    protected function eventLogDebug($message, array $context = [])
    {
        $this->getEventLogger()->debug($this->addCallingInfo($message), $context);
    }


    /**
     * @param string $message
     * @param array  $context
     */
    protected function eventLogInfo($message, array $context = [])
    {
        $this->getEventLogger()->info($this->addCallingInfo($message), $context);
    }


    /**
     * @param string $message
     * @param array  $context
     */
    protected function eventLogNotice($message, array $context = [])
    {
        $this->getEventLogger()->notice($this->addCallingInfo($message), $context);
    }


    /**
     * @param string $message
     *
     * @return string
     */
    private function addCallingInfo($message)
    {
        return $this->getCallingInfo() . " - $message";
    }


    /**
     * @return string
     */
    private function getCallingInfo()
    {
        $debugBacktrace = debug_backtrace();

        return "{$debugBacktrace[2]['class']}::{$debugBacktrace[3]['function']}()";
    }
}