<?php

return [
    'filesystems' => [
        'default' => 'local',

        'cloud' => 's3',

        'disks' => [

            'attachments-s3' => [
                'driver'     => 's3',
                'key'        => env('AWS_KEY'),
                'secret'     => env('AWS_SECRET'),
                'region'     => 'us-east-1',
                'bucket'     => env('FISDAP_ATTACH_S3_BUCKET'),
            ],

            'attachments-temp' => [
                'driver'     => 'local',
                'root'       => public_path(env('FISDAP_ATTACH_TEMP_PUBLIC_RELATIVE_PATH')),
            ],

            'attachments-local' => [
                'driver'     => 'local',
                'root'       => public_path('attachments'),
            ],

            'local' => [
                'driver' => 'local',
                'root'   => storage_path('app'),
            ],

        ],
    ]
];
