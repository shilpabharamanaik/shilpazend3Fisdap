<?php namespace Fisdap\BuildMetadata;

use Illuminate\Support\ServiceProvider;


/**
 * Provides support for version validation based on build metadata
 *
 * @package Fisdap\BuildMetadata
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class BuildMetadataServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/build-metadata.php' => config_path('build-metadata.php')
        ]);

        $this->loadViewsFrom(__DIR__.'/../views', 'build-metadata');

        // set appmon/build route
        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../routes.php';
        }

        // register build:metadata:make command
        $this->commands(BuildMetadataMakeCommand::class);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(BuildMetadataMakeCommand::class, function() {
            return new BuildMetadataMakeCommand();
        });
    }
}
