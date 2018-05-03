<?php

return [
    'appName' => getenv('HEALTH_CHECK_APP_NAME') ? getenv('HEALTH_CHECK_APP_NAME') : 'Fisdap',

    'enabledChecks' => [
        'Fisdap\AppHealthChecks\HealthChecks\CouchbaseHealthCheck',
        'Fisdap\AppHealthChecks\HealthChecks\DatabaseHealthCheck',
        'Fisdap\AppHealthChecks\HealthChecks\RedisHealthCheck',
    ],

    'couchbase' => [
        'hosts'      => getenv('HEALTH_CHECK_COUCHBASE_HOSTS')      ? explode(',', getenv('HEALTH_CHECK_COUCHBASE_HOSTS')) : ['127.0.0.1'],
        'user'       => getenv('HEALTH_CHECK_COUCHBASE_USER')       ? getenv('HEALTH_CHECK_COUCHBASE_USER') : '',
        'password'   => getenv('HEALTH_CHECK_COUCHBASE_PASSWORD')   ? getenv('HEALTH_CHECK_COUCHBASE_PASSWORD') : '',
        'bucket'     => getenv('HEALTH_CHECK_COUCHBASE_BUCKET')     ? getenv('HEALTH_CHECK_COUCHBASE_BUCKET') : 'default',
        'persistent' => getenv('HEALTH_CHECK_COUCHBASE_PERSISTENT') ? getenv('HEALTH_CHECK_COUCHBASE_PERSISTENT') : true,
    ],
];