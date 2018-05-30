<?php namespace Fisdap\Api\Cache;

use Cache;
use Couchbase;
use Illuminate\Cache\Repository;
use Illuminate\Support\ServiceProvider;

/**
 * Extends Laravel cache facility, adding Couchbase driver
 *
 * @package Fisdap\Api\Cache
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 * @todo extract to package
 */
class CouchbaseCacheServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        Cache::extend('couchbase', function ($app) {
            $couchbaseConfig = $app['config']['cache.stores.couchbase'];

            return new Repository(new CouchbaseStore(
                new \Couchbase(
                    $couchbaseConfig['hosts'],
                    $couchbaseConfig['user'],
                    $couchbaseConfig['password'],
                    $couchbaseConfig['bucket'],
                    $couchbaseConfig['persistent']
                ),
                $app['config']['cache.prefix']
            ));
        });
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
    }
}
