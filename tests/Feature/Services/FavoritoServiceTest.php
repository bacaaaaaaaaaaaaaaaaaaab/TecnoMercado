<?php

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Usuario;
use App\Services\FavoritoService;
use App\Services\ProductoService;
use App\Services\UsuarioService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

/*
|--------------------------------------------------------------------------
| Funciones auxiliares
|--------------------------------------------------------------------------
*/

function crearUsuarioParaFavoritosTest(): Usuario
{
    return Usuario::factory()->create();
}

function crearVendedorParaFavoritosTest(): Usuario
{
    $usuario = Usuario::factory()->create();

    app(UsuarioService::class)->convertirEnVendedor(
        $usuario,
        'Tienda para favoritos'
    );

    return $usuario;
}

function crearProductoParaFavoritosTest(
    Usuario $vendedor,
    bool $activo = true
): Producto {
    $categoria = Categoria::create([
        'nombre' => fake()->unique()->word(),
        'descripcion' => 'Categoria para pruebas de favoritos',
        'activa' => true,
    ]);

    $producto = app(ProductoService::class)->crear($vendedor, [
        'id_categoria' => $categoria->id_categoria,
        'nombre' => 'Producto para favoritos',
        'descripcion' => 'Producto utilizado en pruebas',
        'precio' => 150.00,
        'stock_fisico' => 10,
    ]);

    if (! $activo) {
        $producto->update(['activo' => false]);
    }

    return $producto;
}

/*
|--------------------------------------------------------------------------
| Agregar favoritos
|--------------------------------------------------------------------------
*/

test('un usuario puede agregar un producto activo a favoritos', function () {
    $usuario = crearUsuarioParaFavoritosTest();
    $vendedor = crearVendedorParaFavoritosTest();

    $producto = crearProductoParaFavoritosTest($vendedor);

    $resultado = app(FavoritoService::class)->agregar(
        $usuario,
        $producto->id_producto
    );

    expect($resultado)->toBeInstanceOf(Producto::class)
        ->and($resultado->id_producto)->toBe($producto->id_producto)
        ->and($usuario->productosFavoritos()->count())->toBe(1);
});

test('agregar dos veces el mismo producto no genera duplicados', function () {
    $usuario = crearUsuarioParaFavoritosTest();
    $vendedor = crearVendedorParaFavoritosTest();

    $producto = crearProductoParaFavoritosTest($vendedor);

    $servicio = app(FavoritoService::class);

    $servicio->agregar($usuario, $producto->id_producto);
    $servicio->agregar($usuario, $producto->id_producto);

    expect($usuario->productosFavoritos()->count())->toBe(1);
});

test('no se puede agregar un producto inactivo a favoritos', function () {
    $usuario = crearUsuarioParaFavoritosTest();
    $vendedor = crearVendedorParaFavoritosTest();

    $producto = crearProductoParaFavoritosTest($vendedor, false);

    expect(fn () => app(FavoritoService::class)->agregar(
        $usuario,
        $producto->id_producto
    ))->toThrow(ModelNotFoundException::class);

    expect($usuario->productosFavoritos()->count())->toBe(0);
});

test('no se puede agregar un producto inexistente a favoritos', function () {
    $usuario = crearUsuarioParaFavoritosTest();

    expect(fn () => app(FavoritoService::class)->agregar(
        $usuario,
        999999
    ))->toThrow(ModelNotFoundException::class);
});

/*
|--------------------------------------------------------------------------
| Quitar favoritos
|--------------------------------------------------------------------------
*/

test('un usuario puede quitar un producto de favoritos', function () {
    $usuario = crearUsuarioParaFavoritosTest();
    $vendedor = crearVendedorParaFavoritosTest();

    $producto = crearProductoParaFavoritosTest($vendedor);

    $servicio = app(FavoritoService::class);

    $servicio->agregar($usuario, $producto->id_producto);

    $resultado = $servicio->quitar(
        $usuario,
        $producto->id_producto
    );

    expect($resultado)->toBeTrue()
        ->and($usuario->productosFavoritos()->count())->toBe(0);
});

