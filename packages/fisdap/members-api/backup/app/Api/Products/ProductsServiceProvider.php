<?php namespace Fisdap\Api\Products;

use Fisdap\Api\Products\Finder\CachingProductsFinder;
use Fisdap\Api\Products\Finder\FindsProducts;
use Fisdap\Api\Products\Finder\ProductsFinder;
use Illuminate\Auth\AuthManager;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Config\Repository as Config;
use Illuminate\Support\ServiceProvider;

/**
 * Provides product-related services
 *
 * @package Fisdap\Api\Products
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @codeCoverageIgnore
 */
final class ProductsServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    protected $defer = true;


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(FindsProducts::class, function () {
            return new CachingProductsFinder(
                $this->app->make(AuthManager::class),
                $this->app->make(ProductsFinder::class),
                $this->app->make(Cache::class),
                $this->app->make(Config::class)
            );
        });
    }


    /**
     * @inheritdoc
     */
    public function provides()
    {
        return [
            FindsProducts::class
        ];
    }
}
