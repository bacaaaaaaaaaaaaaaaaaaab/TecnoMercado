<?php

namespace App\Services;

use App\Models\Usuario;
use App\Models\Vendedor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class VendedorService
{
    /**
     * Obtener el perfil comercial de un usuario.
     */
    public function obtenerPerfil(Usuario $usuario): Vendedor
    {
        $vendedor = $usuario->vendedor()->first();

        if (! $vendedor) {
            throw (new ModelNotFoundException())
                ->setModel(Vendedor::class);
        }

        return $vendedor;
    }

    /**
     * Actualizar los datos comerciales de un vendedor.
     * No permite modificar propietario, identificador ni estado.
     */
    public function actualizarPerfil(
        Usuario $usuario,
        array $datos
    ): Vendedor {
        $this->validarCuentaActiva($usuario);

        $datosValidados = Validator::make($datos, [
            'nombre_comercial' => [
                'required',
                'string',
                'max:150',
            ],
            'descripcion' => [
                'nullable',
                'string',
            ],
            'telefono_contacto' => [
                'nullable',
                'string',
                'max:25',
            ],
        ])->validate();

        return DB::transaction(function () use (
            $usuario,
            $datosValidados
        ) {
            $vendedor = $this->obtenerPerfil($usuario);

            if ($vendedor->estado !== 'activo') {
                throw new AuthorizationException(
                    'No puedes modificar un perfil comercial inactivo.'
                );
            }

            $vendedor->update($datosValidados);

            return $vendedor->fresh();
        });
    }

    /**
     * Desactivar un perfil comercial sin eliminar información.
     */
    public function desactivarPerfil(Usuario $usuario): Vendedor
    {
        return DB::transaction(function () use ($usuario) {
            $vendedor = $this->obtenerPerfil($usuario);

            $vendedor->estado = 'inactivo';
            $vendedor->save();

            return $vendedor;
        });
    }

    /**
     * Reactivar un perfil comercial existente.
     */
    public function reactivarPerfil(Usuario $usuario): Vendedor
    {
        $this->validarCuentaActiva($usuario);

        return DB::transaction(function () use ($usuario) {
            $vendedor = $this->obtenerPerfil($usuario);

            $vendedor->estado = 'activo';
            $vendedor->save();

            return $vendedor;
        });
    }

    /**
     * Comprobar si un perfil pertenece a un usuario.
     */
    public function esPropietario(
        Usuario $usuario,
        Vendedor $vendedor
    ): bool {
        return (int) $vendedor->id_usuario ===
            (int) $usuario->id_usuario;
    }

    /**
     * Impedir operaciones comerciales con cuentas inactivas.
     */
    private function validarCuentaActiva(Usuario $usuario): void
    {
        if (! $usuario->activo) {
            throw new AuthorizationException(
                'La cuenta debe estar activa para realizar esta operación.'
            );
        }
    }
}