test('quitar un producto que no estaba en favoritos devuelve false', function () {
    $usuario = crearUsuarioParaFavoritosTest();
    $vendedor = crearVendedorParaFavoritosTest();

    $producto = crearProductoParaFavoritosTest($vendedor);

    $resultado = app(FavoritoService::class)->quitar(
        $usuario,
        $producto->id_producto
    );

    expect($resultado)->toBeFalse()
        ->and($usuario->productosFavoritos()->count())->toBe(0);
});

/*
|--------------------------------------------------------------------------
| Consultar favoritos
|--------------------------------------------------------------------------
*/

test('un usuario puede consultar sus productos favoritos', function () {
    $usuario = crearUsuarioParaFavoritosTest();
    $vendedor = crearVendedorParaFavoritosTest();

    $producto1 = crearProductoParaFavoritosTest($vendedor);
    $producto2 = crearProductoParaFavoritosTest($vendedor);

    $servicio = app(FavoritoService::class);

    $servicio->agregar($usuario, $producto1->id_producto);
    $servicio->agregar($usuario, $producto2->id_producto);

    $favoritos = $servicio->listar($usuario);

    expect($favoritos)->toHaveCount(2)
        ->and($favoritos->pluck('id_producto')->all())
        ->toContain($producto1->id_producto, $producto2->id_producto);
});

test('la lista de favoritos no incluye productos desactivados', function () {
    $usuario = crearUsuarioParaFavoritosTest();
    $vendedor = crearVendedorParaFavoritosTest();

    $productoActivo = crearProductoParaFavoritosTest($vendedor);
    $productoDesactivado = crearProductoParaFavoritosTest($vendedor);

    $servicio = app(FavoritoService::class);

    $servicio->agregar($usuario, $productoActivo->id_producto);
    $servicio->agregar($usuario, $productoDesactivado->id_producto);

    $productoDesactivado->update(['activo' => false]);

    $favoritos = $servicio->listar($usuario);

    expect($favoritos)->toHaveCount(1)
        ->and($favoritos->first()->id_producto)
        ->toBe($productoActivo->id_producto);
});

test('los favoritos de un usuario no aparecen en la lista de otro', function () {
    $usuario1 = crearUsuarioParaFavoritosTest();
    $usuario2 = crearUsuarioParaFavoritosTest();
    $vendedor = crearVendedorParaFavoritosTest();

    $producto = crearProductoParaFavoritosTest($vendedor);

    $servicio = app(FavoritoService::class);

    $servicio->agregar($usuario1, $producto->id_producto);

    expect($servicio->listar($usuario1))->toHaveCount(1)
        ->and($servicio->listar($usuario2))->toHaveCount(0);
});

/*
|--------------------------------------------------------------------------
| Permisos de cuenta
|--------------------------------------------------------------------------
*/

test('una cuenta inactiva no puede agregar favoritos', function () {
    $usuario = crearUsuarioParaFavoritosTest();
    $vendedor = crearVendedorParaFavoritosTest();

    $producto = crearProductoParaFavoritosTest($vendedor);

    $usuario->activo = false;
    $usuario->save();

    expect(fn () => app(FavoritoService::class)->agregar(
        $usuario,
        $producto->id_producto
    ))->toThrow(AuthorizationException::class);
});

test('una cuenta inactiva no puede quitar favoritos', function () {
    $usuario = crearUsuarioParaFavoritosTest();
    $vendedor = crearVendedorParaFavoritosTest();

    $producto = crearProductoParaFavoritosTest($vendedor);

    $servicio = app(FavoritoService::class);

    $servicio->agregar($usuario, $producto->id_producto);

    $usuario->activo = false;
    $usuario->save();

    expect(fn () => $servicio->quitar(
        $usuario,
        $producto->id_producto
    ))->toThrow(AuthorizationException::class);
});

test('una cuenta inactiva no puede consultar favoritos', function () {
    $usuario = crearUsuarioParaFavoritosTest();

    $usuario->activo = false;
    $usuario->save();

    expect(fn () => app(FavoritoService::class)->listar(
        $usuario
    ))->toThrow(AuthorizationException::class);
});
