<?php

return [

    'mail' => [
        'driver' => env('MAIL_DRIVER', 'sendmail'),

        'host' => env('MAIL_HOST', 'smtp.mailgun.org'),

        'port' => env('MAIL_PORT', 587),

        'from' => [
            'address' => 'fisdap-robot@fisdap.net',
            'name' => 'Fisdap Robot'
        ],

        'encryption' => env('MAIL_ENCRYPTION', 'tls'),

        'username' => env('MAIL_USERNAME'),

        'password' => env('MAIL_PASSWORD'),

        'sendmail' => '/usr/sbin/sendmail -bs',

        'pretend' => false,
    ]
];
