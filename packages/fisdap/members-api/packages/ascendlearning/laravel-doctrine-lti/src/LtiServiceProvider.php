<?php namespace AscendLearning\Lti;

use AscendLearning\Lti\Console\Consumers\CreateConsumerCommand;
use AscendLearning\Lti\Console\Consumers\DeleteConsumersCommand;
use AscendLearning\Lti\Console\Consumers\ListConsumersCommand;
use AscendLearning\Lti\Console\ToolProviders\CreateToolProviderCommand;
use AscendLearning\Lti\Console\ToolProviders\DeleteToolProvidersCommand;
use AscendLearning\Lti\Console\ToolProviders\ListToolProvidersCommand;
use AscendLearning\Lti\Storage\DoctrineStorage;
use Franzl\Lti\ToolProvider;
use Illuminate\Support\ServiceProvider;

/**
 * Class LtiServiceProvider
 *
 * @package AscendLearning\Lti
 * @author  Ben Getsug <bgetsug@fisdap.net>
 * @author  Nick Karnick <nkarnick@fisdap.net>
 */
class LtiServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Artisan commands
        $this->commands([
            'command.lti.consumers.create',
            'command.lti.consumers.delete',
            'command.lti.consumers.list',

            'command.lti.tool-providers.create',
            'command.lti.tool-providers.delete',
            'command.lti.tool-providers.list',
        ]);

        $this->publishes([
            __DIR__.'/lti.php' => config_path('lti.php'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->registerCommands();

        $this->registerDataAccess();
    }


    private function registerCommands()
    {
        $this->app->bind('command.lti.consumers.create', CreateConsumerCommand::class);
        $this->app->bind('command.lti.consumers.delete', DeleteConsumersCommand::class);
        $this->app->bind('command.lti.consumers.list', ListConsumersCommand::class);

        $this->app->bind('command.lti.tool-providers.create', CreateToolProviderCommand::class);
        $this->app->bind('command.lti.tool-providers.delete', DeleteToolProvidersCommand::class);
        $this->app->bind('command.lti.tool-providers.list', ListToolProvidersCommand::class);
    }


    private function registerDataAccess()
    {
        $this->app->singleton(ToolProvider::class, function () {
            return new ToolProvider(
                $this->app->make(DoctrineStorage::class),
                $this->app->make($this->app['config']->get('lti.handlers.launch'))
            );
        });
    }
}
