<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->string('provinsi_id')->nullable();
            $table->string('kota_id')->nullable();
            $table->string('kecamatan')->nullable();
            $table->text('alamat_detail')->nullable();
            $table->string('kode_pos')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropColumn([
                'provinsi_id',
                'kota_id',
                'kecamatan',
                'alamat_detail',
                'kode_pos'
            ]);

        });
    }
};