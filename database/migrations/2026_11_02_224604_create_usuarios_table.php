
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {

            // Identificador principal
            $table->bigIncrements('id_usuario');

            // Datos personales
            $table->string('nombre', 100);
            $table->string('apellidos', 150);

            // Datos de acceso
            $table->string('correo', 255)->unique();
            $table->string('contrasena', 255);

            // Verificación del correo
            $table->timestamp('correo_verificado_en')->nullable();

            // Estado de la cuenta
            $table->boolean('activo')->default(true);

            // Fechas de creación y actualización
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
