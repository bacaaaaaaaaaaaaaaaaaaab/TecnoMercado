<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Usuario;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param array<string, string> $input
     */
    public function create(array $input): Usuario
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'contrasena' => $this->passwordRules(),
        ])->validate();

        return Usuario::create([
            'nombre' => $input['nombre'],
            'apellidos' => $input['apellidos'],
            'correo' => $input['correo'],
            'contrasena' => $input['contrasena'],
            'activo' => true,
        ]);
    }
}
