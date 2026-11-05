<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_vendedor', function (Blueprint $table) {
            $table->bigIncrements('id_pedido_vendedor');

            $table->unsignedBigInteger('id_pedido');
            $table->unsignedBigInteger('id_vendedor');

            $table->decimal('subtotal_referencia', 12, 2);

            $table->string('estado', 30);

            $table->timestamp('fecha_respuesta')->nullable();

            $table->timestamps();

            $table->unique(['id_pedido', 'id_vendedor']);

            $table->foreign('id_pedido')
                ->references('id_pedido')
                ->on('pedidos')
                ->restrictOnDelete();

            $table->foreign('id_vendedor')
                ->references('id_vendedor')
                ->on('vendedores')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_vendedor');
    }
};
