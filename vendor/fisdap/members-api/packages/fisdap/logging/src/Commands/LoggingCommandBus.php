<?php namespace Fisdap\Logging\Commands;

use Laracasts\Commander\CommandBus;


/**
 * Decorating command bus that logs execution of commands
 *
 * @package Fisdap\Logging\Commands
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class LoggingCommandBus implements CommandBus
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
     * Execute a command
     *
     * @param $command
     *
     * @return mixed
     */
    public function execute($command)
    {
        $reflectionClass = new \ReflectionClass($command);
        $this->logger->info(
            $reflectionClass->getShortName() . ' executed',
            get_object_vars($command)
        );
    }
}