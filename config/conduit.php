<?php

// config for AvocetShores/Conduit
use AvocetShores\Conduit\Drivers\AmazonBedrockDriver;
use AvocetShores\Conduit\Drivers\OpenAIDriver;

return [

    /*
    |--------------------------------------------------------------------------
    | Default AI Driver
    |--------------------------------------------------------------------------
    |
    | This value controls the default AI driver used by Conduit.
    | You may change this to any of the drivers defined in the 'drivers' array.
    |
    */

    'default_driver' => env('CONDUIT_DEFAULT_DRIVER', 'openai'),

    /*
    |--------------------------------------------------------------------------
    | AI Drivers
    |--------------------------------------------------------------------------
    |
    | The drivers listed here will be available for use by Conduit.
    | You may add your own drivers to this array if you wish. Each driver
    | must implement the DriverInterface.
    |
    */

    'drivers' => [
        'openai' => OpenAIDriver::class,
        'amazon_bedrock' => AmazonBedrockDriver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS Bedrock Configuration
    |--------------------------------------------------------------------------
    |
    | This value controls the configuration for the AWS Bedrock provider.
    |
    */

    'amazon_bedrock' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI Configuration
    |--------------------------------------------------------------------------
    |
    | This value controls the configuration for the OpenAI provider.
    |
    */

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'organization' => env('OPENAI_ORGANIZATION'),

        'base_url' => 'https://api.openai.com/v1/',
        'maxTokens' => env('OPENAI_MAX_TOKENS', 10000),
        'completions_endpoint' => env('OPENAI_COMPLETIONS_ENDPOINT', 'https://api.openai.com/v1/chat/completions'),
        'openai_curl_timeout' => env('OPENAI_CURL_TIMEOUT', 180),
    ],
];
