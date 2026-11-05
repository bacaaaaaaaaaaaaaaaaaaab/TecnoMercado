<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detalles_pedido', function (Blueprint $table) {
            $table->bigIncrements('id_detalle_pedido');

            $table->unsignedBigInteger('id_pedido_vendedor');
            $table->unsignedBigInteger('id_producto');

            $table->string('nombre_producto', 150);

            $table->unsignedInteger('cantidad');

            $table->decimal('precio_unitario_referencia', 10, 2);

            $table->timestamps();

            $table->foreign('id_pedido_vendedor')
                ->references('id_pedido_vendedor')
                ->on('pedidos_vendedor')
                ->restrictOnDelete();

            $table->foreign('id_producto')
                ->references('id_producto')
                ->on('productos')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detalles_pedido');
    }
};
