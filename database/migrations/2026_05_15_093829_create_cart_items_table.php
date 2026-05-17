<?php
// database/migrations/xxxx_create_cart_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')
                  ->constrained('carts')
                  ->onDelete('cascade');
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');
            $table->string('size');
            $table->integer('quantity')->default(1);
            $table->timestamps();

            // Satu produk+ukuran tidak boleh duplikat dalam satu cart
            $table->unique(['cart_id', 'product_id', 'size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};