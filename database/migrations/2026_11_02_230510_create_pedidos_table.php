<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->bigIncrements('id_pedido');

            $table->unsignedBigInteger('id_comprador');

            $table->timestamp('fecha_solicitud');

            $table->decimal('total_referencia', 12, 2);

            $table->string('estado_general', 30);

            $table->timestamps();

            $table->foreign('id_comprador')
                ->references('id_usuario')
                ->on('usuarios')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
