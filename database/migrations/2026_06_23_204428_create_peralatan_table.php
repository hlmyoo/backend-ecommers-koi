<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('peralatan', function (Blueprint $table) {

            $table->id();

            $table->string('nama_peralatan');

            $table->string('kategori')->nullable();

            $table->text('deskripsi')->nullable();

            $table->decimal('harga', 15, 2)->default(0);

            $table->unsignedInteger('stok')->default(0);

            $table->longText('gambar')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('peralatan');
    }
};