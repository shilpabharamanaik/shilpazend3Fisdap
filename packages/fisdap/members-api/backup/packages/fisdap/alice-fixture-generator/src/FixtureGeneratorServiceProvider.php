<?php namespace Fisdap\AliceFixtureGenerator;

use Doctrine\ORM\EntityManager;
use Illuminate\Support\ServiceProvider;


/**
 * Provides Alice fixture generation console command
 *
 * @package Fisdap\AliceFixtureGenerator
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class FixtureGeneratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands(GenerateCommand::class);
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->singleton(GenerateCommand::class, function() {
            return new GenerateCommand($this->app->make(EntityManager::class));
        });
    }
}