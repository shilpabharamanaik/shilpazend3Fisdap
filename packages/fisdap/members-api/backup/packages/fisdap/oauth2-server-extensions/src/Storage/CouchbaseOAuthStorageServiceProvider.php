<?php namespace Fisdap\OAuth\Storage;

use Config;
use Fisdap\OAuth\Storage\Couchbase as CouchbaseStorage;
use Illuminate\Support\ServiceProvider;
use Log;

/**
 * Class CouchbaseOAuthStorageServiceProvider
 *
 * @package Fisdap\OAuth\Storage
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class CouchbaseOAuthStorageServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/oauth2-server-extensions.php' => config_path('oauth2-server-extensions.php')
        ]);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/oauth2-server-extensions.php',
            'oauth2-server-extensions'
        );

        // Set up the oauth2 storage object here.
        $this->app->singleton(CouchbaseStorage::class, function () {
            $couchbaseConfig = Config::get('oauth2-server-extensions.couchbase');

            $storage = new Couchbase($couchbaseConfig, [], Log::getMonolog());

            return $storage;
        });

        $this->app->alias(CouchbaseStorage::class, 'couchbase_oauth_storage');
    }
}
