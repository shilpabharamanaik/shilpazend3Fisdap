<?php namespace Fisdap\Console\RouteFilters;

use Illuminate\Support\ServiceProvider;

/**
 * Provides additional Artisan commands for route filters
 *
 * @package Fisdap\Console\RouteFilters
 */
final class RouteFiltersConsoleServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands('command.routes.filters.detail');
        $this->commands('command.routes.filters.list');
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $app = $this->app;

        $this->app->bind(
            'command.routes.filters.detail',
            function () use ($app) {
                return new RouteFiltersDetailCommand($app['router']);
            }
        );

        $this->app->bind(
            'command.routes.filters.list',
            function () use ($app) {
                return new RouteFiltersListCommand($app['router']);
            }
        );
    }


    /**
     * @inheritdoc
     */
    public function provides()
    {
        return [
            'command.routes.filters.detail',
            'command.routes.filters.list'
        ];
    }
}
