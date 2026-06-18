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

    'correct_recognition' => [
        'token' => env('CORRECT_RECOGNITION_TOKEN'),
    ],

    'nopaper' => [
        'base_url' => env('NOPAPER_BASE_URL'),
        'api_key' => env('NOPAPER_API_KEY'),
    ],

    'timesheet' => [
        'url' => env('VERME_API_URL',''),
        'login' => env('VERME_API_LOGIN',''),
        'password' => env('VERME_API_PASSWORD',''),
    ],

    'xFive' => [
        'base_url' => env('XFIVE_BASE_URL', 'https://api.x5.ru/v1/wop-stage'),
        'client_id' => env('XFIVE_CLIENT_ID'),
        'client_secret' => env('XFIVE_CLIENT_SECRET'),
        'token_url' => env('XFIVE_TOKEN_URL', 'https://keycloak.x5.ru/realms/CSIP/protocol/openid-connect/token'),
    ],

    'timeBook' => [
        'base_url' => env('TIMEBOOK_BASE_URL', 'https://integration-test.timebook.ru/erpi'),
        'login' => env('TIMEBOOK_LOGIN'),
        'password' => env('TIMEBOOK_PASSWORD'),
    ],

    'one_c' => [
        'token' => env('ONE_C_TOKEN'),
    ],



];
