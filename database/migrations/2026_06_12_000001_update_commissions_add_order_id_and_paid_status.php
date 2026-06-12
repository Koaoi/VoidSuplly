<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambah kolom order_id (nullable FK ke orders) - CEK DULU
        if (!Schema::hasColumn('commissions', 'order_id')) {
            Schema::table('commissions', function (Blueprint $table) {
                $table->foreignId('order_id')
                      ->nullable()
                      ->after('quoted_price')
                      ->constrained('orders')
                      ->nullOnDelete();
            });
        }

        // 2. Ubah enum status — tambah 'paid'
        // Cek apakah kolom status sudah ada dan ENUM sudah memiliki 'paid'
        $hasPaid = DB::select("
            SELECT COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'commissions' 
            AND COLUMN_NAME = 'status'
        ");

        if (!empty($hasPaid) && !str_contains($hasPaid[0]->COLUMN_TYPE, "'paid'")) {
            DB::statement("
                ALTER TABLE commissions
                MODIFY COLUMN status ENUM(
                    'pending',
                    'reviewing',
                    'accepted',
                    'in_progress',
                    'rejected',
                    'completed',
                    'paid'
                ) NOT NULL DEFAULT 'pending'
            ");
        }
    }

    public function down(): void
    {
        // Hapus kolom order_id (jika ada)
        if (Schema::hasColumn('commissions', 'order_id')) {
            Schema::table('commissions', function (Blueprint $table) {
                $table->dropForeign(['order_id']);
                $table->dropColumn('order_id');
            });
        }

        // Kembalikan enum ke kondisi semula (tanpa 'paid')
        $hasPaid = DB::select("
            SELECT COLUMN_TYPE 
            FROM INFORMATION_SCHEMA.COLUMNS 
            WHERE TABLE_NAME = 'commissions' 
            AND COLUMN_NAME = 'status'
        ");

        if (!empty($hasPaid) && str_contains($hasPaid[0]->COLUMN_TYPE, "'paid'")) {
            DB::statement("
                ALTER TABLE commissions
                MODIFY COLUMN status ENUM(
                    'pending',
                    'reviewing',
                    'accepted',
                    'in_progress',
                    'rejected',
                    'completed'
                ) NOT NULL DEFAULT 'pending'
            ");
        }
    }
};