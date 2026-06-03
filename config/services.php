<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'crosschex' => [
        'accounts' => [
            'main' => [
                'name' => env('CROSSCHEX_MAIN_NAME', 'Main CrossChex'),
                'url' => env('CROSSCHEX_MAIN_URL'),
                'key' => env('CROSSCHEX_MAIN_KEY'),
                'secret' => env('CROSSCHEX_MAIN_SECRET'),
            ],

            'second' => [
                'name' => env('CROSSCHEX_SECOND_NAME', 'Second CrossChex'),
                'url' => env('CROSSCHEX_SECOND_URL'),
                'key' => env('CROSSCHEX_SECOND_KEY'),
                'secret' => env('CROSSCHEX_SECOND_SECRET'),
            ],
        ],
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

];
