<?php namespace Fisdap\Members\ServiceProviders;

use Illuminate\Redis\Database;
use Illuminate\Support\ServiceProvider;


/**
 * Class RedisServiceProvider
 *
 * @package Fisdap\Members
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class RedisServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        // Setup Redis
        $this->app->singleton(Database::class, function () {
            return new Database($this->app['config']->get('database.redis'));
        });
        $this->app->alias(Database::class, 'redis');
    }
}