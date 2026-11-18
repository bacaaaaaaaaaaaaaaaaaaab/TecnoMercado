<?php

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\ItemCarrito;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\PedidoService;
use App\Services\ProductoService;
use App\Services\UsuarioService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;

uses(DatabaseTransactions::class);

/*
|--------------------------------------------------------------------------
| Funciones auxiliares para las pruebas de pedidos
|--------------------------------------------------------------------------
*/

function crearCompradorParaPedidoTest(): Usuario
{
    $usuario = Usuario::factory()->create();

    app(UsuarioService::class)->asignarRol(
        $usuario,
        'comprador'
    );

    Carrito::create([
        'id_usuario' => $usuario->id_usuario,
        'estado' => 'activo',
    ]);

    return $usuario;
}

function crearVendedorParaPedidoTest(): Usuario
{
    $usuario = Usuario::factory()->create();

    app(UsuarioService::class)->convertirEnVendedor(
        $usuario,
        'Tienda para pruebas de pedidos'
    );

    return $usuario;
}

function crearProductoParaPedidoTest(
    Usuario $vendedor,
    float $precio = 150.00,
    int $stockInicial = 10
): Producto {
    $categoria = Categoria::create([
        'nombre' => fake()->unique()->word(),
        'descripcion' => 'Categoría para pruebas de pedidos',
        'activa' => true,
    ]);

    return app(ProductoService::class)->crear($vendedor, [
        'id_categoria' => $categoria->id_categoria,
        'nombre' => 'Producto para pruebas de pedidos',
        'descripcion' => 'Producto utilizado en pruebas automatizadas',
        'precio' => $precio,
        'stock_fisico' => $stockInicial,
    ]);
}

function agregarProductoParaPedidoTest(
    Usuario $comprador,
    Producto $producto,
    int $cantidad
): ItemCarrito {
    $carrito = $comprador->carritos()->firstOrFail();

    return ItemCarrito::create([
        'id_carrito' => $carrito->id_carrito,
        'id_producto' => $producto->id_producto,
        'cantidad' => $cantidad,
    ]);
}

/*
|--------------------------------------------------------------------------
| Generación de pedidos
|--------------------------------------------------------------------------
*/

test('un comprador puede generar un pedido con un vendedor', function () {
    $comprador = crearCompradorParaPedidoTest();
    $vendedor = crearVendedorParaPedidoTest();

    $producto = crearProductoParaPedidoTest(
        $vendedor,
        150.00,
        10
    );

    agregarProductoParaPedidoTest(
        $comprador,
        $producto,
        2
    );

    $pedido = app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    );

    expect($pedido)
        ->toBeInstanceOf(Pedido::class)
        ->and($pedido->estado_general)->toBe('pendiente')
        ->and($pedido->total_referencia)->toBe('300.00')
        ->and($pedido->pedidosVendedor)->toHaveCount(1);

    $solicitud = $pedido->pedidosVendedor->first();

    expect($solicitud->estado)->toBe('pendiente')
        ->and($solicitud->subtotal_referencia)->toBe('300.00')
        ->and($solicitud->detalles)->toHaveCount(1)
        ->and($solicitud->detalles->first()->cantidad)->toBe(2);
});

test('un comprador puede generar un pedido con varios productos del mismo vendedor', function () {
    $comprador = crearCompradorParaPedidoTest();
    $vendedor = crearVendedorParaPedidoTest();

    $productoUno = crearProductoParaPedidoTest(
        $vendedor,
        100.00,
        10
    );

    $productoDos = crearProductoParaPedidoTest(
        $vendedor,
        50.00,
        10
    );

    agregarProductoParaPedidoTest(
        $comprador,
        $productoUno,
        2
    );

    agregarProductoParaPedidoTest(
        $comprador,
        $productoDos,
        1
    );

    $pedido = app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    );

    expect($pedido->total_referencia)->toBe('250.00')
        ->and($pedido->pedidosVendedor)->toHaveCount(1)
        ->and($pedido->pedidosVendedor->first()->detalles)->toHaveCount(2);
});

