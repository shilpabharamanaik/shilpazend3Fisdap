<?php namespace Fisdap\Logging\Commands;

use Config;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Provides command logger configuration and registration
 *
 * @package Fisdap\Logging\Commands
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class LoggingCommandBusServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        // configure logger
        /** @var CommandLogger $commandLogger */
        $commandLogger = $this->app->make(CommandLogger::class);

        /** @var Logger $monologLogger */
        $monologLogger = $commandLogger->getLogger();

        $streamHandler = new StreamHandler(
            Config::get('commands.log_file', storage_path('logs/commands.log')),
            Config::get(
                'commands.log_level',
                Config::get('app.debug') == true ? Logger::DEBUG : Logger::INFO
            )
        );
        $monologLogger->pushHandler($streamHandler);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        // register dedicated logger
        $env = $this->app->environment();
        $monologLogger = new Logger("$env-commands");
        $commandLogger = new CommandLogger($monologLogger);

        $this->app->instance(CommandLogger::class, $commandLogger);
    }
}
