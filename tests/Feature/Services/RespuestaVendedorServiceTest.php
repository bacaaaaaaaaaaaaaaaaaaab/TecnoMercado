<?php

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\HistorialRespuestaProducto;
use App\Models\Inventario;
use App\Models\ItemCarrito;
use App\Models\Notificacion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\PedidoService;
use App\Services\ProductoService;
use App\Services\RespuestaVendedorService;
use App\Services\UsuarioService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;

uses(DatabaseTransactions::class);

function crearCompradorRespuestaTest(): Usuario
{
    $usuario = Usuario::factory()->create();

    app(UsuarioService::class)->asignarRol($usuario, 'comprador');

    Carrito::create([
        'id_usuario' => $usuario->id_usuario,
        'estado' => 'activo',
    ]);

    return $usuario;
}

function crearVendedorRespuestaTest(): Usuario
{
    $usuario = Usuario::factory()->create();

    app(UsuarioService::class)->convertirEnVendedor(
        $usuario,
        'Tienda de respuestas de prueba'
    );

    return $usuario;
}

function crearPedidoRespuestaTest(
    ?Usuario $comprador = null,
    ?Usuario $vendedor = null,
    int $cantidad = 2
): array {
    $comprador ??= crearCompradorRespuestaTest();
    $vendedor ??= crearVendedorRespuestaTest();

    $categoria = Categoria::create([
        'nombre' => fake()->unique()->word(),
        'descripcion' => 'Categoría de prueba',
        'activa' => true,
    ]);

    $producto = app(ProductoService::class)->crear($vendedor, [
        'id_categoria' => $categoria->id_categoria,
        'nombre' => 'Producto de prueba',
        'descripcion' => 'Producto para probar respuestas',
        'precio' => 100,
        'stock_fisico' => 10,
    ]);

    $carrito = $comprador->carritos()->firstOrFail();

    ItemCarrito::create([
        'id_carrito' => $carrito->id_carrito,
        'id_producto' => $producto->id_producto,
        'cantidad' => $cantidad,
    ]);

    $pedido = app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    );

    $solicitud = $pedido->pedidosVendedor->first();
    $detalle = $solicitud->detalles->first();

    return compact(
        'comprador',
        'vendedor',
        'producto',
        'pedido',
        'solicitud',
        'detalle'
    );
}

test('el vendedor puede aceptar un producto y se descuenta el inventario', function () {
    $datos = crearPedidoRespuestaTest();

    $resultado = app(RespuestaVendedorService::class)
        ->responderProducto($datos['vendedor'], $datos['detalle']->id_detalle_pedido, [
            'estado_respuesta' => 'aceptado',
            'disponibilidad_producto' => true,
        ]);

    $inventario = Inventario::where(
        'id_producto',
        $datos['producto']->id_producto
    )->firstOrFail();

    expect($resultado->estado_respuesta)->toBe('aceptado')
        ->and($inventario->stock_fisico)->toBe(8)
        ->and($inventario->stock_reservado)->toBe(0)
        ->and($datos['solicitud']->fresh()->estado)->toBe('aceptado')
        ->and(Notificacion::where('id_usuario', $datos['comprador']->id_usuario)->count())->toBe(1)
        ->and(HistorialRespuestaProducto::count())->toBe(1);
});

test('el vendedor puede rechazar un producto y liberar solamente la reserva', function () {
    $datos = crearPedidoRespuestaTest();

    app(RespuestaVendedorService::class)
        ->responderProducto($datos['vendedor'], $datos['detalle']->id_detalle_pedido, [
            'estado_respuesta' => 'rechazado',
            'disponibilidad_producto' => true,
            'motivo_respuesta' => 'El vendedor decidió no realizar la venta.',
        ]);

    $inventario = Inventario::where(
        'id_producto',
        $datos['producto']->id_producto
    )->firstOrFail();

    expect($inventario->stock_fisico)->toBe(10)
        ->and($inventario->stock_reservado)->toBe(0)
        ->and($datos['solicitud']->fresh()->estado)->toBe('rechazado')
        ->and(Notificacion::where('id_usuario', $datos['comprador']->id_usuario)->exists())->toBeTrue();
});

test('un producto no disponible puede dar de baja su publicación', function () {
    $datos = crearPedidoRespuestaTest();

    app(RespuestaVendedorService::class)
        ->responderProducto($datos['vendedor'], $datos['detalle']->id_detalle_pedido, [
            'estado_respuesta' => 'rechazado',
            'disponibilidad_producto' => false,
            'motivo_respuesta' => 'El producto ya fue vendido por fuera.',
            'dar_de_baja_publicacion' => true,
        ]);

    expect($datos['producto']->fresh()->activo)->toBeFalse()
        ->and($datos['detalle']->fresh()->estado_respuesta)->toBe('rechazado');
});

test('un producto no disponible exige una decisión sobre su publicación', function () {
    $datos = crearPedidoRespuestaTest();

    expect(fn () => app(RespuestaVendedorService::class)
        ->responderProducto($datos['vendedor'], $datos['detalle']->id_detalle_pedido, [
            'estado_respuesta' => 'rechazado',
            'disponibilidad_producto' => false,
            'motivo_respuesta' => 'No tengo existencias.',
        ]))->toThrow(ValidationException::class);

    expect($datos['detalle']->fresh()->estado_respuesta)->toBe('pendiente')
        ->and(HistorialRespuestaProducto::count())->toBe(0);
});

test('mantener publicada una oferta no disponible exige justificación', function () {
    $datos = crearPedidoRespuestaTest();

    expect(fn () => app(RespuestaVendedorService::class)
        ->responderProducto($datos['vendedor'], $datos['detalle']->id_detalle_pedido, [
            'estado_respuesta' => 'rechazado',
            'disponibilidad_producto' => false,
            'motivo_respuesta' => 'No tengo existencias.',
            'dar_de_baja_publicacion' => false,
        ]))->toThrow(ValidationException::class);

    expect($datos['detalle']->fresh()->estado_respuesta)->toBe('pendiente');
});

test('un vendedor diferente no puede responder la solicitud', function () {
    $datos = crearPedidoRespuestaTest();
    $otroVendedor = crearVendedorRespuestaTest();

    expect(fn () => app(RespuestaVendedorService::class)
        ->responderProducto($otroVendedor, $datos['detalle']->id_detalle_pedido, [
            'estado_respuesta' => 'aceptado',
            'disponibilidad_producto' => true,
        ]))->toThrow(AuthorizationException::class);

    expect($datos['detalle']->fresh()->estado_respuesta)->toBe('pendiente');
});

test('un producto no puede recibir dos respuestas', function () {
    $datos = crearPedidoRespuestaTest();

    $servicio = app(RespuestaVendedorService::class);

    $servicio->responderProducto(
        $datos['vendedor'],
        $datos['detalle']->id_detalle_pedido,
        [
            'estado_respuesta' => 'aceptado',
            'disponibilidad_producto' => true,
        ]
    );

    expect(fn () => $servicio->responderProducto(
        $datos['vendedor'],
        $datos['detalle']->id_detalle_pedido,
        [
            'estado_respuesta' => 'rechazado',
            'disponibilidad_producto' => true,
            'motivo_respuesta' => 'Otra respuesta.',
        ]
    ))->toThrow(LogicException::class);

    expect(HistorialRespuestaProducto::count())->toBe(1)
        ->and(Notificacion::count())->toBe(1);
});
