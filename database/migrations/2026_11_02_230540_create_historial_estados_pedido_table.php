<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_estados_pedido', function (Blueprint $table) {
            $table->bigIncrements('id_historial');

            $table->unsignedBigInteger('id_pedido_vendedor');

            $table->string('estado', 30);

            $table->text('comentario')->nullable();

            $table->timestamp('fecha_cambio');

            $table->unsignedBigInteger('id_usuario_responsable')->nullable();

            $table->timestamp('created_at')->nullable();

            $table->foreign('id_pedido_vendedor')
                ->references('id_pedido_vendedor')
                ->on('pedidos_vendedor')
                ->restrictOnDelete();

            $table->foreign('id_usuario_responsable')
                ->references('id_usuario')
                ->on('usuarios')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_estados_pedido');
    }
};
