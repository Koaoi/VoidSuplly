<?php
// config/services.php
return [

    'mailgun' => [ /* ... bawaan Laravel ... */ ],
    'postmark' => [ /* ... */ ],
    'ses' => [ /* ... */ ],

    /*
    |--------------------------------------------------------------------------
    | Google OAuth — Laravel Socialite
    |--------------------------------------------------------------------------
    */
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),
    ],

    // config/services.php — tambahkan di dalam array return
// ... konfigurasi service lain (mailgun, ses, dll)

'rajaongkir' => [
        'api_key' => env('RAJAONGKIR_API_KEY'),
        'base_url' => env('RAJAONGKIR_BASE_URL', 'https://api.komerce.id/v1'),
        'origin_subdistrict' => env('RAJAONGKIR_ORIGIN_SUBDISTRICT', '6521'),
    ],

    'midtrans' => [
    'server_key' => env('MIDTRANS_SERVER_KEY'),
    'client_key' => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
],
];