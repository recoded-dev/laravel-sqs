<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Client configuration
    |--------------------------------------------------------------------------
    |
    | This will be passed to the SQS client directly.
    |
    */

    'client' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue configuration
    |--------------------------------------------------------------------------
    |
    | Used to build the entire queue URL.
    |
    */
    'queue' => [
        'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
        'name' => env('SQS_QUEUE', 'default'),
        'suffix' => env('SQS_SUFFIX'),
    ],

];
