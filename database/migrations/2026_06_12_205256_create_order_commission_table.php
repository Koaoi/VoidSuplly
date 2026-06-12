<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('order_commission', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('commission_id')->constrained('commissions')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('commission_rate', 12, 2)->default(0);
            $table->timestamps();
            
            // Optional: unique constraint to avoid duplicate entries
            $table->unique(['order_id', 'commission_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_commission');
    }
};