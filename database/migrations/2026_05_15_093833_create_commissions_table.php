<?php
// database/migrations/xxxx_create_commissions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->string('reference_image')->nullable(); // upload referensi desain
            $table->string('product_type'); // hoodie, tshirt, jersey, dll
            $table->decimal('budget', 12, 2)->nullable();
            $table->integer('quantity')->default(1);
            $table->enum('status', [
                'pending',   // baru masuk
                'reviewing', // sedang ditinjau admin
                'accepted',  // diterima, menunggu pembayaran
                'in_progress', // sedang dikerjakan
                'rejected',  // ditolak
                'completed', // selesai
            ])->default('pending');
            $table->text('admin_note')->nullable(); // catatan admin
            $table->decimal('quoted_price', 12, 2)->nullable(); // harga yang dikutip admin
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commissions');
    }
};