<?php

return [
    'couchbase' => [
        'hosts'      => getenv('OAUTH2_SERVER_COUCHBASE_HOSTS') ? explode(',', getenv('OAUTH2_SERVER_COUCHBASE_HOSTS')) : ['127.0.0.1'],
        'username'   => env('OAUTH2_SERVER_COUCHBASE_USERNAME', ''),
        'password'   => env('OAUTH2_SERVER_COUCHBASE_PASSWORD', ''),
        'bucket'     => env('OAUTH2_SERVER_COUCHBASE_BUCKET', 'default'),
        'persistent' => env('OAUTH2_SERVER_COUCHBASE_PERSISTENT', true),
    ],
];
