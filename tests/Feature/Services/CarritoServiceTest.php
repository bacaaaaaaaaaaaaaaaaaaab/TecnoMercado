<?php

use App\Models\Carrito;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\CarritoService;
use App\Services\ProductoService;
use App\Services\UsuarioService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;


uses(DatabaseTransactions::class);

/*
|--------------------------------------------------------------------------
| Funciones auxiliares para las pruebas del carrito
|--------------------------------------------------------------------------
*/

function crearUsuarioParaCarritoTest(): Usuario
{
    $usuario = Usuario::factory()->create();

    Carrito::create([
        'id_usuario' => $usuario->id_usuario,
        'estado' => 'activo',
    ]);

    return $usuario;
}

function crearVendedorParaCarritoTest(): Usuario
{
    $usuario = crearUsuarioParaCarritoTest();

    app(UsuarioService::class)->convertirEnVendedor(
        $usuario,
        'Tienda para pruebas de carrito'
    );

    return $usuario;
}

function crearProductoParaCarritoTest(
    Usuario $usuario,
    float $precio = 150.00,
    int $stockInicial = 10
): Producto {
    $categoria = Categoria::create([
        'nombre' => fake()->unique()->word(),
        'descripcion' => 'Categoría para pruebas de carrito',
        'activa' => true,
    ]);

    return app(ProductoService::class)->crear($usuario, [
        'id_categoria' => $categoria->id_categoria,
        'nombre' => 'Producto para pruebas de carrito',
        'descripcion' => 'Producto utilizado en pruebas automatizadas',
        'precio' => $precio,
        'stock_fisico' => $stockInicial,
    ]);
}

/*
|--------------------------------------------------------------------------
| Consultar carrito
|--------------------------------------------------------------------------
*/

test('un usuario puede consultar su carrito permanente', function () {
    $usuario = crearUsuarioParaCarritoTest();

    $carrito = app(CarritoService::class)->consultar($usuario);

    expect($carrito)
        ->toBeInstanceOf(Carrito::class)
        ->and($carrito->id_usuario)->toBe($usuario->id_usuario)
        ->and($carrito->estado)->toBe('activo')
        ->and($carrito->items)->toHaveCount(0);
});

test('consultar el carrito carga las relaciones de sus productos', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor);

    app(CarritoService::class)->agregar(
        $usuario,
        $producto->id_producto,
        2
    );

    $carrito = app(CarritoService::class)->consultar($usuario);

    $item = $carrito->items->first();

    expect($item->relationLoaded('producto'))->toBeTrue()
        ->and($item->producto->relationLoaded('categoria'))->toBeTrue()
        ->and($item->producto->relationLoaded('vendedor'))->toBeTrue()
        ->and($item->producto->relationLoaded('inventario'))->toBeTrue();
});

/*
|--------------------------------------------------------------------------
| Agregar productos
|--------------------------------------------------------------------------
*/

test('un usuario puede agregar un producto al carrito', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor, 150.00, 10);

    $item = app(CarritoService::class)->agregar(
        $usuario,
        $producto->id_producto,
        3
    );

    expect($item->cantidad)->toBe(3)
        ->and($item->id_producto)->toBe($producto->id_producto)
        ->and($usuario->carritos()->first()->items)->toHaveCount(1);
});

test('agregar un producto existente incrementa su cantidad', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor, 150.00, 10);

    $servicio = app(CarritoService::class);

    $servicio->agregar($usuario, $producto->id_producto, 2);
    $item = $servicio->agregar($usuario, $producto->id_producto, 3);

    expect($item->cantidad)->toBe(5)
        ->and($usuario->carritos()->first()->items)->toHaveCount(1);
});

test('agregar productos no modifica ni reserva el inventario', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor, 150.00, 10);

    app(CarritoService::class)->agregar(
        $usuario,
        $producto->id_producto,
        4
    );

    $inventario = $producto->inventario()->firstOrFail();

    expect($inventario->stock_fisico)->toBe(10)
        ->and($inventario->stock_reservado)->toBe(0);
});

test('no se puede agregar un producto inactivo', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor);

    $producto->update([
        'activo' => false,
    ]);

    expect(fn () => app(CarritoService::class)->agregar(
        $usuario,
        $producto->id_producto,
        1
    ))->toThrow(ModelNotFoundException::class);
});

test('no se puede agregar una cantidad superior al stock disponible', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor, 150.00, 10);

    expect(fn () => app(CarritoService::class)->agregar(
        $usuario,
        $producto->id_producto,
        11
    ))->toThrow(ValidationException::class);
});

test('al agregar se considera el stock reservado', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor, 150.00, 10);

    $producto->inventario->update([
        'stock_reservado' => 5,
    ]);

    expect(fn () => app(CarritoService::class)->agregar(
        $usuario,
        $producto->id_producto,
        6
    ))->toThrow(ValidationException::class);
});

test('no se puede agregar una cantidad igual a cero', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor);

    expect(fn () => app(CarritoService::class)->agregar(
        $usuario,
        $producto->id_producto,
        0
    ))->toThrow(ValidationException::class);
});

/*
|--------------------------------------------------------------------------
| Actualizar cantidades
|--------------------------------------------------------------------------
*/

