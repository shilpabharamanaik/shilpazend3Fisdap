<?php namespace Fisdap\AppHealthChecks;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Fisdap\AppHealthChecks\HealthChecks\CouchbaseHealthCheck;
use Fisdap\AppHealthChecks\HealthChecks\DatabaseHealthCheck;
use Illuminate\Support\ServiceProvider;


/**
 * Class AppHealthChecksServiceProvider
 *
 * @package Fisdap\AppHealthChecks
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class AppHealthChecksServiceProvider extends ServiceProvider
{
    /**
     * @inheritdoc
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/health-checks.php' => config_path('health-checks.php')
        ]);


        $this->loadViewsFrom(__DIR__.'/../views', 'health-checks');

        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../routes.php';
        }
    }


    /**
     * @inheritdoc
     */
    public function register()
    {
        // couchbase health check
        $this->app->bind(
            'Fisdap\AppHealthChecks\HealthChecks\CouchbaseHealthCheck',
            function () {
                $couchbaseConfig = Config::get('health-checks.couchbase');

                $couchbase = new \Couchbase(
                    $couchbaseConfig['hosts'],
                    $couchbaseConfig['user'],
                    $couchbaseConfig['password'],
                    $couchbaseConfig['bucket'],
                    $couchbaseConfig['persistent']
                );

                return new CouchbaseHealthCheck($couchbase, $this->app->make('Psr\Log\LoggerInterface'));
            }
        );

        // database health check
        $this->app->bind(
            'Fisdap\AppHealthChecks\HealthChecks\DatabaseHealthCheck',
            function () {
                return new DatabaseHealthCheck(
                    DB::connection('mysql')->getPdo(),
                    $this->app->make('Psr\Log\LoggerInterface')
                );
            }
        );
    }
}
