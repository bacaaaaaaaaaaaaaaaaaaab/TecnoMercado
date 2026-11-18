<?php

namespace App\Services;

use App\Models\Inventario;
use App\Models\Usuario;
use App\Models\Vendedor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InventarioService
{
    public function __construct(
        private readonly UsuarioService $usuarioService,
        private readonly VendedorService $vendedorService
    ) {}

    /**
     * Consulta el inventario de un producto perteneciente al vendedor.
     */
    public function consultar(
        Usuario $usuario,
        int $idProducto
    ): Inventario {
        $vendedor = $this->obtenerVendedorActivo($usuario);

        $producto = $vendedor->productos()
            ->whereKey($idProducto)
            ->firstOrFail();

        $inventario = $producto->inventario()->firstOrFail();

        $inventario->setAttribute(
            'stock_disponible',
            $inventario->stock_fisico - $inventario->stock_reservado
        );

        return $inventario;
    }

    /**
     * Establece una nueva cantidad de stock físico.
     * Nunca permite reducirlo por debajo del stock reservado.
     */
    public function actualizarStockFisico(
        Usuario $usuario,
        int $idProducto,
        int $nuevoStock
    ): Inventario {
        $datos = Validator::make([
            'stock_fisico' => $nuevoStock,
        ], [
            'stock_fisico' => [
                'required',
                'integer',
                'min:0',
            ],
        ])->validate();

        return DB::transaction(function () use (
            $usuario,
            $idProducto,
            $datos
        ) {
            $inventario = $this->obtenerInventarioBloqueado(
                $usuario,
                $idProducto
            );

            if ($datos['stock_fisico'] < $inventario->stock_reservado) {
                throw ValidationException::withMessages([
                    'stock_fisico' => 'El stock físico no puede ser menor que el stock reservado.',
                ]);
            }

            $inventario->stock_fisico = $datos['stock_fisico'];
            $inventario->save();

            return $this->refrescarInventario($inventario);
        });
    }

    /**
     * Aumenta el stock físico del producto.
     */
    public function aumentarStock(
        Usuario $usuario,
        int $idProducto,
        int $cantidad
    ): Inventario {
        $this->validarCantidadPositiva($cantidad);

        return DB::transaction(function () use (
            $usuario,
            $idProducto,
            $cantidad
        ) {
            $inventario = $this->obtenerInventarioBloqueado(
                $usuario,
                $idProducto
            );

            $inventario->stock_fisico += $cantidad;
            $inventario->save();

            return $this->refrescarInventario($inventario);
        });
    }

    /**
     * Reduce el stock físico sin afectar las unidades reservadas.
     */
    public function reducirStock(
        Usuario $usuario,
        int $idProducto,
        int $cantidad
    ): Inventario {
        $this->validarCantidadPositiva($cantidad);

        return DB::transaction(function () use (
            $usuario,
            $idProducto,
            $cantidad
        ) {
            $inventario = $this->obtenerInventarioBloqueado(
                $usuario,
                $idProducto
            );

            $nuevoStock = $inventario->stock_fisico - $cantidad;

            if ($nuevoStock < $inventario->stock_reservado) {
                throw ValidationException::withMessages([
                    'cantidad' => 'No se puede reducir el stock porque existen unidades reservadas. El stock físico debe ser igual o mayor al reservado.',
                ]);
            }

            $inventario->stock_fisico = $nuevoStock;
            $inventario->save();

            return $this->refrescarInventario($inventario);
        });
    }

    /**
     * Comprueba que el usuario y su perfil comercial estén activos.
     */
    private function obtenerVendedorActivo(Usuario $usuario): Vendedor
    {
        if (!$this->usuarioService->estaActivo($usuario)) {
            throw new AuthorizationException(
                'La cuenta debe estar activa para administrar inventarios.'
            );
        }

        $vendedor = $this->vendedorService->obtenerPerfil($usuario);

        if ($vendedor->estado !== 'activo') {
            throw new AuthorizationException(
                'El perfil de vendedor debe estar activo para modificar inventarios.'
            );
        }

        return $vendedor;
    }

    /**
     * Obtiene y bloquea el inventario durante una operación de escritura.
     */
    private function obtenerInventarioBloqueado(
        Usuario $usuario,
        int $idProducto
    ): Inventario {
        $vendedor = $this->obtenerVendedorActivo($usuario);

        $producto = $vendedor->productos()
            ->whereKey($idProducto)
            ->lockForUpdate()
            ->firstOrFail();

        return Inventario::query()
            ->where('id_producto', $producto->id_producto)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * Comprueba que la cantidad que se desea modificar sea positiva.
     */
    private function validarCantidadPositiva(int $cantidad): void
    {
        Validator::make([
            'cantidad' => $cantidad,
        ], [
            'cantidad' => [
                'required',
                'integer',
                'min:1',
            ],
        ])->validate();
    }

    /**
     * Recarga el inventario y calcula el stock disponible.
     */
    private function refrescarInventario(
        Inventario $inventario
    ): Inventario {
        $inventario->refresh();

        $inventario->setAttribute(
            'stock_disponible',
            $inventario->stock_fisico - $inventario->stock_reservado
        );

        return $inventario;
    }
}
