<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Ubah ENUM untuk menambahkan 'paid'
        DB::statement("ALTER TABLE commissions MODIFY COLUMN status ENUM('pending', 'reviewing', 'accepted', 'in_progress', 'completed', 'rejected', 'paid') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Kembalikan ke ENUM awal tanpa 'paid'
        DB::statement("ALTER TABLE commissions MODIFY COLUMN status ENUM('pending', 'reviewing', 'accepted', 'in_progress', 'completed', 'rejected') NOT NULL DEFAULT 'pending'");
    }
};