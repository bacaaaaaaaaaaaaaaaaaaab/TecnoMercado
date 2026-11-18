<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Laravel\Fortify\Features;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::registration());
});

test('registration screen can be rendered', function () {
    $response = $this->get(route('register'));

    $response->assertOk();
});

test('new users can register', function () {
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
});
