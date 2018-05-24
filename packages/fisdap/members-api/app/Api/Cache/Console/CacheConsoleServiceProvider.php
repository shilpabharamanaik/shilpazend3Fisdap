<?php namespace Fisdap\Api\Cache\Console;

use Illuminate\Cache\Repository;
use Illuminate\Support\ServiceProvider;


/**
 * Class CacheConsoleServiceProvider
 *
 * @package Fisdap\Api\Cache\Console
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
class CacheConsoleServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    protected $defer = false;


    /**
     * @inheritdoc
     */
    public function boot()
    {
        $this->commands('command.cache.forget');
        $this->commands('command.cache.flush');
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->bind(
            'command.cache.forget',
            function () {
                return new CacheForgetCommand($this->app->make(Repository::class));
            }
        );

        $this->app->bind(
            'command.cache.flush',
            function () {
                return new CacheFlushCommand($this->app->make(Repository::class));
            }
        );
    }


    /**
     * @inheritdoc
     */
    public function provides()
    {
        return [
            'command.cache.forget',
            'command.cache.flush'
        ];
    }
}