<?php

use App\Models\Usuario;
use App\Services\UsuarioService;
use App\Services\VendedorService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('un vendedor puede consultar su perfil comercial', function () {
    $usuario = Usuario::factory()->create();

    app(UsuarioService::class)->convertirEnVendedor(
        $usuario,
        'Tienda de prueba'
    );

    $perfil = app(VendedorService::class)->obtenerPerfil($usuario);

    expect($perfil->nombre_comercial)->toBe('Tienda de prueba')
        ->and($perfil->id_usuario)->toBe($usuario->id_usuario);
});

test('un vendedor puede actualizar sus datos comerciales', function () {
    $usuario = Usuario::factory()->create();

    app(UsuarioService::class)->convertirEnVendedor(
        $usuario,
        'Tienda original'
    );

    $perfil = app(VendedorService::class)->actualizarPerfil(
        $usuario,
        [
            'nombre_comercial' => 'Nueva tienda',
            'descripcion' => 'Descripción de prueba',
            'telefono_contacto' => '9991234567',
        ]
    );

    expect($perfil->nombre_comercial)->toBe('Nueva tienda')
        ->and($perfil->descripcion)->toBe('Descripción de prueba')
        ->and($perfil->telefono_contacto)->toBe('9991234567');
});

test('no se permite actualizar un perfil comercial inactivo', function () {
    $usuario = Usuario::factory()->create();

    $usuarioService = app(UsuarioService::class);
    $vendedorService = app(VendedorService::class);

    $usuarioService->convertirEnVendedor($usuario, 'Tienda de prueba');
    $vendedorService->desactivarPerfil($usuario);

    expect(fn () => $vendedorService->actualizarPerfil(
        $usuario,
        ['nombre_comercial' => 'Cambio no permitido']
    ))->toThrow(AuthorizationException::class);
});

test('un vendedor puede desactivar su perfil comercial', function () {
    $usuario = Usuario::factory()->create();

    app(UsuarioService::class)->convertirEnVendedor(
        $usuario,
        'Tienda de prueba'
    );

    $perfil = app(VendedorService::class)->desactivarPerfil($usuario);

    expect($perfil->estado)->toBe('inactivo')
        ->and($usuario->fresh()->roles->pluck('nombre')->all())
        ->toContain('comprador', 'vendedor');
});

test('una cuenta activa puede reactivar su perfil comercial', function () {
    $usuario = Usuario::factory()->create();

    $usuarioService = app(UsuarioService::class);
    $vendedorService = app(VendedorService::class);

    $usuarioService->convertirEnVendedor($usuario, 'Tienda de prueba');
    $vendedorService->desactivarPerfil($usuario);

    $perfil = $vendedorService->reactivarPerfil($usuario);

    expect($perfil->estado)->toBe('activo');
});

test('una cuenta inactiva no puede reactivar su perfil comercial', function () {
    $usuario = Usuario::factory()->create();

    $usuarioService = app(UsuarioService::class);
    $vendedorService = app(VendedorService::class);

    $usuarioService->convertirEnVendedor($usuario, 'Tienda de prueba');
    $vendedorService->desactivarPerfil($usuario);

    $usuario->activo = false;
    $usuario->save();

    expect(fn () => $vendedorService->reactivarPerfil($usuario))
        ->toThrow(AuthorizationException::class);
});

test('un usuario no puede consultar un perfil comercial inexistente', function () {
    $usuario = Usuario::factory()->create();

    expect(fn () => app(VendedorService::class)->obtenerPerfil($usuario))
        ->toThrow(ModelNotFoundException::class);
});


test('un usuario no puede consultar el perfil de otro vendedor', function () {
    $propietario = Usuario::factory()->create();
    $otroUsuario = Usuario::factory()->create();

    app(UsuarioService::class)->convertirEnVendedor(
        $propietario,
        'Tienda privada'
    );

    expect(fn () => app(VendedorService::class)
        ->obtenerPerfil($otroUsuario))
        ->toThrow(ModelNotFoundException::class);
});

test('la actualización no permite modificar campos protegidos', function () {
    $usuario = Usuario::factory()->create();

    $perfilOriginal = app(UsuarioService::class)->convertirEnVendedor(
        $usuario,
        'Tienda original'
    );

    $otroUsuario = Usuario::factory()->create();

    $perfil = app(VendedorService::class)->actualizarPerfil(
        $usuario,
        [
            'nombre_comercial' => 'Tienda modificada',
            'id_usuario' => $otroUsuario->id_usuario,
            'estado' => 'inactivo',
        ]
    );

    expect($perfil->id_usuario)->toBe($usuario->id_usuario)
        ->and($perfil->estado)->toBe('activo')
        ->and($perfil->id_vendedor)->toBe($perfilOriginal->id_vendedor);
});
