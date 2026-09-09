<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'tolasaint' => [
        'api_key' => env('TOLASAINT_API_KEY'),
        'api_url' => env('TOLASAINT_API_URL', 'https://api.tolasaint.com/v1/payment'),
        'status_url' => env('TOLASAINT_STATUS_URL', 'https://api.tolasaint.com/v1/payment/status'),
        'provider' => env('TOLASAINT_PROVIDER', 'aba'),
    ],

    'stripe' => [
        'key' => env('STRIPE_TEST_PK') ?? env('STRIPE_KEY') ?? env('STRIPE_PUBLISHABLE_KEY'),
        'secret' => env('STRIPE_TEST_SK') ?? env('STRIPE_SECRET') ?? env('STRIPE_SECRET_KEY'),
    ],

];
