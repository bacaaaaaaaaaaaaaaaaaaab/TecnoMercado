<?php

namespace App\Concerns;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

trait ProfileValidationRules
{
    protected function profileRules(?int $userId = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:150'],
            'correo' => $this->correoRules($userId),
        ];
    }

    protected function correoRules(?int $userId = null): array
    {
        return [
            'required',
            'string',
            'email',
            'max:255',
            $userId === null
                ? Rule::unique('usuarios', 'correo')
                : Rule::unique('usuarios', 'correo')->ignore($userId, 'id_usuario'),
        ];
    }
}
