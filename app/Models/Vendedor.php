<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vendedor extends Model
{
    protected $table = 'vendedores';

    protected $primaryKey = 'id_vendedor';

    protected $fillable = [
        'id_usuario',
        'nombre_comercial',
        'descripcion',
        'telefono_contacto',
        'estado',
    ];

    /**
     * Usuario propietario del perfil de vendedor.
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario',
            'id_usuario'
        );
    }

    /**
     * Productos publicados por el vendedor.
     */
    public function productos(): HasMany
    {
        return $this->hasMany(
            Producto::class,
            'id_vendedor',
            'id_vendedor'
        );
    }

    /**
     * Solicitudes de compra recibidas por el vendedor.
     */
    public function pedidosVendedor(): HasMany
    {
        return $this->hasMany(
            PedidoVendedor::class,
            'id_vendedor',
            'id_vendedor'
        );
    }

}
