<?php namespace Fisdap\Logging\Events;

use Config;
use Event;
use Illuminate\Support\ServiceProvider;
use Log;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Provides event logger configuration and registration
 *
 * @package Fisdap\Logging\Events
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class EventLoggingServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        // configure logger
        /** @var EventLogger $eventLogger */
        $eventLogger = $this->app->make(EventLogger::class);

        /** @var Logger $monologLogger */
        $monologLogger = $eventLogger->getLogger();

        if (Config::get('events.use_laravel_log_processors', true) === true) {
            $processors = Log::getMonolog()->getProcessors();

            foreach ($processors as $processor) {
                $monologLogger->pushProcessor($processor);
            }
        }

        $streamHandler = new StreamHandler(
            Config::get('events.log_file', storage_path('logs/events.log')),
            Config::get(
                'events.log_level',
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
        $monologLogger = new Logger("$env-events");
        $eventLogger = new EventLogger($monologLogger);

        $this->app->instance(EventLogger::class, $eventLogger);


        if (Config::get('events.event_fires.enabled', false) === true) {
            Event::listen('*', function () {
                /** @var EventLogger $eventLogger */
                $eventLogger = $this->app->make(EventLogger::class);

                if (Config::get('events.event_fires.log_as_debug') === true) {
                    $eventLogger->debug('Firing - ' . Event::firing());
                } else {
                    $eventLogger->info('Firing - ' . Event::firing());
                }
            });
        }
    }
}
