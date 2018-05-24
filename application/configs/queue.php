<?php

return [

    'queue' => [

        //'default' => env('APP_ENV') == 'testing' ? 'sync' : env('MEMBERS_QUEUE_DRIVER', 'sync'),

        /*
        |--------------------------------------------------------------------------
        | Queue Connections
        |--------------------------------------------------------------------------
        |
        | Here you may configure the connection information for each server that
        | is used by your application. A default configuration has been added
        | for each back-end shipped with Laravel. You are free to add more.
        |
        */

        'connections' => [

            'sync' => [
                'driver' => 'sync',
            ],

            'database' => [
                'driver' => 'database',
                'table' => 'jobs',
                'queue' => 'default',
                'expire' => 60,
            ],

            'beanstalkd' => [
                'driver' => 'beanstalkd',
                'host'   => env('MEMBERS_BEANSTALKD_HOST', 'localhost'),
                'queue'  => env('MEMBERS_BEANSTALKD_QUEUE', 'default'),
                'ttr'    => 1800,
            ],

            'sqs' => array(
                'driver' => 'sqs',
                'key'    => env('MEMBERS_AWS_KEY'),
                'secret' => env('MEMBERS_AWS_SECRET'),
                'queue'  => env('MEMBERS_AWS_QUEUE_URL'),
                'region' => 'us-east-1',
            ),

            'iron' => [
                'driver'  => 'iron',
                'host'    => 'mq-aws-us-east-1.iron.io',
                'token'   => 'your-token',
                'project' => 'your-project-id',
                'queue'   => 'your-queue-name',
                'encrypt' => true,
            ],

            'redis' => [
                'driver' => 'redis',
                'connection' => 'default',
                'queue'  => 'default',
                'expire' => 60,
            ],

        ],

        /*
        |--------------------------------------------------------------------------
        | Failed Queue Jobs
        |--------------------------------------------------------------------------
        |
        | These options configure the behavior of failed queue job logging so you
        | can control which database and table are used to store the jobs that
        | have failed. You may change them to any database / table you wish.
        |
        */

//        'failed' => [
//            'database' => 'mysql', 'table' => 'FailedJobs',
//        ],
    ]
];
