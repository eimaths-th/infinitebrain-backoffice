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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'google' => [
        'client_id' => '315026935235-1fk08k7dale2t93u6aecsunp6ulrr877.apps.googleusercontent.com', //USE FROM Google DEVELOPER ACCOUNT
        'client_secret' => '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', //USE FROM Google DEVELOPER ACCOUNT
        'redirect' => 'https://school.infinitebrain.co.th/login/google/callback'
    ],

    'line_messaging' => [
        'channel_access_token' => env('LINE_CHANNEL_ACCESS_TOKEN'),
        'group_id' => env('LINE_GROUP_ID'),
        'reviewer_group_id' => env('LINE_REVIEWER_GROUP_ID'),
        'approver_group_id' => env('LINE_APPROVER_GROUP_ID'),
        'manager_group_id' => env('LINE_MANAGER_GROUP_ID'),
        'system_test_group_id' => env('LINE_SYSTEM_TEST_GROUP_ID'),
    ],

    'libreoffice' => [
        'path' => env('LIBREOFFICE_PATH', 'soffice'),
    ],
];
