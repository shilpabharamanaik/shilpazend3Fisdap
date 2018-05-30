<?php namespace Fisdap\Api\Products;

use Fisdap\Api\Products\Http\ProductPackagesController;
use Fisdap\Api\Products\Http\ProductsController;
use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

/**
 * Registers routes for products
 *
 * @package Fisdap\Api\Products
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class ProductRoutesServiceProvider extends ServiceProvider
{
    /**
     * Define route model bindings, pattern filters, etc.
     *
     * @param Router $router
     */
    public function boot()
    {
        $router = app('router'); // Router Instance
        parent::boot();
    }


    /**
     * Define the routes for the application.
     *
     * @param Router $router
     */
    public function map(Router $router)
    {
        $router->get('/products', [
            'as' => 'products.index',
            'uses'       => ProductsController::class . '@index'
        ]);

        $router->get('/products/packages', [
            'as' => 'products.packages.index',
            'uses'       => ProductPackagesController::class . '@index'
        ]);
    }
}
