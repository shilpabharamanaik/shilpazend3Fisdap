<?php

return [
    'log_file' => env('MRAPI_EVENTS_LOG_FILE', storage_path('logs/events.log')),

    'use_laravel_log_processors' => true,

    'event_fires' => [
        'enabled' => false,
        'log_as_debug' => true
    ]
];