<?php

namespace App\Services;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Vendedor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductoService
{
    public function __construct(
        private readonly UsuarioService $usuarioService,
        private readonly VendedorService $vendedorService
    ) {}

    /**
     * Lista los productos pertenecientes al vendedor.
     */
    public function listarDeVendedor(Usuario $usuario): Collection
    {
        $vendedor = $this->obtenerVendedorActivo($usuario);

        return $vendedor->productos()
            ->with(['categoria', 'inventario'])
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Obtiene un producto y comprueba que pertenezca al usuario.
     */
    public function obtener(Usuario $usuario, int $idProducto): Producto
    {
        $vendedor = $this->obtenerVendedor($usuario);

        return $vendedor->productos()
            ->with(['categoria', 'inventario'])
            ->findOrFail($idProducto);
    }

    /**
     * Crea un producto y su inventario inicial en una transacción.
     */
    public function crear(Usuario $usuario, array $datos): Producto
    {
        $vendedor = $this->obtenerVendedorActivo($usuario);

        $datosValidados = $this->validarDatosProducto($datos, true);

        $this->validarCategoriaActiva(
            $datosValidados['id_categoria']
        );

        return DB::transaction(function () use (
            $vendedor,
            $datosValidados
        ) {
            $producto = $vendedor->productos()->create([
                'id_categoria' => $datosValidados['id_categoria'],
                'nombre' => $datosValidados['nombre'],
                'descripcion' => $datosValidados['descripcion'],
                'precio' => $datosValidados['precio'],
                'imagen_principal' => $datosValidados['imagen_principal'] ?? null,
                'imagen_2' => $datosValidados['imagen_2'] ?? null,
                'imagen_3' => $datosValidados['imagen_3'] ?? null,
                'imagen_4' => $datosValidados['imagen_4'] ?? null,
                'imagen_5' => $datosValidados['imagen_5'] ?? null,
                'imagen_6' => $datosValidados['imagen_6'] ?? null,
                'imagen_7' => $datosValidados['imagen_7'] ?? null,
                'video' => $datosValidados['video'] ?? null,
            ]);

            $producto->inventario()->create([
                'stock_fisico' => $datosValidados['stock_fisico'],
                'stock_reservado' => 0,
            ]);

            return $producto->fresh([
                'categoria',
                'inventario',
                'vendedor',
            ]);
        });
    }

    /**
     * Actualiza únicamente la información del producto.
     * No modifica propietario, estado ni inventario.
     */
    public function actualizar(
        Usuario $usuario,
        int $idProducto,
        array $datos
    ): Producto {
        $producto = $this->obtener($usuario, $idProducto);

        $this->validarProductoActivo($producto);

        $datosValidados = $this->validarDatosProducto($datos, false);

        $this->validarCategoriaActiva(
            $datosValidados['id_categoria']
        );

        return DB::transaction(function () use (
            $producto,
            $datosValidados
        ) {
            $producto->update([
                'id_categoria' => $datosValidados['id_categoria'],
                'nombre' => $datosValidados['nombre'],
                'descripcion' => $datosValidados['descripcion'],
                'precio' => $datosValidados['precio'],
                'imagen_principal' => $datosValidados['imagen_principal'] ?? null,
                'imagen_2' => $datosValidados['imagen_2'] ?? null,
                'imagen_3' => $datosValidados['imagen_3'] ?? null,
                'imagen_4' => $datosValidados['imagen_4'] ?? null,
                'imagen_5' => $datosValidados['imagen_5'] ?? null,
                'imagen_6' => $datosValidados['imagen_6'] ?? null,
                'imagen_7' => $datosValidados['imagen_7'] ?? null,
                'video' => $datosValidados['video'] ?? null,
            ]);

            return $producto->fresh([
                'categoria',
                'inventario',
                'vendedor',
            ]);
        });
    }

    /**
     * Desactiva un producto sin eliminar su información relacionada.
     */
    public function desactivar(
        Usuario $usuario,
        int $idProducto
    ): Producto {
        $producto = $this->obtener($usuario, $idProducto);

        $producto->update(['activo' => false]);

        return $producto->fresh([
            'categoria',
            'inventario',
        ]);
    }

    /**
     * Reactiva un producto únicamente si su categoría sigue activa.
     */
    public function reactivar(
        Usuario $usuario,
        int $idProducto
    ): Producto {
        $this->obtenerVendedorActivo($usuario);

        $producto = $this->obtener($usuario, $idProducto);

        $this->validarCategoriaActiva($producto->id_categoria);

        $producto->update(['activo' => true]);

        return $producto->fresh([
            'categoria',
            'inventario',
        ]);
    }

    /**
     * Elimina un producto solamente si no tiene referencias
     * en carritos ni historial de solicitudes de compra.
     */
    public function eliminar(
        Usuario $usuario,
        int $idProducto
    ): void {
        $producto = $this->obtener($usuario, $idProducto);

        DB::transaction(function () use ($producto) {
            $producto = Producto::query()
                ->whereKey($producto->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($producto->detallesPedido()->exists()) {
                throw ValidationException::withMessages([
                    'producto' => 'No se puede eliminar un producto que tiene solicitudes de compra asociadas.',
                ]);
            }

            if ($producto->itemsCarrito()->exists()) {
                throw ValidationException::withMessages([
                    'producto' => 'No se puede eliminar un producto que todavía está en un carrito. Primero debe retirarse del carrito.',
                ]);
            }

            $producto->delete();
        });
    }

    /**
     * Valida los campos permitidos para crear o editar un producto.
     */
    private function validarDatosProducto(
        array $datos,
        bool $requiereStock
    ): array {
        $reglas = [
            'id_categoria' => [
                'required',
                'integer',
                'exists:categorias,id_categoria',
            ],
            'nombre' => [
                'required',
                'string',
                'max:150',
            ],
            'descripcion' => [
                'required',
                'string',
            ],
            'precio' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'imagen_principal' => [
                'nullable',
                'string',
                'max:255',
            ],
            'imagen_2' => ['nullable', 'string', 'max:255'],
            'imagen_3' => ['nullable', 'string', 'max:255'],
            'imagen_4' => ['nullable', 'string', 'max:255'],
            'imagen_5' => ['nullable', 'string', 'max:255'],
            'imagen_6' => ['nullable', 'string', 'max:255'],
            'imagen_7' => ['nullable', 'string', 'max:255'],
            'video' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];

        if ($requiereStock) {
            $reglas['stock_fisico'] = [
                'required',
                'integer',
                'min:0',
            ];
        }

        return Validator::make($datos, $reglas)->validate();
    }

    /**
     * Obtiene el perfil de vendedor del usuario.
     */
    private function obtenerVendedor(Usuario $usuario): Vendedor
    {
        if (!$this->usuarioService->estaActivo($usuario)) {
            throw new AuthorizationException(
                'La cuenta debe estar activa para administrar productos.'
            );
        }

        return $this->vendedorService->obtenerPerfil($usuario);
    }

    /**
     * Obtiene un perfil de vendedor activo.
     */
    private function obtenerVendedorActivo(Usuario $usuario): Vendedor
    {
        $vendedor = $this->obtenerVendedor($usuario);

        if ($vendedor->estado !== 'activo') {
            throw new AuthorizationException(
                'El perfil de vendedor debe estar activo para realizar esta operación.'
            );
        }

        return $vendedor;
    }

    /**
     * Comprueba que la categoría exista y esté activa.
     */
    private function validarCategoriaActiva(int $idCategoria): void
    {
        $categoria = Categoria::findOrFail($idCategoria);

        if (!$categoria->activa) {
            throw ValidationException::withMessages([
                'id_categoria' => 'No se pueden publicar productos en una categoría inactiva.',
            ]);
        }
    }

    /**
     * Comprueba que el producto esté activo.
     */
    private function validarProductoActivo(Producto $producto): void
    {
        if (!$producto->activo) {
            throw new AuthorizationException(
                'No se puede editar un producto desactivado.'
            );
        }
    }
}
