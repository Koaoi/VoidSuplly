<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin utama
        User::create([
            'name'              => 'VOID Admin',
            'email'             => 'admin@voidsupply.id',
            'password'          => Hash::make('admin123'),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        // Customer test
        User::create([
            'name'              => 'Test Customer',
            'email'             => 'customer@test.com',
            'password'          => Hash::make('password'),
            'role'              => 'customer',
            'email_verified_at' => now(),
        ]);
    }
}