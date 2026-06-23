<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
  

    // Buat tabel baru yang digabung
    Schema::create('orders', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('koi_id')->constrained('koi')->onDelete('cascade');
        $table->integer('jumlah');
        $table->integer('harga');
        $table->integer('subtotal');
        $table->enum('status_pesanan', ['pending', 'disetujui', 'ditolak'])->default('pending');
        $table->enum('status_pembayaran', ['belum_bayar', 'menunggu_konfirmasi', 'lunas', 'ditolak'])->default('belum_bayar');
        $table->string('snap_token')->nullable();
        $table->timestamps();
    });
}

public function down()
{
    Schema::dropIfExists('orders');
}
};
