<?php namespace Fisdap\Api\Products\SerialNumbers;

use Fisdap\Api\Products\SerialNumbers\Http\SerialNumbersController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;


/**
 * Registers routes for serial numbers
 *
 * @package Fisdap\Api\Products\SerialNumbers
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class SerialNumbersRoutesServiceProvider extends ServiceProvider
{
    /**
     * Define route model bindings, pattern filters, etc.
     *
     * @param Router $router
     */
    public function boot(Router $router)
    {
        parent::boot($router);
    }


    /**
     * Define the routes for the application.
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->post('/products/serial-numbers', [
            'middleware' => [
                'userMustBeStaff'
            ],
            'as' => 'products.serial-numbers.store',
            'uses'       => SerialNumbersController::class . '@store'
        ]);
    }
}