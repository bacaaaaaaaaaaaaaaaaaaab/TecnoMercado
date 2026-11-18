<?php

use App\Models\Usuario;
use Laravel\Fortify\Features;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register with the buyer role and an active cart', function () {
    $correo = fake()->unique()->safeEmail();

    $response = $this->post(route('register.store'), [
        'nombre' => 'John',
        'apellidos' => 'Doe',
        'correo' => $correo,
        'contrasena' => 'password',
        'contrasena_confirmation' => 'password',
    ]);

    $response->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard', absolute: false));

    $this->assertAuthenticated();

    $usuario = Usuario::where('correo', $correo)->firstOrFail();

    expect($usuario->activo)->toBeTrue()
        ->and($usuario->roles()->pluck('nombre')->all())
        ->toBe(['comprador']);

    $this->assertDatabaseHas('carritos', [
        'id_usuario' => $usuario->id_usuario,
        'estado' => 'activo',
    ]);
});



test('public registration cannot assign the administrator role', function () {
    $response = $this->post(route('register.store'), [
        'nombre' => 'John',
        'apellidos' => 'Doe',
        'correo' => 'admin-attempt@example.com',
        'contrasena' => 'password',
        'contrasena_confirmation' => 'password',
        'rol' => 'administrador',
    ]);

    $response->assertSessionHasNoErrors();

    $usuario = Usuario::where('correo', 'admin-attempt@example.com')->firstOrFail();

    expect($usuario->roles()->pluck('nombre')->all())
        ->toBe(['comprador']);

    expect($usuario->roles()->where('roles.nombre', 'administrador')->exists())
        ->toBeFalse();
});
