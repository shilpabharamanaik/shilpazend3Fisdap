<?php

return [
    /**
     * Set your Bugsnag API Key.
     * You can find your API Key on your Bugsnag dashboard.
     */
    'api_key' => env('BUGSNAG_API_KEY', 'a40cd50241bd2ced3d90202b98b932fd'),

    /**
     * Set which release stages should send notifications to Bugsnag
     * E.g: array('development', 'production')
     */
    'notify_release_stages' => null,
];
