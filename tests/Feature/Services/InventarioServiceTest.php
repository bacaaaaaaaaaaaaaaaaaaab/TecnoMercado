<?php

use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\InventarioService;
use App\Services\ProductoService;
use App\Services\UsuarioService;
use App\Services\VendedorService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;

uses(DatabaseTransactions::class);

/*
|--------------------------------------------------------------------------
| Funciones auxiliares para las pruebas de inventario
|--------------------------------------------------------------------------
*/

function crearVendedorParaInventarioTest(): Usuario
{
    $usuario = Usuario::factory()->create();

    app(UsuarioService::class)->convertirEnVendedor(
        $usuario,
        'Tienda de inventario'
    );

    return $usuario;
}

function crearCategoriaParaInventarioTest(): Categoria
{
    return Categoria::create([
        'nombre' => fake()->unique()->word(),
        'descripcion' => 'Categoria para pruebas de inventario',
        'activa' => true,
    ]);
}

function crearProductoParaInventarioTest(
    Usuario $usuario,
    int $stockInicial = 10
): Producto {
    $categoria = crearCategoriaParaInventarioTest();

    return app(ProductoService::class)->crear($usuario, [
        'id_categoria' => $categoria->id_categoria,
        'nombre' => 'Producto para inventario',
        'descripcion' => 'Producto utilizado en pruebas',
        'precio' => 150.00,
        'stock_fisico' => $stockInicial,
    ]);
}

/*
|--------------------------------------------------------------------------
| Consultar inventario
|--------------------------------------------------------------------------
*/

test('un vendedor puede consultar el inventario de su producto', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario, 10);

    $inventario = app(InventarioService::class)->consultar(
        $usuario,
        $producto->id_producto
    );

    expect($inventario)->toBeInstanceOf(Inventario::class)
        ->and($inventario->stock_fisico)->toBe(10)
        ->and($inventario->stock_reservado)->toBe(0)
        ->and($inventario->stock_disponible)->toBe(10);
});

test('el stock disponible se calcula restando las unidades reservadas', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario, 20);

    $producto->inventario->update([
        'stock_reservado' => 5,
    ]);

    $inventario = app(InventarioService::class)->consultar(
        $usuario,
        $producto->id_producto
    );

    expect($inventario->stock_fisico)->toBe(20)
        ->and($inventario->stock_reservado)->toBe(5)
        ->and($inventario->stock_disponible)->toBe(15);
});

/*
|--------------------------------------------------------------------------
| Aumentar stock físico
|--------------------------------------------------------------------------
*/

test('un vendedor puede aumentar el stock físico de su producto', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario, 10);

    $inventario = app(InventarioService::class)->aumentarStock(
        $usuario,
        $producto->id_producto,
        5
    );

    expect($inventario->stock_fisico)->toBe(15)
        ->and($inventario->stock_reservado)->toBe(0)
        ->and($inventario->stock_disponible)->toBe(15);
});

test('aumentar el stock no modifica las unidades reservadas', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario, 20);

    $producto->inventario->update([
        'stock_reservado' => 5,
    ]);

    $inventario = app(InventarioService::class)->aumentarStock(
        $usuario,
        $producto->id_producto,
        10
    );

    expect($inventario->stock_fisico)->toBe(30)
        ->and($inventario->stock_reservado)->toBe(5)
        ->and($inventario->stock_disponible)->toBe(25);
});

/*
|--------------------------------------------------------------------------
| Reducir stock físico
|--------------------------------------------------------------------------
*/

test('un vendedor puede reducir el stock disponible de su producto', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario, 20);

    $producto->inventario->update([
        'stock_reservado' => 5,
    ]);

    $inventario = app(InventarioService::class)->reducirStock(
        $usuario,
        $producto->id_producto,
        10
    );

    expect($inventario->stock_fisico)->toBe(10)
        ->and($inventario->stock_reservado)->toBe(5)
        ->and($inventario->stock_disponible)->toBe(5);
});

