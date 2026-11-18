<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    /**
     * Registrar los roles iniciales de TecnoMercado.
     */
    public function run(): void
    {
        $roles = [
            [
                'nombre' => 'comprador',
                'descripcion' => 'Usuario que puede explorar productos y solicitar compras.',
            ],
            [
                'nombre' => 'vendedor',
                'descripcion' => 'Usuario que puede publicar y administrar sus productos.',
            ],
            [
                'nombre' => 'administrador',
                'descripcion' => 'Usuario autorizado para administrar la plataforma.',
            ],
        ];

        foreach ($roles as $datos) {
            Rol::updateOrCreate(
                ['nombre' => $datos['nombre']],
                ['descripcion' => $datos['descripcion']]
            );
        }
    }
}
