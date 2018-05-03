<?php namespace Fisdap\Logging;

use Log;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\ServiceProvider;
use Request;
use Monolog\Processor\IntrospectionProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\WebProcessor;


/**
 * Enables additional configuration for Monolog logger (as provided by the Laravel Framework)
 *
 * @package Fisdap\Logging
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class LoggingServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $monolog = Log::getMonolog();

        $monolog->pushProcessor(new IntrospectionProcessor);
        $monolog->pushProcessor(new UserInfoProcessor($this->app->make(Guard::class)));

        // enable extra logging processors and handler in development environments
        if (preg_match('/dev|local/', $this->app->environment()) and Request::has('debug')) {
            $monolog->pushProcessor(new MemoryUsageProcessor);
            $monolog->pushProcessor(new MemoryPeakUsageProcessor);
            $monolog->pushProcessor(new WebProcessor);

            $logCollector = new DebugMonologProcessor;
            $monolog->pushHandler($logCollector);

            $this->app->instance(ProcessesDebugLogs::class, $logCollector);
        }
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
    }
}