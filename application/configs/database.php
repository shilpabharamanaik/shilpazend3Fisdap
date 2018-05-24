<?php

return [
    'database' => [
        'redis' => [

            'cluster' => false,

            'default' => [
                'host'     => env('MEMBERS_REDIS_HOST', '127.0.0.1'),
                'port'     => 6379,
                'database' => 0,
            ],

        ],
    ]
];