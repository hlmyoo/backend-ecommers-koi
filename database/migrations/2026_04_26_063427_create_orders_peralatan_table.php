<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('orders_peralatan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->enum('status_pesanan', ['pending', 'disetujui', 'ditolak'])->default('pending');
        $table->enum('status_pembayaran', ['belum_bayar', 'menunggu_konfirmasi', 'lunas', 'ditolak'])->default('belum_bayar');
        $table->string('snap_token')->nullable();
        $table->timestamps();
    });
}
};
