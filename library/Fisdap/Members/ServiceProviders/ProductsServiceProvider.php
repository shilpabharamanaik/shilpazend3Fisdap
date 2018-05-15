<?php namespace Fisdap\Members\ServiceProviders;

use Fisdap\Api\Products\Finder\FindsProducts;
use Fisdap\Api\Products\Finder\ProductsFinder;
use Illuminate\Support\ServiceProvider;

/**
 * Class ProductsServiceProvider
 *
 * @package Fisdap\Members\ServiceProviders
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class ProductsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(FindsProducts::class, ProductsFinder::class);
    }
}
