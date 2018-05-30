<?php namespace Fisdap\Logging;

use Monolog\Handler\HandlerInterface;

/**
 * Contract for Monolog Processor that captures all logs during a request for use in debugging output
 *
 * @package Fisdap\Logging
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
interface ProcessesDebugLogs extends HandlerInterface
{
    /**
     * @return array
     */
    public function getMessages();
}
