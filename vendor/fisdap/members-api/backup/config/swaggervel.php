<?php

return [
    /*
      |--------------------------------------------------------------------------
      | Absolute path to location where parsed swagger annotations will be stored
      |--------------------------------------------------------------------------
    */
    'doc-dir' => storage_path() . '/docs/swagger',

    /*
      |--------------------------------------------------------------------------
      | Relative path to access parsed swagger annotations.
      |--------------------------------------------------------------------------
    */
    'doc-route' => 'swagger',

    /*
      |--------------------------------------------------------------------------
      | Relative path to access swagger ui.
      |--------------------------------------------------------------------------
    */
    'api-docs-route' => 'docs',

    /*
      |--------------------------------------------------------------------------
      | Absolute path to directory containing the swagger annotations are stored.
      |--------------------------------------------------------------------------
    */
    "app-dir" => "app/Api",

    /*
      |--------------------------------------------------------------------------
      | Absolute path to directories that you would like to exclude from swagger generation
      |--------------------------------------------------------------------------
    */
    "excludes" => array(
        storage_path(),
        base_path()."/tests",
        base_path()."/resources/views",
        base_path()."/config",
        base_path()."/vendor"
    ),

    /*
      |--------------------------------------------------------------------------
      | Turn this off to remove swagger generation on production
      |--------------------------------------------------------------------------
    */
    "generateAlways" => true,

    "api-key" => "access_token",

    /*
      |--------------------------------------------------------------------------
      | Edit to set the api's version number
      |--------------------------------------------------------------------------
    */
    "default-api-version" => "2.4.0",

    /*
      |--------------------------------------------------------------------------
      | Edit to set the swagger version number
      |--------------------------------------------------------------------------
    */
    "default-swagger-version" => "2.0",

    /*
      |--------------------------------------------------------------------------
      | Edit to set the api's base path
      |--------------------------------------------------------------------------
    */
    "default-base-path" => "",

    /*
      |--------------------------------------------------------------------------
      | Edit to trust the proxy's ip address - needed for AWS Load Balancer
      |--------------------------------------------------------------------------
    */
    "behind-reverse-proxy" => true,
    /*
      |--------------------------------------------------------------------------
      | Uncomment to add response headers when swagger is generated
      |--------------------------------------------------------------------------
    */
    /*"viewHeaders" => array(
        'Content-Type' => 'text/plain'
    ),*/

    /*
      |--------------------------------------------------------------------------
      | Uncomment to add request headers when swagger performs requests
      |--------------------------------------------------------------------------
    */
    /*"requestHeaders" => array(
        'TestMe' => 'testValue'
    ),*/
];
