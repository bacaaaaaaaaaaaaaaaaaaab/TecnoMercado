<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistorialEstadoPedido extends Model
{
    protected $table = 'historial_estados_pedido';

    protected $primaryKey = 'id_historial';

    public const UPDATED_AT = null;

    protected $fillable = [
        'id_pedido_vendedor',
        'estado',
        'comentario',
        'fecha_cambio',
        'id_usuario_responsable',
    ];

    protected function casts(): array
    {
        return [
            'fecha_cambio' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Solicitud del vendedor cuyo estado se modificó.
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
     * Usuario responsable del cambio de estado.
     *
     * Puede ser null si el usuario fue eliminado.
     */
    public function usuarioResponsable(): BelongsTo
    {
        return $this->belongsTo(
            Usuario::class,
            'id_usuario_responsable',
            'id_usuario'
        );
    }
}
