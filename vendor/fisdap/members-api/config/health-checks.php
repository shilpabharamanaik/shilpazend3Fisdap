<?php

use Fisdap\AppHealthChecks\HealthChecks\CouchbaseHealthCheck;
use Fisdap\AppHealthChecks\HealthChecks\DatabaseHealthCheck;
use Fisdap\AppHealthChecks\HealthChecks\RedisHealthCheck;


return [
    'appName'       => 'Fisdap Members API',
    'appShortName'  => 'memapi',

    'enabledChecks' => [
        CouchbaseHealthCheck::class,
        DatabaseHealthCheck::class,
        RedisHealthCheck::class,
    ],

    'couchbase'     => [
        'hosts'      => getenv('HEALTH_CHECK_COUCHBASE_HOSTS') ? explode(',',
            getenv('HEALTH_CHECK_COUCHBASE_HOSTS')) : ['127.0.0.1'],
        'user'       => env('HEALTH_CHECK_COUCHBASE_USER', ''),
        'password'   => env('HEALTH_CHECK_COUCHBASE_PASSWORD', ''),
        'bucket'     => env('HEALTH_CHECK_COUCHBASE_BUCKET', 'default'),
        'persistent' => env('HEALTH_CHECK_COUCHBASE_PERSISTENT', true),
    ],
];