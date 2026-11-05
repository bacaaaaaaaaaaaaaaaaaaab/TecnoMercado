<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->bigIncrements('id_producto');

            $table->unsignedBigInteger('id_vendedor');
            $table->unsignedBigInteger('id_categoria');

            $table->string('nombre', 150);
            $table->text('descripcion');
            $table->decimal('precio', 10, 2);

            $table->string('imagen_principal', 255)->nullable();
            $table->string('imagen_2', 255)->nullable();
            $table->string('imagen_3', 255)->nullable();
            $table->string('imagen_4', 255)->nullable();
            $table->string('imagen_5', 255)->nullable();
            $table->string('imagen_6', 255)->nullable();
            $table->string('imagen_7', 255)->nullable();
            $table->string('video', 255)->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->foreign('id_vendedor')
                ->references('id_vendedor')
                ->on('vendedores')
                ->restrictOnDelete();

            $table->foreign('id_categoria')
                ->references('id_categoria')
                ->on('categorias')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
