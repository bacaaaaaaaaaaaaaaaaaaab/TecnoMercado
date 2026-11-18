<?php

use App\Models\Categoria;
use App\Models\Carrito;
use App\Models\DetallePedido;
use App\Models\ItemCarrito;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\ProductoService;
use App\Services\UsuarioService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Validation\ValidationException;

uses(DatabaseTransactions::class);

function crearVendedorParaPrueba(): Usuario
{
    $usuario = Usuario::factory()->create();

    app(UsuarioService::class)->convertirEnVendedor(
        $usuario,
        'Tienda de prueba'
    );

    return $usuario;
}

function crearCategoriaParaPrueba(bool $activa = true): Categoria
{
    return Categoria::create([
        'nombre' => fake()->unique()->word(),
        'descripcion' => 'Categoria de prueba',
        'activa' => $activa,
    ]);
}

function datosProductoParaPrueba(Categoria $categoria): array
{
    return [
        'id_categoria' => $categoria->id_categoria,
        'nombre' => 'Producto de prueba',
        'descripcion' => 'Descripcion del producto de prueba',
        'precio' => 150.00,
        'stock_fisico' => 10,
    ];
}

test('un vendedor puede crear un producto con su inventario inicial', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $producto = app(ProductoService::class)->crear(
        $usuario,
        datosProductoParaPrueba($categoria)
    );

    expect($producto)->toBeInstanceOf(Producto::class)
        ->and($producto->id_categoria)->toBe($categoria->id_categoria)
        ->and($producto->vendedor->id_usuario)->toBe($usuario->id_usuario)
        ->and($producto->activo)->toBeTrue()
        ->and($producto->inventario->stock_fisico)->toBe(10)
        ->and($producto->inventario->stock_reservado)->toBe(0);
});

test('un producto puede crearse sin imagenes ni video', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $producto = app(ProductoService::class)->crear(
        $usuario,
        datosProductoParaPrueba($categoria)
    );

    expect($producto->imagen_principal)->toBeNull()
        ->and($producto->imagen_2)->toBeNull()
        ->and($producto->video)->toBeNull();
});

test('no se puede publicar en una categoria inactiva', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba(false);

    expect(fn () => app(ProductoService::class)->crear(
        $usuario,
        datosProductoParaPrueba($categoria)
    ))->toThrow(ValidationException::class);
});

test('no se puede crear un producto con precio igual a cero', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $datos = datosProductoParaPrueba($categoria);
    $datos['precio'] = 0;

    expect(fn () => app(ProductoService::class)->crear(
        $usuario,
        $datos
    ))->toThrow(ValidationException::class);
});

test('no se puede crear un producto con stock negativo', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $datos = datosProductoParaPrueba($categoria);
    $datos['stock_fisico'] = -1;

    expect(fn () => app(ProductoService::class)->crear(
        $usuario,
        $datos
    ))->toThrow(ValidationException::class);
});

test('un vendedor puede consultar sus productos', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    app(ProductoService::class)->crear(
        $usuario,
        datosProductoParaPrueba($categoria)
    );

    $productos = app(ProductoService::class)
        ->listarDeVendedor($usuario);

    expect($productos)->toHaveCount(1);
});

test('un vendedor no puede consultar el producto de otro vendedor', function () {
    $propietario = crearVendedorParaPrueba();
    $otroVendedor = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $producto = app(ProductoService::class)->crear(
        $propietario,
        datosProductoParaPrueba($categoria)
    );

    expect(fn () => app(ProductoService::class)->obtener(
        $otroVendedor,
        $producto->id_producto
    ))->toThrow(ModelNotFoundException::class);
});

test('un vendedor puede actualizar su producto sin modificar el inventario', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $producto = app(ProductoService::class)->crear(
        $usuario,
        datosProductoParaPrueba($categoria)
    );

    $producto->inventario->update([
        'stock_fisico' => 20,
        'stock_reservado' => 5,
    ]);

    $datos = datosProductoParaPrueba($categoria);
    unset($datos['stock_fisico']);
    $datos['nombre'] = 'Producto actualizado';
    $datos['precio'] = 200;

    $actualizado = app(ProductoService::class)->actualizar(
        $usuario,
        $producto->id_producto,
        $datos
    );

    expect($actualizado->nombre)->toBe('Producto actualizado')
        ->and($actualizado->precio)->toBe('200.00')
        ->and($actualizado->inventario->stock_fisico)->toBe(20)
        ->and($actualizado->inventario->stock_reservado)->toBe(5);
});

test('la actualizacion ignora campos protegidos', function () {
    $usuario = crearVendedorParaPrueba();
    $otroUsuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $producto = app(ProductoService::class)->crear(
        $usuario,
        datosProductoParaPrueba($categoria)
    );

    $datos = datosProductoParaPrueba($categoria);
    unset($datos['stock_fisico']);

    $datos['id_vendedor'] = $otroUsuario->vendedor->id_vendedor;
    $datos['activo'] = false;
    $datos['stock_reservado'] = 999;

    $actualizado = app(ProductoService::class)->actualizar(
        $usuario,
        $producto->id_producto,
        $datos
    );

    expect($actualizado->id_vendedor)->toBe($producto->id_vendedor)
        ->and($actualizado->activo)->toBeTrue()
        ->and($actualizado->inventario->stock_reservado)->toBe(0);
});

test('un vendedor puede desactivar su producto', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $producto = app(ProductoService::class)->crear(
        $usuario,
        datosProductoParaPrueba($categoria)
    );

    $desactivado = app(ProductoService::class)->desactivar(
        $usuario,
        $producto->id_producto
    );

    expect($desactivado->activo)->toBeFalse()
        ->and(Producto::whereKey($producto->id_producto)->exists())
        ->toBeTrue();
});

test('un vendedor puede reactivar su producto', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $producto = app(ProductoService::class)->crear(
        $usuario,
        datosProductoParaPrueba($categoria)
    );

    app(ProductoService::class)->desactivar(
        $usuario,
        $producto->id_producto
    );

    $reactivado = app(ProductoService::class)->reactivar(
        $usuario,
        $producto->id_producto
    );

    expect($reactivado->activo)->toBeTrue();
});

test('no se puede reactivar un producto cuya categoria esta inactiva', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $producto = app(ProductoService::class)->crear(
        $usuario,
        datosProductoParaPrueba($categoria)
    );

    app(ProductoService::class)->desactivar(
        $usuario,
        $producto->id_producto
    );

    $categoria->update(['activa' => false]);

    expect(fn () => app(ProductoService::class)->reactivar(
        $usuario,
        $producto->id_producto
    ))->toThrow(ValidationException::class);
});

test('no se puede eliminar un producto que tiene solicitudes asociadas', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $producto = app(ProductoService::class)->crear(
        $usuario,
        datosProductoParaPrueba($categoria)
    );

    $producto->detallesPedido()->create([
        'id_pedido_vendedor' => 999999,
        'nombre_producto' => $producto->nombre,
        'cantidad' => 1,
        'precio_unitario_referencia' => $producto->precio,
    ]);
})->throws(\Illuminate\Database\QueryException::class);

test('un vendedor puede eliminar un producto sin referencias', function () {
    $usuario = crearVendedorParaPrueba();
    $categoria = crearCategoriaParaPrueba();

    $producto = app(ProductoService::class)->crear(
        $usuario,
        datosProductoParaPrueba($categoria)
    );

    app(ProductoService::class)->eliminar(
        $usuario,
        $producto->id_producto
    );

    expect(Producto::whereKey($producto->id_producto)->exists())
        ->toBeFalse();
});
