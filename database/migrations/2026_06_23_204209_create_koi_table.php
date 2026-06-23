<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('koi', function (Blueprint $table) {

            $table->id();

            $table->string('nama_koi');

            $table->string('jenis')
                  ->nullable();

            $table->string('ukuran', 100)
                  ->nullable();

            $table->integer('harga')
                  ->default(0);

            $table->unsignedInteger('stok')
                  ->default(0);

            $table->text('deskripsi')
                  ->nullable();

            $table->longBlob('gambar')
                  ->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('koi');
    }
};