<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;
    /*** Tabla asociada al modelo*/
    protected $table = 'usuarios';
    /*** Clave primaria personalizada.*/
    protected $primaryKey = 'id_usuario';
    /*** Campos que se pueden asignar masivamente.*/
    protected $fillable = [
        'nombre',
        'apellidos',
        'correo',
        'contrasena',
        'activo',
    ];
    /*** Campos que no deben exponerse.*/
    protected $hidden = [
        'contrasena',
    ];
    /*** Conversiones de atributos.*/
    protected function casts(): array
    {
        return [
            'correo_verificado_en' => 'datetime',
            'contrasena' => 'hashed',
            'activo' => 'boolean',
        ];
    }
    /*** Nombre de la columna que almacena la contraseña.*/
    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }
}