test('un comprador puede generar un pedido general con varios vendedores', function () {
    $comprador = crearCompradorParaPedidoTest();

    $vendedorUno = crearVendedorParaPedidoTest();
    $vendedorDos = crearVendedorParaPedidoTest();

    $productoUno = crearProductoParaPedidoTest(
        $vendedorUno,
        100.00,
        10
    );

    $productoDos = crearProductoParaPedidoTest(
        $vendedorUno,
        50.00,
        10
    );

    $productoTres = crearProductoParaPedidoTest(
        $vendedorDos,
        25.50,
        10
    );

    agregarProductoParaPedidoTest(
        $comprador,
        $productoUno,
        2
    );

    agregarProductoParaPedidoTest(
        $comprador,
        $productoDos,
        1
    );

    agregarProductoParaPedidoTest(
        $comprador,
        $productoTres,
        2
    );

    $pedido = app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    );

    $solicitudes = $pedido->pedidosVendedor;

    expect($pedido->total_referencia)->toBe('301.00')
        ->and($pedido->estado_general)->toBe('pendiente')
        ->and($solicitudes)->toHaveCount(2);

    $subtotales = $solicitudes
        ->pluck('subtotal_referencia')
        ->sort()
        ->values()
        ->all();

    expect($subtotales)->toBe([
        '51.00',
        '250.00',
    ]);

    foreach ($solicitudes as $solicitud) {
        expect($solicitud->estado)->toBe('pendiente');
    }
});

/*
|--------------------------------------------------------------------------
| Reserva de inventario
|--------------------------------------------------------------------------
*/

test('generar un pedido aumenta el stock reservado sin reducir el físico', function () {
    $comprador = crearCompradorParaPedidoTest();
    $vendedor = crearVendedorParaPedidoTest();

    $producto = crearProductoParaPedidoTest(
        $vendedor,
        150.00,
        10
    );

    agregarProductoParaPedidoTest(
        $comprador,
        $producto,
        4
    );

    app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    );

    $inventario = $producto->inventario()->firstOrFail();

    expect($inventario->stock_fisico)->toBe(10)
        ->and($inventario->stock_reservado)->toBe(4)
        ->and($inventario->stock_disponible)->toBe(6);
});

test('la generación de pedidos considera las reservas anteriores', function () {
    $comprador = crearCompradorParaPedidoTest();
    $vendedor = crearVendedorParaPedidoTest();

    $producto = crearProductoParaPedidoTest(
        $vendedor,
        100.00,
        10
    );

    $producto->inventario->update([
        'stock_reservado' => 8,
    ]);

    agregarProductoParaPedidoTest(
        $comprador,
        $producto,
        3
    );

    expect(fn () => app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    ))->toThrow(ValidationException::class);

    $inventario = $producto->inventario()->firstOrFail();

    expect($inventario->stock_fisico)->toBe(10)
        ->and($inventario->stock_reservado)->toBe(8)
        ->and(Pedido::count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Datos históricos del pedido
|--------------------------------------------------------------------------
*/

test('el pedido conserva el nombre y precio originales del producto', function () {
    $comprador = crearCompradorParaPedidoTest();
    $vendedor = crearVendedorParaPedidoTest();

    $producto = crearProductoParaPedidoTest(
        $vendedor,
        19.90,
        10
    );

    $nombreOriginal = $producto->nombre;

    agregarProductoParaPedidoTest(
        $comprador,
        $producto,
        2
    );

    $pedido = app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    );

    $detalle = $pedido->pedidosVendedor
        ->first()
        ->detalles
        ->first();

    $producto->update([
        'nombre' => 'Producto modificado posteriormente',
        'precio' => 99.90,
    ]);

    $detalle->refresh();

    expect($detalle->nombre_producto)->toBe($nombreOriginal)
        ->and($detalle->precio_unitario_referencia)->toBe('19.90')
        ->and($detalle->cantidad)->toBe(2)
        ->and($pedido->total_referencia)->toBe('39.80');
});

/*
|--------------------------------------------------------------------------
| Conservación y vaciado del carrito
|--------------------------------------------------------------------------
*/

test('generar un pedido vacía los artículos pero conserva el carrito', function () {
    $comprador = crearCompradorParaPedidoTest();
    $vendedor = crearVendedorParaPedidoTest();

    $producto = crearProductoParaPedidoTest(
        $vendedor,
        100.00,
        10
    );

    agregarProductoParaPedidoTest(
        $comprador,
        $producto,
        2
    );

    $carritoOriginal = $comprador->carritos()->firstOrFail();

    app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    );

    $carrito = $comprador->carritos()->firstOrFail();

    expect($carrito->id_carrito)->toBe($carritoOriginal->id_carrito)
        ->and($carrito->estado)->toBe('activo')
        ->and($carrito->items()->count())->toBe(0)
        ->and($comprador->carritos()->count())->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Validaciones del carrito y productos
|--------------------------------------------------------------------------
*/

test('no se puede generar un pedido con el carrito vacío', function () {
    $comprador = crearCompradorParaPedidoTest();

    expect(fn () => app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    ))->toThrow(ValidationException::class);

    expect(Pedido::count())->toBe(0);
});

test('no se puede generar un pedido con un producto inactivo', function () {
    $comprador = crearCompradorParaPedidoTest();
    $vendedor = crearVendedorParaPedidoTest();

    $producto = crearProductoParaPedidoTest($vendedor);

    agregarProductoParaPedidoTest(
        $comprador,
        $producto,
        1
    );

    $producto->update([
        'activo' => false,
    ]);

    expect(fn () => app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    ))->toThrow(ValidationException::class);

    expect(Pedido::count())->toBe(0)
        ->and($producto->inventario()->firstOrFail()->stock_reservado)->toBe(0)
        ->and($comprador->carritos()->first()->items()->count())->toBe(1);
});

