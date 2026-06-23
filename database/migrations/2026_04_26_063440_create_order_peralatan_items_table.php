<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('order_peralatan_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('order_peralatan_id')->constrained('orders_peralatan')->onDelete('cascade');
        $table->foreignId('peralatan_id')->constrained('peralatan')->onDelete('cascade');
        $table->integer('jumlah');
        $table->integer('harga');
        $table->integer('subtotal');
        $table->timestamps();
    });
}
};
