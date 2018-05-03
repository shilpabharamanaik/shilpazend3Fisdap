<?php

return [
    'mime_types_blacklist' => [
        'application/zip',
        'application/x-msdownload', // .exe
        'application/x-rar-compressed',
        'application/x-bittorrent',
        'application/x-sh',
        'application/x-csh',
        'text/x-c',
        'text/css',
        'application/x-debian-package',
        'application/x-doom',
        'application/x-gtar',
        'application/java-archive',
        'application/java-vm',
        'application/x-java-jnlp-file',
        'application/java-serialized-object',
        'text/x-java-source,java',
        'application/javascript',
        'application/json',
        'application/mac-binhex40',
        'application/vnd.macports.portpkg',
        'application/vnd.ms-cab-compressed',
        'audio/midi',
        'application/vnd.nokia.n-gage.data',
        'application/vnd.nokia.n-gage.symbian.install',
        'application/vnd.palm',
        'text/x-pascal',
        'application/x-chat',
        'application/x-font-type1',
        'application/x-font-linux-psf',
        'application/x-font-snf',
        'application/sdp',
        'application/x-shar',
        'application/x-stuffit',
        'application/x-stuffitx',
        'application/vnd.trueapp',
        'application/x-font-ttf',
        'application/x-font-woff',
        'application/x-dosexec'
    ],

    'log_file' => env('MRAPI_ATTACH_LOG_FILE'),

    // the path relative to the public/web directory for saving attachments temporarily
    'temp_public_relative_path' => env('MRAPI_ATTACH_TEMP_PUBLIC_RELATIVE_PATH'),

    // wait seconds after attachment files have been copied to permanent storage, before deleting temp files
    'temp_file_delete_delay' => 300,

    'filesystem_disks' => [
        'default' => env('MRAPI_ATTACH_FS_DISKS_DEFAULT', 'attachments-s3'),
        'temp' => 'attachments-temp'
    ],

    'cdn' => [

        'default' => env('MRAPI_ATTACH_CDN_DEFAULT', 'cloudfront'),

        'local' => [
            'url_root' => env('MRAPI_URL') . '/attachments',
        ],

        'cloudfront' => [
            'url_root' => env('MRAPI_ATTACH_CLOUDFRONT_URL_ROOT'),

            // number of seconds a signed URL will be valid
            'signed_url_ttl' => 300,

            'private_key_path' => env('MRAPI_ATTACH_CLOUDFRONT_PRIVATE_KEY_PATH'),

            'key_pair_id' => env('MRAPI_ATTACH_CLOUDFRONT_KEY_PAIR_ID')
        ]
    ]
];