test('no se puede generar un pedido con stock físico insuficiente', function () {
    $comprador = crearCompradorParaPedidoTest();
    $vendedor = crearVendedorParaPedidoTest();

    $producto = crearProductoParaPedidoTest(
        $vendedor,
        100.00,
        5
    );

    agregarProductoParaPedidoTest(
        $comprador,
        $producto,
        6
    );

    expect(fn () => app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    ))->toThrow(ValidationException::class);

    expect(Pedido::count())->toBe(0)
        ->and($producto->inventario()->firstOrFail()->stock_reservado)->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Validaciones de comprador
|--------------------------------------------------------------------------
*/

test('una cuenta inactiva no puede generar pedidos', function () {
    $comprador = crearCompradorParaPedidoTest();

    $comprador->activo = false;
    $comprador->save();

    expect(fn () => app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    ))->toThrow(AuthorizationException::class);

    expect(Pedido::count())->toBe(0);
});

test('un usuario sin rol comprador no puede generar pedidos', function () {
    $usuario = Usuario::factory()->create();

    Carrito::create([
        'id_usuario' => $usuario->id_usuario,
        'estado' => 'activo',
    ]);

    expect(fn () => app(PedidoService::class)->generarDesdeCarrito(
        $usuario
    ))->toThrow(AuthorizationException::class);

    expect(Pedido::count())->toBe(0);
});

test('un usuario sin carrito no puede generar pedidos', function () {
    $comprador = Usuario::factory()->create();

    app(UsuarioService::class)->asignarRol(
        $comprador,
        'comprador'
    );

    expect(fn () => app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    ))->toThrow(ModelNotFoundException::class);

    expect(Pedido::count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Validaciones de vendedores
|--------------------------------------------------------------------------
*/

test('no se puede generar un pedido si la cuenta del vendedor está inactiva', function () {
    $comprador = crearCompradorParaPedidoTest();
    $vendedor = crearVendedorParaPedidoTest();

    $producto = crearProductoParaPedidoTest($vendedor);

    agregarProductoParaPedidoTest(
        $comprador,
        $producto,
        1
    );

    $vendedor->activo = false;
    $vendedor->save();

    expect(fn () => app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    ))->toThrow(ValidationException::class);

    expect(Pedido::count())->toBe(0);
});

test('no se puede generar un pedido si el perfil del vendedor está inactivo', function () {
    $comprador = crearCompradorParaPedidoTest();
    $vendedor = crearVendedorParaPedidoTest();

    $producto = crearProductoParaPedidoTest($vendedor);

    agregarProductoParaPedidoTest(
        $comprador,
        $producto,
        1
    );

    app(UsuarioService::class)->desactivarPerfilVendedor(
        $vendedor
    );

    expect(fn () => app(PedidoService::class)->generarDesdeCarrito(
        $comprador
    ))->toThrow(ValidationException::class);

    expect(Pedido::count())->toBe(0);
});
