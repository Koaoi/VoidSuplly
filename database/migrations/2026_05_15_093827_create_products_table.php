<?php
// database/migrations/xxxx_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->onDelete('restrict');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('details')->nullable(); // spesifikasi detail produk
            $table->decimal('price', 12, 2);
            $table->integer('stock')->default(0);
            $table->integer('weight')->default(0); // dalam gram, untuk ongkir
            $table->enum('status', [
                'available',
                'sold_out',
                'preorder',
                'coming_soon',
            ])->default('available');
            $table->boolean('is_limited')->default(false);
            $table->timestamp('release_date')->nullable(); // untuk countdown drop
            $table->json('sizes')->nullable(); // ['S','M','L','XL','XXL']
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_limited']);
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};