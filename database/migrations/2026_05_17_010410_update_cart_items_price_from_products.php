<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Update cart_items price from products table
        DB::statement('
            UPDATE cart_items 
            JOIN products ON cart_items.product_id = products.id 
            SET cart_items.price = products.price 
            WHERE cart_items.price = 0 OR cart_items.price IS NULL
        ');
    }

    public function down()
    {
        // Nothing to rollback
    }
};