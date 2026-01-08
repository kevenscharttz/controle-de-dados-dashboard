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

    // Optional internal proxy to embed HTTP dashboards in HTTPS panel safely
    'metabase' => [
        // Enable proxy behavior (false by default)
        'proxy_enabled' => env('METABASE_PROXY_ENABLED', false),
        // Upstream base URL (scheme+host[:port]), e.g. http://metabase.internal:3000
        'proxy_base' => env('METABASE_PROXY_BASE'),
        // Comma-separated allowed hosts for safety, e.g. "metabase.internal,metabase.example.com"
        'allowed_hosts' => array_values(array_filter(array_map('trim', explode(',', env('METABASE_ALLOWED_HOSTS', ''))))),
    ],

];