test('no se puede reducir el stock físico por debajo del reservado', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario, 10);

    $producto->inventario->update([
        'stock_reservado' => 5,
    ]);

    expect(fn () => app(InventarioService::class)->reducirStock(
        $usuario,
        $producto->id_producto,
        6
    ))->toThrow(ValidationException::class);

    $inventario = $producto->inventario()->firstOrFail();

    expect($inventario->stock_fisico)->toBe(10)
        ->and($inventario->stock_reservado)->toBe(5);
});

/*
|--------------------------------------------------------------------------
| Actualizar stock físico
|--------------------------------------------------------------------------
*/

test('un vendedor puede establecer una nueva cantidad de stock físico', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario, 10);

    $inventario = app(InventarioService::class)->actualizarStockFisico(
        $usuario,
        $producto->id_producto,
        25
    );

    expect($inventario->stock_fisico)->toBe(25)
        ->and($inventario->stock_reservado)->toBe(0)
        ->and($inventario->stock_disponible)->toBe(25);
});

test('se puede actualizar el stock físico hasta la cantidad reservada', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario, 20);

    $producto->inventario->update([
        'stock_reservado' => 5,
    ]);

    $inventario = app(InventarioService::class)->actualizarStockFisico(
        $usuario,
        $producto->id_producto,
        5
    );

    expect($inventario->stock_fisico)->toBe(5)
        ->and($inventario->stock_reservado)->toBe(5)
        ->and($inventario->stock_disponible)->toBe(0);
});

test('no se puede establecer un stock físico menor al reservado', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario, 20);

    $producto->inventario->update([
        'stock_reservado' => 5,
    ]);

    expect(fn () => app(InventarioService::class)->actualizarStockFisico(
        $usuario,
        $producto->id_producto,
        4
    ))->toThrow(ValidationException::class);
});

/*
|--------------------------------------------------------------------------
| Validaciones de cantidades
|--------------------------------------------------------------------------
*/

test('no se puede aumentar el stock con una cantidad igual a cero', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario);

    expect(fn () => app(InventarioService::class)->aumentarStock(
        $usuario,
        $producto->id_producto,
        0
    ))->toThrow(ValidationException::class);
});

test('no se puede reducir el stock con una cantidad negativa', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario);

    expect(fn () => app(InventarioService::class)->reducirStock(
        $usuario,
        $producto->id_producto,
        -1
    ))->toThrow(ValidationException::class);
});

test('no se puede establecer un stock físico negativo', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario);

    expect(fn () => app(InventarioService::class)->actualizarStockFisico(
        $usuario,
        $producto->id_producto,
        -1
    ))->toThrow(ValidationException::class);
});

/*
|--------------------------------------------------------------------------
| Propiedad y permisos
|--------------------------------------------------------------------------
*/

test('un vendedor no puede consultar el inventario de otro vendedor', function () {
    $propietario = crearVendedorParaInventarioTest();
    $otroVendedor = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($propietario);

    expect(fn () => app(InventarioService::class)->consultar(
        $otroVendedor,
        $producto->id_producto
    ))->toThrow(ModelNotFoundException::class);
});

test('un vendedor no puede modificar el inventario de otro vendedor', function () {
    $propietario = crearVendedorParaInventarioTest();
    $otroVendedor = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($propietario);

    expect(fn () => app(InventarioService::class)->aumentarStock(
        $otroVendedor,
        $producto->id_producto,
        5
    ))->toThrow(ModelNotFoundException::class);
});

test('una cuenta inactiva no puede administrar inventarios', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario);

    $usuario->activo = false;
    $usuario->save();

    expect(fn () => app(InventarioService::class)->consultar(
        $usuario,
        $producto->id_producto
    ))->toThrow(AuthorizationException::class);
});

test('un perfil de vendedor inactivo no puede modificar inventarios', function () {
    $usuario = crearVendedorParaInventarioTest();

    $producto = crearProductoParaInventarioTest($usuario);

    app(VendedorService::class)->desactivarPerfil($usuario);

    expect(fn () => app(InventarioService::class)->aumentarStock(
        $usuario,
        $producto->id_producto,
        5
    ))->toThrow(AuthorizationException::class);
});
