<?php

use App\Models\Usuario;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Laravel\Fortify\Features;

beforeEach(function () {
    $this->skipUnlessFortifyHas(Features::resetPasswords());
});

test('reset password link screen can be rendered', function () {
    $response = $this->get(route('password.request'));

    $response->assertOk();
});

test('reset password link can be requested', function () {
    Notification::fake();

    $usuario = Usuario::factory()->create();

    $this->post(route('password.request'), [
        'correo' => $usuario->correo,
    ]);

    Notification::assertSentTo($usuario, ResetPassword::class);
});

test('reset password screen can be rendered', function () {
    Notification::fake();

    $usuario = Usuario::factory()->create();

    $this->post(route('password.request'), [
        'correo' => $usuario->correo,
    ]);

    Notification::assertSentTo(
        $usuario,
        ResetPassword::class,
        function ($notification) use ($usuario) {
            $response = $this->get(route('password.reset', [
                'token' => $notification->token,
                'email' => $usuario->correo,
            ]));

            $response->assertOk();

            return true;
        }
    );
});

test('password can be reset with valid token', function () {
    Notification::fake();

    $usuario = Usuario::factory()->create();

    $this->post(route('password.request'), [
        'correo' => $usuario->correo,
    ]);

    Notification::assertSentTo(
        $usuario,
        ResetPassword::class,
        function ($notification) use ($usuario) {
            $response = $this->post(route('password.update'), [
                'token' => $notification->token,
                'correo' => $usuario->correo,
                'password' => 'NuevaPassword123!',
                'password_confirmation' => 'NuevaPassword123!',
            ]);

            $response
                ->assertSessionHasNoErrors()
                ->assertRedirect(route('login', absolute: false));

            return true;
        }
    );

    expect(password_verify(
        'NuevaPassword123!',
        $usuario->fresh()->contrasena
    ))->toBeTrue();
});