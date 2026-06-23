<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            $table->string('name');
            $table->string('email')->unique();

            $table->string('no_hp', 20)->nullable();

            $table->dateTime('email_verified_at')->nullable();

            $table->string('password');

            $table->enum('role', ['admin', 'user'])
                  ->default('user');

            $table->rememberToken();

            $table->string('provinsi')->nullable();
            $table->string('kota')->nullable();
            $table->string('kecamatan')->nullable();

            $table->text('alamat_detail')->nullable();

            $table->string('kode_pos', 10)->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};