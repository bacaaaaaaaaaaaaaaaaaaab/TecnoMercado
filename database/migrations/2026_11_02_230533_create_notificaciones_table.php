<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->bigIncrements('id_notificacion');

            $table->unsignedBigInteger('id_usuario');
            $table->unsignedBigInteger('id_pedido_vendedor')->nullable();

            $table->string('tipo', 50);
            $table->text('mensaje');

            $table->boolean('leida')->default(false);

            $table->timestamp('fecha_lectura')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnDelete();

            $table->foreign('id_pedido_vendedor')
                ->references('id_pedido_vendedor')
                ->on('pedidos_vendedor')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
