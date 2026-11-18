<?php

namespace App\Services;

use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class FavoritoService
{
    public function __construct(
        private readonly UsuarioService $usuarioService
    ) {}

    /**
     * Agregar un producto activo a los favoritos del usuario.
     *
     * Si el producto ya está registrado como favorito,
     * no se crea un registro duplicado.
     */
    public function agregar(Usuario $usuario, int $idProducto): Producto
    {
        $this->validarCuentaActiva($usuario);

        return DB::transaction(function () use ($usuario, $idProducto) {
            $producto = Producto::query()
                ->whereKey($idProducto)
                ->where('activo', true)
                ->firstOrFail();

            DB::table('favoritos')->insertOrIgnore([
                'id_usuario' => $usuario->id_usuario,
                'id_producto' => $producto->id_producto,
                'created_at' => now(),
            ]);

            return $usuario->productosFavoritos()
                ->where('productos.id_producto', $producto->id_producto)
                ->firstOrFail();
        });
    }

    /**
     * Quitar un producto de los favoritos del usuario.
     *
     * Devuelve true si se eliminó un registro.
     * Devuelve false si el producto no estaba en favoritos.
     */
    public function quitar(Usuario $usuario, int $idProducto): bool
    {
        $this->validarCuentaActiva($usuario);

        return DB::transaction(function () use ($usuario, $idProducto) {
            $eliminados = $usuario->productosFavoritos()
                ->detach($idProducto);

            return $eliminados > 0;
        });
    }

    /**
     * Obtener los productos favoritos del usuario.
     *
     * Solo devuelve productos que continúan activos.
     */
    public function listar(Usuario $usuario): Collection
    {
        $this->validarCuentaActiva($usuario);

        return $usuario->productosFavoritos()
            ->where('productos.activo', true)
            ->with([
                'categoria',
                'vendedor',
            ])
            ->orderByPivot('created_at', 'desc')
            ->get();
    }

    /**
     * Comprobar que la cuenta del usuario esté activa.
     */
    private function validarCuentaActiva(Usuario $usuario): void
    {
        if (! $this->usuarioService->estaActivo($usuario)) {
            throw new AuthorizationException(
                'La cuenta debe estar activa para administrar favoritos.'
            );
        }
    }
}
