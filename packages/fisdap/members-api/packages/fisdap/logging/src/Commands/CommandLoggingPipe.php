<?php namespace Fisdap\Logging\Commands;

use Closure;


/**
 * Pipe that logs command dispatching
 *
 * @see http://laravel.com/docs/5.0/bus#command-pipeline
 *
 * @package Fisdap\Logging\Commands
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CommandLoggingPipe
{
    /**
     * @var CommandLogger
     */
    private $logger;


    /**
     * @param CommandLogger $logger
     */
    public function __construct(CommandLogger $logger)
    {
        $this->logger = $logger;
    }


    /**
     * Log a command
     *
     * @param object  $command
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle($command, Closure $next)
    {
        $this->logger->info(
            'Dispatched command \'' . get_class($command) . '\'',
            get_object_vars($command)
        );

        return $next($command);
    }
}