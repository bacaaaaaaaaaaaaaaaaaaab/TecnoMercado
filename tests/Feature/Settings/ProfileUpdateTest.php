
<?php

use App\Models\Usuario;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($usuario = Usuario::factory()->create());

    $this->get(route('profile.edit'))->assertOk();
});

test('profile information can be updated', function () {
    $usuario = Usuario::factory()->create();

    $this->actingAs($usuario);

    $response = Livewire::test('pages::settings.profile')
        ->set('nombre', 'Test')
        ->set('apellidos', 'User')
        ->set('correo', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $usuario->refresh();

    expect($usuario->nombre)->toEqual('Test');
    expect($usuario->apellidos)->toEqual('User');
    expect($usuario->correo)->toEqual('test@example.com');
});

test('email address can remain unchanged when updating profile', function () {
    $usuario = Usuario::factory()->create();

    $correoOriginal = $usuario->correo;

    $this->actingAs($usuario);

    $response = Livewire::test('pages::settings.profile')
        ->set('nombre', 'Nuevo')
        ->set('apellidos', 'Nombre')
        ->set('correo', $correoOriginal)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($usuario->refresh()->correo)->toEqual($correoOriginal);
});

test('user can delete their account', function () {
    $usuario = Usuario::factory()->create();

    $this->actingAs($usuario);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($usuario->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $usuario = Usuario::factory()->create();

    $this->actingAs($usuario);

    $response = Livewire::test('pages::settings.delete-user-modal')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($usuario->fresh())->not->toBeNull();
});
