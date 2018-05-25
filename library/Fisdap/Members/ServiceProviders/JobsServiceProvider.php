<?php namespace Fisdap\Members\ServiceProviders;

use Fisdap\Api\Jobs\JobValidationPipe;
use Fisdap\Logging\Commands\CommandLoggingPipe;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Bus\Dispatcher as BusDispatcher;


/**
 * Class JobsServiceProvider
 *
 * @package Fisdap\Members\ServiceProviders
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class JobsServiceProvider extends ServiceProvider
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
    }
}