<?php namespace Fisdap\Api\Auth;

use Auth;
use Fisdap\Data\User\UserRepository;
use Fisdap\OAuth\Storage\Couchbase as CouchbaseStorage;
use Illuminate\Support\ServiceProvider;
use OAuth2\Request as OAuth2Request;
use OAuth2\Server as OAuth2Server;
use OAuth2\Storage\Memory;

/**
 * Registers OAuth2 filter for all requests
 *
 * @package Fisdap\Api\Auth
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
class OAuth2ServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Auth::extend('oauth2', function ($app, $name, array $config) {
            return new OAuth2Guard(
                Auth::createUserProvider(
                $config['provider']
            ),
                $this->app->make(UserRepository::class)
            );
        });

        Auth::provider('oauth2', function () {
            return $this->app->make(OAuth2UserProvider::class);
        });
    }
    
    
    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(OAuth2Server::class, function () {
            if ($this->app->environment() == 'testing') {
                $storage = $this->app->make(Memory::class);
            } else {
                $storage = $this->app->make(CouchbaseStorage::class);
            }

            return new OAuth2Server($storage);
        });

        $this->app->singleton(OAuth2Request::class, function () {
            return OAuth2Request::createFromGlobals();
        });
    }
}
