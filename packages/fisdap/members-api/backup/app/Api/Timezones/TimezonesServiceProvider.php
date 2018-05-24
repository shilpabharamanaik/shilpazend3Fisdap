<?php namespace Fisdap\Api\Timezones;

use Fisdap\Api\Timezones\Http\TimezonesController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;


/**
 * Enables timezone-related routes
 *
 * @package Fisdap\Api\Timezones
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class TimezonesServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot(Router $router)
    {
        parent::boot($router);
    }


    /**
     * Put timezone-related routes here
     *
     * ALL NON-RESOURCE CONTROLLERS SHOULD BE NAMED (in order to work properly with New Relic)
     * see http://laravel.com/docs/routing#named-routes
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->get('timezones', [
            'as'   => 'timezones.index',
            'uses' => TimezonesController::class . '@index'
        ]);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
    }
}