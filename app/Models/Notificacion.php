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
        'id_detalle_pedido',
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
     */
    public function pedidoVendedor(): BelongsTo
    {
        return $this->belongsTo(
            PedidoVendedor::class,
            'id_pedido_vendedor',
            'id_pedido_vendedor'
        );
    }

    /**
     * Producto específico al que hace referencia la notificación.
     */
    public function detallePedido(): BelongsTo
    {
        return $this->belongsTo(
            DetallePedido::class,
            'id_detalle_pedido',
            'id_detalle_pedido'
        );
    }
}
