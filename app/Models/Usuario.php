<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Usuario extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Tabla asociada al modelo.
     */
    protected $table = 'usuarios';

    /**
     * Clave primaria personalizada.
     */
    protected $primaryKey = 'id_usuario';

    /**
     * Campos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'nombre',
        'apellidos',
        'correo',
        'contrasena',
        'activo',
    ];

    /**
     * Campos que no deben exponerse.
     */
    protected $hidden = [
        'contrasena',
    ];

    /**
     * Conversiones de atributos.
     */
    protected function casts(): array
    {
        return [
            'correo_verificado_en' => 'datetime',
            'contrasena' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    /**
     * Nombre completo del usuario.
     */
    public function getNameAttribute(): string
    {
        return trim($this->nombre . ' ' . $this->apellidos);
    }

    /**
     * Correo electrónico del usuario.
     */
    public function getEmailAttribute(): string
    {
        return $this->correo;
    }

    /**
     * Obtener las iniciales del usuario.
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1) . Str::substr($initials, -1)
            : $initials;
    }

    /**
     * Nombre de la columna que almacena la contraseña.
     */
    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    /**
     * Nombre del atributo utilizado para recuperar contraseñas.
     */
    public function getEmailForPasswordReset(): string
    {
        return $this->correo;
    }

    /**
     * Dirección de correo utilizada para enviar notificaciones.
     */
    public function routeNotificationForMail($notification = null): string
    {
        return $this->correo;
    }
}
