<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('koi', function (Blueprint $table) {
        $table->id();
        $table->string('nama_koi');
        $table->string('jenis');
        $table->integer('ukuran');
        $table->integer('harga');
        $table->integer('stok');
        $table->text('deskripsi')->nullable();
        $table->string('gambar')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kois');
    }
};
