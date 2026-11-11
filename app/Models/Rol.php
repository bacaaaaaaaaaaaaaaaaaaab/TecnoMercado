<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Rol extends Model
{
    protected $table = 'roles';

    protected $primaryKey = 'id_rol';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    /*** Usuarios que tienen asignado este rol.*/
    public function usuarios(): BelongsToMany
    {
        return $this->belongsToMany(
            Usuario::class,
            'usuario_roles',
            'id_rol',
            'id_usuario',
            'id_rol',
            'id_usuario'
        )->using(UsuarioRol::class);
    }
}
