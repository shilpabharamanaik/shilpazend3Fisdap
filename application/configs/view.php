<?php

return [

    'view' => [
        'paths' => [
            realpath(base_path('vendor/fisdap/members-api/resources/views')),
        ],

        'compiled' => realpath(storage_path('views'))
    ]
];
