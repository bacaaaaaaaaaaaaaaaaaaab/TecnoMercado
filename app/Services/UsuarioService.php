<?php

namespace App\Services;

use App\Models\Rol;
use App\Models\Usuario;
use App\Models\Vendedor;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class UsuarioService
{
    /**
     * Asignar un rol permitido sin eliminar los existentes.
     * El rol vendedor solamente se asigna mediante convertirEnVendedor().
     */
    public function asignarRol(Usuario $usuario, string $nombreRol): void
    {
        if ($nombreRol === 'administrador' || $nombreRol === 'vendedor') {
            throw new AuthorizationException(
                'Este rol requiere una operación de autorización específica.'
            );
        }

        if ($nombreRol !== 'comprador') {
            throw new \InvalidArgumentException('El rol indicado no es válido.');
        }

        $rol = $this->obtenerRol($nombreRol);

        $usuario->roles()->syncWithoutDetaching([$rol->id_rol]);
    }

    /**
     * Convertir una cuenta activa en vendedor.
     * Conserva el rol comprador y crea un perfil comercial activo.
     */
    public function convertirEnVendedor(
        Usuario $usuario,
        string $nombreComercial,
        ?string $descripcion = null,
        ?string $telefonoContacto = null
    ): Vendedor {
        if (! $this->estaActivo($usuario)) {
            throw new AuthorizationException(
                'No puedes convertir una cuenta inactiva en vendedor.'
            );
        }

        if (trim($nombreComercial) === '') {
            throw new \InvalidArgumentException(
                'El nombre comercial es obligatorio.'
            );
        }

        return DB::transaction(function () use (
            $usuario,
            $nombreComercial,
            $descripcion,
            $telefonoContacto
        ) {
            if ($usuario->vendedor()->exists()) {
                throw new AuthorizationException(
                    'Ya existe un perfil comercial para esta cuenta. '
                    . 'Utiliza la opción de reactivación si está inactivo.'
                );
            }

            $vendedor = $usuario->vendedor()->create([
                'nombre_comercial' => $nombreComercial,
                'descripcion' => $descripcion,
                'telefono_contacto' => $telefonoContacto,
                'estado' => 'activo',
            ]);

            $rolComprador = $this->obtenerRol('comprador');
            $rolVendedor = $this->obtenerRol('vendedor');

            $usuario->roles()->syncWithoutDetaching([
                $rolComprador->id_rol,
                $rolVendedor->id_rol,
            ]);
            return $vendedor;
        });
    }

    /**
     * Desactivar el perfil comercial sin eliminar información.
     * El usuario conserva sus roles y puede seguir comprando.
     */
    public function desactivarPerfilVendedor(Usuario $usuario): Vendedor
    {
        return DB::transaction(function () use ($usuario) {
            $vendedor = $usuario->vendedor()->first();

            if (! $vendedor) {
                throw (new ModelNotFoundException())
                    ->setModel(Vendedor::class);
            }

            $vendedor->estado = 'inactivo';
            $vendedor->save();

            return $vendedor;
        });
    }

    /**
     * Reactivar un perfil comercial existente.
     */
    public function reactivarPerfilVendedor(Usuario $usuario): Vendedor
    {
        if (! $this->estaActivo($usuario)) {
            throw new AuthorizationException(
                'No puedes reactivar el perfil de una cuenta inactiva.'
            );
        }

        return DB::transaction(function () use ($usuario) {
            $vendedor = $usuario->vendedor()->first();

            if (! $vendedor) {
                throw (new ModelNotFoundException())
                    ->setModel(Vendedor::class);
            }

            $rolComprador = $this->obtenerRol('comprador');
            $rolVendedor = $this->obtenerRol('vendedor');

            $usuario->roles()->syncWithoutDetaching([
                $rolComprador->id_rol,
                $rolVendedor->id_rol,
            ]);

            $vendedor->estado = 'activo';
            $vendedor->save();

            return $vendedor;
        });
    }

    /**
     * Comprobar si el usuario tiene un rol determinado.
     */
    public function tieneRol(Usuario $usuario, string $nombreRol): bool
    {
        return $usuario->roles()
            ->where('roles.nombre', $nombreRol)
            ->exists();
    }

    /**
     * Comprobar si la cuenta está activa.
     */
    public function estaActivo(Usuario $usuario): bool
    {
        return (bool) $usuario->activo;
    }

    /**
     * Obtener un rol existente por su nombre.
     */
    private function obtenerRol(string $nombreRol): Rol
    {
        $rol = Rol::query()
            ->where('nombre', $nombreRol)
            ->first();

        if (! $rol) {
            throw (new ModelNotFoundException())
                ->setModel(Rol::class, [$nombreRol]);
        }

        return $rol;
    }
}
