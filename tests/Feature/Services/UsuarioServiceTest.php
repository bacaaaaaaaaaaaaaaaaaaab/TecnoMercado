
<?php

use App\Models\Rol;
use App\Models\Usuario;
use App\Services\UsuarioService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Rol::firstOrCreate(
        ['nombre' => 'comprador'],
        ['descripcion' => 'Usuario que puede realizar compras.']
    );

    Rol::firstOrCreate(
        ['nombre' => 'vendedor'],
        ['descripcion' => 'Usuario que puede vender productos.']
    );
});

test('un comprador puede convertirse en vendedor conservando ambos roles', function () {
    $usuario = Usuario::factory()->create();

    $vendedor = app(UsuarioService::class)->convertirEnVendedor(
        $usuario,
        'Tienda de prueba'
    );

    expect($vendedor->estado)->toBe('activo')
        ->and($usuario->fresh()->roles->pluck('nombre')->all())
        ->toContain('comprador', 'vendedor');
});

test('un vendedor puede desactivar su perfil sin perder sus roles', function () {
    $usuario = Usuario::factory()->create();

    $servicio = app(UsuarioService::class);

    $servicio->convertirEnVendedor($usuario, 'Tienda de prueba');
    $vendedor = $servicio->desactivarPerfilVendedor($usuario);

    expect($vendedor->estado)->toBe('inactivo')
        ->and($usuario->fresh()->roles->pluck('nombre')->all())
        ->toContain('comprador', 'vendedor');
});

test('un vendedor puede reactivar su perfil comercial', function () {
    $usuario = Usuario::factory()->create();

    $servicio = app(UsuarioService::class);

    $servicio->convertirEnVendedor($usuario, 'Tienda de prueba');
    $servicio->desactivarPerfilVendedor($usuario);

    $vendedor = $servicio->reactivarPerfilVendedor($usuario);

    expect($vendedor->estado)->toBe('activo')
        ->and($usuario->fresh()->roles->pluck('nombre')->all())
        ->toContain('comprador', 'vendedor');
});

test('una cuenta inactiva no puede convertirse en vendedor', function () {
    $usuario = Usuario::factory()->create([
        'activo' => false,
    ]);

    expect(fn () => app(UsuarioService::class)
        ->convertirEnVendedor($usuario, 'Tienda de prueba'))
        ->toThrow(AuthorizationException::class);
});

test('una cuenta inactiva no puede reactivar su perfil comercial', function () {
    $usuario = Usuario::factory()->create();

    $servicio = app(UsuarioService::class);

    $servicio->convertirEnVendedor($usuario, 'Tienda de prueba');
    $servicio->desactivarPerfilVendedor($usuario);

    $usuario->activo = false;
    $usuario->save();

    expect(fn () => $servicio->reactivarPerfilVendedor($usuario))
        ->toThrow(AuthorizationException::class);
});
