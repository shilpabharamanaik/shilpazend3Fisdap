<?php namespace Fisdap\ErrorHandling;

use Bugsnag_Client;
use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Intouch\Newrelic\Newrelic;
use Psr\Log\LoggerInterface;


/**
 * Registers custom exception handling and provides routes for testing errors
 *
 * @package Fisdap\ErrorHandling
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
final class ErrorHandlerServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/error-handling.php' => config_path('error-handling.php')
        ]);

        // Test Error endpoint
        /** @var Router $router */
        $router = $this->app['router'];
        $router->get('test/error/{httpCode}', TestErrorController::class . '@error');
        $router->get('test/exception', TestErrorController::class . '@exception');
        $router->get('test/fatal', TestErrorController::class . '@fatal');

        $this->configureNewRelic();
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        $this->app->bind(ErrorHandler::class, function() {
            return new ErrorHandler(
                $this->app['request'],
                $this->app['config'],
                $this->app->make(Guard::class),
                $this->app->make(LoggerInterface::class),
                $this->app['bugsnag'],
                $this->app['newrelic']
            );
        });
    }


    private function configureNewRelic()
    {
        /** @var Config $config */
        $config = $this->app['config'];

        /** @var Newrelic $newrelic */
        $newrelic = $this->app['newrelic'];
        $newrelic->setAppName($config->get('error-handling.appName') . '-' . $this->app->environment());

        if ($config->get('error-handling.newRelicDisableAutoRum') === true) {
            $newrelic->disableAutoRUM();
        }
    }
}