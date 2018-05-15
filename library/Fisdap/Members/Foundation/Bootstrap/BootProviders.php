<?php namespace Fisdap\Members\Foundation\Bootstrap;

use Illuminate\Contracts\Foundation\Application;

/**
 * Class BootProviders
 *
 * @package Fisdap\Members\Foundation\Bootstrap
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class BootProviders
{
    /**
     * Bootstrap the given application.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @return void
     */
    public function bootstrap(Application $app)
    {
        $app->boot();
    }
}
