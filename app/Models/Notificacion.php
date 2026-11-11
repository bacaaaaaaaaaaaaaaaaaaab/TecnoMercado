<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $primaryKey = 'id_notificacion';

    public const UPDATED_AT = null;

    protected $fillable = [
        'id_usuario',
        'id_pedido_vendedor',
        'tipo',
        'mensaje',
        'leida',
        'fecha_lectura',
    ];

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'fecha_lectura' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Usuario que recibe la notificación.
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
     * Solicitud de vendedor relacionada con la notificación.
     *
     * Puede ser null si la notificación no está asociada
     * a una solicitud o si esta fue eliminada.
     */
    public function pedidoVendedor(): BelongsTo
    {
        return $this->belongsTo(
            PedidoVendedor::class,
            'id_pedido_vendedor',
            'id_pedido_vendedor'
        );
    }
}
