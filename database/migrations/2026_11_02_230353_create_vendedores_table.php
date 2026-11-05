<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendedores', function (Blueprint $table) {
            $table->bigIncrements('id_vendedor');

            $table->unsignedBigInteger('id_usuario')->unique();

            $table->string('nombre_comercial', 150);
            $table->text('descripcion')->nullable();
            $table->string('telefono_contacto', 25)->nullable();
            $table->string('estado', 20);

            $table->timestamps();

            $table->foreign('id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendedores');
    }
};
