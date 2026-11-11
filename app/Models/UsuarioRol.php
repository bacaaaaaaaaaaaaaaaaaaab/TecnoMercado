<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class UsuarioRol extends Pivot
{
    protected $table = 'usuario_roles';

    protected $fillable = [
        'id_usuario',
        'id_rol',
    ];

    public $timestamps = false;
}
