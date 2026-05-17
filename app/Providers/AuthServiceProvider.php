<?php
// app/Providers/AuthServiceProvider.php

namespace App\Providers;

use App\Models\CartItem;
use App\Policies\CartItemPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        CartItem::class => CartItemPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}