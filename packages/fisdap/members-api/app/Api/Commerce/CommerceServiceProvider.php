<?php namespace Fisdap\Api\Commerce;

use Fisdap\Api\Commerce\OrderPermissions\Http\OrderPermissionsController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;


/**
 * Enables commerce-related routes
 *
 * @package Fisdap\Api\Commerce
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class CommerceServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
		$router = app('router'); // Router Instance
        parent::boot();
    }


    /**
     * Put commerce-related routes here
     *
     * ALL NON-RESOURCE CONTROLLERS SHOULD BE NAMED (in order to work properly with New Relic)
     * see http://laravel.com/docs/routing#named-routes
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->get('commerce/orders/permissions', [
            'as'   => 'commerce.orders.permissions.index',
            'uses' => OrderPermissionsController::class . '@index'
        ]);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
    }
}