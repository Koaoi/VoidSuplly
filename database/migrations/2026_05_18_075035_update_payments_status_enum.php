<?php
// database/migrations/xxxx_xx_xx_xxxxxx_update_payments_status_enum.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Hapus ENUM constraint dulu
        DB::statement("ALTER TABLE payments MODIFY status VARCHAR(50) DEFAULT 'pending'");
        
        // Update data yang ada
        DB::statement("UPDATE payments SET status = 'pending' WHERE status NOT IN ('pending', 'paid', 'failed')");
        
        // Set ulang ENUM dengan nilai baru
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending', 'paid', 'failed', 'pending_verification') DEFAULT 'pending'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending', 'paid', 'failed') DEFAULT 'pending'");
    }
};