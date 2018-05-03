<?php return [

    /*
    |--------------------------------------------------------------------------
    | AWS SDK Configuration
    |--------------------------------------------------------------------------
    |
    | The configuration options set in the file will be passed directly to the
    | `Aws\Sdk` object, from which all client objects are retrieved. The
    | minimum required options are declared here, but the full set of options
    | are documented at:
    | http://docs.aws.amazon.com/aws-sdk-php/v3/guide/guide/configuration.html
    |
    */
    'credentials' => [
        'key'    => env('MRAPI_AWS_KEY'),
        'secret' => env('MRAPI_AWS_SECRET'),
    ],
    'region' => env('AWS_REGION', 'us-east-1'),
    'version' => 'latest',

];
