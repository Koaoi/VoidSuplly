<?php
// database/migrations/xxxx_create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('restrict');
            $table->string('order_code')->unique(); // VOID-20241201-XXXX
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);
            $table->enum('status', [
                'pending',      // menunggu pembayaran
                'paid',         // sudah bayar, menunggu konfirmasi admin
                'processing',   // sedang diproses/dikemas
                'shipped',      // sudah dikirim
                'completed',    // diterima customer
                'cancelled',    // dibatalkan
            ])->default('pending');
            $table->text('notes')->nullable(); // catatan dari customer
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};