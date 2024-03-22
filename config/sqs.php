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
    | Common queue configuration
    |--------------------------------------------------------------------------
    |
    | This configuration is used as a base configuration for all
    | queues configured.
    |
    */
    'common' => [
        'prefix' => env('SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id'),
        'suffix' => env('SQS_SUFFIX'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default queues.
    |--------------------------------------------------------------------------
    |
    | When not passing queues to the work command these queues
    | will be worked by default.
    |
    */
    'default_queues' => ['default'],

    /*
    |--------------------------------------------------------------------------
    | Queue configuration
    |--------------------------------------------------------------------------
    |
    | Configure each queue you want to work and their properties as
    | an array keyed by name.
    |
    */
    'queues' => [
        'default' => [
            'name' => env('SQS_QUEUE', 'default'),
        ],
    ],

];
