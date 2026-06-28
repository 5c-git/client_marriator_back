<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Yandex.Smena API Connection
    |--------------------------------------------------------------------------
    */
    'host' => env('YANDEX_SMENA_HOST', 'https://smena.yandex.ru'),
    'token' => env('YANDEX_SMENA_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    */
    'queue' => env('YANDEX_SMENA_QUEUE', 'yandex-smena'),

    /*
    |--------------------------------------------------------------------------
    | Reference data mappings
    |
    | Yandex creates site_id, profession_id and payment_id during onboarding.
    | These IDs are then stored in the database mapping tables, but for
    | initial seeding or local development they can be defined here.
    |--------------------------------------------------------------------------
    */
    'sites' => [
        // 'place_uuid_or_id' => 'yandex_site_id',
    ],

    'professions' => [
        // 'view_activity_uuid_or_id' => 'yandex_profession_id',
    ],

    'payments' => [
        // 'local_code' => [
        //     'name' => '...',
        //     'payment_id' => 'yandex_payment_id',
        //     'amount_per_hour' => 100,
        //     'currency' => 'RUB',
        // ],
    ],
];
