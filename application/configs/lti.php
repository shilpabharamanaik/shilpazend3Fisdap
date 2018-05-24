<?php

use Fisdap\Members\Lti\LaunchHandler;

return [
    'lti' => [
        'handlers' => [
             'launch' => LaunchHandler::class
         ]
     ]
];
