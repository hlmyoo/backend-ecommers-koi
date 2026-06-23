<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeStatusPesananToEnum extends Migration
{
    public function up()
    {
        DB::statement("
            ALTER TABLE orders 
            MODIFY status_pesanan 
            ENUM('pending','distujui','ditolak') 
            DEFAULT 'pending'
        ");
    }

    public function down()
    {
        DB::statement("
            ALTER TABLE orders 
            MODIFY status_pesanan VARCHAR(255)
        ");
    }
}