<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items_carrito', function (Blueprint $table) {
            $table->bigIncrements('id_item_carrito');

            $table->unsignedBigInteger('id_carrito');
            $table->unsignedBigInteger('id_producto');

            $table->unsignedInteger('cantidad');

            $table->timestamps();

            $table->unique(['id_carrito', 'id_producto']);

            $table->foreign('id_carrito')
                ->references('id_carrito')
                ->on('carritos')
                ->cascadeOnDelete();

            $table->foreign('id_producto')
                ->references('id_producto')
                ->on('productos')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items_carrito');
    }
};
