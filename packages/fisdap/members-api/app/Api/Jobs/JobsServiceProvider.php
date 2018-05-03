<?php namespace Fisdap\Api\Jobs;

use Fisdap\Api\Support\JsonRequestMapper;
use Fisdap\Logging\Commands\CommandLoggingPipe;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;
use Illuminate\Support\ServiceProvider;
use Fisdap\Api\Jobs\RequestHydrated as RequestHydrated;


/**
 * Provides additional functionality for Job classes
 *
 * @package Fisdap\Api\Jobs
 * @author  Ben Getsug <bgetsug@fisdap.net
 * @codeCoverageIgnore
 */
final class JobsServiceProvider extends ServiceProvider
{
    /**
     * @param BusDispatcher $busDispatcher
     */
    public function boot(BusDispatcher $busDispatcher)
    {
        $busDispatcher->pipeThrough([
            CommandLoggingPipe::class,
            JobValidationPipe::class
        ]);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        // Use JsonRequestMapper to map Request JSON data to a resolving Job implementing the RequestHydrated interface
        $this->app->resolving(Fisdap\Api\Jobs\RequestHydrated::class, function ($requestHydrated, $app) {

            /** @var JsonRequestMapper $jsonRequestMapper */
            $jsonRequestMapper = $app->make(JsonRequestMapper::class);

            $jsonRequestMapper->map($requestHydrated);
        });
    }
}