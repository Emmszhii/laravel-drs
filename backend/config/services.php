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

    'optimization' => [
        'url' => env('OPTIMIZATION_SERVICE_URL', 'http://solver-service:8001'),
    ],

    'osrm' => [
        'url' => env('OSRM_URL', 'http://osrm:5000'),
    ],

    'disaster_feed' => [
        'usgs_url' => env('DISASTER_FEED_USGS_URL', 'https://earthquake.usgs.gov/fdsnws/event/1/query'),
        'gdacs_url' => env('DISASTER_FEED_GDACS_URL', 'https://www.gdacs.org/gdacsapi/api/events/geteventlist/SEARCH'),
        'lookback_days' => (int) env('DISASTER_FEED_LOOKBACK_DAYS', 3),
        'auto_resolve_max_days' => (int) env('DISASTER_FEED_AUTO_RESOLVE_MAX_DAYS', 3),
        // Philippines area of responsibility — keeps both feeds scoped to PH-relevant events.
        'bbox' => [
            'min_lat' => 4.5,
            'max_lat' => 21.5,
            'min_lng' => 116.0,
            'max_lng' => 127.0,
        ],
    ],

];
