<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Carrito;
use App\Models\Usuario;
use App\Services\UsuarioService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(
        private UsuarioService $usuarioService
    ) {}

    /**
     * Validar y registrar un nuevo usuario.
     *
     * @param array<string, string> $input
     */
    public function create(array $input): Usuario
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'contrasena' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $usuario = Usuario::create([
                'nombre' => $input['nombre'],
                'apellidos' => $input['apellidos'],
                'correo' => $input['correo'],
                'contrasena' => $input['contrasena'],
                'activo' => true,
            ]);

            $this->usuarioService->asignarRol(
                $usuario,
                'comprador'
            );

            Carrito::create([
                'id_usuario' => $usuario->id_usuario,
                'estado' => 'activo',
            ]);

            return $usuario;
        });
    }
}
