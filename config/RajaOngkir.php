<?php
// config/rajaongkir.php

return [

    /*
    |--------------------------------------------------------------------------
    | Komerce RajaOngkir API v1
    | Docs: https://rajaongkir.komerce.id/api/v1
    |--------------------------------------------------------------------------
    */

    'api_key'   => env('RAJAONGKIR_API_KEY'),

    'base_url'  => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),

    /*
     | Subdistrict ID asal toko — didapat dari endpoint:
     | GET /destination/domestic-destination?search=nama_kelurahan
     | Contoh: 17473 = Grogol, Grogol Petamburan, Jakarta Barat
     */
    'origin_id' => env('RAJAONGKIR_ORIGIN_ID', '17473'),

    'timeout'   => (int) env('RAJAONGKIR_TIMEOUT', 15),

    'cache_ttl' => (int) env('RAJAONGKIR_CACHE_TTL', 86400), // 24 jam

    /*
     | Kurir yang didukung Komerce API
     | Kunci = kode untuk API, nilai = label tampilan
     */
    'couriers'  => [
        'jne'      => 'JNE',
        'jnt'      => 'J&T Express',
        'sicepat'  => 'SiCepat',
        'anteraja' => 'AnterAja',
        'tiki'     => 'TIKI',
        'pos'      => 'POS Indonesia',
        'lion'     => 'Lion Parcel',
        'ninja'    => 'Ninja Xpress',
    ],
];