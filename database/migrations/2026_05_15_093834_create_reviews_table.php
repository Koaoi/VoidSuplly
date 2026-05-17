<?php
// database/migrations/xxxx_create_reviews_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->foreignId('product_id')
                  ->constrained('products')
                  ->onDelete('cascade');
            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->onDelete('cascade');
            $table->tinyInteger('rating'); // 1 - 5
            $table->text('comment')->nullable();
            $table->string('image')->nullable(); // foto review produk
            $table->boolean('is_approved')->default(true);
            $table->timestamps();

            // 1 user hanya bisa review 1 produk per order
            $table->unique(['user_id', 'product_id', 'order_id']);
            $table->index(['product_id', 'is_approved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};