<?php
// database/migrations/xxxx_create_shipping_addresses_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->onDelete('cascade');
            $table->string('recipient_name');
            $table->string('phone', 20);
            $table->string('province');
            $table->string('province_id'); // ID dari RajaOngkir
            $table->string('city');
            $table->string('city_id');     // ID dari RajaOngkir
            $table->string('district')->nullable();
            $table->string('postal_code', 10);
            $table->text('address_detail');
            $table->string('courier');     // jne, jnt, sicepat, dll
            $table->string('service');     // REG, YES, OKE, dll
            $table->string('service_description')->nullable();
            $table->integer('estimated_days')->nullable(); // estimasi hari
            $table->timestamps();

            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_addresses');
    }
};