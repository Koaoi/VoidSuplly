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
    'rajaongkir' => [
    'api_key'  => env('RAJAONGKIR_API_KEY'),
    'base_url' => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1/'),
    'origin'   => env('RAJAONGKIR_ORIGIN_CITY_ID', '501'), // 501 = Surabaya, ganti sesuai kota toko
],
];