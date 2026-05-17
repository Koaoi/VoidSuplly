<?php
// app/Providers/RajaOngkirServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RajaOngkirServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('rajaongkir', function () {
            return new \App\Http\Controllers\RajaOngkirController();
        });
    }
}