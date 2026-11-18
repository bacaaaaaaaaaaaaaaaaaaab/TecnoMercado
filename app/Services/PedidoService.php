<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\Inventario;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Models\Vendedor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class PedidoService
{
    private const ESTADO_INICIAL = 'pendiente';

    private const TOTAL_MAXIMO_CENTAVOS = 999999999999;

    public function __construct(
        private readonly UsuarioService $usuarioService
    ) {}

    /**
     * Generar un pedido general a partir del carrito permanente.
     *
     * Crea una solicitud independiente por vendedor,
     * genera los detalles, reserva el inventario
     * y vacía los artículos del carrito.
     */
    public function generarDesdeCarrito(Usuario $comprador): Pedido
    {
        return DB::transaction(function () use ($comprador) {
            $comprador = $this->obtenerCompradorBloqueado($comprador);

            $carrito = $this->obtenerCarritoBloqueado($comprador);

            $items = $carrito->items()
                ->orderBy('id_producto')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw ValidationException::withMessages([
                    'carrito' => 'No puedes generar un pedido porque tu carrito está vacío.',
                ]);
            }

            $idsProductos = $items
                ->pluck('id_producto')
                ->unique()
                ->sort()
                ->values();

            $productos = Producto::query()
                ->whereIn('id_producto', $idsProductos)
                ->orderBy('id_producto')
                ->lockForUpdate()
                ->get()
                ->keyBy('id_producto');

            if ($productos->count() !== $idsProductos->count()) {
                throw (new ModelNotFoundException())
                    ->setModel(Producto::class);
            }

            $inventarios = Inventario::query()
                ->whereIn('id_producto', $idsProductos)
                ->orderBy('id_producto')
                ->lockForUpdate()
                ->get()
                ->keyBy('id_producto');

            if ($inventarios->count() !== $idsProductos->count()) {
                throw (new ModelNotFoundException())
                    ->setModel(Inventario::class);
            }

            $this->validarProductosActivos($productos);

            $idsVendedores = $productos
                ->pluck('id_vendedor')
                ->unique()
                ->sort()
                ->values();

            $vendedores = Vendedor::query()
                ->whereIn('id_vendedor', $idsVendedores)
                ->orderBy('id_vendedor')
                ->lockForUpdate()
                ->get()
                ->keyBy('id_vendedor');

            if ($vendedores->count() !== $idsVendedores->count()) {
                throw (new ModelNotFoundException())
                    ->setModel(Vendedor::class);
            }

            $idsUsuariosVendedores = $vendedores
                ->pluck('id_usuario')
                ->unique()
                ->sort()
                ->values();

            $usuariosVendedores = Usuario::query()
                ->whereIn('id_usuario', $idsUsuariosVendedores)
                ->orderBy('id_usuario')
                ->lockForUpdate()
                ->get()
                ->keyBy('id_usuario');

            $this->validarVendedoresActivos(
                $vendedores,
                $usuariosVendedores
            );

            $grupos = $this->agruparProductosPorVendedor(
                $items,
                $productos,
                $inventarios
            );

            $totalGeneralCentavos = array_sum(
                array_column($grupos, 'subtotal_centavos')
            );

            $this->validarImporteMaximo($totalGeneralCentavos);

            $pedido = $comprador->pedidos()->create([
                'fecha_solicitud' => now(),
                'total_referencia' => $this->formatearCentavos(
                    $totalGeneralCentavos
                ),
                'estado_general' => self::ESTADO_INICIAL,
            ]);

            foreach ($grupos as $idVendedor => $grupo) {
                $solicitud = $pedido->pedidosVendedor()->create([
                    'id_vendedor' => $idVendedor,
                    'subtotal_referencia' => $this->formatearCentavos(
                        $grupo['subtotal_centavos']
                    ),
                    'estado' => self::ESTADO_INICIAL,
                ]);

                foreach ($grupo['items'] as $detalle) {
                    $solicitud->detalles()->create([
                        'id_producto' => $detalle['producto']->id_producto,
                        'nombre_producto' => $detalle['producto']->nombre,
                        'cantidad' => $detalle['cantidad'],
                        'precio_unitario_referencia' => $this->formatearCentavos(
                            $detalle['precio_centavos']
                        ),
                    ]);
                }
            }

            foreach ($grupos as $grupo) {
                foreach ($grupo['items'] as $detalle) {
                    $inventario = $inventarios->get(
                        $detalle['producto']->id_producto
                    );

                    $inventario->stock_reservado += $detalle['cantidad'];
                    $inventario->save();
                }
            }

            $carrito->items()->delete();

            return $pedido->load([
                'comprador',
                'pedidosVendedor.vendedor',
                'pedidosVendedor.detalles.producto',
            ]);
        }, 3);
    }

    /**
     * Obtener y bloquear al comprador.
     */
    private function obtenerCompradorBloqueado(
        Usuario $comprador
    ): Usuario {
        $compradorActual = Usuario::query()
            ->whereKey($comprador->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        if (! $this->usuarioService->estaActivo($compradorActual)) {
            throw new AuthorizationException(
                'La cuenta debe estar activa para generar pedidos.'
            );
        }

        if (! $this->usuarioService->tieneRol(
            $compradorActual,
            'comprador'
        )) {
            throw new AuthorizationException(
                'La cuenta no tiene autorización para realizar compras.'
            );
        }

        return $compradorActual;
    }

    /**
     * Obtener y bloquear el carrito permanente.
     */
    private function obtenerCarritoBloqueado(
        Usuario $comprador
    ): Carrito {
        $carritos = $comprador->carritos()
            ->lockForUpdate()
            ->get();

        if ($carritos->isEmpty()) {
            throw (new ModelNotFoundException())
                ->setModel(Carrito::class);
        }

        if ($carritos->count() > 1) {
            throw new LogicException(
                'El comprador tiene más de un carrito registrado. '
                . 'Debe corregirse esta inconsistencia.'
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
     * Comprobar que todos los productos estén activos.
     */
    private function validarProductosActivos($productos): void
    {
        foreach ($productos as $producto) {
            if (! $producto->activo) {
                throw ValidationException::withMessages([
                    'carrito' => "El producto {$producto->nombre} ya no está disponible.",
                ]);
            }
        }
    }

    /**
     * Comprobar que los perfiles y cuentas de vendedores estén activos.
     */
    private function validarVendedoresActivos(
        $vendedores,
        $usuariosVendedores
    ): void {
        foreach ($vendedores as $vendedor) {
            $usuarioVendedor = $usuariosVendedores->get(
                $vendedor->id_usuario
            );

            if (
                ! $usuarioVendedor
                || ! $usuarioVendedor->activo
                || $vendedor->estado !== 'activo'
            ) {
                throw ValidationException::withMessages([
                    'carrito' => 'Uno de los vendedores tiene su cuenta o perfil comercial inactivo. No se puede generar el pedido.',
                ]);
            }
        }
    }

    /**
     * Agrupar productos por vendedor y comprobar existencias.
     *
     * Los importes se calculan en centavos enteros.
     */
    private function agruparProductosPorVendedor(
        $items,
        $productos,
        $inventarios
    ): array {
        $grupos = [];

        foreach ($items as $item) {
            $producto = $productos->get($item->id_producto);

            $inventario = $inventarios->get($item->id_producto);

            $cantidad = (int) $item->cantidad;

            if ($cantidad < 1) {
                throw ValidationException::withMessages([
                    'carrito' => 'El carrito contiene una cantidad inválida.',
                ]);
            }

            $stockFisico = (int) $inventario->stock_fisico;
            $stockReservado = (int) $inventario->stock_reservado;

            if (
                $stockFisico < 0
                || $stockReservado < 0
                || $stockReservado > $stockFisico
            ) {
                throw ValidationException::withMessages([
                    'carrito' => "El inventario de {$producto->nombre} presenta inconsistencias.",
                ]);
            }

            $stockDisponible = $stockFisico - $stockReservado;

            if ($cantidad > $stockDisponible) {
                throw ValidationException::withMessages([
                    'carrito' => "No existe suficiente stock disponible de {$producto->nombre}.",
                ]);
            }

            $precioCentavos = $this->convertirPrecioACentavos(
                (string) $producto->precio
            );

            if ($precioCentavos > intdiv(PHP_INT_MAX, $cantidad)) {
                throw ValidationException::withMessages([
                    'carrito' => 'El importe de uno de los productos supera el límite permitido.',
                ]);
            }

            $subtotalCentavos = $precioCentavos * $cantidad;

            $idVendedor = (int) $producto->id_vendedor;

            if (! isset($grupos[$idVendedor])) {
                $grupos[$idVendedor] = [
                    'subtotal_centavos' => 0,
                    'items' => [],
                ];
            }

            $grupos[$idVendedor]['subtotal_centavos']
                += $subtotalCentavos;

            $this->validarImporteMaximo(
                $grupos[$idVendedor]['subtotal_centavos']
            );

            $grupos[$idVendedor]['items'][] = [
                'producto' => $producto,
                'cantidad' => $cantidad,
                'precio_centavos' => $precioCentavos,
            ];
        }

        return $grupos;
    }

    /**
     * Convertir un precio decimal a centavos sin utilizar float.
     */
    private function convertirPrecioACentavos(string $precio): int
    {
        if (! preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $precio)) {
            throw ValidationException::withMessages([
                'carrito' => 'Uno de los productos tiene un precio inválido.',
            ]);
        }

        [$pesos, $centavos] = array_pad(
            explode('.', $precio, 2),
            2,
            '0'
        );

        $centavos = str_pad($centavos, 2, '0');

        $resultado = ((int) $pesos * 100) + (int) $centavos;

        if ($resultado <= 0) {
            throw ValidationException::withMessages([
                'carrito' => 'Uno de los productos tiene un precio inválido.',
            ]);
        }

        return $resultado;
    }

    /**
     * Comprobar que el importe no exceda DECIMAL(12,2).
     */
    private function validarImporteMaximo(int $centavos): void
    {
        if (
            $centavos < 0
            || $centavos > self::TOTAL_MAXIMO_CENTAVOS
        ) {
            throw ValidationException::withMessages([
                'carrito' => 'El importe total supera el límite permitido para generar el pedido.',
            ]);
        }
    }

    /**
     * Convertir centavos enteros a una cadena decimal.
     */
    private function formatearCentavos(int $centavos): string
    {
        return intdiv($centavos, 100)
            . '.'
            . str_pad(
                (string) ($centavos % 100),
                2,
                '0',
                STR_PAD_LEFT
            );
    }
}
