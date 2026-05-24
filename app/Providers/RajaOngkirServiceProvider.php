<?php
// app/Providers/RajaOngkirServiceProvider.php

namespace App\Providers;

use App\Services\RajaOngkirService;
use Illuminate\Support\ServiceProvider;

class RajaOngkirServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(RajaOngkirService::class, function ($app) {
            return new RajaOngkirService();
        });
    }

    public function boot(): void
    {
        //
    }
}