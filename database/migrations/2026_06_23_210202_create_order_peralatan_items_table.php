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

            $table->foreignId('order_peralatan_id')
                  ->constrained('orders_peralatan')
                  ->cascadeOnDelete();

            $table->foreignId('peralatan_id')
                  ->constrained('peralatan')
                  ->cascadeOnDelete();

            $table->unsignedInteger('jumlah')
                  ->default(1);

            $table->decimal('harga', 15, 2)
                  ->default(0);

            $table->decimal('subtotal', 15, 2)
                  ->default(0);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_peralatan_items');
    }
};