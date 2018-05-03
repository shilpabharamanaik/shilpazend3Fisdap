<?php namespace Fisdap\Logging;

use Monolog\Handler\AbstractProcessingHandler;


/**
 * Monolog Processor that captures all logs during a request for use in debugging output
 *
 * @package Fisdap\Logging
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class DebugMonologProcessor extends AbstractProcessingHandler implements ProcessesDebugLogs
{
    /**
     * @var array
     */
    protected $records = [];

    /**
     * @param array $record
     */
    protected function write(array $record)
    {
        $record['datetime'] = $record['datetime']->format('Y-m-d H:i:s');
        unset($record['formatted']);

        $this->records[] = $record;
    }


    public function getMessages()
    {
        return $this->records;
    }
}