test('un usuario puede actualizar la cantidad de un producto', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor, 150.00, 10);

    $servicio = app(CarritoService::class);

    $servicio->agregar($usuario, $producto->id_producto, 2);

    $item = $servicio->actualizarCantidad(
        $usuario,
        $producto->id_producto,
        5
    );

    expect($item->cantidad)->toBe(5);
});

test('no se puede actualizar una cantidad superior al stock disponible', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor, 150.00, 10);

    $servicio = app(CarritoService::class);

    $servicio->agregar($usuario, $producto->id_producto, 2);

    expect(fn () => $servicio->actualizarCantidad(
        $usuario,
        $producto->id_producto,
        11
    ))->toThrow(ValidationException::class);

    $item = $usuario->carritos()->first()->items()->first();

    expect($item->cantidad)->toBe(2);
});

test('no se puede actualizar una cantidad igual a cero', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor);

    app(CarritoService::class)->agregar(
        $usuario,
        $producto->id_producto,
        2
    );

    expect(fn () => app(CarritoService::class)->actualizarCantidad(
        $usuario,
        $producto->id_producto,
        0
    ))->toThrow(ValidationException::class);
});

/*
|--------------------------------------------------------------------------
| Quitar productos
|--------------------------------------------------------------------------
*/

test('un usuario puede quitar un producto de su carrito', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor);

    $servicio = app(CarritoService::class);

    $servicio->agregar($usuario, $producto->id_producto, 2);

    $resultado = $servicio->quitar(
        $usuario,
        $producto->id_producto
    );

    expect($resultado)->toBeTrue()
        ->and($usuario->carritos()->first()->items)->toHaveCount(0);
});

test('quitar devuelve false cuando el producto no existe en el carrito', function () {
    $usuario = crearUsuarioParaCarritoTest();

    $resultado = app(CarritoService::class)->quitar(
        $usuario,
        999999
    );

    expect($resultado)->toBeFalse();
});

/*
|--------------------------------------------------------------------------
| Vaciar carrito
|--------------------------------------------------------------------------
*/

test('un usuario puede vaciar su carrito sin eliminarlo', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $productoUno = crearProductoParaCarritoTest($vendedor);
    $productoDos = crearProductoParaCarritoTest($vendedor);

    $servicio = app(CarritoService::class);

    $servicio->agregar($usuario, $productoUno->id_producto, 2);
    $servicio->agregar($usuario, $productoDos->id_producto, 3);

    $carritoOriginal = $usuario->carritos()->firstOrFail();

    $cantidadEliminada = $servicio->vaciar($usuario);

    $carrito = $usuario->carritos()->firstOrFail();

    expect($cantidadEliminada)->toBe(2)
        ->and($carrito->id_carrito)->toBe($carritoOriginal->id_carrito)
        ->and($carrito->items()->count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Calcular total
|--------------------------------------------------------------------------
*/

test('el total del carrito se calcula correctamente', function () {
    $usuario = crearUsuarioParaCarritoTest();

    $vendedorUno = crearVendedorParaCarritoTest();
    $vendedorDos = crearVendedorParaCarritoTest();

    $productoUno = crearProductoParaCarritoTest(
        $vendedorUno,
        150.00,
        10
    );

    $productoDos = crearProductoParaCarritoTest(
        $vendedorDos,
        25.50,
        10
    );

    $servicio = app(CarritoService::class);

    $servicio->agregar($usuario, $productoUno->id_producto, 2);
    $servicio->agregar($usuario, $productoDos->id_producto, 3);

    $total = $servicio->calcularTotal($usuario);

    expect($total)->toBe('376.50');
});

/*
|--------------------------------------------------------------------------
| Cuenta y consistencia del carrito
|--------------------------------------------------------------------------
*/

test('una cuenta inactiva no puede consultar su carrito', function () {
    $usuario = crearUsuarioParaCarritoTest();

    $usuario->activo = false;
    $usuario->save();

    expect(fn () => app(CarritoService::class)->consultar(
        $usuario
    ))->toThrow(AuthorizationException::class);
});

test('una cuenta inactiva no puede agregar productos al carrito', function () {
    $usuario = crearUsuarioParaCarritoTest();
    $vendedor = crearVendedorParaCarritoTest();

    $producto = crearProductoParaCarritoTest($vendedor);

    $usuario->activo = false;
    $usuario->save();

    expect(fn () => app(CarritoService::class)->agregar(
        $usuario,
        $producto->id_producto,
        1
    ))->toThrow(AuthorizationException::class);
});

test('se genera una excepción cuando el usuario no tiene carrito', function () {
    $usuario = Usuario::factory()->create();

    expect(fn () => app(CarritoService::class)->consultar(
        $usuario
    ))->toThrow(ModelNotFoundException::class);
});

test('se detecta cuando un usuario tiene carritos duplicados', function () {
    $usuario = crearUsuarioParaCarritoTest();

    Carrito::create([
        'id_usuario' => $usuario->id_usuario,
        'estado' => 'activo',
    ]);

    expect(fn () => app(CarritoService::class)->consultar(
        $usuario
    ))->toThrow(LogicException::class);
});

test('no se puede administrar un carrito que no está activo', function () {
    $usuario = crearUsuarioParaCarritoTest();

    $carrito = $usuario->carritos()->firstOrFail();

    $carrito->update([
        'estado' => 'inactivo',
    ]);

    expect(fn () => app(CarritoService::class)->consultar(
        $usuario
    ))->toThrow(AuthorizationException::class);
});
