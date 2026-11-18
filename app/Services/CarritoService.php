<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\Inventario;
use App\Models\ItemCarrito;
use App\Models\Producto;
use App\Models\Usuario;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use LogicException;

class CarritoService
{
    public function __construct(
        private readonly UsuarioService $usuarioService
    ) {}

    /**
     * Consultar el carrito permanente del usuario.
     *
     * Incluye sus productos, categorías, vendedores e inventarios.
     */
    public function consultar(Usuario $usuario): Carrito
    {
        $this->validarCuentaActiva($usuario);

        $carrito = $this->obtenerCarrito($usuario);

        return $carrito->load([
            'items.producto.categoria',
            'items.producto.vendedor',
            'items.producto.inventario',
        ]);
    }

    /**
     * Agregar un producto al carrito.
     *
     * Si el producto ya existe, incrementa su cantidad.
     * No modifica ni reserva el inventario.
     */
    public function agregar(
        Usuario $usuario,
        int $idProducto,
        int $cantidad
    ): ItemCarrito {
        $this->validarCuentaActiva($usuario);
        $this->validarCantidadPositiva($cantidad);

        return DB::transaction(function () use (
            $usuario,
            $idProducto,
            $cantidad
        ) {
            $carrito = $this->obtenerCarritoBloqueado($usuario);

            $producto = $this->obtenerProductoDisponible(
                $idProducto
            );

            $inventario = $this->obtenerInventarioBloqueado(
                $producto
            );

            $stockDisponible = $inventario->stock_fisico
                - $inventario->stock_reservado;

            $item = $carrito->items()
                ->where('id_producto', $producto->id_producto)
                ->lockForUpdate()
                ->first();

            $cantidadActual = $item?->cantidad ?? 0;
            $nuevaCantidad = $cantidadActual + $cantidad;

            if ($nuevaCantidad > $stockDisponible) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad solicitada supera el stock disponible del producto.',
                ]);
            }

            if ($item) {
                $item->cantidad = $nuevaCantidad;
                $item->save();
            } else {
                $item = $carrito->items()->create([
                    'id_producto' => $producto->id_producto,
                    'cantidad' => $cantidad,
                ]);
            }

            return $item->load('producto');
        });
    }

    /**
     * Actualizar la cantidad de un producto existente en el carrito.
     *
     * La cantidad debe ser positiva y no superar el stock disponible.
     */
    public function actualizarCantidad(
        Usuario $usuario,
        int $idProducto,
        int $cantidad
    ): ItemCarrito {
        $this->validarCuentaActiva($usuario);
        $this->validarCantidadPositiva($cantidad);

        return DB::transaction(function () use (
            $usuario,
            $idProducto,
            $cantidad
        ) {
            $carrito = $this->obtenerCarritoBloqueado($usuario);

            $item = $carrito->items()
                ->where('id_producto', $idProducto)
                ->lockForUpdate()
                ->firstOrFail();

            $producto = $this->obtenerProductoDisponible(
                $idProducto
            );

            $inventario = $this->obtenerInventarioBloqueado(
                $producto
            );

            $stockDisponible = $inventario->stock_fisico
                - $inventario->stock_reservado;

            if ($cantidad > $stockDisponible) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad solicitada supera el stock disponible del producto.',
                ]);
            }

            $item->cantidad = $cantidad;
            $item->save();

            return $item->load('producto');
        });
    }

    /**
     * Quitar un producto del carrito.
     *
     * Devuelve true si se eliminó el artículo.
     * Devuelve false si no existía en el carrito.
     */
    public function quitar(
        Usuario $usuario,
        int $idProducto
    ): bool {
        $this->validarCuentaActiva($usuario);

        return DB::transaction(function () use (
            $usuario,
            $idProducto
        ) {
            $carrito = $this->obtenerCarritoBloqueado($usuario);

            $item = $carrito->items()
                ->where('id_producto', $idProducto)
                ->lockForUpdate()
                ->first();

            if (! $item) {
                return false;
            }

            $item->delete();

            return true;
        });
    }

    /**
     * Vaciar el carrito sin eliminar su registro permanente.
     *
     * Devuelve la cantidad de artículos eliminados.
     */
    public function vaciar(Usuario $usuario): int
    {
        $this->validarCuentaActiva($usuario);

        return DB::transaction(function () use ($usuario) {
            $carrito = $this->obtenerCarritoBloqueado($usuario);

            return $carrito->items()->delete();
        });
    }

    /**
     * Calcular el importe total de los artículos del carrito.
     *
     * Utiliza el precio actual de cada producto.
     * Devuelve una cadena decimal con dos posiciones.
     */
    public function calcularTotal(Usuario $usuario): string
    {
        $carrito = $this->consultar($usuario);

        $totalCentavos = 0;

        foreach ($carrito->items as $item) {
            $precio = number_format(
                (float) $item->producto->precio,
                2,
                '.',
                ''
            );

            $precioCentavos = (int) str_replace('.', '', $precio);

            $totalCentavos += $precioCentavos * $item->cantidad;
        }

        return intdiv($totalCentavos, 100)
            . '.'
            . str_pad(
                (string) ($totalCentavos % 100),
                2,
                '0',
                STR_PAD_LEFT
            );
    }

    /**
     * Comprobar que la cuenta del usuario esté activa.
     */
    private function validarCuentaActiva(Usuario $usuario): void
    {
        if (! $this->usuarioService->estaActivo($usuario)) {
            throw new AuthorizationException(
                'La cuenta debe estar activa para administrar el carrito.'
            );
        }
    }

    /**
     * Validar que la cantidad sea un número entero positivo.
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
     * Obtener el carrito permanente del usuario.
     *
     * No crea carritos automáticamente.
     * Detecta registros duplicados para el mismo usuario.
     */
    private function obtenerCarrito(Usuario $usuario): Carrito
    {
        return $this->buscarCarrito($usuario, false);
    }

    /**
     * Obtener y bloquear el carrito durante una modificación.
     */
    private function obtenerCarritoBloqueado(
        Usuario $usuario
    ): Carrito {
        return $this->buscarCarrito($usuario, true);
    }

    /**
     * Buscar el único carrito perteneciente al usuario.
     */
    private function buscarCarrito(
        Usuario $usuario,
        bool $bloquear
    ): Carrito {
        $consulta = $usuario->carritos();

        if ($bloquear) {
            $consulta->lockForUpdate();
        }

        $carritos = $consulta->get();

        if ($carritos->isEmpty()) {
            throw (new ModelNotFoundException())
                ->setModel(Carrito::class);
        }

        if ($carritos->count() > 1) {
            throw new LogicException(
                'El usuario tiene más de un carrito registrado. Debe corregirse esta inconsistencia.'
            );
        }

        $carrito = $carritos->first();

        if ($carrito->estado !== 'activo') {
            throw new AuthorizationException(
                'El carrito no se encuentra activo.'
            );
        }

        return $carrito;
    }

    /**
     * Obtener un producto que se encuentre activo.
     */
    private function obtenerProductoDisponible(
        int $idProducto
    ): Producto {
        return Producto::query()
            ->whereKey($idProducto)
            ->where('activo', true)
            ->firstOrFail();
    }

    /**
     * Obtener y bloquear el inventario del producto.
     */
    private function obtenerInventarioBloqueado(
        Producto $producto
    ): Inventario {
        return Inventario::query()
            ->where('id_producto', $producto->id_producto)
            ->lockForUpdate()
            ->firstOrFail();
    }
}
