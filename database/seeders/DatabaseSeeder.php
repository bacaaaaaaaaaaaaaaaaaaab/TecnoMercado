<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Ejecutar los seeders principales de TecnoMercado.
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
        ]);
    }
}
