<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->text('admin_reply')->nullable()->after('comment');
            $table->timestamp('admin_reply_updated_at')->nullable()->after('admin_reply');
        });
    }

    public function down()
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['admin_reply', 'admin_reply_updated_at']);
        });
    }
};