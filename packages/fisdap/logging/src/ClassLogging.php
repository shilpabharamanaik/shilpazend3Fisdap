<?php namespace Fisdap\Logging;

use Log;
use Psr\Log\LoggerInterface;


/**
 * Utility Trait for logging actions/messages with class/method info
 *
 * @package Fisdap\Logging
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
trait ClassLogging
{
    /**
     * @var LoggerInterface
     */
    protected $classLogger;


    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    protected function setClassLogger(LoggerInterface $logger)
    {
        $this->classLogger = $logger;

        return $this;
    }


    /**
     * @return LoggerInterface
     */
    protected function getClassLogger()
    {
        return $this->classLogger ?: Log::getMonolog();
    }


    /**
     * @param string $message
     * @param array  $context
     */
    protected function classLogDebug($message, array $context = [])
    {
        $this->getClassLogger()->debug($this->addCallingInfo($message), $context);
    }


    /**
     * @param string $message
     * @param array  $context
     */
    protected function classLogInfo($message, array $context = [])
    {
        $this->getClassLogger()->info($this->addCallingInfo($message), $context);
    }


    /**
     * @param string $message
     * @param array  $context
     */
    protected function classLogNotice($message, array $context = [])
    {
        $this->getClassLogger()->notice($this->addCallingInfo($message), $context);
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
