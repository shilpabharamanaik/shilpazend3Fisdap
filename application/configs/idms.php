<?php

return [
    'idms' => [
        'base_url'      => env('IDMS_BASE_URL', 'https://id.fisdap.net'),
        'client_id'     => env('IDMS_CLIENT_ID', 'fisdap-members'),
        'client_secret' => env('IDMS_CLIENT_SECRET', '')
    ]
];
