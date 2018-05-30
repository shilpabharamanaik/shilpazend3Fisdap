<?php namespace Fisdap\Members\ServiceProviders;

use Fisdap\AppHealthChecks\HealthChecks\CouchbaseHealthCheck;
use Fisdap\AppHealthChecks\HealthChecks\DatabaseHealthCheck;
use Illuminate\Support\ServiceProvider;
use Zend_Db_Adapter_Pdo_Mysql;

/**
 * Class MembersHealthChecksServiceProvider
 *
 * @package Fisdap\Members
 * @author  Ben Getsug <bgetsug@fisdap.net>
 */
class MembersHealthChecksServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // couchbase health check
        $this->app->bind(
            'Fisdap\AppHealthChecks\HealthChecks\CouchbaseHealthCheck',
            function () {
                $couchbaseConfig = $this->app->make('config')->get('health-checks')['couchbase'];

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

                /** @var Zend_Db_Adapter_Pdo_Mysql $db */
                $db = \Zend_Registry::get('db');

                return new DatabaseHealthCheck(
                    $db->getConnection(),
                    $this->app->make('Psr\Log\LoggerInterface')
                );
            }
        );
    }
}
