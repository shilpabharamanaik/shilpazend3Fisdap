<?php

return [

    'mime_types_blacklist' => [],

    'log_file' => getenv('FISDAP_ATTACH_LOG_FILE'),

    // the path relative to the public/web directory for saving attachments temporarily
    'temp_public_relative_path' => getenv('FISDAP_ATTACH_TEMP_PUBLIC_RELATIVE_PATH'),

    // wait seconds after attachment files have been copied to permanent storage, before deleting temp files
    'temp_file_delete_delay' => 300,

    'filesystem_disks' => [
        'default' => 'attachments-s3',
        'temp' => 'attachments-temp'
    ],

    'cdn' => [

        'default' => 'cloudfront',

        'cloudfront' => [
            'url_root' => getenv('FISDAP_ATTACH_CLOUDFRONT_URL_ROOT'),

            // number of seconds a signed URL will be valid
            'signed_url_ttl' => 300,

            'private_key_path' => getenv('FISDAP_ATTACH_CLOUDFRONT_PRIVATE_KEY_PATH'),

            'key_pair_id' => getenv('FISDAP_ATTACH_CLOUDFRONT_KEY_PAIR_ID')
        ]
    ]
];
