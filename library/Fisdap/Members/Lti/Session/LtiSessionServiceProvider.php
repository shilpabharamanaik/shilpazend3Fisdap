<?php namespace Fisdap\Members\Lti\Session;

use Illuminate\Support\ServiceProvider;


/**
 * Class LtiSessionServiceProvider
 *
 * @package Fisdap\Members\Lti\Session
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class LtiSessionServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(LtiSession::class, LtiSession::class);
    }
}