<?php namespace Fisdap\Api\Users\CurrentUser;

use Illuminate\Support\ServiceProvider;

/**
 * Class CurrentUserServiceProvider
 *
 * @package Fisdap\Api\Users\CurrentUser
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class CurrentUserServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        if (class_exists('Zend_Auth')) {
            $this->app->singleton(CurrentUser::class, ZendCurrentUser::class);
        } elseif (class_exists('Auth')) {
            $this->app->singleton(CurrentUser::class, LaravelCurrentUser::class);
        }
    }
}
