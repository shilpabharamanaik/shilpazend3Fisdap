<?php

return [
    'great-plains-api' => [
        'baseUri' => env('GREAT_PLAINS_API_BASE_URI', 'https://stg.fisdap-gp.ascendlearning.com/'),
        'apiKey' => env('GREAT_PLAINS_API_API_KEY'),
        'appId' => env('GREAT_PLAINS_API_APP_ID'),
        'timeout' => env('GREAT_PLAINS_API_TIMEOUT', 20),
        'debug' => env('GREAT_PLAINS_API_DEBUG', true)
    ]
];