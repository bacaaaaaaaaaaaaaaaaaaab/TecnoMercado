<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventarios', function (Blueprint $table) {
            $table->bigIncrements('id_inventario');

            $table->unsignedBigInteger('id_producto')->unique();

            $table->unsignedInteger('stock_fisico')->default(0);
            $table->unsignedInteger('stock_reservado')->default(0);

            $table->timestamp('updated_at')->nullable();

            $table->foreign('id_producto')
                ->references('id_producto')
                ->on('productos')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventarios');
    }
};
