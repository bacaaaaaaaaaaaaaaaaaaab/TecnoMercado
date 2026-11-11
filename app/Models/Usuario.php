<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;


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

    /**
     * Roles que tiene asignados el usuario.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Rol::class,
            'usuario_roles',
            'id_usuario',
            'id_rol',
            'id_usuario',
            'id_rol'
        )->using(UsuarioRol::class);
    }

    /**
     * Perfil de vendedor asociado al usuario.
     */
    public function vendedor(): HasOne
    {
        return $this->hasOne(
            Vendedor::class,
            'id_usuario',
            'id_usuario'
        );
    }

    /**
     * Productos guardados en favoritos por el usuario.
     */
    public function productosFavoritos(): BelongsToMany
    {
        return $this->belongsToMany(
            Producto::class,
            'favoritos',
            'id_usuario',
            'id_producto',
            'id_usuario',
            'id_producto'
        )->using(Favorito::class)->withPivot('created_at');
    }

    /**
     * Carritos pertenecientes al usuario.
     */
    public function carritos(): HasMany
    {
        return $this->hasMany(
            Carrito::class,
            'id_usuario',
            'id_usuario'
        );
    }

    /**
     * Pedidos realizados por el usuario como comprador.
     */
    public function pedidos(): HasMany
    {
        return $this->hasMany(
            Pedido::class,
            'id_comprador',
            'id_usuario'
        );
    }

    /**
     * Registros de historial en los que el usuario fue responsable.
     */
    public function historialesEstadosResponsable(): HasMany
    {
        return $this->hasMany(
            HistorialEstadoPedido::class,
            'id_usuario_responsable',
            'id_usuario'
        );
    }

    /**
     * Notificaciones recibidas por el usuario.
     */
    public function notificaciones(): HasMany
    {
        return $this->hasMany(
            Notificacion::class,
            'id_usuario',
            'id_usuario'
        );
    }

}
