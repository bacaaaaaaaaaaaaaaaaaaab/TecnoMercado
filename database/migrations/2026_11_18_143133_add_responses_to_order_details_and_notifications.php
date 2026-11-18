
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detalles_pedido', function (Blueprint $table) {
            $table->string('estado_respuesta', 20)
                ->default('pendiente');

            $table->boolean('disponibilidad_producto')
                ->nullable();

            $table->text('motivo_respuesta')
                ->nullable();

            $table->boolean('dar_de_baja_publicacion')
                ->nullable();

            $table->text('justificacion_publicacion')
                ->nullable();

            $table->timestamp('fecha_respuesta')
                ->nullable();
        });

        Schema::create('historial_respuestas_producto', function (Blueprint $table) {
            $table->bigIncrements('id_historial_respuesta');

            $table->unsignedBigInteger('id_detalle_pedido');
            $table->unsignedBigInteger('id_usuario_responsable')->nullable();

            $table->string('estado_anterior', 20)->nullable();
            $table->string('estado_nuevo', 20);

            $table->boolean('disponibilidad_producto')->nullable();
            $table->text('motivo_respuesta')->nullable();
            $table->boolean('dar_de_baja_publicacion')->nullable();
            $table->text('justificacion_publicacion')->nullable();

            $table->timestamp('fecha_respuesta');
            $table->timestamp('created_at')->nullable();

            $table->foreign('id_detalle_pedido')
                ->references('id_detalle_pedido')
                ->on('detalles_pedido')
                ->restrictOnDelete();

            $table->foreign('id_usuario_responsable')
                ->references('id_usuario')
                ->on('usuarios')
                ->nullOnDelete();
        });

        Schema::table('notificaciones', function (Blueprint $table) {
            $table->unsignedBigInteger('id_detalle_pedido')->nullable();

            $table->foreign('id_detalle_pedido')
                ->references('id_detalle_pedido')
                ->on('detalles_pedido')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('notificaciones', function (Blueprint $table) {
            $table->dropForeign(['id_detalle_pedido']);
            $table->dropColumn('id_detalle_pedido');
        });

        Schema::dropIfExists('historial_respuestas_producto');

        Schema::table('detalles_pedido', function (Blueprint $table) {
            $table->dropColumn([
                'estado_respuesta',
                'disponibilidad_producto',
                'motivo_respuesta',
                'dar_de_baja_publicacion',
                'justificacion_publicacion',
                'fecha_respuesta',
            ]);
        });
    }
};
