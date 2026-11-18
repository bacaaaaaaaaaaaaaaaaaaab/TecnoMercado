<?php

namespace App\Services;

use App\Models\Categoria;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoriaService
{
    /**
     * Obtener todas las categorias.
     * Permite filtrar solamente las activas.
     */
    public function listar(bool $soloActivas = false)
    {
        $consulta = Categoria::query()
            ->orderBy('nombre');

        if ($soloActivas) {
            $consulta->where('activa', true);
        }

        return $consulta->get();
    }

    /**
     * Obtener una categoria por su identificador.
     */
    public function obtener(int $idCategoria): Categoria
    {
        return Categoria::findOrFail($idCategoria);
    }

    /**
     * Crear una nueva categoria.
     */
    public function crear(array $datos): Categoria
    {
        $datosValidados = Validator::make($datos, [
            'nombre' => [
                'required',
                'string',
                'max:100',
                'unique:categorias,nombre',
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:255',
            ],
        ])->validate();

        return DB::transaction(function () use ($datosValidados) {
            $categoria = Categoria::create($datosValidados);

            return $categoria->fresh();
        });
    }

    /**
     * Actualizar los datos de una categoria.
     */
    public function actualizar(
        int $idCategoria,
        array $datos
    ): Categoria {
        $categoria = $this->obtener($idCategoria);

        $datosValidados = Validator::make($datos, [
            'nombre' => [
                'required',
                'string',
                'max:100',
                'unique:categorias,nombre,' . $categoria->id_categoria . ',id_categoria',
            ],
            'descripcion' => [
                'nullable',
                'string',
                'max:255',
            ],
        ])->validate();

        return DB::transaction(function () use (
            $categoria,
            $datosValidados
        ) {
            $categoria->update($datosValidados);

            return $categoria->fresh();
        });
    }

    /**
     * Desactivar una categoria sin eliminarla.
     */
    public function desactivar(int $idCategoria): Categoria
    {
        return DB::transaction(function () use ($idCategoria) {
            $categoria = $this->obtener($idCategoria);

            $categoria->activa = false;
            $categoria->save();

            return $categoria;
        });
    }

    /**
     * Reactivar una categoria existente.
     */
    public function reactivar(int $idCategoria): Categoria
    {
        return DB::transaction(function () use ($idCategoria) {
            $categoria = $this->obtener($idCategoria);

            $categoria->activa = true;
            $categoria->save();

            return $categoria;
        });
    }
}
