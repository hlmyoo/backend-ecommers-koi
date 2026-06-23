<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusColumnsToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {

            if (!Schema::hasColumn('orders', 'status_pesanan')) {
                $table->enum('status_pesanan', ['pending', 'distujui', 'ditolak'])
                      ->default('pending');
            }

            if (!Schema::hasColumn('orders', 'status_pembayaran')) {
                $table->enum('status_pembayaran', ['belum_bayar', 'menunggu_konfirmasi', 'lunas', 'ditolak'])
                      ->default('belum_bayar');
            }

        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'status_pesanan')) {
                $table->dropColumn('status_pesanan');
            }

            if (Schema::hasColumn('orders', 'status_pembayaran')) {
                $table->dropColumn('status_pembayaran');
            }
        });
    }
}